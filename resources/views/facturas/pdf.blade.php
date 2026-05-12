<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Factura {{ $factura->numero }}</title>
    <style>
        @page { margin: 34px 36px; }
        * { box-sizing: border-box; }
        body {
            color: #1f2937;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.45;
            margin: 0;
        }
        .topbar {
            background: #0f766e;
            color: #ffffff;
            padding: 22px 24px;
        }
        .brand {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: .5px;
            margin: 0;
            text-transform: uppercase;
        }
        .brand-wrap { width: 72%; }
        .logo {
            height: 74px;
            margin-right: 12px;
            object-fit: contain;
            width: 74px;
        }
        .brand-cell { vertical-align: middle; }
        .document-title {
            font-size: 16px;
            font-weight: 700;
            margin: 4px 0 0;
            text-align: right;
        }
        .muted { color: #6b7280; }
        .white-muted { color: #ccfbf1; }
        .header-table, .info-table, .items-table, .totals-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; }
        .header-table .right { text-align: right; }
        .summary {
            border: 1px solid #d1d5db;
            margin-top: 22px;
            padding: 16px;
        }
        .summary td {
            padding: 4px 0;
            vertical-align: top;
        }
        .summary .label {
            color: #6b7280;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            width: 92px;
        }
        .section-title {
            color: #0f766e;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .4px;
            margin: 24px 0 8px;
            text-transform: uppercase;
        }
        .info-card {
            border: 1px solid #e5e7eb;
            padding: 14px;
            vertical-align: top;
            width: 50%;
        }
        .info-card + .info-card { border-left: 0; }
        .info-card h3 {
            font-size: 13px;
            margin: 0 0 8px;
        }
        .items-table {
            border: 1px solid #d1d5db;
            margin-top: 8px;
        }
        .items-table th {
            background: #f3f4f6;
            border-bottom: 1px solid #d1d5db;
            color: #374151;
            font-size: 10px;
            padding: 10px 9px;
            text-align: left;
            text-transform: uppercase;
        }
        .items-table td {
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 9px;
            vertical-align: top;
        }
        .items-table tr:last-child td { border-bottom: 0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals-wrap {
            margin-top: 16px;
            width: 100%;
        }
        .totals-table {
            margin-left: auto;
            width: 260px;
        }
        .totals-table td {
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 0;
        }
        .totals-table .grand td {
            border-bottom: 0;
            color: #0f766e;
            font-size: 18px;
            font-weight: 700;
            padding-top: 12px;
        }
        .footer {
            border-top: 1px solid #e5e7eb;
            bottom: 0;
            color: #6b7280;
            font-size: 10px;
            left: 0;
            padding-top: 10px;
            position: fixed;
            right: 0;
        }
    </style>
</head>
<body>
@php
    $cliente = $pedido->cliente;
    $repartidor = $pedido->repartidor;
    $fecha = optional($factura->fecha)->format('d/m/Y H:i') ?? \Carbon\Carbon::parse($factura->fecha)->format('d/m/Y H:i');
    $totalFactura = round((float) $factura->total, 2);
    $igicTipo = 0.07;
    $baseImponible = round($totalFactura / (1 + $igicTipo), 2);
    $igic = round($totalFactura - $baseImponible, 2);
    $logoPath = public_path('images/logo.png');
@endphp

<div class="topbar">
    <table class="header-table">
        <tr>
            <td class="brand-wrap">
                <table>
                    <tr>
                        <td class="brand-cell">
                            @if(file_exists($logoPath))
                                <img class="logo" src="{{ $logoPath }}" alt="Tropa">
                            @endif
                        </td>
                        <td class="brand-cell">
                            <p class="brand">Tropa</p>
                            <div class="white-muted">Gestión de pedidos y reparto</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="right">
                <div class="document-title">Factura</div>
                <div class="white-muted">{{ $factura->numero }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="summary">
    <table class="header-table">
        <tr>
            <td>
                <table class="summary-table">
                    <tr><td class="label">Pedido</td><td>#{{ $pedido->id }}</td></tr>
                    <tr><td class="label">Fecha</td><td>{{ $fecha }}</td></tr>
                    <tr><td class="label">Estado</td><td>{{ ucfirst($pedido->estado) }}</td></tr>
                </table>
            </td>
            <td class="right">
                <div class="muted">Importe total</div>
                <div style="font-size: 24px; font-weight: 700; color: #0f766e;">
                    {{ number_format($totalFactura, 2, ',', '.') }} EUR
                </div>
                <div class="muted">Incluye IGIC  {{ number_format($igicTipo * 100, 0, ',', '.') }}%</div>
            </td>
        </tr>
    </table>
</div>

<p class="section-title">Datos del pedido</p>
<table class="info-table">
    <tr>
        <td class="info-card">
            <h3>Cliente</h3>
            <strong>{{ $cliente->name ?? 'Cliente no disponible' }}</strong><br>
            <span class="muted">{{ $cliente->email ?? 'Sin email' }}</span>
        </td>
        <td class="info-card">
            <h3>Repartidor</h3>
            <strong>{{ $repartidor->name ?? 'Sin asignar' }}</strong><br>
            <span class="muted">{{ $repartidor->email ?? 'Sin email' }}</span>
        </td>
    </tr>
</table>

<p class="section-title">Productos facturados</p>
<table class="items-table">
    <thead>
        <tr>
            <th>Producto</th>
            <th class="text-center">Cantidad</th>
            <th class="text-right">Precio unitario</th>
            <th class="text-right">Importe</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pedido->productos as $producto)
            @php
                $cantidad = (int) $producto->pivot->cantidad;
                $precio = (float) $producto->pivot->precio_unitario;
                $linea = $cantidad * $precio;
            @endphp
            <tr>
                <td>
                    <strong>{{ $producto->nombre }}</strong><br>
                    <span class="muted">{{ $producto->categoria->nombre ?? 'Sin categoria' }} · {{ $producto->unidad }}</span>
                </td>
                <td class="text-center">{{ $cantidad }}</td>
                <td class="text-right">{{ number_format($precio, 2, ',', '.') }} EUR</td>
                <td class="text-right">{{ number_format($linea, 2, ',', '.') }} EUR</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="totals-wrap">
    <table class="totals-table">
        <tr>
            <td class="muted">Base imponible</td>
            <td class="text-right">{{ number_format($baseImponible, 2, ',', '.') }} EUR</td>
        </tr>
        <tr>
            <td class="muted">IGIC {{ number_format($igicTipo * 100, 0, ',', '.') }}%</td>
            <td class="text-right">{{ number_format($igic, 2, ',', '.') }} EUR</td>
        </tr>
        <tr class="grand">
            <td>Total</td>
            <td class="text-right">{{ number_format($totalFactura, 2, ',', '.') }} EUR</td>
        </tr>
    </table>
</div>

<div class="footer">
    Si tiene preguntas relacionada con esta factura, ponganse en contacto con Tropa a travé de <a href="mailto:admin@tropa.com">admin@tropa.com</a>
</div>
</body>
</html>
