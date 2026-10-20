<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;

class ImageStorage
{
    public static function disk(): FilesystemAdapter
    {
        return Storage::disk();
    }

    public static function store(UploadedFile $file, string $directory, int $width): string
    {
        $path = $directory.'/'.$file->hashName();
        $image = (new ImageManager(Driver::class))->decode($file->getRealPath());
        $image->scaleDown(width: $width);

        if (! self::disk()->put($path, (string) $image->encode())) {
            throw new RuntimeException('Failed to store the image.');
        }

        return $path;
    }
}
