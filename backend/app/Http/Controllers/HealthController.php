<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class HealthController extends Controller
{
    /** GET /health: 200 bila basis data dan cache sehat, 503 bila ada yang bermasalah. */
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->periksaDatabase(),
            'cache' => $this->periksaCache(),
        ];

        $sehat = collect($checks)->every(fn (array $c) => $c['status'] === 'up');

        return response()->json([
            'status' => $sehat ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $sehat ? 200 : 503);
    }

    /** Benar-benar menjalankan kueri ke basis data, bukan sekadar membalas teks. */
    private function periksaDatabase(): array
    {
        try {
            DB::select('select 1');

            return ['status' => 'up'];
        } catch (Throwable $e) {
            Log::error('Health check gagal: basis data tidak dapat dihubungi', [
                'komponen' => 'database',
                'aksi' => 'health_check',
                // Pesan PDO (penyebab asli) tanpa SQL maupun parameter; tidak memuat password.
                'sebab' => $e->getPrevious()?->getMessage() ?? $e->getMessage(),
            ]);

            return ['status' => 'down', 'pesan' => 'Basis data tidak dapat dihubungi.'];
        }
    }

    /** Menulis lalu membaca kembali satu kunci di cache. */
    private function periksaCache(): array
    {
        $kunci = 'health:ping';

        try {
            Cache::put($kunci, 'ok', 10);

            if (Cache::get($kunci) !== 'ok') {
                throw new RuntimeException('Nilai cache tidak terbaca kembali setelah ditulis.');
            }

            Cache::forget($kunci);

            return ['status' => 'up'];
        } catch (Throwable $e) {
            Log::error('Health check gagal: cache tidak dapat dibaca atau ditulis', [
                'komponen' => 'cache',
                'aksi' => 'health_check',
                'sebab' => $e->getMessage(),
            ]);

            return ['status' => 'down', 'pesan' => 'Cache tidak dapat dibaca atau ditulis.'];
        }
    }
}