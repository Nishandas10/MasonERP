<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkProgress extends Model
{
    protected $fillable = [
        'project_id', 'boq_item_id', 'date', 'quantity_done', 'logged_by', 'remarks',
    ];

    protected $casts = [
        'date'          => 'date',
        'quantity_done' => 'decimal:3',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function boqItem()
    {
        return $this->belongsTo(BoqItem::class);
    }

    public function logger()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
