<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class ProviderController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        $SocialUser = Socialite::driver($provider)->user();

        $user = User::updateOrCreate([
            'provider_id' => $SocialUser->getId(),
            'provider' => $provider,
        ], [
            'name' => $SocialUser->getName(),
            'email' => $SocialUser->getEmail(),
            'avatar' => $SocialUser->getAvatar(),
            'provider_token' => $SocialUser->token,
        ]);

        Auth::login($user, true);

        return redirect()->route('dashboard');

    }
}
