<?php

namespace App\Providers;

use App\Support\ImageUrl;
use App\Support\SettingStore;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Use project storage for PHP temp files to avoid system temp warnings
        $tmpDir = storage_path('tmp');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }
        $tmpDir = realpath($tmpDir) ?: sys_get_temp_dir();
        putenv("TMPDIR={$tmpDir}");
        putenv("TEMP={$tmpDir}");
        putenv("TMP={$tmpDir}");

        view()->composer('app', function ($view) {
            $seoData = SettingStore::seoSettings();
            $seoDefaults = [
                'default_description' => $seoData['default_description'] ?? 'Experience exquisite fine dining, artisan pizzas, gourmet burgers, sizzling steaks, and warm hospitality at RH Restro.',
                'default_og_image' => ImageUrl::resolve($seoData['default_og_image']['url'] ?? $seoData['default_og_image_path'] ?? $seoData['default_og_image'] ?? null) ?: url('/rh-icon-512.png'),
                'google_analytics_id' => $seoData['google_analytics_id'] ?? '',
                'google_site_verification' => $seoData['google_site_verification'] ?? '',
            ];

            $view->with('seoDefaults', $seoDefaults);
        });
    }
}
