<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report – Canson</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #111827; background: #fff; padding: 32px; }
        .header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 28px; padding-bottom: 16px; border-bottom: 2px solid #10b981; }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand-icon { width: 42px; height: 42px; background: #10b981; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 20px; }
        .brand-name { font-size: 18px; font-weight: 800; color: #111827; }
        .brand-sub { font-size: 10px; color: #6b7280; letter-spacing: 0.08em; }
        .report-meta { text-align: right; }
        .report-meta h2 { font-size: 15px; font-weight: 700; color: #111827; }
        .report-meta p { font-size: 11px; color: #6b7280; margin-top: 2px; }
        .summary-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px; }
        .summary-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; }
        .summary-card .label { font-size: 10px; color: #6b7280; font-weight: 500; }
        .summary-card .value { font-size: 18px; font-weight: 800; color: #111827; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        thead tr { background: #f0fdf4; }
        th { padding: 10px 12px; text-align: left; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #374151; border-bottom: 2px solid #d1fae5; }
        tbody tr { border-bottom: 1px solid #f3f4f6; }
        tbody tr:nth-child(even) { background: #fafafa; }
        td { padding: 9px 12px; font-size: 11px; color: #374151; }
        td.amount { font-weight: 700; color: #111827; }
        .status { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 600; }
        .status-completed        { background: #f0fdf4; color: #16a34a; }
        .status-in-progress      { background: #ecfdf5; color: #059669; }
        .status-ready            { background: #eff6ff; color: #2563eb; }
        .status-delivered        { background: #f0fdfa; color: #0d9488; }
        .status-pending          { background: #f9fafb; color: #6b7280; }
        .footer { margin-top: 28px; padding-top: 14px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; font-size: 10px; color: #9ca3af; }
        @media print {
            body { padding: 16px; }
            @page { margin: 16mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    {{-- Print Button (hidden on print) --}}
    <div class="no-print" style="margin-bottom: 16px; text-align: right;">
        <button onclick="window.print()"
                style="background:#10b981;color:white;border:0;padding:8px 18px;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
            🖨 Print
        </button>
        <button onclick="window.close()"
                style="margin-left:8px;background:#f3f4f6;color:#374151;border:0;padding:8px 18px;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;">
            ✕ Close
        </button>
    </div>

    {{-- Header --}}
    <div class="header">
        <div class="brand">
            <div class="brand-icon">C</div>
            <div>
                <div class="brand-name">Canson's</div>
                <div class="brand-sub">School &amp; Office Supplies</div>
            </div>
        </div>
        <div class="report-meta">
            <h2>Sales Report</h2>
            <p>Generated: {{ $generatedAt }}</p>
            @if(request()->filled('date'))
            <p>Date Filter: {{ request('date') }}</p>
            @endif
            @if(request()->filled('status') && request('status') !== 'all')
            <p>Status Filter: {{ request('status') }}</p>
            @endif
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="summary-row">
        <div class="summary-card">
            <div class="label">Total Revenue</div>
            <div class="value">₱{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Transactions</div>
            <div class="value">{{ $totalOrders }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Average Order Value</div>
            <div class="value">₱{{ $totalOrders > 0 ? number_format($totalRevenue / $totalOrders, 2) : '0.00' }}</div>
        </div>
    </div>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Transaction ID</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Qty</th>
                <th style="text-align:right">Amount</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $i => $order)
            @php
                $statusClass = match($order['status']) {
                    'Completed'          => 'status-completed',
                    'In-Progress'        => 'status-in-progress',
                    'Ready for Delivery' => 'status-ready',
                    'Delivered'          => 'status-delivered',
                    default              => 'status-pending',
                };
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td style="font-weight:700">{{ $order['id'] }}</td>
                <td>{{ $order['customer'] }}</td>
                <td>{{ Str::limit($order['items'], 35) }}</td>
                <td>{{ $order['qty'] }}</td>
                <td class="amount" style="text-align:right">₱{{ number_format($order['amount'], 2) }}</td>
                <td><span class="status {{ $statusClass }}">{{ $order['status'] }}</span></td>
                <td>{{ $order['date'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center;padding:20px;color:#9ca3af">No records found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <span>Canson's School &amp; Office Supplies — Confidential</span>
        <span>Generated on {{ $generatedAt }}</span>
    </div>

</body>
</html>
