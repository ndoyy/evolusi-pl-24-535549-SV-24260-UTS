<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LogTerstrukturTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_berhasil_dicatat_tanpa_password_email_dan_token(): void
    {
        Log::spy();
        $user = User::factory()->create(['password' => 'rahasia123']);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'rahasia123'])
            ->assertStatus(200);

        Log::shouldHaveReceived('info')->withArgs(function ($pesan, $konteks) use ($user) {
            return $pesan === 'Login berhasil'
                && (int) $konteks['user_id'] === $user->id
                && ! str_contains(json_encode($konteks), 'rahasia123')
                && ! str_contains(json_encode($konteks), $user->email)
                && ! array_key_exists('access_token', $konteks);
        })->once();
    }

    public function test_login_gagal_dicatat_sebagai_warning_tanpa_data_pribadi(): void
    {
        Log::spy();
        $user = User::factory()->create();

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'salah-total'])
            ->assertStatus(401);

        Log::shouldHaveReceived('warning')->withArgs(function ($pesan, $konteks) use ($user) {
            return $pesan === 'Login gagal'
                && array_key_exists('sebab', $konteks)
                && ! str_contains(json_encode($konteks), 'salah-total')
                && ! str_contains(json_encode($konteks), $user->email);
        })->once();
    }

    public function test_validasi_gagal_dicatat_nama_field_saja_bukan_nilainya(): void
    {
        Log::spy();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/buku', ['judul' => '', 'isbn' => '', 'deskripsi' => 'NILAI-RAHASIA-123'])
            ->assertStatus(422);

        Log::shouldHaveReceived('warning')->withArgs(function ($pesan, $konteks) use ($user) {
            return $pesan === 'Validasi permintaan gagal'
                && (int) $konteks['user_id'] === $user->id
                && in_array('judul', $konteks['field_bermasalah'], true)
                && in_array('isbn', $konteks['field_bermasalah'], true)
                && ! str_contains(json_encode($konteks), 'NILAI-RAHASIA-123');
        })->once();
    }

    public function test_penambahan_buku_dicatat_dengan_siapa_dan_apa(): void
    {
        Log::spy();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/buku', [
            'judul' => 'Pemrograman Laravel',
            'penulis' => 'Budi Santoso',
            'tahun_terbit' => 2024,
            'isbn' => '9786021234567',
        ])->assertStatus(201);

        Log::shouldHaveReceived('info')->withArgs(function ($pesan, $konteks) use ($user) {
            return $pesan === 'Buku ditambahkan'
                && (int) $konteks['user_id'] === $user->id
                && $konteks['aksi'] === 'buku.tambah'
                && isset($konteks['buku_id']);
        })->once();
    }
}