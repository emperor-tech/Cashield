<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reports Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .severity-high {
            color: #d32f2f;
            font-weight: bold;
        }
        .severity-medium {
            color: #f57c00;
            font-weight: bold;
        }
        .severity-low {
            color: #388e3c;
            font-weight: bold;
        }
        .status-open {
            color: #1976d2;
        }
        .status-resolved {
            color: #388e3c;
        }
        .status-in_progress {
            color: #f57c00;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Security Reports Export</h1>
        <p>Generated on: {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p>Total Reports: {{ $reports->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Location</th>
                <th>Severity</th>
                <th>Status</th>
                <th>Description</th>
                <th>Reporter</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
                <tr>
                    <td>#{{ $report->id }}</td>
                    <td>{{ $report->location }}</td>
                    <td class="severity-{{ $report->severity }}">
                        {{ ucfirst($report->severity) }}
                    </td>
                    <td class="status-{{ $report->status }}">
                        {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                    </td>
                    <td>{{ Str::limit($report->description, 50) }}</td>
                    <td>
                        @if($report->anonymous)
                            Anonymous
                        @else
                            {{ $report->user ? $report->user->name : 'N/A' }}
                        @endif
                    </td>
                    <td>{{ $report->created_at->format('M d, Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No reports found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the {{ config('app.name') }} system.</p>
        <p>For questions or concerns, please contact the security administration.</p>
    </div>
</body>
</html> 