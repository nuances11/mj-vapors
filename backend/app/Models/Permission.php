<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Guard;

/**
 * Class Permission
 *
 * @package App\Http\Models
 */
class Permission extends \Spatie\Permission\Models\Permission
{
    public $guard_name = 'api';

    protected $fillable = [
        'name',
        'id',
        'guard_name',
        'group',
        'created_at',
        'updated_at'
    ];

    public static function findOrCreate(string $name, $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);
        $permission = static::getPermissions(['name' => $name, 'guard_name' => $guardName])->first();

        if (! $permission) {
            return static::query()->create(['name' => $name, 'guard_name' => $guardName]);
        }

        return $permission;
    }
}
