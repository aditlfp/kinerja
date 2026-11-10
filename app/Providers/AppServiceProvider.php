<?php

namespace App\Providers;

use App\Models\Izin;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.custom-cuy');

        $this->fallbackCacheToFileWhenRedisUnavailable();
        $this->preloadAuthUserRelations();
        $this->shareMitraShellData();

        $startTime = Cache::get('app_start_time');

        if (!$startTime) {
            Cache::put('app_start_time', now(), now()->addHours(24)); // Store the start time for 24 hours
            $startTime = now();
        }

        $uptime = now()->diffInSeconds($startTime);

        $hours = intdiv($uptime, 3600);
        $minutes = intdiv(($uptime % 3600), 60);
        $seconds = $uptime % 60;

        $formattedUptime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        view()->share('uptime', $formattedUptime);
    }

    /**
     * Data ringan untuk sidebar dan topbar mitra: nama workspace, jumlah personel,
     * dan pengajuan izin yang menunggu persetujuan.
     */
    private function shareMitraShellData(): void
    {
        view()->composer('components.mitra-layout', function ($view) {
            $user = Auth::user();

            if (!$user) {
                $view->with([
                    'mitraWorkspace' => null,
                    'mitraPersonelTotal' => null,
                    'mitraIzinMenunggu' => 0,
                ]);

                return;
            }

            $kerjasamaId = $user->kerjasama_id;
            $isMitra = ($user->jabatan?->code_jabatan ?? $user->divisi?->jabatan?->code_jabatan) === 'MITRA';

            if (!$isMitra || !$kerjasamaId) {
                $view->with([
                    'mitraWorkspace' => $user->kerjasama?->client?->name,
                    'mitraPersonelTotal' => null,
                    'mitraIzinMenunggu' => 0,
                ]);

                return;
            }

            $view->with([
                'mitraWorkspace' => $user->kerjasama?->client?->name,
                'mitraPersonelTotal' => User::query()
                    ->where('kerjasama_id', $kerjasamaId)
                    ->whereNotIn('nama_lengkap', User::PLACEHOLDER_ACCOUNT_NAMES)
                    ->count(),
                'mitraIzinMenunggu' => Izin::query()
                    ->where('kerjasama_id', $kerjasamaId)
                    ->where('approve_status', 'process')
                    ->count(),
            ]);
        });
    }

    private function preloadAuthUserRelations(): void
    {
        if (!Auth::check()) {
            return;
        }

        Auth::user()->loadMissing([
            'jabatan',
            'divisi.jabatan',
            'kerjasama.client',
        ]);
    }

    private function fallbackCacheToFileWhenRedisUnavailable(): void
    {
        if (Config::get('cache.default') !== 'redis') {
            return;
        }

        $connectionName = Config::get('cache.stores.redis.connection', 'cache');
        $connection = Config::get("database.redis.{$connectionName}", []);
        $statusFile = storage_path('framework/cache/redis-health.json');

        try {
            // Reuse the last health-check result for a short window to avoid
            // slow network probes on every request boot.
            $health = $this->readRedisHealth($statusFile);
            if ($health && $health['checked_at'] > (time() - 300)) {
                if ($health['is_up'] === false) {
                    $this->switchCacheDriverToFile();
                }
                return;
            }

            $isUp = $this->isRedisReachable($connection);
            $this->writeRedisHealth($statusFile, $isUp);

            if (!$isUp) {
                $this->switchCacheDriverToFile();
            }
        } catch (Throwable $e) {
            Config::set('cache.default', 'file');
            Cache::setDefaultDriver('file');

            Log::warning('Redis cache unavailable, fallback to file cache.', [
                'connection' => $connectionName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function switchCacheDriverToFile(): void
    {
        Config::set('cache.default', 'file');
        Cache::setDefaultDriver('file');
    }

    private function isRedisReachable(array $connection): bool
    {
        // Unix socket mode
        if (($connection['scheme'] ?? null) === 'unix' || !empty($connection['path'])) {
            $path = $connection['path'] ?? null;
            return is_string($path) && $path !== '' && file_exists($path);
        }

        // TCP mode
        $host = (string) ($connection['host'] ?? '');
        $port = (int) ($connection['port'] ?? 6379);
        if ($host === '' || $port <= 0) {
            return false;
        }

        $timeoutSeconds = 0.15;
        $errno = 0;
        $errstr = '';
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeoutSeconds);
        if (is_resource($socket)) {
            fclose($socket);
            return true;
        }

        return false;
    }

    private function readRedisHealth(string $statusFile): ?array
    {
        if (!is_file($statusFile)) {
            return null;
        }

        $raw = @file_get_contents($statusFile);
        if (!is_string($raw) || $raw === '') {
            return null;
        }

        $json = json_decode($raw, true);
        if (!is_array($json) || !array_key_exists('is_up', $json) || !array_key_exists('checked_at', $json)) {
            return null;
        }

        return [
            'is_up' => (bool) $json['is_up'],
            'checked_at' => (int) $json['checked_at'],
        ];
    }

    private function writeRedisHealth(string $statusFile, bool $isUp): void
    {
        $dir = dirname($statusFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        @file_put_contents($statusFile, json_encode([
            'is_up' => $isUp,
            'checked_at' => time(),
        ]));
    }
}
