<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cierre de Caja</title>
    <style>
        @page { margin: 5px; }
        body { font-family: 'Courier', 'Arial', sans-serif; font-size: 10px; color: #000; margin: 0; padding: 5px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .divider-dashed { border-top: 1px dashed #000; margin: 6px 0; }
        .divider-double { border-top: 3px double #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; font-size: 9.5px; }
    </style>
</head>
<body>

    <div class="text-center">
        <h3 style="margin:0;">PIES FELICES</h3>
        <div class="fw-bold" style="font-size: 11px;">CIERRE DE CAJA GENERAL</div>
        <div style="font-size: 8.5px;">{{ $sucursalTitle }}</div>
        <div style="font-size: 8.5px;">Rango: {{ $fecha1 }} al {{ $fecha2 }}</div>
    </div>

    <div class="divider-dashed"></div>

    {{-- CRÉDITO --}}
    <table>
        <tr><td>Medicamento Crédito:</td><td class="text-right">${{ number_format($credito['medicamento'], 2) }}</td></tr>
        <tr><td>Calzado Crédito:</td><td class="text-right">${{ number_format($credito['calzado'], 2) }}</td></tr>
        <tr><td>Consulta Crédito:</td><td class="text-right">${{ number_format($credito['consulta'], 2) }}</td></tr>
        <tr class="fw-bold"><td>TOTAL CRÉDITO:</td><td class="text-right">${{ number_format($totalCredito, 2) }}</td></tr>
    </table>

    <div class="divider-dashed"></div>

    {{-- CONTADO --}}
    <table>
        <tr><td>Medicamento Contado:</td><td class="text-right">${{ number_format($contado['medicamento'], 2) }}</td></tr>
        <tr><td>Calzado Contado:</td><td class="text-right">${{ number_format($contado['calzado'], 2) }}</td></tr>
        <tr><td>Consulta Contado:</td><td class="text-right">${{ number_format($contado['consulta'], 2) }}</td></tr>
        <tr class="fw-bold"><td>TOTAL CONTADO:</td><td class="text-right">${{ number_format($totalContado, 2) }}</td></tr>
    </table>

    <div class="divider-dashed"></div>

    {{-- LIQUIDADA --}}
    <table>
        <tr><td>Medicamento Liquidada:</td><td class="text-right">${{ number_format($liquidado['medicamento'], 2) }}</td></tr>
        <tr><td>Calzado Liquidada:</td><td class="text-right">${{ number_format($liquidado['calzado'], 2) }}</td></tr>
        <tr><td>Consulta Liquidada:</td><td class="text-right">${{ number_format($liquidado['consulta'], 2) }}</td></tr>
        <tr class="fw-bold"><td>TOTAL LIQUIDADA:</td><td class="text-right">${{ number_format($totalLiquidado, 2) }}</td></tr>
    </table>

    <div class="divider-dashed"></div>

    {{-- ABONOS Y GASTOS --}}
    <table>
        <tr><td>Total Abonos Cobrados:</td><td class="text-right">${{ number_format($totalAbonos, 2) }}</td></tr>
        <tr><td>Gastos Proveedores:</td><td class="text-right">-${{ number_format($gastosProveedor, 2) }}</td></tr>
        <tr><td>Gastos Consultorio:</td><td class="text-right">-${{ number_format($gastosConsultorio, 2) }}</td></tr>
        <tr><td>Gastos Sueldos:</td><td class="text-right">-${{ number_format($gastosSueldos, 2) }}</td></tr>
        <tr class="fw-bold"><td>TOTAL GASTOS:</td><td class="text-right">-${{ number_format($totalGastos, 2) }}</td></tr>
    </table>

    <div class="divider-double"></div>

    {{-- TOTALES NETOS --}}
    <table>
        <tr class="fw-bold" style="font-size: 11px;">
            <td>TOTAL EFECTIVO NETO:</td>
            <td class="text-right">${{ number_format($totalEfectivoNeto, 2) }}</td>
        </tr>
        <tr class="fw-bold" style="font-size: 11px;">
            <td>EFECTIVO + CRÉDITO:</td>
            <td class="text-right">${{ number_format($totalEfectivoMasCredito, 2) }}</td>
        </tr>
    </table>

</body>
</html>