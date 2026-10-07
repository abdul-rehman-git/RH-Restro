<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle(Request $request)
    {
        $previous = url()->previous();
        $signInUrl = route('public.sign-in');

        Session::put('previous_url', $previous === $signInUrl ? url('/') : $previous);
        Session::put('google_action', $request->query('action', 'signin'));

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('public.sign-in')->withErrors(['google' => 'Google authentication failed. Please try again.']);
        }

        $action = Session::pull('google_action', 'signin');

        $existingAccount = SocialAccount::where('provider_name', 'google')
            ->where('provider_id', $googleUser->getId())
            ->first();

        if ($existingAccount) {
            Auth::guard('customer')->login($existingAccount->customer);

            return redirect()->to(Session::pull('previous_url', '/'));
        }

        $existingCustomer = Customer::where('email', $googleUser->getEmail())->first();

        if ($existingCustomer) {
            if ($action === 'signup') {
                return redirect()->route('public.sign-in')->withErrors([
                    'google' => 'You already have an account. Please sign in.',
                ]);
            }

            Auth::guard('customer')->login($existingCustomer);

            return redirect()->to(Session::pull('previous_url', '/'));
        }

        $customer = Customer::create([
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'password' => null,
        ]);

        $customer->socialAccounts()->create([
            'provider_name' => 'google',
            'provider_id' => $googleUser->getId(),
            'provider_token' => $googleUser->token,
            'provider_refresh_token' => $googleUser->refreshToken,
        ]);

        Auth::guard('customer')->login($customer);

        return redirect()->to(Session::pull('previous_url', '/'));
    }
}
