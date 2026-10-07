<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\CreatesTestImages;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestImages;

    protected ?string $storedBusinessLogoPath = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        if (is_string($this->storedBusinessLogoPath)) {
            File::delete($this->resolveStoredPath($this->storedBusinessLogoPath));
        }

        $this->cleanupGeneratedImages();

        parent::tearDown();
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_business_logo_upload_is_optimized_before_it_is_saved(): void
    {
        $user = User::factory()->create();
        $upload = $this->makePatternedJpegUpload('business-logo.jpg', 2200, 1800);

        $this->actingAs($user)
            ->post('/profile/business-settings', [
                'business_name' => 'Studio Noor',
                'business_logo' => $upload,
                'remove_business_logo' => false,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings');

        $settings = Setting::query()->first();

        $this->assertNotNull($settings);

        $logo = $settings->data['business_logo'] ?? null;
        $this->assertIsArray($logo);

        $logoPath = $logo['local_path'] ?? null;

        $this->assertIsString($logoPath);
        $this->assertStringEndsWith('.webp', $logoPath);
        $this->storedBusinessLogoPath = 'storage/'.$logoPath;

        $storedFullPath = storage_path('app/public/'.$logoPath);
        $this->assertFileExists($storedFullPath);

        $imageInfo = getimagesize($storedFullPath);

        $this->assertIsArray($imageInfo);
        $this->assertLessThanOrEqual(1600, $imageInfo[0]);
        $this->assertLessThanOrEqual(1600, $imageInfo[1]);
    }

    public function test_business_logo_can_be_removed_from_public_storage(): void
    {
        $user = User::factory()->create();
        $upload = $this->makePatternedJpegUpload('business-logo.jpg', 2200, 1800);

        $this->actingAs($user)->post('/profile/business-settings', [
            'business_name' => 'Studio Noor',
            'business_logo' => $upload,
            'remove_business_logo' => false,
        ])->assertSessionHasNoErrors();

        $logo = Setting::query()->first()?->data['business_logo'] ?? null;
        $this->assertIsArray($logo);
        $logoPath = $logo['local_path'] ?? null;
        $this->assertIsString($logoPath);
        $storedFullPath = storage_path('app/public/'.$logoPath);
        $this->assertFileExists($storedFullPath);

        $this->actingAs($user)->post('/profile/business-settings', [
            'business_name' => 'Studio Noor',
            'remove_business_logo' => true,
        ])->assertSessionHasNoErrors();

        $this->assertFileDoesNotExist($storedFullPath);
        $this->assertNull(Setting::query()->first()?->data['business_logo'] ?? null);
    }
}
