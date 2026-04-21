<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'code', 'unit', 'category',
        'standard_rate', 'current_stock', 'min_stock', 'description', 'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function indentItems()
    {
        return $this->hasMany(IndentItem::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
