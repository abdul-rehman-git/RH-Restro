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
            $seoDefaults = [
                'default_description' => 'Discover modern essentials, smart gadgets, and lifestyle collections at RH Store.',
                'default_og_image' => null,
                'google_analytics_id' => '',
                'google_site_verification' => '',
            ];

            if (Schema::hasTable('setting')) {
                $seoData = SettingStore::seoSettings();
                $seoDefaults = [
                    'default_description' => $seoData['default_description'] ?? $seoDefaults['default_description'],
                    'default_og_image' => ImageUrl::resolve($seoData['default_og_image']['url'] ?? $seoData['default_og_image_path'] ?? null),
                    'google_analytics_id' => $seoData['google_analytics_id'] ?? '',
                    'google_site_verification' => $seoData['google_site_verification'] ?? '',
                ];
            }

            $view->with('seoDefaults', $seoDefaults);
        });
    }
}
