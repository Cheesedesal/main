<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['created_by', 'tracking_code', 'requester_name', 'email', 'student_id', 'document_type', 'purpose', 'copies', 'status', 'staff_notes', 'processed_at'])]
class DocumentRequest extends Model
{
    use SoftDeletes;
    public const STATUSES = [
        'submitted' => 'Submitted',
        'under_review' => 'Under review',
        'ready' => 'Ready for pickup',
        'released' => 'Released',
        'declined' => 'Declined',
    ];

    protected function casts(): array
    {
        return ['processed_at' => 'datetime'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(RequestStatusHistory::class)->oldest();
    }

    public function files(): HasMany
    {
        return $this->hasMany(RequestFile::class);
    }
}