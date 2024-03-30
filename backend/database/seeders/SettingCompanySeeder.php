<?php

namespace Database\Seeders;

use App\Models\SettingCompany;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SettingCompany::truncate();
        SettingCompany::insert([
            [
                'company_name' => 'My Company',
                'logo' => null,
            ],
        ]);
    }
}
