@extends('layouts.app')

@section('content')
<h1 class="text-2xl mb-4">Admin Dashboard</h1>
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-blue-100 p-4 rounded shadow">
        <div class="text-3xl font-bold">{{ $stats['total_reports'] }}</div>
        <div>Total Reports</div>
    </div>
    <div class="bg-green-100 p-4 rounded shadow">
        <div class="text-3xl font-bold">{{ $stats['total_users'] }}</div>
        <div>Total Users</div>
    </div>
    <div class="bg-red-100 p-4 rounded shadow">
        <div class="text-3xl font-bold">{{ $stats['panic_alerts'] }}</div>
        <div>Panic Alerts</div>
    </div>
    <div class="bg-yellow-100 p-4 rounded shadow">
        <div class="text-3xl font-bold">{{ $stats['today_reports'] }}</div>
        <div>Reports Today</div>
    </div>
</div>
<div class="mb-4 flex gap-2">
    <a href="{{ route('admin.reports.export.csv') }}" class="bg-green-600 text-white px-4 py-2 rounded">Export CSV</a>
    <a href="{{ route('admin.reports.export.pdf') }}" class="bg-blue-600 text-white px-4 py-2 rounded">Export PDF</a>
</div>
<h2 class="text-xl font-semibold mb-2">All Reports</h2>
<div class="overflow-x-auto mb-8">
<table class="w-full bg-white dark:bg-gray-800 shadow rounded" aria-label="All Reports Table">
    <thead>
        <tr class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            <th class="p-2">Campus</th>
            <th class="p-2">Location</th>
            <th class="p-2">Severity</th>
            <th class="p-2">Date</th>
            <th class="p-2">User</th>
            <th class="p-2">Status</th>
        </tr>
    </thead>
    <tbody>
    @foreach($reports as $r)
        <tr class="border-t @if($r->severity=='high') bg-red-50 dark:bg-red-900 @endif border-gray-200 dark:border-gray-700">
            <td class="p-2">{{ $r->campus }}</td>
            <td class="p-2">{{ $r->location }}</td>
            <td class="p-2 capitalize @if($r->severity=='high')text-red-600 @elseif($r->severity=='medium')text-yellow-600 @else text-green-600 @endif">{{ $r->severity }}</td>
            <td class="p-2">{{ $r->created_at->format('M d, Y H:i') }}</td>
            <td class="p-2"><a href="{{ route('admin.report.show', $r->id) }}" class="text-blue-700 underline">@if($r->user){{ $r->user->name }}@else Guest @endif</a></td>
            <td class="p-2">
                <form method="POST" action="{{ route('admin.report.status', $r->id) }}">
                    @csrf
                    @method('PATCH')
                    <select name="status" onchange="this.form.submit()" class="border rounded p-1">
                        <option value="open" @if($r->status=='open')selected @endif>Open</option>
                        <option value="in_progress" @if($r->status=='in_progress')selected @endif>In Progress</option>
                        <option value="resolved" @if($r->status=='resolved')selected @endif>Resolved</option>
                    </select>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
<h2 class="text-xl font-semibold mb-2">All Users</h2>
<div class="overflow-x-auto">
<table class="w-full bg-white dark:bg-gray-800 shadow rounded" aria-label="All Users Table">
    <thead>
        <tr class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            <th class="p-2">Name</th>
            <th class="p-2">Email</th>
            <th class="p-2">Role</th>
            <th class="p-2">Joined</th>
        </tr>
    </thead>
    <tbody>
    @foreach($users as $u)
        <tr class="border-t border-gray-200 dark:border-gray-700">
            <td class="p-2">{{ $u->name }}</td>
            <td class="p-2">{{ $u->email }}</td>
            <td class="p-2 capitalize">{{ $u->role }}</td>
            <td class="p-2">{{ $u->created_at->format('M d, Y') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
<h2 class="text-xl font-semibold mb-2 mt-8">Recent Admin Actions (Audit Log)</h2>
<div class="overflow-x-auto mb-8">
<table class="w-full bg-white dark:bg-gray-800 shadow rounded" aria-label="Audit Log Table">
    <thead>
        <tr class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            <th class="p-2">Admin</th>
            <th class="p-2">Action</th>
            <th class="p-2">Subject</th>
            <th class="p-2">Details</th>
            <th class="p-2">Date</th>
        </tr>
    </thead>
    <tbody>
    @foreach(\App\Models\AuditLog::with('user')->latest()->limit(20)->get() as $log)
        <tr class="border-t border-gray-200 dark:border-gray-700">
            <td class="p-2">{{ $log->user ? $log->user->name : 'Unknown' }}</td>
            <td class="p-2">{{ $log->action }}</td>
            <td class="p-2">{{ $log->subject_type }} #{{ $log->subject_id }}</td>
            <td class="p-2 text-xs">{{ json_encode($log->details) }}</td>
            <td class="p-2">{{ $log->created_at->format('M d, Y H:i') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
@endsection
