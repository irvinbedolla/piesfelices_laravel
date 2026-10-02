<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario - <?php echo e($branch->name ?? 'Pies Felices'); ?></title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        .header { border-bottom: 2px solid #5a2a82; padding-bottom: 10px; margin-bottom: 15px; }
        .title { font-size: 18px; font-weight: bold; color: #5a2a82; margin: 0; }
        .subtitle { font-size: 11px; color: #666; margin-top: 3px; }
        .meta-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .meta-table td { padding: 4px 0; font-size: 10px; }
        .badge { padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-warning { background-color: #fff3cd; color: #856404; }
        .badge-info { background-color: #d1ecf1; color: #0c5460; }
        .badge-success { background-color: #d4edda; color: #155724; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th { background-color: #f4f4f6; color: #333; text-align: left; padding: 7px; font-size: 10px; border-bottom: 2px solid #ddd; }
        .table td { padding: 6px 7px; border-bottom: 1px solid #eee; font-size: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 9px; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <h1 class="title">PIES FELICES - REPORTE DE INVENTARIO</h1>
                    <div class="subtitle">Sucursal: <strong><?php echo e($branch->name ?? 'Matriz Central'); ?></strong></div>
                </td>
                <td class="text-right">
                    <div style="font-size: 10px; color: #666;">Fecha de emisión: <?php echo e(date('d/m/Y h:i A')); ?></div>
                    <div style="font-size: 10px; color: #666;">Generado por: <?php echo e($user->name ?? $user->username ?? 'Sistema'); ?></div>
                </td>
            </tr>
        </table>
    </div>

    
    <table class="meta-table">
        <tr>
            <td><strong>Filtro Tipo:</strong> <?php echo e($selectedType ?: 'TODOS LOS TIPOS'); ?></td>
            <td><strong>Estado de Consulta:</strong> 
                <?php if($onlyLowStock): ?>
                    <span class="badge badge-warning">SOLO BAJO STOCK</span>
                <?php else: ?>
                    <span class="badge badge-success">DISPONIBLES EN EXISTENCIA</span>
                <?php endif; ?>
            </td>
            <td class="text-right"><strong>Total Artículos:</strong> <?php echo e(count($products)); ?></td>
        </tr>
    </table>

    
    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">Código</th>
                <th style="width: 35%;">Producto / Detalles</th>
                <th style="width: 15%;">Tipo</th>
                <th class="text-right" style="width: 15%;">Precio Venta</th>
                <th class="text-center" style="width: 15%;">Stock</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $branchPivot = $p->branches->first()?->pivot;
                    $stock = $branchPivot ? $branchPivot->stock_current : 0;
                    $stockMin = $branchPivot ? $branchPivot->stock_min : 1;
                ?>
                <tr>
                    <td class="text-center"><?php echo e($index + 1); ?></td>
                    <td><?php echo e($p->barcode ?? 'S/C'); ?></td>
                    <td>
                        <strong><?php echo e($p->name); ?></strong>
                        <?php if($p->type === 'CALZADO' && ($p->model || $p->color)): ?>
                            <br><small style="color: #666;">Mod: <?php echo e($p->model); ?> | Col: <?php echo e($p->color); ?> | Pág: <?php echo e($p->page_number); ?></small>
                        <?php elseif($p->type === 'MEDICAMENTO' && $p->substance): ?>
                            <br><small style="color: #666;">Sustancia: <?php echo e($p->substance); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge badge-info"><?php echo e($p->type); ?></span></td>
                    <td class="text-right fw-bold">$<?php echo e(number_format($p->sale_price, 2)); ?></td>
                    <td class="text-center">
                        <strong style="color: <?php echo e($stock <= $stockMin ? '#d9534f' : '#333'); ?>;"><?php echo e($stock); ?></strong> 
                        <span style="font-size: 9px; color: #888;">(Mín: <?php echo e($stockMin); ?>)</span>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px; color: #888;">No hay productos que coincidan con los filtros seleccionados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Pies Felices - Sistema de Control de Inventarios y Puntos de Venta &copy; <?php echo e(date('Y')); ?>

    </div>

</body>
</html><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/inventory/pdf.blade.php ENDPATH**/ ?>