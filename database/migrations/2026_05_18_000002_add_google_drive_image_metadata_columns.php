<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('image_google_drive_file_id')->nullable()->after('image');
            $table->unsignedBigInteger('image_google_drive_account_id')->nullable()->after('image_google_drive_file_id');
            $table->string('banner_image_google_drive_file_id')->nullable()->after('banner_image');
            $table->unsignedBigInteger('banner_image_google_drive_account_id')->nullable()->after('banner_image_google_drive_file_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('image_google_drive_file_id')->nullable()->after('image');
            $table->unsignedBigInteger('image_google_drive_account_id')->nullable()->after('image_google_drive_file_id');
            $table->json('gallery_image_files')->nullable()->after('gallery_images');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->json('gallery_image_files')->nullable()->after('gallery_images');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('profile_photo_url')->nullable()->after('profile_photo_path');
            $table->string('profile_photo_google_drive_file_id')->nullable()->after('profile_photo_url');
            $table->unsignedBigInteger('profile_photo_google_drive_account_id')->nullable()->after('profile_photo_google_drive_file_id');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'profile_photo_url',
                'profile_photo_google_drive_file_id',
                'profile_photo_google_drive_account_id',
            ]);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('gallery_image_files');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'image_google_drive_file_id',
                'image_google_drive_account_id',
                'gallery_image_files',
            ]);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'image_google_drive_file_id',
                'image_google_drive_account_id',
                'banner_image_google_drive_file_id',
                'banner_image_google_drive_account_id',
            ]);
        });
    }
};
