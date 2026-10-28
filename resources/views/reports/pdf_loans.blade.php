<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Préstamos y Abonos</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #212529;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e040fb;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h2 {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            color: #212529;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header h3 {
            font-size: 14px;
            font-weight: 600;
            margin: 4px 0 0 0;
            color: #e040fb;
        }
        .header p {
            font-size: 10px;
            color: #6c757d;
            margin: 4px 0 0 0;
        }
        .badge-branch {
            display: inline-block;
            background-color: #f3e5f5;
            color: #ab47bc;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 10px;
            margin-top: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f8f9fa;
            color: #495057;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 10px;
            border-bottom: 2px solid #dee2e6;
            text-align: left;
        }
        td {
            padding: 6px 10px;
            border-bottom: 1px solid #e9ecef;
            font-size: 11px;
        }
        .loan-row {
            background-color: #ffffff;
            font-weight: bold;
        }
        .abono-row td {
            padding-top: 3px;
            padding-bottom: 3px;
            border-bottom: 1px dashed #f0f0f0;
            font-size: 10px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }

        /* Tarjeta de Resumen */
        .summary-box {
            width: 50%;
            margin-left: auto;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: #fafafa;
            padding: 12px 16px;
        }
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        .summary-label {
            display: table-cell;
            font-size: 11px;
            color: #495057;
            font-weight: 600;
        }
        .summary-value {
            display: table-cell;
            text-align: right;
            font-size: 12px;
            font-weight: bold;
        }
        .divider {
            border-top: 1px dashed #ccc;
            margin: 6px 0;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>PRÉSTAMOS Y ABONOS</h2>
        <h3>Pies Felices</h3>
        <p>FECHA: {{ $fecha1 }} al {{ $fecha2 }}</p>
        <div class="badge-branch">Sucursal: {{ $sucursal }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Préstamo / Concepto</th>
                <th class="text-center">Fecha</th>
                <th class="text-right">Total</th>
                <th class="text-right">Restante</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loans as $l)
                @php
                    // Obtener los abonos correspondientes al préstamo
                    $abonosDelPrestamo = $abonosPorPrestamo->get($l->pres_id, collect());
                    $totalAbonadoPrestamo = $abonosDelPrestamo->sum('monto');
                    $restanteReal = $l->pres_costo - $totalAbonadoPrestamo;
                @endphp

                <!-- Renglón Principal del Préstamo -->
                <tr class="loan-row">
                    <td>
                        <div class="fw-bold">{{ strtoupper($l->pres_descripcion) }}</div>
                    </td>
                    <td class="text-center">{{ $l->pres_fecha }}</td>
                    <td class="text-right fw-bold">${{ number_format($l->pres_costo, 2) }}</td>
                    <td class="text-right fw-bold text-danger">${{ number_format($restanteReal, 2) }}</td>
                </tr>

                <!-- Desglose de Abonos (Incluye abonos con fechas posteriores) -->
                @if($abonosDelPrestamo->count() > 0)
                    <tr class="abono-row">
                        <td colspan="4" style="color: #8e24aa; font-weight: bold; padding-left: 15px; background-color: #fcf4ff;">
                            Abonos:
                        </td>
                    </tr>
                    @foreach($abonosDelPrestamo as $abono)
                        <tr class="abono-row">
                            <td colspan="2" style="padding-left: 25px; color: #495057;">
                                Fecha abono: {{ $abono->fechaPretamo }}
                            </td>
                            <td colspan="2" class="text-right" style="color: #2e7d32; font-weight: bold;">
                                Total de abono: ${{ number_format($abono->monto, 2) }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr class="abono-row">
                        <td colspan="4" style="padding-left: 25px; color: #adb5bd; font-style: italic;">
                            Sin abonos registrados
                        </td>
                    </tr>
                @endif

            @empty
                <tr>
                    <td colspan="4" class="text-center py-3" style="color: #6c757d;">
                        No se registraron préstamos en el rango de fechas seleccionado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- BLOQUE DE TOTALES --}}
    <div class="summary-box">
        <div class="summary-row">
            <span class="summary-label">Total Préstamo:</span>
            <span class="summary-value">${{ number_format($totalPrestamos, 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Abonos:</span>
            <span class="summary-value" style="color: #2e7d32;">${{ number_format($totalAbonos, 2) }}</span>
        </div>
        <div class="divider"></div>
        <div class="summary-row">
            <span class="summary-label">Restante:</span>
            <span class="summary-value" style="color: #c62828;">${{ number_format($totalRestante, 2) }}</span>
        </div>
        <div class="divider"></div>
        <div class="summary-row">
            <span class="summary-label">Préstamos Personales:</span>
            <span class="summary-value" style="color: #8e24aa;">${{ number_format($prestamosPersonales, 2) }}</span>
        </div>
    </div>

</body>
</html>