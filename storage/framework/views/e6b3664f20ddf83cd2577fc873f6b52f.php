<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas a Crédito</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #212529; }
        .header { text-align: center; border-bottom: 2px solid #ff9800; padding-bottom: 10px; margin-bottom: 15px; }
        .title { font-size: 16px; font-weight: bold; color: #ff9800; margin: 0; }
        .subtitle { font-size: 10px; color: #6c757d; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #ff9800; color: #fff; padding: 6px; font-size: 9px; text-transform: uppercase; }
        td { padding: 6px; border-bottom: 1px solid #dee2e6; font-size: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .total-row { background-color: #fff3e0; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">REPORTE DE VENTAS A CRÉDITO</h1>
        <div class="subtitle">Período: <?php echo e($fecha1); ?> al <?php echo e($fecha2); ?> | Sucursal: <?php echo e($sucursal); ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">FOLIO</th>
                <th class="text-center">FECHA</th>
                <th>CLIENTE</th>
                <th>VENDEDOR / ATENDIÓ</th>
                <th class="text-center">SUCURSAL</th>
                <th class="text-right">TOTAL VENTA</th>
                <th class="text-right">ABONADO</th>
                <th class="text-right">SALDO RESTANTE</th>
            </tr>
        </thead>
        <tbody>
            <?php $tTotal = 0; $tAbono = 0; $tRestante = 0; ?>
            <?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php 
                    $vendedor = optional($s->seller)->name ?? optional($s->seller)->username ?? 'S/A';
                    $restante = $s->venta_restante > 0 ? $s->venta_restante : ($s->venta_total - $s->venta_abono);
                    $tTotal += $s->venta_total;
                    $tAbono += $s->venta_abono;
                    $tRestante += $restante;
                ?>
                <tr>
                    <td class="text-center fw-bold">#<?php echo e($s->venta_id); ?></td>
                    <td class="text-center"><?php echo e($s->venta_fecha); ?></td>
                    <td><?php echo e($s->nombre_cliente ?? 'Cliente General'); ?></td>
                    <td class="fw-bold"><?php echo e(strtoupper($vendedor)); ?></td>
                    <td class="text-center"><?php echo e($s->venta_sucursal); ?></td>
                    <td class="text-right">$<?php echo e(number_format($s->venta_total, 2)); ?></td>
                    <td class="text-right style="color:#2e7d32;"">$<?php echo e(number_format($s->venta_abono, 2)); ?></td>
                    <td class="text-right fw-bold" style="color:#c62828;">$<?php echo e(number_format($restante, 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="text-center py-3">No se encontraron ventas a crédito en el rango seleccionado.</td>
                </tr>
            <?php endif; ?>
            <?php if(count($sales) > 0): ?>
                <tr class="total-row">
                    <td colspan="5" class="text-right">TOTALES GENERALES:</td>
                    <td class="text-right">$<?php echo e(number_format($tTotal, 2)); ?></td>
                    <td class="text-right" style="color:#2e7d32;">$<?php echo e(number_format($tAbono, 2)); ?></td>
                    <td class="text-right" style="color:#c62828;">$<?php echo e(number_format($tRestante, 2)); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html><?php /**PATH C:\xampp\htdocs\PiesFelices\resources\views/reports/pdf_credit_sales.blade.php ENDPATH**/ ?>