<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Requests Report</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 10px; }
        .meta { margin-bottom: 12px; color: #374151; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
    </style>
</head>
<body>
    <h1>Manual Wallet Recharge Requests</h1>
    <div class="meta">
        <div>Generated: {{ now()->format('d M Y, h:i A') }}</div>
        <div>Status Filter: {{ $status ?: 'All' }}</div>
        <div>Search: {{ $q ?: 'N/A' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Email</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Reference</th>
                <th>Status</th>
                <th>Admin Note</th>
                <th>Requested</th>
                <th>Processed</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td>{{ $r->user?->name ?? '-' }}</td>
                    <td>{{ $r->user?->email ?? '-' }}</td>
                    <td>{{ number_format((float) $r->amount, 2) }}</td>
                    <td>{{ $r->method ?: '-' }}</td>
                    <td>{{ $r->reference ?: '-' }}</td>
                    <td>{{ ucfirst($r->status) }}</td>
                    <td>{{ $r->admin_note ?: '-' }}</td>
                    <td>{{ optional($r->created_at)->format('d M Y h:i A') }}</td>
                    <td>{{ optional($r->processed_at)->format('d M Y h:i A') ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">No payment requests found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
