<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Surtido #<?php echo e($order->id); ?></title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        .header { border-bottom: 2px solid #5a2a82; padding-bottom: 10px; margin-bottom: 15px; }
        .title { font-size: 18px; font-weight: bold; color: #5a2a82; margin: 0; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th { background-color: #f4f4f6; color: #333; padding: 8px; font-size: 10px; border-bottom: 2px solid #ddd; text-align: left; }
        .table td { padding: 8px; border-bottom: 1px solid #eee; font-size: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .notes-box { background: #f9f9f9; border: 1px solid #ddd; padding: 10px; border-radius: 5px; margin-top: 15px; }
    </style>
</head>
<body>

    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <h1 class="title">ORDEN DE SURTIDO DE MATERIALES #<?php echo e($order->id); ?></h1>
                    <div style="font-size: 11px; color: #666; margin-top: 3px;">
                        Sucursal Solicita: <strong><?php echo e($order->branch->name ?? 'Sucursal'); ?></strong>
                    </div>
                </td>
                <td class="text-right">
                    <div><strong>Fecha:</strong> <?php echo e($order->created_at->format('d/m/Y h:i A')); ?></div>
                    <div><strong>Estatus:</strong> <span style="color: #d9534f; font-weight: bold;"><?php echo e($order->status); ?></span></div>
                    <div><strong>Solicitante:</strong> <?php echo e($order->user->name ?? $order->user->username ?? 'Usuario'); ?></div>
                </td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 20%;">Código</th>
                <th style="width: 45%;">Producto / Material</th>
                <th class="text-center" style="width: 15%;">Stock al Pedir</th>
                <th class="text-center" style="width: 15%;">Cantidad Pedida</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $order->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="text-center"><?php echo e($index + 1); ?></td>
                    <td><?php echo e($d->product->barcode ?? 'S/C'); ?></td>
                    <td class="fw-bold"><?php echo e($d->product->name ?? 'Producto no encontrado'); ?></td>
                    <td class="text-center" style="color: #666;"><?php echo e($d->stock_at_request); ?></td>
                    <td class="text-center fw-bold" style="font-size: 12px; color: #5a2a82;"><?php echo e($d->quantity_requested); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <?php if($order->notes): ?>
        <div class="notes-box">
            <strong>Observaciones o Notas:</strong><br>
            <?php echo e($order->notes); ?>

        </div>
    <?php endif; ?>

</body>
</html><?php /**PATH C:\xampp\htdocs\PiesFelices\resources\views/supply_orders/pdf.blade.php ENDPATH**/ ?>