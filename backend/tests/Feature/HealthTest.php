<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDOException;
use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_200_tanpa_token_saat_basis_data_dan_cache_sehat(): void
    {
        $this->getJson('/health')
            ->assertStatus(200)
            ->assertJsonPath('status', 'healthy')
            ->assertJsonPath('checks.database.status', 'up')
            ->assertJsonPath('checks.cache.status', 'up');
    }

    public function test_health_503_dan_menyebut_basis_data_saat_koneksi_gagal(): void
    {
        Log::spy();
        DB::shouldReceive('select')
            ->once()
            ->andThrow(new PDOException('SQLSTATE[HY000] [2002] Connection refused'));

        $this->getJson('/health')
            ->assertStatus(503)
            ->assertJsonPath('status', 'unhealthy')
            ->assertJsonPath('checks.database.status', 'down')
            ->assertJsonPath('checks.cache.status', 'up');

        // Sebab kegagalan harus tercatat di log
        Log::shouldHaveReceived('error')->withArgs(function ($pesan, $konteks) {
            return str_contains($pesan, 'basis data')
                && $konteks['komponen'] === 'database'
                && str_contains($konteks['sebab'], 'Connection refused');
        })->once();
    }
}