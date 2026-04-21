<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    protected $fillable = [
        'project_id', 'name', 'description', 'due_date',
        'completed_date', 'status', 'progress_percent',
    ];

    protected $casts = [
        'due_date'       => 'date',
        'completed_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
