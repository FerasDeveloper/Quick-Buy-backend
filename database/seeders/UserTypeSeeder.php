<?php

namespace Database\Seeders;

use App\Models\Usertype;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Usertype::query()->create([
          'role' => 'Admin'
        ]);
        Usertype::query()->create([
          'role' => 'Customer'
        ]);
        Usertype::query()->create([
          'role' => 'Trader'
        ]);

    }
}
