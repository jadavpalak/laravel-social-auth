<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;
use Log;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Services\SocialAuthService;

class SocialAuthController extends Controller
{
    /**
     * Create a redirect method to google api.
     *
     * @return void
     */
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback(SocialAuthService $service, $provider, Request $request)
    {
        try {
            if ($provider != 'twitter' && !$request->input('code') || $request->has('denied')) {
                return redirect('login')->withErrors('Login failed: ' . $request->input('error') . ' - ' . $request->input('error_reason'));
            }
            $user = $service->createOrGetUser(Socialite::driver($provider)->user(), $provider);
            if ($user) {
                $authUSer = User::find($user->id);
                Auth::login($authUSer, true);
                return redirect()->to('/home');
            } else {
                return redirect('login')->withErrors('Login failed: Please provide valid details.');
            }
        } catch (Exception $ex) {
            Log::info($ex);
        }
    }
}
