<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Util\FileHelper;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProviderController extends Controller
{
    /**
     * Checks database/seed/admins.txt to see if the email is an admin
     *
     * @param  $email  string the email to check
     * @return bool true if the email is an admin
     */
    public static function isAdmin(string $email): bool
    {
        if (! $email) {
            return false;
        }

        $admins = file_get_contents(FileHelper::getAdminFilePath());
        $admins = explode("\n", $admins);
        $admins = array_map('trim', $admins);

        return in_array($email, $admins);
    }

    public function redirect($provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider): RedirectResponse
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
            'is_admin' => $this->isAdmin($SocialUser->getEmail()),
        ]);

        $user->connectCourses();

        Auth::login($user, true);

        return redirect()->route('dashboard');
    }
}
