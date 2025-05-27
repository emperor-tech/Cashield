@extends('layouts.app')

@section('content')
<h1 class="text-3xl font-extrabold mb-6 text-blue-900 dark:text-blue-200 tracking-tight">Admin Dashboard</h1>
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-xl shadow flex flex-col items-center">
        <div class="text-3xl font-bold text-blue-800 dark:text-blue-200">{{ $stats['total_reports'] }}</div>
        <div class="text-blue-900 dark:text-blue-100">Total Reports</div>
    </div>
    <div class="bg-green-100 dark:bg-green-900 p-4 rounded-xl shadow flex flex-col items-center">
        <div class="text-3xl font-bold text-green-800 dark:text-green-200">{{ $stats['total_users'] }}</div>
        <div class="text-green-900 dark:text-green-100">Total Users</div>
    </div>
    <div class="bg-red-100 dark:bg-red-900 p-4 rounded-xl shadow flex flex-col items-center">
        <div class="text-3xl font-bold text-red-800 dark:text-red-200">{{ $stats['panic_alerts'] }}</div>
        <div class="text-red-900 dark:text-red-100">Panic Alerts</div>
    </div>
    <div class="bg-yellow-100 dark:bg-yellow-900 p-4 rounded-xl shadow flex flex-col items-center">
        <div class="text-3xl font-bold text-yellow-800 dark:text-yellow-200">{{ $stats['today_reports'] }}</div>
        <div class="text-yellow-900 dark:text-yellow-100">Reports Today</div>
    </div>
</div>
<div class="mb-4 flex gap-2">
    <a href="/admin/reports/export/csv" class="bg-green-600 text-white px-4 py-2 rounded">Export CSV</a>
    <a href="/admin/reports/export/pdf" class="bg-blue-600 text-white px-4 py-2 rounded">Export PDF</a>
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
            <td class="p-2"><a href="/admin/report/{{ $r->id }}" class="text-blue-700 underline">@if($r->user){{ $r->user->name }}@else Guest @endif</a></td>
            <td class="p-2">
                <form method="POST" action="/admin/report/{{ $r->id }}/status">
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
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded shadow p-4">
        <h3 class="font-bold mb-2">Reports by Severity</h3>
        <canvas id="severityChart" height="120"></canvas>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded shadow p-4">
        <h3 class="font-bold mb-2">Reports Over Time</h3>
        <canvas id="timeChart" height="120"></canvas>
    </div>
</div>
<div class="bg-white dark:bg-gray-800 rounded shadow p-4 mb-8">
    <h3 class="font-bold mb-2">Real-Time Feed</h3>
    <ul id="realtime-feed" class="max-h-40 overflow-y-auto"></ul>
</div>
<div class="bg-white dark:bg-gray-800 rounded shadow p-4 mb-8">
    <h3 class="font-bold mb-2">Incident Heatmap</h3>
    <div id="incident-heatmap" class="w-full h-96 rounded"></div>
</div>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.heat/dist/leaflet-heat.js"></script>
<script>
const severityData = {
    labels: ['High', 'Medium', 'Low'],
    datasets: [{
        label: 'Reports',
        data: [
            {{ $reports->where('severity','high')->count() }},
            {{ $reports->where('severity','medium')->count() }},
            {{ $reports->where('severity','low')->count() }}
        ],
        backgroundColor: ['#dc2626', '#fbbf24', '#22d3ee'],
    }]
};
const severityChart = new Chart(document.getElementById('severityChart'), {
    type: 'doughnut',
    data: severityData,
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
const timeLabels = @json($reports->groupBy(fn($r) => $r->created_at->format('Y-m-d'))->keys());
const timeData = @json($reports->groupBy(fn($r) => $r->created_at->format('Y-m-d'))->map->count()->values());
const timeChart = new Chart(document.getElementById('timeChart'), {
    type: 'line',
    data: {
        labels: timeLabels,
        datasets: [{
            label: 'Reports',
            data: timeData,
            borderColor: '#2563eb',
            backgroundColor: '#93c5fd',
            fill: true,
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
// Real-time feed
window.Echo.channel('reports').listen('ReportCreated', (e) => {
    const feed = document.getElementById('realtime-feed');
    const li = document.createElement('li');
    li.className = 'mb-2';
    li.innerHTML = `<span class='font-bold text-blue-700'>New Report:</span> ${e.description} <span class='text-xs text-gray-400'>(${e.created_at})</span>`;
    feed.prepend(li);
});
const map = L.map('incident-heatmap').setView([6.5244, 3.3792], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
const heatData = [
    @foreach($reports as $r)
        @if($r->location && strpos($r->location, ',') !== false)
            @php $parts = explode(',', $r->location); @endphp
            [{{ floatval($parts[0]) }}, {{ floatval($parts[1]) }}, 0.5],
        @endif
    @endforeach
];
if (heatData.length) {
    L.heatLayer(heatData, { radius: 25, blur: 15, maxZoom: 17 }).addTo(map);
}
</script>
@endsection
