<?php

namespace App\Jobs;

use Botble\Marketplace\Services\SponsoredVideoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteExpiredSponsoredVideos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes

    /**
     * Execute the job
     */
    public function handle(SponsoredVideoService $service): void
    {
        try {
            $deletedCount = $service->deleteExpiredVideos();

            if ($deletedCount > 0) {
                Log::info("Deleted {$deletedCount} expired sponsored videos");
            } else {
                Log::debug('No expired sponsored videos to delete');
            }
        } catch (\Exception $e) {
            Log::error('Error deleting expired sponsored videos: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }
}
