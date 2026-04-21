<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'material_id', 'quantity', 'unit',
        'rate', 'tax_percent', 'received_quantity',
    ];

    protected $casts = [
        'quantity'          => 'decimal:3',
        'rate'              => 'decimal:2',
        'tax_percent'       => 'decimal:2',
        'received_quantity' => 'decimal:3',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function grnItems()
    {
        return $this->hasMany(GrnItem::class);
    }
}
