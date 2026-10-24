<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BukuTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'judul' => 'Pemrograman Laravel',
            'penulis' => 'Budi Santoso',
            'penerbit' => 'Informatika',
            'tahun_terbit' => 2024,
            'isbn' => '9786021234567',
            'kategori' => 'Teknologi',
            'stok' => 5,
        ], $override);
    }

    public function test_tanpa_token_ditolak_401(): void
    {
        $this->getJson('/api/buku')->assertStatus(401);
        $this->postJson('/api/buku', $this->payload())->assertStatus(401);
    }

    public function test_bisa_menampilkan_semua_buku(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Buku::factory()->count(3)->create();

        $this->getJson('/api/buku')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_pencarian_menyaring_berdasarkan_judul_penulis_atau_isbn(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Buku::factory()->create([
            'judul' => 'Pemrograman Laravel', 'penulis' => 'Budi', 'isbn' => '9780000000001',
        ]);
        Buku::factory()->create([
            'judul' => 'Sejarah Nusantara', 'penulis' => 'Sari', 'isbn' => '9780000000002',
        ]);

        $this->getJson('/api/buku?search=Laravel')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.judul', 'Pemrograman Laravel');

        $this->getJson('/api/buku?search=Sari')->assertJsonCount(1, 'data');
        $this->getJson('/api/buku?search=9780000000002')->assertJsonCount(1, 'data');
        $this->getJson('/api/buku?search=tidak-ada-yang-cocok')->assertJsonCount(0, 'data');
    }

    public function test_bisa_menampilkan_satu_buku(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $buku = Buku::factory()->create();

        $this->getJson("/api/buku/{$buku->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.isbn', $buku->isbn);
    }

    public function test_bisa_menambah_buku_201(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/buku', $this->payload())
            ->assertStatus(201)
            ->assertJsonPath('data.judul', 'Pemrograman Laravel');

        $this->assertDatabaseHas('bukus', ['isbn' => '9786021234567']);
    }

    public function test_validasi_gagal_422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/buku', $this->payload(['judul' => '', 'isbn' => '']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['judul', 'isbn']);
    }

    public function test_tahun_terbit_di_masa_depan_ditolak_422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/buku', $this->payload(['tahun_terbit' => (int) date('Y') + 1]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tahun_terbit']);
    }

    public function test_isbn_harus_unik_422(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Buku::factory()->create(['isbn' => '9786021234567']);

        $this->postJson('/api/buku', $this->payload())->assertStatus(422);
    }

    public function test_bisa_mengubah_buku(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $buku = Buku::factory()->create();

        $this->putJson("/api/buku/{$buku->id}", $this->payload(['judul' => 'Judul Baru']))
            ->assertStatus(200)
            ->assertJsonPath('data.judul', 'Judul Baru');
    }

    public function test_mengubah_buku_dengan_isbn_sendiri_tidak_dianggap_duplikat(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $buku = Buku::factory()->create(['isbn' => '9786021234567']);

        $this->putJson("/api/buku/{$buku->id}", $this->payload())
            ->assertStatus(200);
    }

    public function test_bisa_mengubah_sebagian_field_dengan_patch(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $buku = Buku::factory()->create(['judul' => 'Judul Lama', 'penulis' => 'Penulis Tetap']);

        $this->patchJson("/api/buku/{$buku->id}", ['judul' => 'Judul Baru'])
            ->assertStatus(200)
            ->assertJsonPath('data.judul', 'Judul Baru')
            ->assertJsonPath('data.penulis', 'Penulis Tetap');
    }

    public function test_bisa_menghapus_buku(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $buku = Buku::factory()->create();

        $this->deleteJson("/api/buku/{$buku->id}")->assertStatus(200);
        $this->assertDatabaseMissing('bukus', ['id' => $buku->id]);
    }

    public function test_buku_tidak_ditemukan_404(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/buku/9999')->assertStatus(404);
    }
}