<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\User; // Tambahkan import User
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum; // Tambahkan import Sanctum
use Tests\TestCase;

class MahasiswaTest extends TestCase
{
    use RefreshDatabase;

    public function test_menolak_nim_yang_tidak_valid()
    {
        // 1. Buat user dummy dan login-kan menggunakan Sanctum
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // 2. Kirim request dengan data kosong
        $response = $this->postJson('/api/mahasiswa', [
            'nim' => '', // NIM kosong, harusnya ditolak
            'nama' => 'Budi',
            'jurusan' => 'Informatika',
        ]);

        // 3. Pastikan statusnya 422 (Unprocessable Entity), bukan 401
        $response->assertStatus(422);
    }

    public function test_bisa_mengambil_data_mahasiswa()
    {
        // 1. Buat user dummy dan login-kan menggunakan Sanctum
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // 2. Buat data mahasiswa dummy
        Mahasiswa::factory()->create();

        // 3. Kirim request GET
        $response = $this->getJson('/api/mahasiswa');

        // 4. Pastikan statusnya 200 (OK)
        $response->assertStatus(200);
    }
}