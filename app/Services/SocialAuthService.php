<?php

namespace App\Services;

use App\SocialAuth;
use Socialite;
use App\User;
use Laravel\Socialite\Contracts\User as ProviderUser;

class SocialAuthService
{
    public function createOrGetUser(ProviderUser $providerUser, $provider)
    {
        $account = SocialAuth::whereProvider($provider)
            ->whereProviderUserId($providerUser->getId())
            ->first();
        if ($account) {
            return $account->user;
        } else {
            $account = new SocialAuth([
                'provider_user_id' => $providerUser->getId(),
                'provider' => $provider
            ]);
            if ($providerUser->getEmail()) {
                $user = User::whereEmail($providerUser->getEmail())->first();
                if (!$user) {
                    $user = User::create([
                        'email' => $providerUser->getEmail(),
                        'name' => $providerUser->getName(),
                        'password' => bcrypt(rand(1, 10000)),
                    ]);
                }
                $account->user()->associate($user);
                $account->save();
                return $user;
            } else {
                return null;
            }
        }
    }
}
