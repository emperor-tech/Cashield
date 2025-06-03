<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Log a model change to the audit log
     *
     * @param string $modelType The type of model being changed (e.g., 'report', 'user')
     * @param int $modelId The ID of the model being changed
     * @param array $changes The changes being made to the model
     * @return AuditLog|null
     */
    public static function logModelChange(string $modelType, int $modelId, array $changes)
    {
        return self::logAction(
            $modelType,
            $modelId,
            'update',
            'Model updated',
            $changes
        );
    }

    /**
     * Log an action to the audit log
     *
     * @param string $subjectType The type of subject (e.g., 'report', 'user')
     * @param int $subjectId The ID of the subject
     * @param string $action The action being performed (e.g., 'create', 'update', 'delete')
     * @param string $description A description of the action
     * @param array|null $data Additional data about the action
     * @return AuditLog|null
     */
    public static function logAction(string $subjectType, int $subjectId, string $action, string $description, ?array $data = null)
    {
        try {
            return AuditLog::create([
                'user_id' => Auth::id() ?? 1, // Use system user (ID 1) if no user is authenticated
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'action' => $action,
                'description' => $description,
                'data' => $data,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Log the error but don't throw it to avoid disrupting the main application flow
            \Illuminate\Support\Facades\Log::error("Failed to create audit log: " . $e->getMessage());
            return null;
        }
    }
}