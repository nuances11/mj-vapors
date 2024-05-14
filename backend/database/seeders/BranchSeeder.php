<?php

namespace Database\Seeders;

use App\Models\Branch;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();
        $status = ['active', 'inactive'];
        for ($i = 0; $i <= 10; $i++) {
            Branch::create([
                'name' => $faker->company,
                'status' => $status[array_rand($status)],
                'opening' => '08:00:00',
                'closing' => '21:00:00',
                'commission_threshold' => 5000,
                'commission_rate' => 1,
            ]);
        }

    }
}
