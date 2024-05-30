<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements Auditable
{
    use HasApiTokens, HasRoles, Notifiable, SoftDeletes, \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'user_type',
        'user_name',
        'status'
    ];

    protected $appends = ['full_name', 'branch_id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $with = [
        'branch'
    ];

    /**
     * Get the phone associated with the user.
     */
    public function commission(): HasOne
    {
        return $this->hasOne(UserCommission::class);
    }

    public function branch(): HasOne
    {
        return $this->hasOne(UserBranch::class);
    }

    protected function getDefaultGuardName(): string { return 'api'; }

    public function scopeSearch($query, $keyword)
    {
        $query->when($keyword !== '', function ($query) use ($keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->whereRaw("first_name LIKE '%$keyword%'")
                    ->orWhereRaw("last_name LIKE '%$keyword%'");
            });
        });
    }

    public function scopeFilter($query, $filters)
    {
        foreach ($filters as $filter_name => $filter_value) {
            $query->where($filter_name, $filter_value);
        }
    }

    public function getFullNameAttribute()
    {

        return $this->first_name . ' ' . $this->last_name;

    }

    public function getBranchIdAttribute()
    {

        return $this->branch->branch_id ?? null;

    }

    public function getBranchNameAttribute()
    {

        return $this->branch->branch_id ?? null;

    }

    public function areVendorUsers()
    {
        return $this->where('user_type', 'vendor')->get();
    }

    public function areAdminUsers()
    {
        return $this->where('user_type', 'admin')->get();
    }
}
