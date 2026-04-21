<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'code', 'description', 'client_name',
        'client_contact', 'location', 'start_date', 'end_date',
        'contract_value', 'budget', 'status', 'progress_percent', 'created_by',
    ];

    protected $casts = [
        'start_date'     => 'date',
        'end_date'       => 'date',
        'contract_value' => 'decimal:2',
        'budget'         => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    public function boqItems()
    {
        return $this->hasMany(BoqItem::class);
    }

    public function siteLogs()
    {
        return $this->hasMany(SiteLog::class);
    }

    public function workProgress()
    {
        return $this->hasMany(WorkProgress::class);
    }

    public function indents()
    {
        return $this->hasMany(Indent::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function subcontractorContracts()
    {
        return $this->hasMany(SubcontractorContract::class);
    }

    public function equipmentAssignments()
    {
        return $this->hasMany(EquipmentAssignment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
