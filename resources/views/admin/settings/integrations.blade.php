@extends('layouts.admin')

@section('content')
<div class="admin-main">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Integration Settings</h1>
    </div>

    <!-- Settings Form -->
    <div class="admin-card">
        <form action="{{ route('admin.settings.integrations') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- SMS Gateway -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">SMS Gateway</h2>
                
                <div>
                    <label for="sms_provider" class="admin-label">SMS Provider</label>
                    <select name="sms_provider" id="sms_provider" class="admin-select">
                        <option value="">Select Provider</option>
                        <option value="twilio" {{ config('integrations.sms_provider') === 'twilio' ? 'selected' : '' }}>Twilio</option>
                        <option value="nexmo" {{ config('integrations.sms_provider') === 'nexmo' ? 'selected' : '' }}>Nexmo</option>
                        <option value="africas_talking" {{ config('integrations.sms_provider') === 'africas_talking' ? 'selected' : '' }}>Africa's Talking</option>
                    </select>
                </div>

                <div>
                    <label for="sms_api_key" class="admin-label">API Key</label>
                    <input type="password" 
                           name="sms_api_key" 
                           id="sms_api_key" 
                           class="admin-input" 
                           value="{{ config('integrations.sms_api_key') }}">
                </div>

                <div>
                    <label for="sms_api_secret" class="admin-label">API Secret</label>
                    <input type="password" 
                           name="sms_api_secret" 
                           id="sms_api_secret" 
                           class="admin-input" 
                           value="{{ config('integrations.sms_api_secret') }}">
                </div>
            </div>

            <!-- Email Service -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Email Service</h2>
                
                <div>
                    <label for="mail_provider" class="admin-label">Email Provider</label>
                    <select name="mail_provider" id="mail_provider" class="admin-select">
                        <option value="">Select Provider</option>
                        <option value="smtp" {{ config('integrations.mail_provider') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                        <option value="sendgrid" {{ config('integrations.mail_provider') === 'sendgrid' ? 'selected' : '' }}>SendGrid</option>
                        <option value="mailgun" {{ config('integrations.mail_provider') === 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                    </select>
                </div>

                <div>
                    <label for="mail_api_key" class="admin-label">API Key</label>
                    <input type="password" 
                           name="mail_api_key" 
                           id="mail_api_key" 
                           class="admin-input" 
                           value="{{ config('integrations.mail_api_key') }}">
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button type="submit" class="admin-btn-primary">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 