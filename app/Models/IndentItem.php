<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndentItem extends Model
{
    protected $fillable = [
        'indent_id', 'material_id', 'quantity', 'unit',
        'specifications', 'ordered_quantity',
    ];

    protected $casts = [
        'quantity'         => 'decimal:3',
        'ordered_quantity' => 'decimal:3',
    ];

    public function indent()
    {
        return $this->belongsTo(Indent::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
