<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas por Artículo</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 3px solid #8e24aa; padding-bottom: 8px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; border-bottom: 3px solid #8e24aa; padding-bottom: 8px; }
        .header-table td { vertical-align: middle; }
        .logo-img { height: 50px; width: auto; max-width: 160px; }
        .header-title { text-align: center; }
        .header-title h2 { color: #8e24aa; margin: 0; font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .header-title p { margin: 2px 0 0 0; font-weight: bold; color: #555; font-size: 11px; }
        
        .header h2 { color: #8e24aa; margin: 0; font-size: 20px; }
        .info { margin-bottom: 12px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th { background-color: #8e24aa; color: white; padding: 8px 5px; font-size: 10px; text-transform: uppercase; }
        td { padding: 6px 5px; border-bottom: 1px solid #e0e0e0; font-size: 10px; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .total-row { background-color: #f3e5f5; font-weight: bold; }
    </style>
</head>
<body>

    
    <table class="header-table">
        <tr>
            <td width="20%" style="text-align: left;">
                <?php if(file_exists(public_path('images/logo_pdf.svg'))): ?>
                    <img src="<?php echo e(public_path('images/logo_pdf.svg')); ?>" class="logo-img" alt="Pies Felices">
                <?php elseif(file_exists(public_path('images/logo.png'))): ?>
                    <img src="<?php echo e(public_path('images/logo.png')); ?>" class="logo-img" alt="Pies Felices">
                <?php endif; ?>
            </td>
            <td width="60%" class="header-title">
                <h2>PIES FELICES</h2>
                <p>Control e Historial de Citas Registradas</p>
            </td>
            <td width="20%" style="text-align: right;">
                <?php if(file_exists(public_path('images/logo_sociedad.svg'))): ?>
                    <img src="<?php echo e(public_path('images/logo_sociedad.svg')); ?>" class="logo-img" alt="Sociedad Mexicana de Podología">
                <?php elseif(file_exists(public_path('images/logo_sociedad.png'))): ?>
                    <img src="<?php echo e(public_path('images/logo_sociedad.png')); ?>" class="logo-img" alt="Sociedad Mexicana de Podología">
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <div class="info">
        <strong>Periodo:</strong> <?php echo e($fecha1); ?> al <?php echo e($fecha2); ?> | 
        <strong>Sucursal:</strong> <?php echo e($sucursal); ?> 
        <?php if(!empty($articulo)): ?> | <strong>Filtro Artículo:</strong> "<?php echo e($articulo); ?>" <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th width="8%"># VENTA</th>
                <th width="10%">FECHA</th>
                <th width="10%">HORA</th>
                <th width="22%">CLIENTE</th>
                <th width="25%">ARTÍCULO / PRODUCTO</th>
                <th width="7%" class="text-center">CANT.</th>
                <th width="9%" class="text-end">PRECIO U.</th>
                <th width="9%" class="text-end">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php $granTotal = 0; ?>
            <?php $__empty_1 = true; $__currentLoopData = $salesData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $subtotal = $item->cantidad * $item->precio; $granTotal += $subtotal; ?>
                <tr>
                    <td class="text-center"><strong>#<?php echo e($item->venta_id); ?></strong></td>
                    <td class="text-center"><?php echo e($item->venta_fecha); ?></td>
                    <td class="text-center"><?php echo e($item->venta_hora); ?></td>
                    <td><?php echo e($item->nombre_cliente ?? 'Cliente General'); ?></td>
                    <td><strong><?php echo e($item->producto_nombre); ?></strong></td>
                    <td class="text-center"><?php echo e($item->cantidad); ?></td>
                    <td class="text-end">$<?php echo e(number_format($item->precio, 2)); ?></td>
                    <td class="text-end"><strong>$<?php echo e(number_format($subtotal, 2)); ?></strong></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #777;">
                        No se encontraron ventas para los criterios seleccionados.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <?php if(count($salesData) > 0): ?>
            <tfoot>
                <tr class="total-row">
                    <td colspan="7" class="text-end" style="padding: 8px;">TOTAL GENERAL:</td>
                    <td class="text-end" style="padding: 8px; color: #8e24aa;">$<?php echo e(number_format($granTotal, 2)); ?></td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>

</body>
</html><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/reports/pdf_articulos.blade.php ENDPATH**/ ?>