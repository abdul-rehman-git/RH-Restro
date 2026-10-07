<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Support\ImageUrl;
use App\Support\SeoService;
use App\Support\SettingStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $site = SettingStore::publicSite();
        $seoSettings = SettingStore::seoSettings();

        $businessSettings = [
            'name' => $site['name'] ?? 'RH Restro',
            'logoUrl' => ImageUrl::resolve($site['logo']['url'] ?? $site['logo_path'] ?? $site['logo'] ?? null),
        ];

        $seoDefaults = [
            'default_description' => $seoSettings['default_description'] ?? 'Experience exquisite fine dining, artisan pizzas, gourmet burgers, sizzling steaks, and warm hospitality at RH Restro.',
            'default_og_image' => ImageUrl::resolve($seoSettings['default_og_image']['url'] ?? $seoSettings['default_og_image_path'] ?? $seoSettings['default_og_image'] ?? null) ?: url('/rh-icon-512.png'),
            'google_analytics_id' => $seoSettings['google_analytics_id'] ?? '',
            'google_site_verification' => $seoSettings['google_site_verification'] ?? '',
        ];

        return [
            ...parent::share($request),
            'csrfToken' => fn () => $request->session()->token(),
            'auth' => [
                'user' => $request->user(),
            ],
            'customer' => $request->user('customer') ? [
                'id' => $request->user('customer')->id,
                'name' => $request->user('customer')->name,
                'email' => $request->user('customer')->email,
                'phone' => $request->user('customer')->phone,
                'profile_photo_url' => $request->user('customer')->profilePhotoUrl(),
            ] : null,
            'cartCount' => $request->user('customer') ? $request->user('customer')->cartItems()->sum('quantity') : 0,
            'flash' => [
                'message' => fn() => $this->formatStatusMessage(
                    $request->session()->get('status'),
                ),
                'type' => fn() => $this->resolveStatusType(
                    $request->session()->get('status'),
                ),
            ],
            'businessSettings' => $businessSettings,
            'seoDefaults' => $seoDefaults,
        ];
    }

    protected function formatStatusMessage(?string $status): ?string
    {
        return match ($status) {
            null => null,
            'verification-link-sent' => 'A new verification link has been sent to your email address.',
            'business-settings-updated' => 'Business settings updated successfully.',
            default => $status,
        };
    }

    protected function resolveStatusType(?string $status): string
    {
        return match ($status) {
            'Category cannot be deleted while products are assigned to it.' => 'error',
            default => 'success',
        };
    }
}
