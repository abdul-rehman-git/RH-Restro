<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PublicContent\UpdateAboutPageRequest;
use App\Http\Requests\PublicContent\UpdateHomePageRequest;
use App\Http\Requests\PublicContent\UpdatePublicSiteRequest;
use App\Http\Requests\PublicContent\UpdateSeoSettingsRequest;
use App\Services\ImageUploadService;
use App\Support\ImageUrl;
use App\Support\SettingStore;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PublicContentController extends Controller
{
    public function edit(): Response
    {
        $seo = SettingStore::seoSettings();

        return Inertia::render('PublicContent/Edit', [
            'publicSite' => $this->publicSite(),
            'homePage' => $this->homePage(),
            'aboutPage' => $this->aboutPage(),
            'seoSettings' => [
                'default_description' => $seo['default_description'] ?? '',
                'default_og_image_url' => ImageUrl::resolve($seo['default_og_image']['url'] ?? $seo['default_og_image_path'] ?? null),
                'google_analytics_id' => $seo['google_analytics_id'] ?? '',
                'google_site_verification' => $seo['google_site_verification'] ?? '',
            ],
        ]);
    }

    public function updateSiteSettings(UpdatePublicSiteRequest $request, ImageUploadService $uploads): RedirectResponse
    {
        $validated = $request->validated();
        $current = SettingStore::publicSite();

        $payload = [
            'name' => $validated['name'],
            'tagline' => $validated['tagline'] ?? '',
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'whatsapp' => $validated['whatsapp'] ?? '',
            'address' => $validated['address'],
            'map_embed_url' => $validated['map_embed_url'] ?? null,
            'footer_text' => $validated['footer_text'] ?? '',
            'copyright_text' => $validated['copyright_text'] ?? '',
            'social_links' => [
                'facebook' => $validated['facebook_url'] ?? '',
                'instagram' => $validated['instagram_url'] ?? '',
                'twitter' => $validated['twitter_url'] ?? '',
            ],
            'business_hours' => $this->parseLines($validated['business_hours_text'] ?? '', ['day', 'hours']),
            'logo' => $current['logo'] ?? null,
            'footer_logo' => $current['footer_logo'] ?? null,
        ];

        if ($request->boolean('remove_logo')) {
            $uploads->delete($current['logo'] ?? null);
            $payload['logo'] = null;
        }

        if ($request->boolean('remove_footer_logo')) {
            $uploads->delete($current['footer_logo'] ?? null);
            $payload['footer_logo'] = null;
        }

        $queued = [];

        if ($request->logoFile() !== null) {
            $uploads->delete($current['logo'] ?? null);
            $payload['logo'] = $uploads->upload($request->logoFile(), 'logo', 'site-logo');
            $queued[] = $payload['logo'];
        }

        if ($request->footerLogoFile() !== null) {
            $uploads->delete($current['footer_logo'] ?? null);
            $payload['footer_logo'] = $uploads->upload($request->footerLogoFile(), 'footer_logo', 'footer-logo');
            $queued[] = $payload['footer_logo'];
        }

        SettingStore::put('public_site', $payload);
        $uploads->queueCloudinaryUploads($queued);

        return redirect()
            ->route('public-content.edit')
            ->with('status', 'Public site settings updated successfully.');
    }

    public function updateHomePage(UpdateHomePageRequest $request, ImageUploadService $uploads): RedirectResponse
    {
        $validated = $request->validated();
        $current = SettingStore::homePage();

        $payload = [
            'hero_title' => $validated['hero_title'],
            'hero_subtitle' => $validated['hero_subtitle'],
            'stats' => $this->parseLines($validated['stats_text'] ?? '', ['label', 'value']),
            'hero_image' => $current['hero_image'] ?? null,
        ];

        if ($request->boolean('remove_hero_image')) {
            $uploads->delete($current['hero_image'] ?? null);
            $payload['hero_image'] = null;
        }

        if ($request->heroImageFile() !== null) {
            $uploads->delete($current['hero_image'] ?? null);
            $payload['hero_image'] = $uploads->upload($request->heroImageFile(), 'hero_image', 'home-hero');
        }

        SettingStore::put('public_pages.home', $payload);

        if (is_array($payload['hero_image'] ?? null) && isset($payload['hero_image']['uploaded_image_id'])) {
            $uploads->queueCloudinaryUpload((int) $payload['hero_image']['uploaded_image_id']);
        }

        return redirect()
            ->route('public-content.edit')
            ->with('status', 'Homepage content updated successfully.');
    }

    public function updateAboutPage(UpdateAboutPageRequest $request, ImageUploadService $uploads): RedirectResponse
    {
        $validated = $request->validated();
        $current = SettingStore::aboutPage();

        $payload = [
            'hero_title' => $validated['hero_title'],
            'hero_subtitle' => $validated['hero_subtitle'],
            'story_content' => $validated['story_content'],
            'artist_name' => $validated['artist_name'],
            'artist_quote' => $validated['artist_quote'] ?? '',
            'stats' => $this->parseLines($validated['stats_text'] ?? '', ['label', 'value']),
            'process_steps' => $this->parseLines($validated['process_steps_text'] ?? '', ['title', 'description']),
            'values' => $this->parseLines($validated['values_text'] ?? '', ['title', 'description']),
            'artist_image' => $current['artist_image'] ?? null,
        ];

        if ($request->boolean('remove_artist_image')) {
            $uploads->delete($current['artist_image'] ?? null);
            $payload['artist_image'] = null;
        }

        if ($request->artistImageFile() !== null) {
            $uploads->delete($current['artist_image'] ?? null);
            $payload['artist_image'] = $uploads->upload($request->artistImageFile(), 'artist_image', 'about-artist');
        }

        SettingStore::put('public_pages.about', $payload);

        if (is_array($payload['artist_image'] ?? null) && isset($payload['artist_image']['uploaded_image_id'])) {
            $uploads->queueCloudinaryUpload((int) $payload['artist_image']['uploaded_image_id']);
        }

        return redirect()
            ->route('public-content.edit')
            ->with('status', 'About page content updated successfully.');
    }

    protected function publicSite(): array
    {
        $site = SettingStore::publicSite();

        return [
            ...$site,
            'logo_url' => ImageUrl::resolve($site['logo']['url'] ?? $site['logo_path'] ?? null),
            'footer_logo_url' => ImageUrl::resolve($site['footer_logo']['url'] ?? $site['footer_logo_path'] ?? null),
            'facebook_url' => $site['social_links']['facebook'] ?? '',
            'instagram_url' => $site['social_links']['instagram'] ?? '',
            'twitter_url' => $site['social_links']['twitter'] ?? '',
            'business_hours_text' => $this->formatLines($site['business_hours'] ?? [], ['day', 'hours']),
        ];
    }

    protected function homePage(): array
    {
        $page = SettingStore::homePage();

        return [
            'hero_title' => $page['hero_title'] ?? '',
            'hero_subtitle' => $page['hero_subtitle'] ?? '',
            'hero_image_url' => ImageUrl::resolve($page['hero_image']['url'] ?? $page['hero_image_path'] ?? null),
            'stats_text' => $this->formatLines($page['stats'] ?? [], ['label', 'value']),
        ];
    }

    protected function aboutPage(): array
    {
        $page = SettingStore::aboutPage();

        return [
            'hero_title' => $page['hero_title'] ?? '',
            'hero_subtitle' => $page['hero_subtitle'] ?? '',
            'story_content' => $page['story_content'] ?? '',
            'artist_name' => $page['artist_name'] ?? '',
            'artist_quote' => $page['artist_quote'] ?? '',
            'artist_image_url' => ImageUrl::resolve($page['artist_image']['url'] ?? $page['artist_image_path'] ?? null),
            'stats_text' => $this->formatLines($page['stats'] ?? [], ['label', 'value']),
            'process_steps_text' => $this->formatLines($page['process_steps'] ?? [], ['title', 'description']),
            'values_text' => $this->formatLines($page['values'] ?? [], ['title', 'description']),
        ];
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<int, array<string, string>>
     */
    protected function parseLines(string $value, array $keys): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->map(function (string $line) use ($keys): array {
                $parts = array_map('trim', explode('|', $line, count($keys)));

                return collect($keys)
                    ->mapWithKeys(fn (string $key, int $index): array => [$key => $parts[$index] ?? ''])
                    ->all();
            })
            ->filter(fn (array $item): bool => collect($item)->filter()->isNotEmpty())
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, string>>  $items
     * @param  array<int, string>  $keys
     */
    public function updateSeoSettings(UpdateSeoSettingsRequest $request, ImageUploadService $uploads): RedirectResponse
    {
        $validated = $request->validated();
        $current = SettingStore::seoSettings();

        $payload = [
            'default_description' => $validated['default_description'] ?? '',
            'default_og_image' => $current['default_og_image'] ?? null,
            'google_analytics_id' => $validated['google_analytics_id'] ?? '',
            'google_site_verification' => $validated['google_site_verification'] ?? '',
        ];

        if ($request->boolean('remove_default_og_image')) {
            $uploads->delete($current['default_og_image'] ?? null);
            $payload['default_og_image'] = null;
        }

        if ($request->defaultOgImageFile() !== null) {
            $uploads->delete($current['default_og_image'] ?? null);
            $payload['default_og_image'] = $uploads->upload($request->defaultOgImageFile(), 'default_og_image', 'seo-og');
        }

        SettingStore::put('seo', $payload);

        if (is_array($payload['default_og_image'] ?? null) && isset($payload['default_og_image']['uploaded_image_id']) && $request->defaultOgImageFile() !== null) {
            $uploads->queueCloudinaryUpload((int) $payload['default_og_image']['uploaded_image_id']);
        }

        return redirect()
            ->route('public-content.edit')
            ->with('status', 'SEO settings updated successfully.');
    }

    protected function formatLines(array $items, array $keys): string
    {
        return collect($items)
            ->map(fn (array $item): string => implode('|', array_map(
                fn (string $key): string => trim((string) ($item[$key] ?? '')),
                $keys,
            )))
            ->filter()
            ->implode("\n");
    }
}
