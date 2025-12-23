<?php

namespace Database\Seeders;

use App\Models\Offer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Offer::query()->create([
          'name' => 'Small offer',
          'price' => '100',
          'amount' => '10',
        ]);

        Offer::query()->create([
          'name' => 'Medium offer',
          'price' => '450',
          'amount' => '50',
        ]);

        Offer::query()->create([
          'name' => 'Big offer',
          'price' => '1000',
          'amount' => '120',
        ]);

        Offer::query()->create([
          'name' => 'No offer',
          'price' => '0',
          'amount' => '0',
        ]);

    }
}
