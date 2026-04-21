<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    protected $fillable = [
        'equipment_id', 'maintenance_date', 'type', 'description',
        'cost', 'done_by', 'next_maintenance_date', 'status',
    ];

    protected $casts = [
        'maintenance_date'      => 'date',
        'next_maintenance_date' => 'date',
        'cost'                  => 'decimal:2',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
