<?php

namespace Database\Seeders;

use App\Models\Domain;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Domain::query()->create([
          'name' => 'Food'
        ]);
        Domain::query()->create([
          'name' => 'Clothes'
        ]);
        Domain::query()->create([
          'name' => 'Electronic Devices'
        ]);

    }
}
