<?php

namespace Botble\Marketplace\Http\Controllers\Fronts\Concerns;

use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Services\MetaApiClient;
use Illuminate\Support\Facades\Log;

trait HandlesMetaApiResults
{
    /**
     * Returns null on success, or a vendor-facing error message on failure. An
     * expired/revoked token also flags the account as disconnected so the vendor
     * is prompted to reconnect instead of every later call failing silently.
     */
    protected function metaError(array $result, MetaAdAccount $adAccount, string $action): ?string
    {
        $message = MetaApiClient::errorMessage($result);

        if ($message === null) {
            return null;
        }

        Log::warning("Meta API {$action} failed", ['store_id' => $adAccount->store_id, 'error' => $result['error']]);

        if (MetaApiClient::isTokenError($result)) {
            $adAccount->update(['is_connected' => false]);

            return 'Your Facebook connection has expired. Please reconnect your Facebook account and try again.';
        }

        return $message;
    }
}
