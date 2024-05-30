<?php

namespace Database\Seeders;

use App\Http\Acl;
use App\Models\User;
use App\Models\UserBranch;
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
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'user_type' => 'super_admin',
            'status' => 'active',
            'user_name' => 'super_admin',
            'email' => 'sirnoel.webdev@gmail.com',
            'password' => Hash::make('Welcome@'.date('Y')),
        ]);

        $superAdminRole = Role::findByName(Acl::ROLE_SUPER_ADMIN);

        $superAdmin->syncRoles($superAdminRole);

        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'user_type' => 'admin',
            'status' => 'active',
            'user_name' => 'admin_admin',
            'email' => 'sirnoel.webdev+admin@gmail.com',
            'password' => Hash::make('Welcome@'.date('Y')),
        ]);

        $adminRole = Role::findByName(Acl::ROLE_ADMIN);

        $admin->syncRoles($adminRole);

        $vendor = User::create([
            'first_name' => 'Vendor',
            'last_name' => 'Vendor',
            'user_type' => 'vendor',
            'status' => 'active',
            'user_name' => 'vendor',
            'email' => 'sirnoel.webdev+vendor@gmail.com',
            'password' => Hash::make('Welcome@'.date('Y')),
        ]);

        $vendorRole = Role::findByName(Acl::ROLE_VENDOR);

        $vendor->syncRoles($vendorRole);

        $branchAdmin = User::create([
            'first_name' => 'Branch',
            'last_name' => 'Admin',
            'user_type' => 'branch_admin',
            'status' => 'active',
            'user_name' => 'branch_admin',
            'email' => 'sirnoel.webdev+branch.admin@gmail.com',
            'password' => Hash::make('Welcome@'.date('Y')),
        ]);

        $branch = new UserBranch(['branch_id' => 1]);
        $branchAdmin->branch()->save($branch);

        $branchAdminRole = Role::findByName(Acl::ROLE_BRANCH_ADMIN);

        $branchAdmin->syncRoles($branchAdminRole);
    }
}
