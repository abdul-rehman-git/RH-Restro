<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('customer_id')
                ->nullable()
                ->after('product_id')
                ->constrained('customers')
                ->nullOnDelete();

            $table->dropColumn('reviewer_role');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');

            $table->string('reviewer_role')->nullable()->after('reviewer_name');
        });
    }
};
