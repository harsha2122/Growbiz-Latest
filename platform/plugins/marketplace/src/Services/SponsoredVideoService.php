<?php

namespace Botble\Marketplace\Services;

use Botble\Marketplace\Models\StoreSponsoredVideo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SponsoredVideoService
{
    protected const DISK = 'public';
    protected const VIDEO_PATH = 'sponsored-videos';
    protected const MAX_VIDEO_SIZE = 500 * 1024 * 1024; // 500 MB
    protected const ALLOWED_EXTENSIONS = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv'];

    /**
     * Store uploaded video file
     */
    public function storeVideo(UploadedFile $file, int $storeId): array
    {
        // Validate file
        if (!$this->validateVideo($file)) {
            return [
                'success' => false,
                'error' => 'Invalid video file. Only ' . implode(', ', self::ALLOWED_EXTENSIONS) . ' are allowed. Max size: 500MB.',
            ];
        }

        try {
            // Generate unique filename
            $filename = $this->generateFilename($file);

            // Store file
            $path = $file->storeAs(
                self::VIDEO_PATH . '/' . $storeId,
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

    /**
     * Validate video file
     */
    protected function validateVideo(UploadedFile $file): bool
    {
        // Check file size
        if ($file->getSize() > self::MAX_VIDEO_SIZE) {
            return false;
        }

        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return false;
        }

        // Check MIME type
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

    /**
     * Generate unique filename
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));

        return $name . '_' . Str::random(10) . '.' . $extension;
    }

    /**
     * Delete video file
     */
    public function deleteVideo(StoreSponsoredVideo $video): bool
    {
        if ($video->isLocalVideo()) {
            return $video->deleteVideoFile();
        }
        return true;
    }

    /**
     * Update video with new file or expiry
     */
    public function updateVideo(
        StoreSponsoredVideo $video,
        ?UploadedFile $file = null,
        ?string $expiresAt = null
    ): array
    {
        $updateData = [];

        if ($file) {
            // Delete old file if exists
            $this->deleteVideo($video);

            // Store new file
            $result = $this->storeVideo($file, $video->store_id);
            if (!$result['success']) {
                return $result;
            }

            $updateData['video_file'] = $result['path'];
            $updateData['video_type'] = 'local';
            $updateData['video_size'] = $result['size'];
            $updateData['video_url'] = null;
        }

        if ($expiresAt) {
            $expiresAtDate = \Carbon\Carbon::createFromFormat('Y-m-d', $expiresAt);
            $updateData['expires_at'] = $expiresAtDate;
            $updateData['scheduled_deletion_at'] = $expiresAtDate->copy()->addDays(10);
        }

        if ($updateData) {
            $video->update($updateData);
        }

        return [
            'success' => true,
            'message' => 'Video updated successfully.',
        ];
    }

    /**
     * Get videos due for deletion
     */
    public function getVideosDueForDeletion()
    {
        return StoreSponsoredVideo::where('scheduled_deletion_at', '<=', now())
            ->where('video_type', 'local')
            ->get();
    }

    /**
     * Delete expired videos
     */
    public function deleteExpiredVideos(): int
    {
        $videos = $this->getVideosDueForDeletion();
        $count = 0;

        foreach ($videos as $video) {
            try {
                if ($this->deleteVideo($video)) {
                    $video->update([
                        'video_file' => null,
                        'video_type' => null,
                        'scheduled_deletion_at' => null,
                    ]);
                    $count++;
                    Log::info('Deleted expired sponsored video', ['video_id' => $video->id, 'store_id' => $video->store_id]);
                }
            } catch (\Exception $e) {
                Log::error('Error deleting video: ' . $e->getMessage(), ['video_id' => $video->id]);
            }
        }

        return $count;
    }
}
