<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Support\SettingStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_content_editor_uses_the_current_backend_contract(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/public-content');

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('PublicContent/Edit')
                ->has('publicSite')
                ->has('homePage')
                ->has('aboutPage')
                ->where('homePage.hero_title', SettingStore::homePage()['hero_title'])
                ->where('aboutPage.hero_title', SettingStore::aboutPage()['hero_title']));
    }

    public function test_public_content_sections_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/public-content/site-settings', [
                'name' => 'Studio Noor',
                'tagline' => 'Handmade luxury wall art',
                'email' => 'hello@studionoor.test',
                'phone' => '+92-300-0000000',
                'whatsapp' => '+923000000000',
                'address' => 'Main Boulevard, Lahore',
                'map_embed_url' => 'https://maps.example.com/embed',
                'footer_text' => 'Curated craft for modern homes.',
                'copyright_text' => 'Studio Noor. All rights reserved.',
                'facebook_url' => 'https://facebook.com/studionoor',
                'instagram_url' => 'https://instagram.com/studionoor',
                'twitter_url' => 'https://twitter.com/studionoor',
                'business_hours_text' => "Monday - Friday|9 AM - 6 PM\nSaturday|10 AM - 4 PM",
            ])
            ->assertRedirect('/public-content');

        $this->actingAs($user)
            ->post('/public-content/home-page', [
                'hero_title' => 'Modern Art for Statement Spaces',
                'hero_subtitle' => 'Admin-managed copy that powers the hero area.',
                'stats_text' => "Artworks|120+\nCollectors|80+",
            ])
            ->assertRedirect('/public-content');

        $this->actingAs($user)
            ->post('/public-content/about-page', [
                'hero_title' => 'Built Around Craft and Detail',
                'hero_subtitle' => 'A reliable about-page story managed from admin.',
                'story_content' => "Paragraph one.\n\nParagraph two.",
                'artist_name' => 'Ayesha Khan',
                'artist_quote' => 'Craft should feel personal.',
                'stats_text' => "Commissions|250+\nYears|15+",
                'process_steps_text' => "Discover|We learn your vision.\nCreate|We build the final piece.",
                'values_text' => "Honesty|We keep communication clear.\nQuality|We focus on finish and durability.",
            ])
            ->assertRedirect('/public-content');

        $this->assertSame('Studio Noor', SettingStore::publicSite()['name']);
        $this->assertSame('Modern Art for Statement Spaces', SettingStore::homePage()['hero_title']);
        $this->assertSame('Ayesha Khan', SettingStore::aboutPage()['artist_name']);
        $this->assertSame('Saturday', SettingStore::publicSite()['business_hours'][1]['day']);
        $this->assertSame('Create', SettingStore::aboutPage()['process_steps'][1]['title']);
    }

    public function test_public_reviews_api_summary_respects_the_product_filter(): void
    {
        $category = Category::query()->create([
            'name' => 'Canvas',
            'slug' => 'canvas',
        ]);

        $focusProduct = Product::query()->create([
            'category_id' => $category->id,
            'title' => 'Focus Piece',
            'slug' => 'focus-piece',
            'price' => 250,
            'is_active' => true,
        ]);

        $otherProduct = Product::query()->create([
            'category_id' => $category->id,
            'title' => 'Other Piece',
            'slug' => 'other-piece',
            'price' => 300,
            'is_active' => true,
        ]);

        Review::query()->create([
            'product_id' => $focusProduct->id,
            'reviewer_name' => 'A',
            'rating' => 5,
            'comment' => 'Great',
            'reviewed_at' => '2026-05-01',
            'is_approved' => true,
        ]);

        Review::query()->create([
            'product_id' => $focusProduct->id,
            'reviewer_name' => 'B',
            'rating' => 4,
            'comment' => 'Strong',
            'reviewed_at' => '2026-05-02',
            'is_approved' => true,
        ]);

        Review::query()->create([
            'product_id' => $otherProduct->id,
            'reviewer_name' => 'C',
            'rating' => 1,
            'comment' => 'Ignore for this filter',
            'reviewed_at' => '2026-05-03',
            'is_approved' => true,
        ]);

        $response = $this->getJson('/api/public/reviews?product=focus-piece');

        $response
            ->assertOk()
            ->assertJsonPath('summary.total_reviews', 2)
            ->assertJsonPath('summary.average_rating', 4.5)
            ->assertJsonPath('reviews.total', 2)
            ->assertJsonPath('reviews.data.0.product.slug', 'focus-piece');
    }
}
