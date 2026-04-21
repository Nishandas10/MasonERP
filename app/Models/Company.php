<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'email', 'phone', 'address', 'city',
        'state', 'country', 'pincode', 'gstin', 'pan', 'logo', 'status',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }

    public function subcontractors()
    {
        return $this->hasMany(Subcontractor::class);
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }
}
