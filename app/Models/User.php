<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'first_name',
        'last_name',
        'slug',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'address',
        'area',
        'city',
        'state',
        'country',
        'country_code',
        'zipcode',
        'latitude',
        'longitude',
        'timezone',
        'avatar',
        'bio',
        'device_token',
        'device_type',
        'company_id',
        'branch_id',
        'email_verified_at',
        'phone_verified_at',
    ];

    protected $appends = ['avatar_full_path'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getAvatarFullPathAttribute()
    {
        if($this->avatar != ''){
            return asset($this->avatar);
        }else{
            return "";
        }
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function accessibleCompanies()
    {
        return $this->belongsToMany(Company::class, 'user_companies');
    }

    public function salary()
    {
        return $this->hasOne(EmployeeSalary::class, 'user_id');
    }

    public function salaryRevisions()
    {
        return $this->hasMany(SalaryRevision::class, 'user_id');
    }

    public function incentives()
    {
        return $this->hasMany(EmployeeIncentive::class, 'user_id');
    }

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class, 'user_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    public function leaves()
    {
        return $this->hasMany(EmployeeLeave::class, 'user_id');
    }

    public function advances()
    {
        return $this->hasMany(EmployeeAdvance::class, 'user_id');
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('Super Admin') || in_array(strtolower(trim($this->role ?? '')), ['super admin', 'super_admin', 'superadmin', 'admin']);
    }

    public function isCompanyAdmin()
    {
        return $this->hasRole('Company Admin') || in_array(strtolower(trim($this->role ?? '')), ['company admin', 'company_admin']);
    }

    public function isBranchManager()
    {
        return $this->hasRole('Branch Manager') || in_array(strtolower(trim($this->role ?? '')), ['branch manager', 'branch_manager']);
    }

    public function isAdmin()
    {
        return $this->isSuperAdmin() || $this->isCompanyAdmin() || $this->isBranchManager();
    }

    public function canAccessCompany($companyId)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->company_id == $companyId) {
            return true;
        }

        return $this->accessibleCompanies()->where('companies.id', $companyId)->exists();
    }

    public function canAccessBranch($branchId)
    {
        if ($this->isSuperAdmin() || $this->isCompanyAdmin()) {
            return true;
        }

        if ($this->isBranchManager() && $this->branch_id == $branchId) {
            return true;
        }

        return false;
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByRole($query, $role)
    {
        return $query->whereHas('roles', function($q) use ($role) {
            $q->where('name', $role);
        });
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where(function($q) use ($companyId) {
            $q->where('company_id', $companyId)
              ->orWhereHas('roles', function($r) {
                  $r->where('name', 'Super Admin');
              });
        });
    }
}
