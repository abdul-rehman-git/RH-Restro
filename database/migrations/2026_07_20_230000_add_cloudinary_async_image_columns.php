<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uploaded_images', function (Blueprint $table) {
            $table->id();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('url');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('cloudinary_public_id')->nullable()->unique();
            $table->boolean('is_uploaded_to_cloudinary')->default(false);
            $table->timestamps();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('image_cloudinary_public_id')->nullable()->after('image_google_drive_account_id');
            $table->boolean('image_is_uploaded_to_cloudinary')->default(false)->after('image_cloudinary_public_id');
            $table->foreignId('image_uploaded_image_id')->nullable()->after('image_is_uploaded_to_cloudinary')
                ->constrained('uploaded_images')->nullOnDelete();

            $table->string('banner_image_cloudinary_public_id')->nullable()->after('banner_image_google_drive_account_id');
            $table->boolean('banner_image_is_uploaded_to_cloudinary')->default(false)->after('banner_image_cloudinary_public_id');
            $table->foreignId('banner_image_uploaded_image_id')->nullable()->after('banner_image_is_uploaded_to_cloudinary')
                ->constrained('uploaded_images')->nullOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('image_cloudinary_public_id')->nullable()->after('image_google_drive_account_id');
            $table->boolean('image_is_uploaded_to_cloudinary')->default(false)->after('image_cloudinary_public_id');
            $table->foreignId('image_uploaded_image_id')->nullable()->after('image_is_uploaded_to_cloudinary')
                ->constrained('uploaded_images')->nullOnDelete();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('profile_photo_cloudinary_public_id')->nullable()->after('profile_photo_google_drive_account_id');
            $table->boolean('profile_photo_is_uploaded_to_cloudinary')->default(false)->after('profile_photo_cloudinary_public_id');
            $table->foreignId('profile_photo_uploaded_image_id')->nullable()->after('profile_photo_is_uploaded_to_cloudinary')
                ->constrained('uploaded_images')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('profile_photo_uploaded_image_id');
            $table->dropColumn([
                'profile_photo_cloudinary_public_id',
                'profile_photo_is_uploaded_to_cloudinary',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('image_uploaded_image_id');
            $table->dropColumn([
                'image_cloudinary_public_id',
                'image_is_uploaded_to_cloudinary',
            ]);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('image_uploaded_image_id');
            $table->dropConstrainedForeignId('banner_image_uploaded_image_id');
            $table->dropColumn([
                'image_cloudinary_public_id',
                'image_is_uploaded_to_cloudinary',
                'banner_image_cloudinary_public_id',
                'banner_image_is_uploaded_to_cloudinary',
            ]);
        });

        Schema::dropIfExists('uploaded_images');
    }
};
