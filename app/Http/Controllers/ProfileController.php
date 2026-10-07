<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Setting;
use App\Services\ImageUploadService;
use App\Support\ImageUpload;
use App\Support\SettingStore;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
        ]);
    }

    /**
     * Display the admin settings page.
     */
    public function settings(): Response
    {
        return Inertia::render('Settings/Edit', [
            'cloudinary' => SettingStore::cloudinaryForAdmin(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Update the business settings for the admin panel.
     */
    public function updateBusinessSettings(Request $request, ImageUploadService $uploads): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_logo' => ['nullable', ImageUpload::validationRule()],
            'remove_business_logo' => ['nullable', 'boolean'],
        ]);

        $settings = Setting::query()->firstOrCreate([], [
            'data' => [],
        ]);
        $data = $settings->data ?? [];
        $data['business_name'] = $validated['business_name'];
        unset($data['business_logo_path']);
        $currentLogo = $data['business_logo'] ?? null;

        if ($request->boolean('remove_business_logo')) {
            $uploads->delete($currentLogo);
            $data['business_logo'] = null;
        }

        if ($request->hasFile('business_logo')) {
            $uploads->delete($currentLogo);
            $data['business_logo'] = $uploads->upload($request->file('business_logo'), 'business_logo', 'admin-business-logo');
        }

        $settings->data = $data;
        $settings->save();

        if (is_array($data['business_logo'] ?? null) && isset($data['business_logo']['uploaded_image_id'])) {
            $uploads->queueCloudinaryUpload((int) $data['business_logo']['uploaded_image_id']);
        }

        return Redirect::route('settings.edit')->with('status', 'business-settings-updated');
    }

    /**
     * Update Cloudinary credentials used for image uploads.
     */
    public function updateCloudinarySettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cloud_name' => ['required', 'string', 'max:255'],
            'api_key' => ['required', 'string', 'max:255'],
            'api_secret' => ['nullable', 'string', 'max:255'],
            'folder' => ['nullable', 'string', 'max:255'],
        ]);

        $current = SettingStore::cloudinary();
        $apiSecret = trim((string) ($validated['api_secret'] ?? ''));

        if ($apiSecret === '') {
            $apiSecret = $current['api_secret'];
        }

        if ($apiSecret === '') {
            return Redirect::route('settings.edit')
                ->withErrors(['api_secret' => 'API secret is required for Cloudinary.'])
                ->withInput();
        }

        SettingStore::put('cloudinary', [
            'cloud_name' => trim($validated['cloud_name']),
            'api_key' => trim($validated['api_key']),
            'api_secret' => $apiSecret,
            'folder' => trim((string) ($validated['folder'] ?? '')) ?: 'rh-commerce',
        ]);

        return Redirect::route('settings.edit')->with('status', 'cloudinary-settings-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
