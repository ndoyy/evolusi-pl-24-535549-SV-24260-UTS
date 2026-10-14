<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MahasiswaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nim' => $this->faker->unique()->numerify('24-########'),
            'nama' => $this->faker->name(),
            'jurusan' => $this->faker->randomElement(['Informatika', 'Sistem Informasi', 'Teknik Komputer']),
        ];
    }
}