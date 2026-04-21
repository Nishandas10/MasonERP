<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subcontractor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'code', 'contact_person', 'phone', 'email',
        'address', 'gstin', 'pan', 'specialization',
        'bank_name', 'bank_account', 'bank_ifsc', 'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function contracts()
    {
        return $this->hasMany(SubcontractorContract::class);
    }

    public function bills()
    {
        return $this->hasMany(SubcontractorBill::class);
    }
}
