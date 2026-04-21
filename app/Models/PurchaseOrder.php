<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'project_id', 'vendor_id', 'indent_id', 'po_number',
        'po_date', 'delivery_date', 'delivery_address', 'subtotal',
        'tax_amount', 'total_amount', 'status', 'terms_and_conditions', 'created_by',
    ];

    protected $casts = [
        'po_date'       => 'date',
        'delivery_date' => 'date',
        'subtotal'      => 'decimal:2',
        'tax_amount'    => 'decimal:2',
        'total_amount'  => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function indent()
    {
        return $this->belongsTo(Indent::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function grns()
    {
        return $this->hasMany(Grn::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
