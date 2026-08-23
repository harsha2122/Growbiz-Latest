<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('mp_store_sponsored_videos')) {
            Schema::create('mp_store_sponsored_videos', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('store_id')->constrained('mp_stores')->cascadeOnDelete();
                $table->string('video_url');
                $table->string('thumbnail')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // Migrate each store's existing single sponsored video into the new table.
        if (Schema::hasColumn('mp_stores', 'sponsored_video_url')) {
            $stores = DB::table('mp_stores')
                ->whereNotNull('sponsored_video_url')
                ->where('sponsored_video_url', '!=', '')
                ->select(['id', 'sponsored_video_url', 'sponsored_video_thumbnail', 'sponsored_video_expires_at'])
                ->get();

            foreach ($stores as $store) {
                $exists = DB::table('mp_store_sponsored_videos')
                    ->where('store_id', $store->id)
                    ->where('video_url', $store->sponsored_video_url)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('mp_store_sponsored_videos')->insert([
                    'store_id' => $store->id,
                    'video_url' => $store->sponsored_video_url,
                    'thumbnail' => $store->sponsored_video_thumbnail,
                    'expires_at' => $store->sponsored_video_expires_at,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_store_sponsored_videos');
    }
};
