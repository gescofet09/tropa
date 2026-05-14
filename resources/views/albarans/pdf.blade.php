<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Albarán pedido #{{ $pedido->id }}</title>
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
            background: #0B1F3A;
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
            background-color: #ffffff;
            border-radius: 50%;
            display: inline-block;
            height: 40px;
            margin-right: 12px;
            margin-top: 6px;
            object-fit: contain;
            padding: 6px;
            width: 40px;
        }
        .brand-cell { vertical-align: middle; }
        .document-title {
            font-size: 16px;
            font-weight: 700;
            margin: 4px 0 0;
            text-align: right;
        }
        .muted { color: #6b7280; }
        .white-muted { color: #dbeafe; }
        .header-table, .info-table, .items-table, .signature-table { width: 100%; border-collapse: collapse; }
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
            width: 94px;
        }
        .section-title {
            color: #0B1F3A;
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
        .text-center { text-align: center; }
        .status {
            border-radius: 12px;
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 8px;
            text-transform: uppercase;
        }
        .status-ok { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .signature-table {
            margin-top: 34px;
        }
        .signature-table td {
            padding-right: 18px;
            width: 50%;
        }
        .signature-box {
            border-top: 1px solid #9ca3af;
            color: #6b7280;
            font-size: 10px;
            padding-top: 8px;
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
    $fecha = optional($albaran->fecha)->format('d/m/Y H:i') ?? \Carbon\Carbon::parse($albaran->fecha)->format('d/m/Y H:i');
    $productosPreparados = $pedido->productos->filter(fn ($producto) => (bool) $producto->pivot->preparado)->count();
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
                            <div class="white-muted">Preparación y entrega de pedido</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="right">
                <div class="document-title">Albarán</div>
                <div class="white-muted">A-{{ str_pad((string) $albaran->id, 6, '0', STR_PAD_LEFT) }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="summary">
    <table class="header-table">
        <tr>
            <td>
                <table>
                    <tr><td class="label">Pedido</td><td>#{{ $pedido->id }}</td></tr>
                    <tr><td class="label">Fecha</td><td>{{ $fecha }}</td></tr>
                    <tr><td class="label">Estado</td><td>{{ ucfirst($pedido->estado) }}</td></tr>
                </table>
            </td>
            <td class="right">
                <div class="muted">Productos preparados</div>
                <div style="font-size: 24px; font-weight: 700; color: #0B1F3A;">
                    {{ $productosPreparados }}/{{ $pedido->productos->count() }}
                </div>
            </td>
        </tr>
    </table>
</div>

<p class="section-title">Datos de entrega</p>
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

<p class="section-title">Mercancía preparada</p>
<table class="items-table">
    <thead>
        <tr>
            <th>Producto</th>
            <th class="text-center">Cantidad</th>
            <th class="text-center">Unidad</th>
            <th class="text-center">Preparación</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pedido->productos as $producto)
            <tr>
                <td>
                    <strong>{{ $producto->nombre }}</strong><br>
                    <span class="muted">{{ $producto->categoria->nombre ?? 'Sin categoria' }}</span>
                </td>
                <td class="text-center">{{ (int) $producto->pivot->cantidad }}</td>
                <td class="text-center">{{ $producto->unidad }}</td>
                <td class="text-center">
                    @if($producto->pivot->preparado)
                        <span class="status status-ok">Preparado</span>
                    @else
                        <span class="status status-pending">Pendiente</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="signature-table">
    <tr>
        <td><div class="signature-box">Firma del repartidor</div></td>
        <td><div class="signature-box">Firma del cliente</div></td>
    </tr>
</table>

<div class="footer">
    Albarán generado automaticamente para el pedido #{{ $pedido->id }}. Comprueba cantidades antes de la entrega.
</div>
</body>
</html>
