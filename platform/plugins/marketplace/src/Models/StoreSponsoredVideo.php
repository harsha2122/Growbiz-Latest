<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class StoreSponsoredVideo extends BaseModel
{
    protected $table = 'mp_store_sponsored_videos';

    protected $fillable = [
        'store_id',
        'video_url',
        'video_file',
        'video_type',
        'video_size',
        'thumbnail',
        'expires_at',
        'scheduled_deletion_at',
        'sort_order',
        'clicks',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'scheduled_deletion_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    /**
     * Check if video is active (not expired)
     */
    public function isActive(): bool
    {
        return ! ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Check if video is stored locally on server
     */
    public function isLocalVideo(): bool
    {
        return $this->video_type === 'local' && $this->video_file;
    }

    /**
     * Check if video is external link
     */
    public function isExternalVideo(): bool
    {
        return $this->video_type === 'external' && $this->video_url;
    }

    /**
     * Get the playable video URL
     */
    public function getVideoUrl(): string
    {
        if ($this->isLocalVideo()) {
            return Storage::disk('public')->url($this->video_file);
        }
        return $this->video_url ?? '';
    }

    /**
     * Delete the video file from storage
     */
    public function deleteVideoFile(): bool
    {
        if ($this->isLocalVideo() && $this->video_file) {
            return Storage::disk('public')->delete($this->video_file);
        }
        return true;
    }

    /**
     * Calculate scheduled deletion date (expires_at + 10 days)
     */
    public function calculateScheduledDeletion(): ?\DateTime
    {
        if ($this->expires_at) {
            return $this->expires_at->copy()->addDays(10);
        }
        return null;
    }

    /**
     * Update scheduled deletion date
     */
    public function updateScheduledDeletion(): void
    {
        if ($this->expires_at) {
            $this->update([
                'scheduled_deletion_at' => $this->calculateScheduledDeletion(),
            ]);
        }
    }

    /**
     * Format file size for display
     */
    public function getFormattedSize(): string
    {
        if (! $this->video_size) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->video_size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $model->updateScheduledDeletion();
        });

        static::updated(function ($model) {
            $model->updateScheduledDeletion();
        });

        static::deleting(function ($model) {
            $model->deleteVideoFile();
        });
    }
}
