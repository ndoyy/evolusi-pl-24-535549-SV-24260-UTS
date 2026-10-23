<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun untuk login. Ganti password lewat environment SEED_PASSWORD.
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin Arsip', 'password' => env('SEED_PASSWORD', 'password')]
        );

        if (Buku::count() === 0) {
            Buku::create([
                'judul' => 'Laskar Pelangi', 'penulis' => 'Andrea Hirata',
                'penerbit' => 'Bentang Pustaka', 'tahun_terbit' => 2005,
                'isbn' => '9789793062792', 'kategori' => 'Fiksi', 'stok' => 3,
                'deskripsi' => 'Novel tentang persahabatan anak-anak Belitung.',
            ]);
            Buku::create([
                'judul' => 'Clean Code', 'penulis' => 'Robert C. Martin',
                'penerbit' => 'Prentice Hall', 'tahun_terbit' => 2008,
                'isbn' => '9780132350884', 'kategori' => 'Teknologi', 'stok' => 2,
                'deskripsi' => 'Panduan menulis kode yang bersih dan mudah dirawat.',
            ]);
        }
    }
}