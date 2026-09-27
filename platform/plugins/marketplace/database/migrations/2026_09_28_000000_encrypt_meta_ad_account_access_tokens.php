<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('meta_ad_accounts')) {
            return;
        }

        DB::table('meta_ad_accounts')->whereNotNull('access_token')->orderBy('id')->each(function ($row): void {
            // Skip rows that are already encrypted, so re-running this is safe.
            try {
                Crypt::decryptString($row->access_token);

                return;
            } catch (DecryptException) {
            }

            DB::table('meta_ad_accounts')
                ->where('id', $row->id)
                ->update(['access_token' => Crypt::encryptString($row->access_token)]);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('meta_ad_accounts')) {
            return;
        }

        DB::table('meta_ad_accounts')->whereNotNull('access_token')->orderBy('id')->each(function ($row): void {
            try {
                $plain = Crypt::decryptString($row->access_token);
            } catch (DecryptException) {
                return;
            }

            DB::table('meta_ad_accounts')->where('id', $row->id)->update(['access_token' => $plain]);
        });
    }
};
