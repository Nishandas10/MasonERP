<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoqItem extends Model
{
    protected $fillable = [
        'project_id', 'item_code', 'description', 'unit',
        'quantity', 'rate', 'consumed_quantity', 'category',
    ];

    protected $casts = [
        'quantity'          => 'decimal:3',
        'rate'              => 'decimal:2',
        'consumed_quantity' => 'decimal:3',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function workProgress()
    {
        return $this->hasMany(WorkProgress::class);
    }

    public function getAmountAttribute(): float
    {
        return (float) $this->quantity * (float) $this->rate;
    }

    public function getRemainingQuantityAttribute(): float
    {
        return (float) $this->quantity - (float) $this->consumed_quantity;
    }
}
