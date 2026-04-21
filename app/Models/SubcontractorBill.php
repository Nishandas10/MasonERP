<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubcontractorBill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'subcontractor_contract_id', 'subcontractor_id',
        'bill_number', 'bill_date', 'description', 'gross_amount',
        'retention_amount', 'tax_deducted', 'other_deductions', 'net_payable',
        'paid_amount', 'status', 'payment_date', 'payment_reference', 'approved_by',
    ];

    protected $casts = [
        'bill_date'        => 'date',
        'payment_date'     => 'date',
        'gross_amount'     => 'decimal:2',
        'retention_amount' => 'decimal:2',
        'tax_deducted'     => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'net_payable'      => 'decimal:2',
        'paid_amount'      => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function contract()
    {
        return $this->belongsTo(SubcontractorContract::class, 'subcontractor_contract_id');
    }

    public function subcontractor()
    {
        return $this->belongsTo(Subcontractor::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getBalanceDueAttribute(): float
    {
        return (float) $this->net_payable - (float) $this->paid_amount;
    }
}
