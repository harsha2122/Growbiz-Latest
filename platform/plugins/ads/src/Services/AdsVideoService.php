<?php

namespace Botble\Ads\Services;

use Botble\Ads\Models\Ads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AdsVideoService
{
    protected const DISK = 'public';
    protected const VIDEO_PATH = 'ads-videos';
    protected const MAX_VIDEO_SIZE = 500 * 1024 * 1024; // 500 MB
    protected const ALLOWED_EXTENSIONS = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv'];

    public function storeVideo(UploadedFile $file, int $adId): array
    {
        if (!$this->validateVideo($file)) {
            return [
                'success' => false,
                'error' => 'Invalid video file. Only ' . implode(', ', self::ALLOWED_EXTENSIONS) . ' are allowed. Max size: 500MB.',
            ];
        }

        try {
            $filename = $this->generateFilename($file);

            $path = $file->storeAs(
                self::VIDEO_PATH . '/' . $adId,
                $filename,
                self::DISK
            );

            if (!$path) {
                return [
                    'success' => false,
                    'error' => 'Failed to upload video file.',
                ];
            }

            return [
                'success' => true,
                'path' => $path,
                'size' => $file->getSize(),
                'url' => Storage::disk(self::DISK)->url($path),
            ];
        } catch (\Exception $e) {
            Log::error('Video upload error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'An error occurred while uploading the video.',
            ];
        }
    }

    protected function validateVideo(UploadedFile $file): bool
    {
        if ($file->getSize() > self::MAX_VIDEO_SIZE) {
            return false;
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return false;
        }

        $mimeType = $file->getMimeType();
        $allowedMimes = [
            'video/mp4',
            'video/webm',
            'video/ogg',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-matroska',
        ];

        return in_array($mimeType, $allowedMimes);
    }

    protected function generateFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));

        return $name . '_' . Str::random(10) . '.' . $extension;
    }

    public function deleteVideo(Ads $ad): bool
    {
        if ($ad->isLocalVideo()) {
            return $ad->deleteVideoFile();
        }
        return true;
    }
}
