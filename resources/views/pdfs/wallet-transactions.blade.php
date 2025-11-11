<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Wallet Transactions Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
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
            font-size: 24px;
            color: #1a56db;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .filters {
            background-color: #f3f4f6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .filters p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table thead {
            background-color: #1f2937;
            color: white;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            font-weight: bold;
        }
        table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .amount-credit {
            color: #059669;
            font-weight: bold;
        }
        .amount-debit {
            color: #dc2626;
            font-weight: bold;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-deposit { background-color: #d1fae5; color: #065f46; }
        .badge-sale { background-color: #dbeafe; color: #1e40af; }
        .badge-adjustment { background-color: #fef3c7; color: #92400e; }
        .badge-refund { background-color: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .summary {
            margin-top: 20px;
            background-color: #e5e7eb;
            padding: 15px;
            border-radius: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Wallet Transactions Report</h1>
        <p><strong>User:</strong> {{ $user->name }}</p>
        <p><strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <div class="filters">
        <p><strong>Report Filters:</strong></p>
        @if($filters['from_date'] && $filters['to_date'])
            <p>Date Range: {{ \Carbon\Carbon::parse($filters['from_date'])->format('d M Y') }} to {{ \Carbon\Carbon::parse($filters['to_date'])->format('d M Y') }}</p>
        @elseif($filters['from_date'])
            <p>From: {{ \Carbon\Carbon::parse($filters['from_date'])->format('d M Y') }}</p>
        @elseif($filters['to_date'])
            <p>To: {{ \Carbon\Carbon::parse($filters['to_date'])->format('d M Y') }}</p>
        @else
            <p>Date Range: All Time</p>
        @endif
        
        @if($filters['type'])
            <p>Transaction Type: {{ ucfirst($filters['type']) }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Operator</th>
                <th>Type</th>
                <th>Reference</th>
                <th style="text-align: right;">Amount</th>
                <th style="text-align: right;">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y H:i') }}</td>
                <td>{{ $transaction->walletAccount->operator->name }}</td>
                <td>
                    <span class="badge badge-{{ $transaction->transaction_type }}">
                        {{ ucfirst($transaction->transaction_type) }}
                    </span>
                </td>
                <td>{{ $transaction->reference }}</td>
                <td style="text-align: right;" class="{{ $transaction->credit > 0 ? 'amount-credit' : 'amount-debit' }}">
                    {{ $transaction->credit > 0 ? '+' : '-' }}Rs. {{ number_format($transaction->credit > 0 ? $transaction->credit : $transaction->debit, 2) }}
                </td>
                <td style="text-align: right;">Rs. {{ number_format($transaction->balance_after, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: #999;">No transactions found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($transactions->count() > 0)
    <div class="summary">
        <p><strong>Summary:</strong></p>
        <div class="summary-row">
            <span>Total Transactions:</span>
            <strong>{{ $transactions->count() }}</strong>
        </div>
        <div class="summary-row">
            <span>Total Credits:</span>
            <strong class="amount-credit">Rs. {{ number_format($transactions->sum('credit'), 2) }}</strong>
        </div>
        <div class="summary-row">
            <span>Total Debits:</span>
            <strong class="amount-debit">Rs. {{ number_format($transactions->sum('debit'), 2) }}</strong>
        </div>
        <div class="summary-row">
            <span>Net Amount:</span>
            <strong>Rs. {{ number_format($transactions->sum('credit') - $transactions->sum('debit'), 2) }}</strong>
        </div>
    </div>
    @endif

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }} - Wallet Transactions Report</p>
        <p>This is a computer-generated document. No signature is required.</p>
    </div>
</body>
</html>
