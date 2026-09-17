<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['document_request_id', 'changed_by', 'status', 'note'])]
class RequestStatusHistory extends Model
{
    public function request(): BelongsTo { return $this->belongsTo(DocumentRequest::class, 'document_request_id'); }
    public function changer(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}