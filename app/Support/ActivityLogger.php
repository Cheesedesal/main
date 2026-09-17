<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function record(string $action, string $description, ?Model $subject = null, ?Request $request = null): ActivityLog
    {
        $authenticatedUser = Auth::user();

        return ActivityLog::create([
            'user_id' => $authenticatedUser instanceof User ? $authenticatedUser->getKey() : null,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'ip_address' => $request?->ip(),
        ]);
    }
}