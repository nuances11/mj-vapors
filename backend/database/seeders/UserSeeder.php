<?php

namespace Database\Seeders;

use App\Http\Acl;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Artisan::call("setup:role_permissions");

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Artisan::call('passport:install');

        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'sirnoel.webdev@gmail.com',
            'password' => Hash::make('Welcome@'.date('Y')),
        ]);

        $superAdminRole = Role::findByName(Acl::ROLE_SUPER_ADMIN);

        $superAdmin->syncRoles($superAdminRole);

        $admin = User::create([
            'name' => 'Admin Admin',
            'email' => 'sirnoel.webdev+admin@gmail.com',
            'password' => Hash::make('Welcome@'.date('Y')),
        ]);

        $adminRole = Role::findByName(Acl::ROLE_ADMIN);

        $admin->syncRoles($adminRole);

        $vendor = User::create([
            'name' => 'Vendor Vendor',
            'email' => 'sirnoel.webdev+vendor@gmail.com',
            'password' => Hash::make('Welcome@'.date('Y')),
        ]);

        $vendorRole = Role::findByName(Acl::ROLE_VENDOR);

        $vendor->syncRoles($vendorRole);
    }
}
