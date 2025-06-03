<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SystemController extends Controller
{
    /**
     * Display system logs
     *
     * @return \Illuminate\View\View
     */
    public function logs()
    {
        $logFile = storage_path('logs/laravel.log');
        $logContent = '';
        
        if (File::exists($logFile)) {
            // Get the last 1000 lines of the log file
            $logContent = $this->tailFile($logFile, 1000);
        }
        
        return view('admin.system.logs', [
            'logContent' => $logContent
        ]);
    }
    
    /**
     * Display audit logs
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function audit(Request $request)
    {
        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc');
            
        // Filter by subject type if provided
        if ($request->has('subject_type') && $request->subject_type) {
            $query->where('subject_type', $request->subject_type);
        }
        
        // Filter by action if provided
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }
        
        // Filter by user if provided
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        $auditLogs = $query->paginate(20);
        
        return view('admin.system.audit', [
            'auditLogs' => $auditLogs,
            'subjectTypes' => AuditLog::distinct('subject_type')->pluck('subject_type'),
            'actions' => AuditLog::distinct('action')->pluck('action')
        ]);
    }
    
    /**
     * Display system health information
     *
     * @return \Illuminate\View\View
     */
    public function health()
    {
        $diskSpace = [
            'total' => disk_total_space('/'),
            'free' => disk_free_space('/'),
            'used' => disk_total_space('/') - disk_free_space('/')
        ];
        
        $phpInfo = [
            'version' => phpversion(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize')
        ];
        
        $databaseInfo = [
            'driver' => config('database.default'),
            'database' => config('database.connections.' . config('database.default') . '.database')
        ];
        
        return view('admin.system.health', [
            'diskSpace' => $diskSpace,
            'phpInfo' => $phpInfo,
            'databaseInfo' => $databaseInfo
        ]);
    }
    
    /**
     * Get the last n lines of a file
     *
     * @param string $filePath
     * @param int $lines
     * @return string
     */
    private function tailFile($filePath, $lines = 100)
    {
        $file = new \SplFileObject($filePath, 'r');
        $file->seek(PHP_INT_MAX);
        $lastLine = $file->key();
        
        $output = '';
        $startLine = max(0, $lastLine - $lines);
        
        $file->seek($startLine);
        
        while (!$file->eof()) {
            $output .= $file->fgets();
        }
        
        return $output;
    }
}