<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket Venta #{{ $sale->venta_id }}</title>
    <style>
        @page { margin: 5px; }
        body {
            font-family: 'Courier', 'Arial', sans-serif;
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 5px;
            width: 100%;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .table th, .table td { font-size: 9px; padding: 2px 0; text-align: left; }
    </style>
</head>
<body>

    <div class="text-center">
        <h2 style="font-size: 14px; margin: 0; text-transform: uppercase;">PIES FELICES</h2>
        <div style="font-size: 9px;">Sucursal: {{ $sale->venta_sucursal }}</div>
        <div style="font-size: 9px;">Fecha: {{ \Carbon\Carbon::parse($sale->venta_fecha)->format('d/m/Y') }} {{ $sale->venta_hora }}</div>
        <div class="fw-bold" style="font-size: 11px; margin-top: 3px;">TICKET DE VENTA #{{ $sale->venta_id }}</div>
    </div>

    <div class="divider"></div>

    <div style="font-size: 9px;">
        <div><strong>Cliente:</strong> {{ $sale->customer->nombre ?? $sale->nombre_cliente ?: 'Cliente General' }}</div>
        <div><strong>Vendedor:</strong> {{ $sale->seller->name ?? $sale->seller->username ?? 'Empleado' }}</div>
        <div><strong>Tipo Pago:</strong> {{ $sale->venta_tipopago }} ({{ $sale->venta_tipo == '1' ? 'Contado' : 'Crédito' }})</div>
    </div>

    <div class="divider"></div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 15%;">Cant</th>
                <th style="width: 55%;">Producto/Servicio</th>
                <th style="width: 30%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->cantidad }}</td>
                    <td>{{ $item->nombre }}</td>
                    <td class="text-right">${{ number_format($item->cantidad * $item->precio, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table style="width: 100%; font-size: 10px;">
        @if($sale->venta_descuento > 0)
            <tr>
                <td class="text-right">Descuento:</td>
                <td class="text-right">-${{ number_format($sale->venta_descuento, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td class="text-right fw-bold" style="font-size: 12px;">TOTAL:</td>
            <td class="text-right fw-bold" style="font-size: 12px;">${{ number_format($sale->venta_total, 2) }}</td>
        </tr>
        @if($sale->venta_abono > 0)
            <tr>
                <td class="text-right">Abono / Efectivo:</td>
                <td class="text-right">${{ number_format($sale->venta_abono, 2) }}</td>
            </tr>
        @endif
    </table>

    <div class="divider"></div>

    <div class="text-center" style="font-size: 8px; margin-top: 8px;">
        ¡Gracias por su preferencia!<br>
        Pies Felices - Salud y Cuidado para tus Pies
    </div>

</body>
</html>