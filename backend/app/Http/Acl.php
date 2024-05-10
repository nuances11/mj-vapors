<?php
namespace App\Http;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Acl
{
    const ROLE_SUPER_ADMIN = 'superadmin';
    const ROLE_ADMIN = 'admin';
    const ROLE_VENDOR = 'vendor';
    const ROLE_BRANCH_ADMIN = 'branch_admin';


    const PERMISSION_MANAGE_USER = 'manage user';
    const PERMISSION_PERMISSION_MANAGE = 'manage permission';
    const PERMISSION_PRODUCT_MANAGE = 'manage product';
    const PERMISSION_MANAGE_SALES = 'manage sales';

    const PERMISSION_USER_ADD = 'add user';
    const PERMISSION_USER_UPDATE = 'update user';
    const PERMISSION_USER_DELETE = 'delete user';
    const PERMISSION_USER_RESTORE = 'restore user';

    const PERMISSION_VIEW_MENU_USERS = 'view menu users';
    const PERMISSION_VIEW_MENU_PRODUCTS = 'view menu products';

    /**
     * @param array $exclusives Exclude some permissions from the list
     * @return array
     */
    public static function permissions(array $exclusives = []): array
    {
        try {
            $class = new \ReflectionClass(__CLASS__);
            $constants = $class->getConstants();
            $permissions = Arr::where($constants, function ($value, $key) use ($exclusives) {
                return !in_array($value, $exclusives) && Str::startsWith($key, 'PERMISSION_');
            });

            return array_values($permissions);
        } catch (\ReflectionException $exception) {
            return [];
        }
    }

    public static function menuPermissions(): array
    {
        try {
            $class = new \ReflectionClass(__CLASS__);
            $constants = $class->getConstants();
            $permissions = Arr::where($constants, function ($value, $key) {
                return Str::startsWith($key, 'PERMISSION_VIEW_MENU_');
            });

            return array_values($permissions);
        } catch (\ReflectionException $exception) {
            return [];
        }
    }

    /**
     * @return array
     */
    public static function roles(): array
    {
        try {
            $class = new \ReflectionClass(__CLASS__);
            $constants = $class->getConstants();
            $roles =  Arr::where($constants, function ($value, $key) {
                return Str::startsWith($key, 'ROLE_');
            });

            return array_values($roles);
        } catch (\ReflectionException $exception) {
            return [];
        }
    }
}
