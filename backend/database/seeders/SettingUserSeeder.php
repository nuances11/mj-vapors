<?php

namespace Database\Seeders;

use App\Models\SettingUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SettingUser::truncate();
        SettingUser::insert([
            [
                'base_salary' => 400,
                'commission_threshold' => 1000,
                'commission_rate' => 1,
                'additional_salary' => 10,
            ],
        ]);
    }
}
