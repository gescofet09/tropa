<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        h1 { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; }
    </style>
</head>
<body>

<h1>Factura #{{ $factura->numero }}</h1>

<p><strong>Fecha:</strong> {{ $factura->fecha }}</p>
<p><strong>Pedido:</strong> #{{ $factura->pedido->id }}</p>
<p><strong>Cliente:</strong> {{ $factura->pedido->cliente->name }}</p>

<h3>Productos</h3>

<table>
    <thead>
        <tr>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio</th>
        </tr>
    </thead>
    <tbody>
        @foreach($factura->pedido->productos as $producto)
        <tr>
            <td>{{ $producto->nombre }}</td>
            <td>{{ $producto->pivot->cantidad }}</td>
            <td>{{ $producto->pivot->precio_unitario }} €</td>
        </tr>
        @endforeach
    </tbody>
</table>

<h2>Total: {{ $factura->total }} €</h2>

</body>
</html>
