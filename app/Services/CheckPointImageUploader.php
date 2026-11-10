<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
/**
 * Stores checkpoint evidence photos untouched.
 *
 * The request-level 20 MB limit controls payload size; this class preserves
 * the original resolution and quality.
 */
class CheckPointImageUploader
{
    /**
     * @return string the stored file name (relative to storage/app/public/images)
     */
    public function store(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = 'data' . md5(uniqid((string) mt_rand(), true)) . '.' . $extension;
        $file->storeAs('images', $name, 'public');
        return $name;
    }

    public function storeToken(string $token, int $userId): string
    {
        $metaPath = "checkpoint-complete/{$userId}/{$token}.json";
        abort_unless(Storage::disk('local')->exists($metaPath), 422, 'Foto upload tidak ditemukan.');
        $meta = json_decode(Storage::disk('local')->get($metaPath), true);
        $source = $meta['path'] ?? null;
        abort_unless(is_string($source) && is_file($source), 422, 'Foto upload tidak lengkap.');
        $extension = strtolower(pathinfo($meta['name'] ?? 'jpg', PATHINFO_EXTENSION) ?: 'jpg');
        $name = 'data' . md5(uniqid((string) mt_rand(), true)) . '.' . $extension;
        Storage::disk('public')->put('images/' . $name, fopen($source, 'rb'));
        Storage::disk('local')->delete($metaPath);
        @unlink($source);
        return $name;
    }
}
