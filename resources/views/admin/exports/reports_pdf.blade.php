<h2>All Reports</h2>
<table border="1" cellpadding="4" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Campus</th>
            <th>Location</th>
            <th>Severity</th>
            <th>Status</th>
            <th>Description</th>
            <th>User</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
    @foreach($reports as $r)
        <tr>
            <td>{{ $r->id }}</td>
            <td>{{ $r->campus }}</td>
            <td>{{ $r->location }}</td>
            <td>{{ $r->severity }}</td>
            <td>{{ $r->status }}</td>
            <td>{{ $r->description }}</td>
            <td>@if($r->user){{ $r->user->name }}@else Guest @endif</td>
            <td>{{ $r->created_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table> 