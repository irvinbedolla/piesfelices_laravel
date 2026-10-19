<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Comisiones</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 20px; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #ffc107; padding-bottom: 10px; }
        .header h3 { margin: 0; color: #333; text-transform: uppercase; }
        .header p { margin: 4px 0 0 0; color: #666; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #f8f9fa; font-weight: bold; text-transform: uppercase; font-size: 9px; padding: 8px; border: 1px solid #dee2e6; text-align: left; }
        td { padding: 8px; border: 1px solid #dee2e6; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background-color: #f8f9fa; font-weight: bold; font-size: 12px; }
    </style>
</head>
<body>

    <div class="header">
        <h3>Reporte de Comisiones</h3>
        <p>
            <strong>Empleado:</strong> {{ $employeeName }} | 
            <strong>Periodo:</strong> {{ $fecha1 }} al {{ $fecha2 }} | 
            <strong>Sucursal:</strong> {{ $sucursal ?: 'TODOS' }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Concepto / Categoría</th>
                <th class="text-center">% Comisión</th>
                <th class="text-end">Comisión Ganada</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Venta General</td>
                <td class="text-center">{{ $pctGeneral }}%</td>
                <td class="text-end">${{ number_format($comisionGeneral, 2) }}</td>
            </tr>
            <tr>
                <td>Venta de Calzado</td>
                <td class="text-center">{{ $pctCalzado }}%</td>
                <td class="text-end">${{ number_format($comisionCalzado, 2) }}</td>
            </tr>
            <tr>
                <td>Venta de Medicamento</td>
                <td class="text-center">{{ $pctMedicamento }}%</td>
                <td class="text-end">${{ number_format($comisionMedicamento, 2) }}</td>
            </tr>
            <tr>
                <td>Consultas y Servicios</td>
                <td class="text-center">{{ $pctConsulta }}%</td>
                <td class="text-end">${{ number_format($comisionConsulta, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2" class="text-end">TOTAL A PAGAR:</td>
                <td class="text-end" style="color: #0d6efd;">${{ number_format($totalComisiones, 2) }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>