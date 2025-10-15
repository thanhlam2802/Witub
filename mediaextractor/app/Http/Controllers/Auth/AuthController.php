<?php

namespace App\Http\Controllers\Auth;

use App\Repositories\UserRepository;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Http\Traits\ApiResponse;
use App\Exceptions\BusinessException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Laravel\Socialite\Facades\Socialite;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\ForgotPasswordRequest;
use App\Http\Requests\User\ResetPasswordRequest;

class AuthController extends Controller
{
    use ApiResponse;
    protected $userRepository;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    public function showLoginForm()
    {
        return view('auth.login');
    }
    public function showRegistrationForm()
    {

        return view('auth.register');
    }
    public function handleRegistration(RegisterRequest $request): RedirectResponse
    {

        $result = $this->authService->register($request->validated());
        $user = $result['user'];

        Auth::login($user);
        return redirect()->route('home', ['locale' => session('locale', 'vi')])
            ->with('success', 'Đăng ký tài khoản thành công! Chào mừng bạn.');
    }

    public function handleLogin(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();


            if (in_array($user->role, ['admin', 'poster'])) {

                return redirect()->route('admin.dashboard')->with('success', 'Đăng nhập thành công!');
            }

            return redirect()->route('studio.index')->with('success', 'Đăng nhập thành công!');
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }


    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }



    public function handleGoogleCallback()
    {
        try {
            $user = $this->authService->handleGoogleCallback();

            // Kiểm tra vai trò và chuyển hướng đến trang quản lý
            if (in_array($user->role, ['admin', 'poster'])) {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Đăng nhập bằng Google thành công! Chào mừng ' . $user->full_name);
            }


            return redirect()->route('studio.index')
                ->with('success', 'Đăng nhập bằng Google thành công! Chào mừng ' . $user->full_name);
        } catch (BusinessException $e) {

            return redirect()->route('auth.login.view')->with('error', $e->getMessage());
        }
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        $data = [
            'user' => $result['user'],
            'access_token' => $result['token'],
        ];

        return $this->success($data, 'Đăng ký thành công!');
    }



    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (!$result) {
            throw new BusinessException('Email hoặc mật khẩu không chính xác.');
        }

        $user = $result['user'];
        $token = $result['token'];


        $redirectPath = ($user->role === 'admin') ? '/admin/dashboard' : '/home';

        $data = [
            'access_token' => $token,
            'user' => $user,
            'redirect_path' => $redirectPath,
        ];

        return $this->success($data, 'Đăng nhập thành công!');
    }

    public function loginWithGoogle(Request $request): JsonResponse
    {

        $request->validate([
            'token' => 'required|string',
        ]);

        $result = $this->authService->handleGoogleLogin($request->input('token'));

        $data = [
            'user' => $result['user'],
            'access_token' => $result['token'],
        ];

        return $this->success($data, 'Đăng nhập bằng Google thành công!');
    }




    public function logout(Request $request)
    {

        $this->authService->webLogout($request);

        return redirect('/auth/login');
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword($request->validated());
        return $this->success(null, 'Đổi mật khẩu thành công!');
    }

    public function webChangePassword(array $data): bool
    {
        $user = Auth::user();


        if (!Hash::check($data['current_password'], $user->password_hash)) {
            return false;
        }
        return (bool) $this->userRepository->update($user->user_id, [
            'password_hash' => Hash::make($data['new_password'])
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->sendPasswordResetLink($request->validated());

        if ($status === Password::RESET_LINK_SENT) {
            return $this->success(null, 'Link đặt lại mật khẩu đã được gửi vào email của bạn!');
        }

        throw new BusinessException('Không thể gửi link. Vui lòng thử lại.');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->resetPassword($request->validated());

        if ($status === Password::PASSWORD_RESET) {
            return $this->success(null, 'Mật khẩu đã được đặt lại thành công!');
        }

        throw new BusinessException('Token không hợp lệ hoặc đã hết hạn.');
    }
}
