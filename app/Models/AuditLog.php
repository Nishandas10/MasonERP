<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'event', 'auditable_type',
        'auditable_id', 'old_values', 'new_values', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    public static function log(string $event, Model $model, array $oldValues = [], array $newValues = []): void
    {
        $user = Auth::user();
        static::create([
            'company_id'      => $user?->company_id ?? $model->company_id ?? null,
            'user_id'         => $user?->id,
            'event'           => $event,
            'auditable_type'  => get_class($model),
            'auditable_id'    => $model->getKey(),
            'old_values'      => $oldValues,
            'new_values'      => $newValues,
            'ip_address'      => request()->ip(),
            'user_agent'      => request()->userAgent(),
        ]);
    }
}
