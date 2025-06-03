<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    /**
     * Show general settings.
     */
    public function general()
    {
        return view('admin.settings.general');
    }

    /**
     * Save general settings.
     */
    public function saveGeneral(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:1000',
            'contact_email' => 'required|email',
            'emergency_phone' => 'required|string|max:20',
        ]);

        $this->updateEnvFile([
            'APP_NAME' => $validated['site_name'],
            'APP_DESCRIPTION' => $validated['site_description'],
            'CONTACT_EMAIL' => $validated['contact_email'],
            'EMERGENCY_PHONE' => $validated['emergency_phone'],
        ]);

        return back()->with('success', 'General settings updated successfully.');
    }

    /**
     * Show security settings.
     */
    public function security()
    {
        return view('admin.settings.security');
    }

    /**
     * Save security settings.
     */
    public function saveSecurity(Request $request)
    {
        $validated = $request->validate([
            'two_factor_enabled' => 'boolean',
            'force_password_change' => 'boolean',
            'session_timeout' => 'required|integer|min:1',
            'single_session' => 'boolean',
        ]);

        $this->updateEnvFile([
            'TWO_FACTOR_ENABLED' => $validated['two_factor_enabled'] ? 'true' : 'false',
            'FORCE_PASSWORD_CHANGE' => $validated['force_password_change'] ? 'true' : 'false',
            'SESSION_TIMEOUT' => $validated['session_timeout'],
            'SINGLE_SESSION' => $validated['single_session'] ? 'true' : 'false',
        ]);

        return back()->with('success', 'Security settings updated successfully.');
    }

    /**
     * Show notification settings.
     */
    public function notifications()
    {
        return view('admin.settings.notifications');
    }

    /**
     * Save notification settings.
     */
    public function saveNotifications(Request $request)
    {
        $validated = $request->validate([
            'email_new_report' => 'boolean',
            'email_report_updates' => 'boolean',
            'push_enabled' => 'boolean',
            'push_severity' => 'required|in:low,medium,high,critical',
        ]);

        $this->updateEnvFile([
            'EMAIL_NEW_REPORT' => $validated['email_new_report'] ? 'true' : 'false',
            'EMAIL_REPORT_UPDATES' => $validated['email_report_updates'] ? 'true' : 'false',
            'PUSH_NOTIFICATIONS_ENABLED' => $validated['push_enabled'] ? 'true' : 'false',
            'PUSH_MIN_SEVERITY' => $validated['push_severity'],
        ]);

        return back()->with('success', 'Notification settings updated successfully.');
    }

    /**
     * Show integration settings.
     */
    public function integrations()
    {
        return view('admin.settings.integrations');
    }

    /**
     * Save integration settings.
     */
    public function saveIntegrations(Request $request)
    {
        $validated = $request->validate([
            'sms_provider' => 'nullable|string',
            'sms_api_key' => 'required_with:sms_provider|string',
            'sms_api_secret' => 'required_with:sms_provider|string',
            'mail_provider' => 'nullable|string',
            'mail_api_key' => 'required_with:mail_provider|string',
        ]);

        $this->updateEnvFile([
            'SMS_PROVIDER' => $validated['sms_provider'],
            'SMS_API_KEY' => $validated['sms_api_key'],
            'SMS_API_SECRET' => $validated['sms_api_secret'],
            'MAIL_PROVIDER' => $validated['mail_provider'],
            'MAIL_API_KEY' => $validated['mail_api_key'],
        ]);

        return back()->with('success', 'Integration settings updated successfully.');
    }

    /**
     * Show backup settings.
     */
    public function backup()
    {
        return view('admin.settings.backup');
    }

    /**
     * Save backup settings.
     */
    public function saveBackup(Request $request)
    {
        $validated = $request->validate([
            'backup_frequency' => 'required|in:daily,weekly,monthly',
            'backup_time' => 'required|date_format:H:i',
            'retention_days' => 'required|integer|min:1',
            'storage_disk' => 'required|in:local,s3,google',
            'compress_backup' => 'boolean',
        ]);

        $this->updateEnvFile([
            'BACKUP_FREQUENCY' => $validated['backup_frequency'],
            'BACKUP_TIME' => $validated['backup_time'],
            'BACKUP_RETENTION_DAYS' => $validated['retention_days'],
            'BACKUP_DISK' => $validated['storage_disk'],
            'BACKUP_COMPRESS' => $validated['compress_backup'] ? 'true' : 'false',
        ]);

        // Update backup schedule
        Artisan::call('schedule:clear-cache');

        return back()->with('success', 'Backup settings updated successfully.');
    }

    /**
     * Update environment file with new values.
     */
    private function updateEnvFile(array $values)
    {
        $envFile = base_path('.env');
        $contents = file_get_contents($envFile);

        foreach ($values as $key => $value) {
            // If value contains spaces, wrap in quotes
            if (str_contains($value, ' ')) {
                $value = '"' . $value . '"';
            }

            // Replace existing value
            if (preg_match("/^{$key}=.*/m", $contents)) {
                $contents = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $contents);
            } else {
                // Add new value
                $contents .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envFile, $contents);
    }
} 