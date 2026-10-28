<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cierre de Caja</title>
    <style>
        @page { margin: 5px; }
        body { 
            font-family: 'Courier', 'Arial', monospace; 
            font-size: 10px; 
            color: #000; 
            margin: 0; 
            padding: 5px; 
        }
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
        <div class="fw-bold" style="font-size: 12px;">CIERRE DE CAJA GENERAL</div>
        <div class="fw-bold" style="font-size: 12px; margin-top: 2px;">PIES FELICES</div>
        <div class="fw-bold" style="font-size: 10px; margin-top: 2px;">REPORTE DE CAJA</div>
    </div>

    <div class="divider-dashed"></div>

    <div style="font-size: 9px; line-height: 1.3;">
        <div><span class="fw-bold">FECHA:</span> <?php echo e($fecha1); ?> AL <?php echo e($fecha2); ?></div>
        <div><span class="fw-bold">SUCURSAL:</span> <?php echo e($sucursalTitle); ?></div>
    </div>

    <div class="divider-dashed"></div>

    
    <table>
        <tr>
            <td>Medicamento Crédito:</td>
            <td class="text-right">$<?php echo e(number_format($credito['medicamento'] ?? 0, 0)); ?></td>
        </tr>
        <tr>
            <td>Calzado Crédito:</td>
            <td class="text-right">$<?php echo e(number_format($credito['calzado'] ?? 0, 0)); ?></td>
        </tr>
        <tr>
            <td>Consulta Crédito:</td>
            <td class="text-right">$<?php echo e(number_format($credito['consulta'] ?? 0, 0)); ?></td>
        </tr>
        <?php if(($descuentoCredito ?? 0) > 0): ?>
            <tr>
                <td>Descuento Crédito:</td>
                <td class="text-right">-$<?php echo e(number_format($descuentoCredito, 0)); ?></td>
            </tr>
        <?php endif; ?>
        <tr class="fw-bold">
            <td>TOTAL CRÉDITO:</td>
            <td class="text-right">$<?php echo e(number_format($totalCredito ?? 0, 0)); ?></td>
        </tr>
    </table>

    <div class="divider-dashed"></div>

    
    <table>
        <tr>
            <td>Medicamento Contado:</td>
            <td class="text-right">$<?php echo e(number_format($contado['medicamento'] ?? 0, 0)); ?></td>
        </tr>
        <tr>
            <td>Calzado Contado:</td>
            <td class="text-right">$<?php echo e(number_format($contado['calzado'] ?? 0, 0)); ?></td>
        </tr>
        <tr>
            <td>Consulta Contado:</td>
            <td class="text-right">$<?php echo e(number_format($contado['consulta'] ?? 0, 0)); ?></td>
        </tr>
        <tr>
            <td>Descuento Contado:</td>
            <td class="text-right">-$<?php echo e(number_format($descuentoContado ?? 0, 0)); ?></td>
        </tr>
        <tr class="fw-bold">
            <td>TOTAL CONTADO:</td>
            <td class="text-right">$<?php echo e(number_format($totalContado ?? 0, 0)); ?></td>
        </tr>
    </table>

    <div class="divider-dashed"></div>

    
    <table>
        <tr>
            <td>Medicamento Liquidada:</td>
            <td class="text-right">$<?php echo e(number_format($liquidado['medicamento'] ?? 0, 0)); ?></td>
        </tr>
        <tr>
            <td>Calzado Liquidada:</td>
            <td class="text-right">$<?php echo e(number_format($liquidado['calzado'] ?? 0, 0)); ?></td>
        </tr>
        <tr>
            <td>Consulta Liquidada:</td>
            <td class="text-right">$<?php echo e(number_format($liquidado['consulta'] ?? 0, 0)); ?></td>
        </tr>
        <tr>
            <td>Descuento Liquidada:</td>
            <td class="text-right">-$<?php echo e(number_format($descuentoLiquidada ?? 0, 0)); ?></td>
        </tr>
        <tr class="fw-bold">
            <td>TOTAL LIQUIDADA:</td>
            <td class="text-right">$<?php echo e(number_format($totalLiquidado ?? 0, 0)); ?></td>
        </tr>
    </table>

    <div class="divider-dashed"></div>

    
    <table>
        <tr>
            <td>Total Abono:</td>
            <td class="text-right">$<?php echo e(number_format($totalAbonos ?? 0, 0)); ?></td>
        </tr>
    </table>

    <div class="divider-dashed"></div>

    
    <table>
        <tr>
            <td>Gastos Proveedor:</td>
            <td class="text-right">-$<?php echo e(number_format($gastosProveedor ?? 0, 0)); ?></td>
        </tr>
        <tr>
            <td>Gastos Consultorio:</td>
            <td class="text-right">-$<?php echo e(number_format($gastosConsultorio ?? 0, 0)); ?></td>
        </tr>
        <tr>
            <td>Gastos Sueldos:</td>
            <td class="text-right">-$<?php echo e(number_format($gastosSueldos ?? 0, 0)); ?></td>
        </tr>
        <tr class="fw-bold">
            <td>TOTAL GASTOS:</td>
            <td class="text-right">-$<?php echo e(number_format($totalGastos ?? 0, 0)); ?></td>
        </tr>
    </table>

    <div class="divider-dashed"></div>


    <div class="divider-double"></div>

    
    <table>
        <tr class="fw-bold" style="font-size: 10.5px;">
            <td>TOTAL EFECTIVO:</td>
            <td class="text-right">$<?php echo e(number_format($totalEfectivoNeto ?? 0, 0)); ?></td>
        </tr>
    </table>

    <div class="divider-dashed"></div>

    <table>
        <tr class="fw-bold" style="font-size: 10.5px;">
            <td>EFECTIVO + CRÉDITO:</td>
            <td class="text-right">$<?php echo e(number_format($totalEfectivoMasCredito ?? 0, 0)); ?></td>
        </tr>
    </table>

    <div class="divider-double"></div>

</body>
</html><?php /**PATH C:\xampp\htdocs\PiesFelices\resources\views/cash_closing/daily.blade.php ENDPATH**/ ?>