<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['document_request_id', 'uploaded_by', 'disk', 'path', 'original_name', 'mime_type', 'size'])]
class RequestFile extends Model
{
    public function request(): BelongsTo { return $this->belongsTo(DocumentRequest::class, 'document_request_id'); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }
}