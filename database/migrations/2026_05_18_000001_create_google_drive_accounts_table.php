<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_drive_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('folder_id');
            $table->string('folder_link')->nullable();
            $table->longText('service_account_json');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_drive_accounts');
    }
};
