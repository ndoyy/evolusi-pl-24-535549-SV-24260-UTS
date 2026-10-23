<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BukuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'judul' => $this->faker->sentence(3),
            'penulis' => $this->faker->name(),
            'penerbit' => $this->faker->company(),
            'tahun_terbit' => $this->faker->numberBetween(1990, 2025),
            'isbn' => $this->faker->unique()->numerify('978##########'),
            'kategori' => $this->faker->randomElement(['Fiksi', 'Sains', 'Teknologi', 'Sejarah']),
            'stok' => $this->faker->numberBetween(0, 20),
            'deskripsi' => $this->faker->paragraph(),
        ];
    }
}