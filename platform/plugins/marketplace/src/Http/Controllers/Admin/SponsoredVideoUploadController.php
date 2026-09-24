<?php

namespace Botble\Marketplace\Http\Controllers\Admin;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Marketplace\Models\StoreSponsoredVideo;
use Botble\Marketplace\Services\SponsoredVideoService;
use Illuminate\Http\Request;

class SponsoredVideoUploadController extends BaseController
{
    protected $videoService;

    public function __construct(SponsoredVideoService $videoService)
    {
        $this->videoService = $videoService;
    }

    /**
     * Upload video file via AJAX
     */
    public function upload(Request $request): BaseHttpResponse
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,webm,ogg,mov,avi,mkv|max:512000', // 500MB
            'store_id' => 'required|integer',
        ]);

        $result = $this->videoService->storeVideo(
            $request->file('video'),
            $request->input('store_id')
        );

        if (!$result['success']) {
            return $this->httpResponse()
                ->setError()
                ->setMessage($result['error']);
        }

        return $this->httpResponse()
            ->setData([
                'path' => $result['path'],
                'url' => $result['url'],
                'size' => $result['size'],
            ])
            ->setMessage('Video uploaded successfully');
    }
}
