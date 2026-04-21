<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'company_id', 'project_id', 'laborer_id', 'date',
        'status', 'hours_worked', 'overtime_hours', 'remarks', 'marked_by',
    ];

    protected $casts = [
        'date'           => 'date',
        'hours_worked'   => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function laborer()
    {
        return $this->belongsTo(Laborer::class);
    }

    public function marker()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
