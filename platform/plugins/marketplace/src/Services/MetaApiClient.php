<?php

namespace Botble\Marketplace\Services;

use Botble\Marketplace\Facades\MarketplaceHelper;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaApiClient
{
    private string $accessToken = '';

    private function baseUrl(): string
    {
        return 'https://graph.facebook.com/' . MarketplaceHelper::getMetaApiVersion();
    }

    public function withToken(string $token): static
    {
        $clone = clone $this;
        $clone->accessToken = $token;

        return $clone;
    }

    public function get(string $endpoint, array $params = []): array
    {
        $params['access_token'] = $this->accessToken;

        $response = $this->client()->get($this->baseUrl() . '/' . ltrim($endpoint, '/'), $params);

        return $this->parse($response);
    }

    public function post(string $endpoint, array $data = []): array
    {
        $data['access_token'] = $this->accessToken;

        $response = $this->client()->post($this->baseUrl() . '/' . ltrim($endpoint, '/'), $data);

        return $this->parse($response);
    }

    public function delete(string $endpoint, array $params = []): array
    {
        $params['access_token'] = $this->accessToken;

        $response = $this->client()->delete($this->baseUrl() . '/' . ltrim($endpoint, '/'), $params);

        return $this->parse($response);
    }

    private function client(): PendingRequest
    {
        return Http::timeout(30)->acceptJson();
    }

    private function parse(\Illuminate\Http\Client\Response $response): array
    {
        $body = $response->json() ?? [];

        if (isset($body['error'])) {
            $message = $body['error']['message'] ?? 'Meta API error';
            Log::error('Meta API error', ['error' => $body['error']]);

            throw new Exception($message, $body['error']['code'] ?? 0);
        }

        return $body;
    }
}
