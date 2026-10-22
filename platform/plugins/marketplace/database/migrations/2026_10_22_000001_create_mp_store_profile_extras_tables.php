<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('mp_stores', 'is_minimal_profile')) {
            Schema::table('mp_stores', function (Blueprint $table): void {
                $table->boolean('is_minimal_profile')->default(false)->after('is_key_account');
            });
        }

        if (! Schema::hasColumn('mp_stores', 'service_tags')) {
            Schema::table('mp_stores', function (Blueprint $table): void {
                $table->json('service_tags')->nullable()->after('is_minimal_profile');
            });
        }

        if (! Schema::hasColumn('mp_stores', 'gallery_image_limit')) {
            Schema::table('mp_stores', function (Blueprint $table): void {
                $table->unsignedSmallInteger('gallery_image_limit')->default(7)->after('service_tags');
            });
        }

        if (! Schema::hasTable('mp_store_brands')) {
            Schema::create('mp_store_brands', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('store_id')->constrained('mp_stores')->cascadeOnDelete();
                $table->string('name');
                $table->string('logo')->nullable();
                $table->timestamps();

                $table->index('store_id');
            });
        }

        if (! Schema::hasTable('mp_store_gallery')) {
            Schema::create('mp_store_gallery', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('store_id')->constrained('mp_stores')->cascadeOnDelete();
                $table->string('image');
                $table->timestamps();

                $table->index('store_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_store_gallery');
        Schema::dropIfExists('mp_store_brands');

        if (Schema::hasColumn('mp_stores', 'gallery_image_limit')) {
            Schema::table('mp_stores', function (Blueprint $table): void {
                $table->dropColumn('gallery_image_limit');
            });
        }

        if (Schema::hasColumn('mp_stores', 'service_tags')) {
            Schema::table('mp_stores', function (Blueprint $table): void {
                $table->dropColumn('service_tags');
            });
        }

        if (Schema::hasColumn('mp_stores', 'is_minimal_profile')) {
            Schema::table('mp_stores', function (Blueprint $table): void {
                $table->dropColumn('is_minimal_profile');
            });
        }
    }
};
