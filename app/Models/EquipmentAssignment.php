<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentAssignment extends Model
{
    protected $fillable = [
        'equipment_id', 'project_id', 'assigned_date', 'released_date', 'remarks', 'assigned_by',
    ];

    protected $casts = [
        'assigned_date'  => 'date',
        'released_date'  => 'date',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
