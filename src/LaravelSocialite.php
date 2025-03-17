<?php

namespace Ichinya\LaravelSocialite;

use App\Http\Exceptions\AuthException;
use App\Models\User;
use App\MoonShine\Pages\ProfilePage;
use Ichinya\LaravelSocialite\Models\SocialAccount;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use MoonShine\Laravel\Http\Controllers\MoonShineController;
use MoonShine\Support\Enums\ToastType;
use RuntimeException;

class LaravelSocialite extends MoonShineController
{

    /**
     * @throws AuthException
     */
    public function redirect(string $driver)
    {
        $this->ensureSocialiteIsInstalled();

        if (!$this->hasDriver($driver)) {
            throw AuthException::driverNotFound();
        }

        return Socialite::driver($driver)->redirect();
    }


    protected function ensureSocialiteIsInstalled(): void
    {
        if (class_exists(Socialite::class)) {
            return;
        }

        throw new RuntimeException(
            'Please install the Socialite: laravel/socialite'
        );
    }

    protected function hasDriver(string $driver): bool
    {
        return isset($this->drivers()[$driver]);
    }

    protected function drivers(): array
    {
        return config('socialite.drivers', []);
    }


    /**
     * @throws AuthException
     */
    public function callback(string $driver): RedirectResponse
    {
        $this->ensureSocialiteIsInstalled();

        if (!$this->hasDriver($driver)) {
            throw AuthException::driverNotFound();
        }

        $socialiteUser = Socialite::driver($driver)->user();

        // проверяем делаем привязку из профиля или нет
        if ($this->auth()->check()) {
            return $this->bindAccount($this->auth()->user(), $socialiteUser, $driver);
        }

        // проверяем есть ли привязки
        $account = SocialAccount::query()
            ->where('driver', $driver)
            ->where('identity', $socialiteUser->getId())
            ->first();


        if ($account instanceof SocialAccount) {
            // авторизуем пользователя
            $this->auth()->loginUsingId($account->user_id);
            // редиректим на главную
            return redirect(moonshineRouter()->getEndpoints()->home());
        }


        // привязки нет, проверяем email
        if (empty($socialiteUser->getEmail())) {
            $this->toast('oauth не предоставил email', ToastType::ERROR);
            return redirect(moonshineRouter()->to('login'))->withErrors(['username' => 'oauth не предоставил email',]);
        }

        // проверяем есть ли пользователь с таким email
        $user = User::query()->with('socials')->where('email', $socialiteUser->getEmail())->first();

        if ($user && $user->socials->count() > 0) {
            $this->toast('У пользователя уже есть привязки, дополнительные делаются из профиля', ToastType::ERROR);
            return redirect(moonshineRouter()->to('login'))->withErrors(['username' => 'У пользователя уже есть привязки, дополнительные делаются из профиля',]);
        }

        // если пользователь не найден, создаем
        if (!$user) {
            $user = User::query()->create([
                'email' => $socialiteUser->getEmail(),
                'name' => $socialiteUser->getName(),
                'password' => bcrypt($socialiteUser->getId()),
                'avatar' => $this->saveAvatar($socialiteUser->getAvatar()),
            ]);
        }

        // авторизуем пользователя
        $this->auth()->loginUsingId($user->id);
        // привязываем аккаунт
        return $this->bindAccount($user, $socialiteUser, $driver);
    }


    protected function bindAccount(Authenticatable $user, SocialiteUser $socialUser, string $driver): RedirectResponse
    {
        $account = SocialAccount::query()
            ->where('user_id', $user->id)
            ->where('driver', $driver)
            ->first();

        if ($account instanceof SocialAccount) {
            $this->toast('Аккаунт уже привязан');
            $account->update([
                'username' => $socialUser->getNickname(),
                'email' => $socialUser->getEmail(),
                'avatar' => $socialUser->getAvatar(),
            ]);
            if (!empty($socialUser->getAvatar()) && empty($user->avatar)) {
                $user->update(['avatar' => $this->saveAvatar($socialUser->getAvatar())]);
            }
        } else {
            SocialAccount::query()->create([
                'user_id' => $user->id,
                'driver' => $driver,
                'identity' => $socialUser->getId(),
                'username' => $socialUser->getNickname(),
                'email' => $socialUser->getEmail(),
                'avatar' => $socialUser->getAvatar(),
            ]);
            if (!empty($socialUser->getEmail())) {
                $user->update(['email' => $socialUser->getEmail()]);
            }
            if (!empty($socialUser->getAvatar()) && empty($user->avatar)) {
                $user->update(['avatar' => $this->saveAvatar($socialUser->getAvatar())]);
            }

            $this->toast('Аккаунт привязан', ToastType::SUCCESS);
        }

        return toPage(moonshineConfig()->getPage('profile', ProfilePage::class), redirect: true);
    }

    private function saveAvatar(?string $avatar): ?string
    {
        if (empty($avatar)) {
            return null;
        }

        $content = file_get_contents($avatar);

        // Создаем объект finfo для определения MIME-типа
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $content);
        finfo_close($finfo);

        // Определяем расширение файла на основе MIME-типа
        $extension = match ($mimeType) {
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
            'image/tiff' => 'tiff',
            'image/svg+xml' => 'svg',
            'image/x-icon', 'image/vnd.microsoft.icon' => 'ico',
            default => 'jpg', // По умолчанию используем jpg, если тип неизвестен
        };

        $path = 'avatars/' . md5($avatar) . '.' . $extension;
        $disk = Storage::disk(moonshineConfig()->getDisk());

        if ($disk->exists($path)) {
            return $path;
        }

        $disk->put($path, $content);

        return $path;

    }


}
