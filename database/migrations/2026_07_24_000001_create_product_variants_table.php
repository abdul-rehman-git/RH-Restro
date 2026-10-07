<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->string('image')->nullable();
            $table->string('image_google_drive_file_id')->nullable();
            $table->unsignedBigInteger('image_google_drive_account_id')->nullable();
            $table->string('image_cloudinary_public_id')->nullable();
            $table->boolean('image_is_uploaded_to_cloudinary')->default(false);
            $table->unsignedBigInteger('image_uploaded_image_id')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
