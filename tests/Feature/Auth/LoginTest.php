<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_correct_credentials_even_when_an_old_session_exists(): void
    {
        config(['session.driver' => 'database']);

        $admin = User::factory()->create([
            'email' => 'admin@webanhang.com',
            'password' => '12345678',
            'is_admin' => true,
        ]);
        $this->createOldSessionFor($admin);

        $this->post('/dang-nhap', [
            'email' => 'admin@webanhang.com',
            'password' => '12345678',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseMissing('sessions', ['id' => 'old-session-id']);
    }

    public function test_wrong_password_does_not_login(): void
    {
        User::factory()->create([
            'email' => 'admin@webanhang.com',
            'password' => '12345678',
            'is_admin' => true,
        ]);

        $this->from('/dang-nhap')->post('/dang-nhap', [
            'email' => 'admin@webanhang.com',
            'password' => 'sai_password',
        ])->assertRedirect('/dang-nhap')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_unknown_email_does_not_login(): void
    {
        $this->from('/dang-nhap')->post('/dang-nhap', [
            'email' => 'khong-tontai@example.com',
            'password' => '12345678',
        ])->assertRedirect('/dang-nhap')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_regular_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'user@webanhang.com',
            'password' => '12345678',
            'is_admin' => false,
        ]);

        $this->post('/dang-nhap', [
            'email' => 'user@webanhang.com',
            'password' => '12345678',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_access_dashboard_after_login(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@webanhang.com',
            'password' => '12345678',
            'is_admin' => true,
        ]);

        $this->post('/dang-nhap', [
            'email' => 'admin@webanhang.com',
            'password' => '12345678',
        ]);

        $this->get('/admin')->assertOk();
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $this->get('/admin')->assertRedirect('/dang-nhap');
    }

    public function test_logout_invalidates_session_and_user_can_login_again(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@webanhang.com',
            'password' => '12345678',
            'is_admin' => true,
        ]);

        $this->post('/dang-nhap', [
            'email' => 'admin@webanhang.com',
            'password' => '12345678',
        ]);
        $this->assertAuthenticatedAs($admin);

        $this->post('/dang-xuat')->assertRedirect('/');
        $this->assertGuest();

        $this->post('/dang-nhap', [
            'email' => 'admin@webanhang.com',
            'password' => '12345678',
        ])->assertRedirect('/');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_api_login_is_not_blocked_by_an_existing_web_session(): void
    {
        $user = User::factory()->create([
            'email' => 'api-user@example.com',
            'password' => '12345678',
        ]);
        $this->createOldSessionFor($user);

        $this->postJson('/api/login', [
            'email' => 'API-USER@example.com',
            'password' => '12345678',
        ])->assertOk()
            ->assertJsonPath('message', 'Đăng nhập thành công')
            ->assertJsonPath('user.email', 'api-user@example.com')
            ->assertJsonStructure(['token']);
    }

    private function createOldSessionFor(User $user): void
    {
        DB::table('sessions')->insert([
            'id' => 'old-session-id',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Feature test',
            'payload' => '',
            'last_activity' => now()->getTimestamp(),
        ]);
    }
}
