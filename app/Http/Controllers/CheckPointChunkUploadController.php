<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CheckPointChunkUploadController extends Controller
{
    private const CHUNK_BYTES = 1024 * 1024;

    public function chunk(Request $request)
    {
        $this->cleanupStaleUploads((int) $request->user()->id);
        $request->validate([
            'upload_id' => ['required', 'uuid'],
            'chunk' => ['required', 'integer', 'min:0'],
            'total' => ['required', 'integer', 'min:1', 'max:20'],
            'file' => ['required', 'file', 'max:2048'],
        ]);

        $id = $request->string('upload_id')->toString();
        $dir = "checkpoint-chunks/{$request->user()->id}/{$id}";
        $meta = Storage::disk('local')->get($dir . '/meta.json');
        if ($meta && json_decode($meta, true)['total'] !== $request->integer('total')) abort(422);
        Storage::disk('local')->put($dir . '/meta.json', json_encode(['total' => $request->integer('total'), 'name' => $request->file('file')->getClientOriginalName()]));
        $request->file('file')->storeAs($dir, $request->integer('chunk'), 'local');
        return ['upload_id' => $id, 'chunk' => $request->integer('chunk')];
    }

    public function complete(Request $request)
    {
        $this->cleanupStaleUploads((int) $request->user()->id);
        $request->validate(['upload_id' => ['required', 'uuid']]);
        $id = $request->string('upload_id')->toString();
        $dir = "checkpoint-chunks/{$request->user()->id}/{$id}";
        $meta = json_decode(Storage::disk('local')->get($dir . '/meta.json'), true);
        $path = storage_path('app/' . $dir . '/assembled');
        $out = fopen($path, 'wb');
        for ($i = 0; $i < $meta['total']; $i++) {
            $chunk = storage_path("app/{$dir}/{$i}");
            abort_unless(is_file($chunk), 422, 'Chunk belum lengkap.');
            stream_copy_to_stream(fopen($chunk, 'rb'), $out);
        }
        fclose($out);
        $token = Str::uuid()->toString();
        $completeDir = "checkpoint-complete/{$request->user()->id}";
        Storage::disk('local')->makeDirectory($completeDir);
        Storage::disk('local')->move($dir . '/assembled', $completeDir . "/{$token}");
        Storage::disk('local')->deleteDirectory($dir);
        Storage::disk('local')->put("{$completeDir}/{$token}.json", json_encode(['path' => storage_path("app/{$completeDir}/{$token}"), 'name' => $meta['name']]));
        return ['token' => $token];
    }

    private function cleanupStaleUploads(int $userId): void
    {
        $disk = Storage::disk('local');
        $cutoff = now()->subHours(6)->timestamp;

        foreach (["checkpoint-chunks/{$userId}", "checkpoint-complete/{$userId}"] as $root) {
            foreach ($disk->directories($root) as $directory) {
                $latest = collect($disk->allFiles($directory))
                    ->map(fn (string $file) => $disk->lastModified($file))
                    ->max();
                if ($latest !== null && $latest < $cutoff) {
                    $disk->deleteDirectory($directory);
                }
            }
        }
    }
}
