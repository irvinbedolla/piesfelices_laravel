<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas al Contado</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #212529; }
        .header { text-align: center; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; margin-bottom: 15px; }
        .title { font-size: 16px; font-weight: bold; color: #0d6efd; margin: 0; }
        .subtitle { font-size: 10px; color: #6c757d; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #0d6efd; color: #fff; padding: 6px; font-size: 9px; text-transform: uppercase; }
        td { padding: 6px; border-bottom: 1px solid #dee2e6; font-size: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .total-row { background-color: #e7f1ff; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">REPORTE DE VENTAS AL CONTADO</h1>
        <div class="subtitle">Período: <?php echo e($fecha1); ?> al <?php echo e($fecha2); ?> | Sucursal: <?php echo e($sucursal); ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">FOLIO</th>
                <th class="text-center">FECHA</th>
                <th class="text-center">HORA</th>
                <th>CLIENTE</th>
                <th>VENDEDOR / ATENDIÓ</th>
                <th class="text-center">SUCURSAL</th>
                <th class="text-center">TIPO PAGO</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php $grandTotal = 0; ?>
            <?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php 
                    $grandTotal += $s->venta_total;
                    $vendedor = optional($s->seller)->name ?? optional($s->seller)->username ?? 'S/A';
                ?>
                <tr>
                    <td class="text-center fw-bold">#<?php echo e($s->venta_id); ?></td>
                    <td class="text-center"><?php echo e($s->venta_fecha); ?></td>
                    <td class="text-center"><?php echo e($s->venta_hora); ?></td>
                    <td><?php echo e($s->nombre_cliente ?? 'Cliente General'); ?></td>
                    <td class="fw-bold"><?php echo e(strtoupper($vendedor)); ?></td>
                    <td class="text-center"><?php echo e($s->venta_sucursal); ?></td>
                    <td class="text-center"><?php echo e($s->venta_tipopago); ?></td>
                    <td class="text-right fw-bold">$<?php echo e(number_format($s->venta_total, 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="text-center py-3">No se encontraron ventas al contado en el rango seleccionado.</td>
                </tr>
            <?php endif; ?>
            <?php if(count($sales) > 0): ?>
                <tr class="total-row">
                    <td colspan="7" class="text-right">TOTAL RECAUDADO:</td>
                    <td class="text-right">$<?php echo e(number_format($grandTotal, 2)); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html><?php /**PATH C:\xampp\htdocs\PiesFelices\resources\views/reports/pdf_normal_sales.blade.php ENDPATH**/ ?>