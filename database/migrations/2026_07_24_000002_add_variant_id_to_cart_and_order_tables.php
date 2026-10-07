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
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->index('customer_id', 'cart_items_customer_id_index');
                $table->dropUnique(['customer_id', 'product_id']);
                $table->foreignId('product_variant_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('product_variants')
                    ->nullOnDelete();
                $table->unique(['customer_id', 'product_id', 'product_variant_id'], 'cart_items_cust_prod_var_unique');
            });
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->foreignId('product_variant_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('product_variants')
                    ->nullOnDelete();
                $table->string('variant_name')->nullable()->after('product_title');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropUnique('cart_items_cust_prod_var_unique');
                $table->dropForeign(['product_variant_id']);
                $table->dropColumn('product_variant_id');
                $table->unique(['customer_id', 'product_id']);
                $table->dropIndex('cart_items_customer_id_index');
            });
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropForeign(['product_variant_id']);
                $table->dropColumn(['product_variant_id', 'variant_name']);
            });
        }
    }
};
