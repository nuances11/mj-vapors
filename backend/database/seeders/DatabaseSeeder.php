<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\SettingUser;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(BranchSeeder::class);
        $this->call(UserSeeder::class);

        $this->call(ProductSeeder::class);
        $this->call(AttributeSeeder::class);
        $this->call(SettingCompanySeeder::class);
        $this->call(SettingUserSeeder::class);

    }
}
