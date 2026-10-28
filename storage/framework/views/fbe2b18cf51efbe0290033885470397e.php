<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Facturación</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #8E24AA; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; color: #8E24AA; margin: 0; }
        .subtitle { font-size: 11px; color: #666; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #8E24AA; color: #fff; padding: 6px; font-size: 9px; text-align: left; }
        td { padding: 6px; border-bottom: 1px solid #ddd; font-size: 9px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; background-color: #f3e5f5; }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="title">REPORTE DE VENTAS FACTURADAS</h1>
        <div class="subtitle">
            Período: <?php echo e($fecha1); ?> al <?php echo e($fecha2); ?> | Sucursal: <?php echo e($sucursal); ?>

        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">FOLIO</th>
                <th class="text-center">N° FACTURA</th>
                <th class="text-center">FECHA</th>
                <th>CLIENTE</th>
                <th>CORREO</th>
                <th class="text-center">CFDI</th>
                <th class="text-center">SUCURSAL</th>
                <th class="text-center">TIPO PAGO</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php $grandTotal = 0; ?>
            <?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php 
                    $grandTotal += $sale->venta_total; 
                    $correo = $sale->correo_cliente ?? $sale->customer->correo ?? $sale->customer->email ?? 'N/A';
                ?>
                <tr>
                    <td class="text-center">#<?php echo e($sale->venta_id); ?></td>
                    <td class="text-center"><strong><?php echo e($sale->numero_factura ?? 'S/N'); ?></strong></td>
                    <td class="text-center"><?php echo e($sale->venta_fecha); ?></td>
                    <td><?php echo e($sale->nombre_cliente ?? 'Cliente General'); ?></td>
                    <td><?php echo e($correo); ?></td>
                    <td class="text-center"><?php echo e($sale->venta_cfdi ?? 'G03'); ?></td>
                    <td class="text-center"><?php echo e($sale->venta_sucursal); ?></td>
                    <td class="text-center"><?php echo e($sale->venta_tipopago); ?></td>
                    <td class="text-right">$<?php echo e(number_format($sale->venta_total, 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="9" class="text-center py-3">No se encontraron ventas facturadas en el período seleccionado.</td>
                </tr>
            <?php endif; ?>
            <?php if(count($sales) > 0): ?>
                <tr class="total-row">
                    <td colspan="8" class="text-right">TOTAL FACTURADO:</td>
                    <td class="text-right">$<?php echo e(number_format($grandTotal, 2)); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html><?php /**PATH C:\xampp\htdocs\PiesFelices\resources\views/reports/pdf_invoices.blade.php ENDPATH**/ ?>