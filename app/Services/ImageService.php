<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Yüklənmiş şəkillər storage/app/public/uploads altında saxlanılır.
 * Thumbnail-lar OpenCart kimi ölçüyə "contain" (ağ fonla) kəsilib
 * public/storage/cache/{w}x{h}/... altında keşlənir.
 */
class ImageService
{
    public const PLACEHOLDER = 'images/placeholder.png';

    public function store(UploadedFile $file, string $folder = 'misc'): string
    {
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image';
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $path = 'uploads/'.trim($folder, '/').'/'.date('Y/m').'/'.$name.'-'.Str::lower(Str::random(6)).'.'.$ext;

        Storage::disk('public')->putFileAs(dirname($path), $file, basename($path));

        return $path;
    }

    public function url(?string $path): string
    {
        if (! $path) {
            return asset(self::PLACEHOLDER);
        }
        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return asset('storage/'.$path);
    }

    public function thumb(?string $path, int $width, int $height, string $mode = 'contain'): string
    {
        if (! $path || Str::startsWith($path, ['http://', 'https://'])) {
            return $path ?: asset(self::PLACEHOLDER);
        }

        $source = Storage::disk('public')->path($path);
        if (! is_file($source)) {
            return asset(self::PLACEHOLDER);
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'svg' || $ext === 'gif') {
            return $this->url($path);
        }

        $relative = "cache/{$width}x{$height}".($mode === 'contain' ? '' : "-{$mode}").'/'.$path;
        $target = Storage::disk('public')->path($relative);

        if (! is_file($target) || filemtime($target) < filemtime($source)) {
            try {
                File::ensureDirectoryExists(dirname($target));
                $image = ImageManager::usingDriver(Driver::class)->decode($source);
                $image = $mode === 'cover'
                    ? $image->cover($width, $height)
                    : $image->contain($width, $height, $ext === 'png' || $ext === 'webp' ? null : 'ffffff');
                $image->save($target, quality: 90);
            } catch (Throwable $e) {
                report($e);

                return $this->url($path);
            }
        }

        return asset('storage/'.$relative);
    }

    public function delete(?string $path): void
    {
        if ($path && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
