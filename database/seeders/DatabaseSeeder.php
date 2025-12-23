<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
          UserTypeSeeder::class
        ]);
        $this->call([
          AdminSeeder::class
        ]);
        $this->call([
          DomainSeeder::class
        ]);
        $this->call([
          OfferSeeder::class
        ]);
    }
}
