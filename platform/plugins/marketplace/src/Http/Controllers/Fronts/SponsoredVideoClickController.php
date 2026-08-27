<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Marketplace\Models\StoreSponsoredVideo;

class SponsoredVideoClickController extends BaseController
{
    public function store(int|string $id): BaseHttpResponse
    {
        StoreSponsoredVideo::query()->where('id', $id)->increment('clicks');

        return $this->httpResponse();
    }
}
