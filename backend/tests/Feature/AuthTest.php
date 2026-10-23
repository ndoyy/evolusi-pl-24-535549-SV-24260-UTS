<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_berhasil_mengembalikan_token(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'rahasia123'])
            ->assertStatus(200)
            ->assertJsonStructure(['access_token', 'token_type']);
    }

    public function test_login_password_salah_401(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'salah'])
            ->assertStatus(401);
    }

    public function test_login_validasi_gagal_422(): void
    {
        $this->postJson('/api/login', [])->assertStatus(422);
    }

    public function test_logout_menghapus_token(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);
        $token = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'rahasia123'])
            ->json('access_token');

        $this->withToken($token)->postJson('/api/logout')->assertStatus(200);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}