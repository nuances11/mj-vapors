<?php

namespace App\Console\Commands;

use App\Http\Acl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SetupRolePermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:role_permissions {truncate?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup role permission data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $isDelete = $this->argument('truncate');

        if ($isDelete){
            if ((int) $isDelete === 1) {

                foreach (Acl::roles() as $roleName) {
                    $role = Role::where('name', $roleName)->first();
                    if($role) {
                        $permissions = $role->permissions()->get();
                        foreach ($permissions as $permission) {
                            if ($role->hasPermissionTo($permission->name)) {
                                $role->revokePermissionTo($permission->name);
                            }
                        }
                    }
                }

                Permission::truncate();


            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // create roles
        foreach (Acl::roles() as $role) {
            Role::findOrCreate($role, 'web');
            Role::findOrCreate($role, 'api');
        }

        // create permissions
        foreach (Acl::permissions() as $permission) {
            Permission::findOrCreate($permission, 'web');
            Permission::findOrCreate($permission, 'api');
        }

        // create default permission per roles
        $superAdminRole = Role::findByName(Acl::ROLE_SUPER_ADMIN);
        $adminRole = Role::findByName(Acl::ROLE_ADMIN);
        $vendorRole = Role::findByName(Acl::ROLE_VENDOR);
        $branchAdminRole = Role::findByName(Acl::ROLE_BRANCH_ADMIN);

        $superAdminRole->givePermissionTo(Acl::permissions());
        $superAdminRole->givePermissionTo(Acl::menuPermissions());

        $adminRole->givePermissionTo(Acl::permissions());
        $adminRole->givePermissionTo(Acl::menuPermissions());

        $branchAdminRole->givePermissionTo(Acl::permissions());
        $branchAdminRole->givePermissionTo(Acl::menuPermissions());

        $vendorRole->givePermissionTo(Acl::PERMISSION_MANAGE_SALES);
    }
}
