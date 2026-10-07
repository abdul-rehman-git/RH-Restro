<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            if (Schema::hasColumn('categories', 'seo_title')) {
                $table->dropColumn('seo_title');
            }

            if (Schema::hasColumn('categories', 'seo_description')) {
                $table->dropColumn('seo_description');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'seo_title')) {
                $table->dropColumn('seo_title');
            }

            if (Schema::hasColumn('products', 'seo_description')) {
                $table->dropColumn('seo_description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            if (! Schema::hasColumn('categories', 'seo_title')) {
                $table->string('seo_title')->nullable()->after('description');
            }

            if (! Schema::hasColumn('categories', 'seo_description')) {
                $table->string('seo_description', 500)->nullable()->after('seo_title');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'seo_title')) {
                $table->string('seo_title')->nullable()->after('badge_label');
            }

            if (! Schema::hasColumn('products', 'seo_description')) {
                $table->string('seo_description', 500)->nullable()->after('seo_title');
            }
        });
    }
};
