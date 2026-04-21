<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubcontractorContract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'project_id', 'subcontractor_id', 'contract_number',
        'start_date', 'end_date', 'scope_of_work', 'contract_value',
        'payment_terms', 'retention_percent', 'status', 'document_path',
    ];

    protected $casts = [
        'start_date'      => 'date',
        'end_date'        => 'date',
        'contract_value'  => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function subcontractor()
    {
        return $this->belongsTo(Subcontractor::class);
    }

    public function bills()
    {
        return $this->hasMany(SubcontractorBill::class);
    }
}
