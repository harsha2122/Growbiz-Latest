<?php

namespace Botble\Ads\Http\Controllers;

use Botble\Ads\Models\Ads;
use Botble\Ads\Services\AdsVideoService;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Illuminate\Http\Request;

class AdsVideoUploadController extends BaseController
{
    protected $videoService;

    public function __construct(AdsVideoService $videoService)
    {
        $this->videoService = $videoService;
    }

    public function upload(Request $request): BaseHttpResponse
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,webm,ogg,mov,avi,mkv|max:512000',
            'ad_id' => 'required|integer',
        ]);

        $result = $this->videoService->storeVideo(
            $request->file('video'),
            $request->input('ad_id')
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
