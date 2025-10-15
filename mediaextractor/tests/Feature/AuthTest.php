<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http; // <-- 1. Thêm Http facade
use App\Models\User;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_register_successfully(): void
    {
        $userData = [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'terms_accepted' => true,
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        // SỬA: AuthController trả về 200 nên test cũng cần assert 200
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['user', 'access_token']
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    #[Test]
    public function user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login-test@example.com',
            'password_hash' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'login-test@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['access_token']]);
    }

    #[Test]
    public function user_cannot_login_with_incorrect_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        // Lỗi nghiệp vụ (sai pass) trả về 400
        $response->assertStatus(400)
            ->assertJson(['message' => 'Email hoặc mật khẩu không chính xác.']);
    }

    #[Test]
    public function authenticated_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password_hash' => Hash::make('old-password'),
        ]);

        // Giả lập user đã đăng nhập
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/auth/change-password', [
            'current_password' => 'old-password',
            'new_password' => 'NewPassword@123',
            'new_password_confirmation' => 'NewPassword@123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Đổi mật khẩu thành công!']);

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword@123', $user->password_hash));
    }

    #[Test]
    public function user_can_login_with_google_and_a_new_user_is_created(): void
    {
        // 2. Cấu hình Google Client ID tạm thời cho bài test này
        $googleClientId = 'test-google-client-id';
        config(['services.google.client_id' => $googleClientId]);

        // 3. Giả lập phản hồi thành công từ Google
        Http::fake([
            'https://www.googleapis.com/oauth2/v3/tokeninfo*' => Http::response([
                'aud' => $googleClientId, // Phải khớp với client id đã cấu hình
                'email' => 'new.user@google.com',
                'name' => 'New Google User',
                'email_verified' => true,
            ], 200)
        ]);

        // 4. Gọi đến endpoint đăng nhập Google
        $response = $this->postJson('/api/auth/google/login', [
            'token' => 'fake-google-id-token'
        ]);

        // 5. Kiểm tra kết quả
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['user', 'access_token']])
            ->assertJsonPath('data.user.email', 'new.user@google.com');

        // 6. Kiểm tra xem người dùng mới đã được tạo trong database chưa
        $this->assertDatabaseHas('users', [
            'email' => 'new.user@google.com',
            'full_name' => 'New Google User',
        ]);
    }

    #[Test]
    public function user_can_login_with_google_using_an_existing_account(): void
    {
        // 1. Tạo sẵn một người dùng trong DB
        $existingUser = User::factory()->create([
            'email' => 'existing.user@google.com',
            'full_name' => 'Existing User',
        ]);

        $googleClientId = 'test-google-client-id';
        config(['services.google.client_id' => $googleClientId]);

        Http::fake([
            'https://www.googleapis.com/oauth2/v3/tokeninfo*' => Http::response([
                'aud' => $googleClientId,
                'email' => 'existing.user@google.com', // Email của người dùng đã tồn tại
                'name' => 'Name From Google', // Tên có thể khác
            ], 200)
        ]);

        $response = $this->postJson('/api/auth/google/login', [
            'token' => 'fake-google-id-token'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['user', 'access_token']])
            ->assertJsonPath('data.user.email', 'existing.user@google.com');

        // 2. Đảm bảo không có người dùng mới nào được tạo ra
        $this->assertDatabaseCount('users', 1);
    }
}
