<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $sale->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 14px;
            line-height: 1.5;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .invoice-header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .invoice-header p {
            margin: 5px 0;
            color: #666;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .invoice-info-item {
            flex: 1;
        }
        .invoice-info-item h2 {
            font-size: 16px;
            margin: 0 0 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .invoice-table th,
        .invoice-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .invoice-table th {
            background-color: #f5f5f5;
        }
        .text-right {
            text-align: right;
        }
        .total-row td {
            font-weight: bold;
        }
        .invoice-footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .signature-area {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 70px;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>INVOICE</h1>
            <p>{{ config('app.name', 'Laravel') }}</p>
            <p>Jl. Example Street No. 123, City</p>
            <p>Phone: (123) 456-7890 | Email: info@example.com</p>
        </div>

        <div class="invoice-info">
            <div class="invoice-info-item">
                <h2>Bill To</h2>
                <p><strong>{{ $sale->customer->name }}</strong></p>
                <p>{{ $sale->customer->address ?? 'N/A' }}</p>
                <p>Phone: {{ $sale->customer->phone ?? 'N/A' }}</p>
                <p>Email: {{ $sale->customer->email ?? 'N/A' }}</p>
            </div>

            <div class="invoice-info-item">
                <h2>Invoice Details</h2>
                <p><strong>Invoice Number:</strong> {{ $sale->invoice_number }}</p>
                <p><strong>Invoice Date:</strong> {{ $sale->date->format('d/m/Y') }}</p>
                <p><strong>Status:</strong> {{ ucfirst($sale->status) }}</p>
                <p><strong>Payment Method:</strong> Cash/Transfer</p>
            </div>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right">Subtotal</td>
                    <td class="text-right">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-right">Tax (10%)</td>
                    <td class="text-right">Rp {{ number_format($sale->tax, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-right">Discount</td>
                    <td class="text-right">Rp {{ number_format($sale->discount, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="4" class="text-right">Total Amount</td>
                    <td class="text-right">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        @if($sale->notes)
        <div>
            <h2>Notes</h2>
            <p>{{ $sale->notes }}</p>
        </div>
        @endif

        <div class="signature-area">
            <div class="signature-box">
                <p class="signature-line">Customer Signature</p>
            </div>
            <div class="signature-box">
                <p class="signature-line">Authorized Signature</p>
            </div>
        </div>

        <div class="invoice-footer">
            <p>Thank you for your business!</p>
            <p>This invoice was generated on {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
