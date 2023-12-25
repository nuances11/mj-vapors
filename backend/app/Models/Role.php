<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Contracts\Role as RoleContract;

/**
 * Class Role
 *
 * @property Permission[] $permissions
 * @property string $name
 * @package App\Http\Models
 */
class Role extends \Spatie\Permission\Models\Role
{
    public $guard_name = 'api';

    public static function findOrCreate(string $name, $guardName = null, $description = null): RoleContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $role = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (! $role) {
            $data = ['name' => $name, 'guard_name' => $guardName];
            if($description){
                $data['description'] = $description;
            }

            return static::query()->create($data);
        }

        return $role;
    }
}
