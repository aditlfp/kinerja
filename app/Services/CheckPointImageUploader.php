<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

/**
 * Stores checkpoint evidence photos, downscaling them the same way the
 * generic `UploadImage` helper does so a phone photo cannot blow up storage.
 *
 * Only photos that fit in the memory the request has left are decoded. A
 * modern phone can produce a 100+ megapixel photo (108 MP camera mode), and
 * GD needs `width * height * 4` bytes for one of those — around 430 MB, which
 * is the whole `memory_limit` on its own. Exhausting the limit is a fatal
 * error, not a catchable exception, so such a photo used to take the whole
 * create/edit form down with "Allowed memory size exhausted" instead of
 * saving. Those photos are now stored untouched.
 */
class CheckPointImageUploader
{
    private const MAX_WIDTH = 1920;
    private const MAX_HEIGHT = 1920;

    /**
     * Largest bitmap worth decoding: 32 MP needs ~128 MB, which is about as
     * much as a shared PHP request can spare. Ordinary phone photos (12–24 MP)
     * stay well under this and are still downscaled.
     */
    private const MAX_DECODE_PIXELS = 32_000_000;

    /** Memory to keep free for the framework and the rest of the request. */
    private const MEMORY_HEADROOM_BYTES = 128 * 1024 * 1024;

    /**
     * @return string the stored file name (relative to storage/app/public/images)
     */
    public function store(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = 'data' . md5(uniqid((string) mt_rand(), true)) . '.' . $extension;
        $source = (string) $file->getRealPath();
        $pixels = $this->pixels($source);

        if ($pixels === null || ! $this->canDecodePixels($pixels)) {
            $this->logSkippedDownscale($file, $pixels);
            $file->storeAs('images', $name, 'public');

            return $name;
        }

        $image = null;

        try {
            $image = Image::make($source);
            $image->resize(self::MAX_WIDTH, self::MAX_HEIGHT, function ($constraint): void {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $image->save(storage_path('app/public/images/' . $name));
        } catch (\Throwable $e) {
            // Fall back to the untouched upload if the image driver fails.
            $file->storeAs('images', $name, 'public');
        } finally {
            // Release the bitmap before the next upload: one request can carry
            // several photos, and a retained one is what pushes the following
            // decode over the limit.
            if ($image instanceof \Intervention\Image\Image) {
                $image->destroy();
            }
        }

        return $name;
    }

    /**
     * Whether a bitmap of `$pixels` pixels can be decoded with the memory this
     * request has left. Callers that cannot decode must not try: the failure
     * mode is a fatal error, not an exception.
     */
    public function canDecodePixels(int $pixels): bool
    {
        if ($pixels < 1 || $pixels > self::MAX_DECODE_PIXELS) {
            return false;
        }

        $limit = $this->memoryLimitBytes();

        if ($limit === null) {
            return true;
        }

        // GD wants 4 bytes per pixel, plus the copies resampling keeps around.
        $needed = (int) ($pixels * 4 * 1.3);

        return memory_get_usage() + $needed + self::MEMORY_HEADROOM_BYTES <= $limit;
    }

    /**
     * Pixel count read straight from the file header, so it costs nothing no
     * matter how large the photo is. Null when GD cannot read the file at all.
     */
    private function pixels(string $path): ?int
    {
        $info = @getimagesize($path);

        if (! is_array($info) || empty($info[0]) || empty($info[1])) {
            return null;
        }

        return (int) $info[0] * (int) $info[1];
    }

    /**
     * The configured `memory_limit` in bytes, or null when it is unlimited.
     */
    private function memoryLimitBytes(): ?int
    {
        $limit = trim((string) ini_get('memory_limit'));

        if ($limit === '' || $limit === '-1') {
            return null;
        }

        $bytes = (int) $limit;

        return match (strtolower(substr($limit, -1))) {
            'g' => $bytes * 1024 * 1024 * 1024,
            'm' => $bytes * 1024 * 1024,
            'k' => $bytes * 1024,
            default => $bytes,
        };
    }

    private function logSkippedDownscale(UploadedFile $file, ?int $pixels): void
    {
        Log::warning('Checkpoint photo stored without downscaling.', [
            'file' => $file->getClientOriginalName(),
            'bytes' => $file->getSize(),
            'pixels' => $pixels,
            'max_pixels' => self::MAX_DECODE_PIXELS,
            'memory_limit' => ini_get('memory_limit'),
        ]);
    }
}
