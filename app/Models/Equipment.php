<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipment';

    protected $fillable = [
        'company_id', 'name', 'code', 'type', 'make', 'model',
        'registration_number', 'purchase_date', 'purchase_value',
        'ownership', 'rental_rate_per_day', 'status',
    ];

    protected $casts = [
        'purchase_date'       => 'date',
        'purchase_value'      => 'decimal:2',
        'rental_rate_per_day' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function assignments()
    {
        return $this->hasMany(EquipmentAssignment::class);
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function currentAssignment()
    {
        return $this->hasOne(EquipmentAssignment::class)->whereNull('released_date');
    }
}
