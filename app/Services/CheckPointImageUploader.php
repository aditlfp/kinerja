<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

/**
 * Stores checkpoint evidence photos, downscaling them the same way the
 * generic `UploadImage` helper does so a phone photo cannot blow up storage.
 */
class CheckPointImageUploader
{
    private const MAX_WIDTH = 450;

    private const MAX_HEIGHT = 450;

    /**
     * @return string the stored file name (relative to storage/app/public/images)
     */
    public function store(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = 'data' . md5(uniqid((string) mt_rand(), true)) . '.' . $extension;

        try {
            $image = Image::make($file->getRealPath());
            $image->resize(self::MAX_WIDTH, self::MAX_HEIGHT, function ($constraint): void {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $image->save(storage_path('app/public/images/' . $name));
        } catch (\Throwable $e) {
            // Fall back to the untouched upload if the image driver fails.
            $file->storeAs('images', $name, 'public');
        }

        return $name;
    }
}
