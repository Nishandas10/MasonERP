<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteLog extends Model
{
    protected $fillable = [
        'project_id', 'logged_by', 'log_date', 'work_done',
        'issues', 'labor_count', 'remarks', 'weather_conditions',
    ];

    protected $casts = [
        'log_date'           => 'date',
        'weather_conditions' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function logger()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
