<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Indent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'project_id', 'indent_number', 'indent_date',
        'required_by_date', 'status', 'requested_by', 'approved_by',
        'approved_at', 'remarks', 'rejection_reason',
    ];

    protected $casts = [
        'indent_date'      => 'date',
        'required_by_date' => 'date',
        'approved_at'      => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(IndentItem::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
