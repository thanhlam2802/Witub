<?php

namespace App\Services;

use App\Mail\WelcomeEmail;
use App\Services\MailService;
use Illuminate\Support\Facades\Http;
use App\Exceptions\BusinessException;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AuthService extends BaseService
{
    protected UserRepository $userRepository;
    protected MailService $mailService;

    public function __construct(UserRepository $userRepository, MailService $mailService)
    {
        $this->userRepository = $userRepository;
        $this->mailService = $mailService;
    }

    public function register(array $data): array
    {
        $data['password_hash'] = Hash::make($data['password']);
        unset($data['password']);

        $user = $this->userRepository->create($data);
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->sendEmailVerificationNotification();



        return ['user' => $user, 'token' => $token];
    }



    public function login(array $credentials): ?array
    {
        $user = $this->userRepository->findByEmail($credentials['email']);


        if ($user && !$user->isActive()) {
            throw new BusinessException('Tài khoản của bạn đã bị vô hiệu hóa.');
        }


        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;
            return ['token' => $token, 'user' => $user];
        }

        return null;
    }

    public function logout(): void
    {
        Auth::user()->tokens()->delete();
    }


    public function webLogout(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }


    public function changePassword(array $data): bool
    {
        $user = Auth::user();
        return (bool) $this->userRepository->update($user->user_id, [
            'password_hash' => Hash::make($data['new_password'])
        ]);
    }

    public function sendPasswordResetLink(array $data): string
    {
        $broker = Password::broker();
        return $broker->sendResetLink($data);
    }

    public function resetPassword(array $data): string
    {
        $broker = Password::broker();

        $status = $broker->reset($data, function ($user, $password) {
            $user->forceFill([
                'password_hash' => Hash::make($password)
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        });

        return $status;
    }

    public function handleGoogleCallback(): User
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();


            $user = $this->findOrCreateUserFromSocialite($googleUser);

            Auth::login($user, true);

            return $user;
        } catch (\Exception $e) {

            if ($e instanceof BusinessException) {
                throw $e;
            }
            throw new BusinessException('Xác thực với Google thất bại, vui lòng thử lại.');
        }
    }



    public function handleGoogleLogin(string $googleIdToken): array
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($googleIdToken);


            $user = $this->findOrCreateUserFromSocialite($googleUser);

            $token = $user->createToken('auth_token')->plainTextToken;

            return ['user' => $user, 'token' => $token];
        } catch (\Exception $e) {
            if ($e instanceof BusinessException) {
                throw $e;
            }
            throw new BusinessException('Token Google không hợp lệ hoặc đã hết hạn.');
        }
    }

    private function findOrCreateUserFromSocialite(SocialiteUser $socialiteUser): User
    {

        $user = User::where('email', $socialiteUser->getEmail())->first();


        if ($user && !$user->isActive()) {
            throw new BusinessException('Tài khoản của bạn đã bị vô hiệu hóa.');
        }
        $user = User::updateOrCreate(
            ['email' => $socialiteUser->getEmail()],
            [
                'full_name' => $socialiteUser->getName(),
                'avatar'    => $socialiteUser->getAvatar(),
                'google_id' => $socialiteUser->getId(),
                'email_verified_at' => now(),
                'password_hash' => Hash::make(Str::random(24))
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->mailService->send($user->email, new WelcomeEmail($user));
        }
        return $user;
    }
}
