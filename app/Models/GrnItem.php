<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnItem extends Model
{
    protected $fillable = [
        'grn_id', 'purchase_order_item_id', 'material_id',
        'received_quantity', 'accepted_quantity', 'rejected_quantity', 'rejection_reason',
    ];

    protected $casts = [
        'received_quantity' => 'decimal:3',
        'accepted_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
    ];

    public function grn()
    {
        return $this->belongsTo(Grn::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
