<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Log user activity, filtering out GET/view requests.
     */
    protected function audit(string $action, string $subjectType = 'App\\Models\\User', ?int $subjectId = null, array $details = []): void
    {
        // Only log mutating requests: POST, PUT, PATCH, DELETE
        if (request()->isMethod('GET') || str_starts_with($action, 'viewed_')) {
            return;
        }

        if (auth()->check()) {
            \App\Models\ActivityRecord::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId ?? auth()->id(),
                'details' => $details,
            ]);
        }
    }
}
