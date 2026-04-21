<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laborer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'code', 'phone', 'trade',
        'daily_rate', 'aadhaar', 'emergency_contact', 'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }
}
