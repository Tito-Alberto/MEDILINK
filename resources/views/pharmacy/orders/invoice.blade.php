<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Factura {{ $order->invoice_number ?? ('Pedido #' . $order->id) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111827;
            margin: 0;
            background: #f8fafc;
        }
        .page {
            max-width: 900px;
            margin: 24px auto;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }
        .top {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .title {
            font-size: 26px;
            font-weight: 700;
            margin: 0;
        }
        .muted {
            color: #64748b;
            margin: 4px 0 0;
            font-size: 13px;
        }
        .grid {
            margin-top: 20px;
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px;
        }
        .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: #64748b;
            margin: 0 0 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            font-size: 13px;
        }
        th {
            background: #f8fafc;
            color: #475569;
        }
        tr:last-child td {
            border-bottom: 0;
        }
        .right {
            text-align: right;
        }
        .total-box {
            margin-top: 16px;
            margin-left: auto;
            width: 280px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: 14px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 14px;
            margin: 6px 0;
        }
        .row.total {
            font-weight: 700;
            font-size: 16px;
            color: #166534;
        }
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn {
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #0f172a;
            border-radius: 999px;
            padding: 10px 14px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn.primary {
            border-color: #a3e635;
            background: #a3e635;
        }
        @media print {
            body {
                background: #fff;
            }
            .page {
                margin: 0;
                box-shadow: none;
                border: 0;
                border-radius: 0;
                max-width: none;
            }
            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    @php
        $isEmbedded = request()->boolean('embed');
        $itemsSubtotal = (float) $total;
        $deliveryFee = (float) ($order->delivery_fee ?? 0);
        $taxAmount = (float) ($order->tax_amount ?? 0);
        $shippingFee = $deliveryFee > 0 ? $deliveryFee : $taxAmount;
        $invoiceTotal = $itemsSubtotal + $shippingFee;
    @endphp
    <div class="page" @if($isEmbedded) style="margin:0;max-width:none;min-height:100vh;border:0;border-radius:0;box-shadow:none;" @endif>
        <div class="top">
            <div>
                <h1 class="title">Factura</h1>
                <p class="muted">Documento do pedido #{{ $order->id }}</p>
                <p class="muted">Factura: {{ $order->invoice_number ?? '-' }}</p>
                <p class="muted">Data: {{ $order->invoice_date?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="label">Farmacia</p>
                <div class="card">
                    <p style="margin:0;font-weight:700;">{{ $pharmacy->name ?? 'Farmacia' }}</p>
                    <p class="muted">{{ $pharmacy->responsible_name ?? '' }}</p>
                    @if (!empty($pharmacy?->nif))
                        <p class="muted">NIF: {{ $pharmacy->nif }}</p>
                    @endif
                    <p class="muted">{{ $pharmacy->phone ?? '' }}</p>
                    <p class="muted">{{ $pharmacy->email ?? '' }}</p>
                </div>
            </div>
        </div>

        <div class="grid">
            <div class="card">
                <p class="label">Cliente</p>
                <p style="margin:0;font-weight:700;">{{ $order->customer_name }}</p>
                <p class="muted">{{ $order->customer_phone }}</p>
                <p class="muted">{{ $order->customer_address }}</p>
                @if (!empty($order->customer_nif))
                    <p class="muted">NIF: {{ $order->customer_nif }}</p>
                @endif
            </div>
            <div class="card">
                <p class="label">Pedido</p>
                <p style="margin:0;font-weight:700;">Estado: {{ strtoupper((string) $order->status) }}</p>
                <p class="muted">Criado em {{ optional($order->created_at)->format('d/m/Y H:i') }}</p>
                @if (!empty($order->notes))
                    <p class="muted">Obs: {{ $order->notes }}</p>
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th class="right">Qtd</th>
                    <th class="right">Preco unit.</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td class="right">{{ $item->quantity }}</td>
                        <td class="right">Kz {{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                        <td class="right">Kz {{ number_format((float) $item->line_total, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-box">
            <div class="row">
                <span>Subtotal</span>
                <span>Kz {{ number_format($itemsSubtotal, 2, ',', '.') }}</span>
            </div>
            <div class="row">
                <span>Taxa</span>
                <span>Kz {{ number_format($shippingFee, 2, ',', '.') }}</span>
            </div>
            <div class="row total">
                <span>Total</span>
                <span>Kz {{ number_format($invoiceTotal, 2, ',', '.') }}</span>
            </div>
        </div>

        <div class="actions">
            <button type="button" class="btn primary" onclick="window.print()">Imprimir factura</button>
            @if (! $isEmbedded)
                <a class="btn" href="{{ route('pharmacy.orders.show', $order->id) }}">Voltar ao pedido</a>
            @endif
        </div>
    </div>
</body>
</html>
