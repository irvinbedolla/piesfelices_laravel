<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Directorio de Clientes - Pies Felices</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        .header { border-bottom: 2px solid #5a2a82; padding-bottom: 10px; margin-bottom: 15px; }
        .title { font-size: 16px; font-weight: bold; color: #5a2a82; margin: 0; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th { background-color: #f4f4f6; color: #333; text-align: left; padding: 6px; font-size: 9px; border-bottom: 2px solid #ddd; }
        .table td { padding: 6px; border-bottom: 1px solid #eee; font-size: 9px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>

    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <h1 class="title">DIRECTORIO DE CLIENTES / PACIENTES</h1>
                    <div style="font-size: 10px; color: #666; margin-top: 3px;">Sucursal Filtro: <strong><?php echo e($selectedBranch); ?></strong></div>
                </td>
                <td class="text-right">
                    <div>Fecha: <?php echo e(date('d/m/Y h:i A')); ?></div>
                    <div>Generado por: <?php echo e($user->name ?? $user->username ?? 'Sistema'); ?></div>
                </td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 25%;">Nombre</th>
                <th style="width: 15%;">Teléfono</th>
                <th style="width: 15%;">RFC</th>
                <th style="width: 15%;">Correo</th>
                <th style="width: 16%;">Localidad / CP</th>
                <th style="width: 10%;">Sucursal</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="text-center"><?php echo e($index + 1); ?></td>
                    <td><strong><?php echo e($c->nombre); ?></strong></td>
                    <td><?php echo e($c->telefono ?? 'N/A'); ?></td>
                    <td><?php echo e($c->rfc ?? 'N/A'); ?></td>
                    <td><?php echo e($c->correo ?? 'N/A'); ?></td>
                    <td><?php echo e($c->localidad ?? 'N/A'); ?> (CP: <?php echo e($c->cp ?? 'S/N'); ?>)</td>
                    <td><?php echo e($c->sucursal); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">No hay clientes registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/customers/pdf.blade.php ENDPATH**/ ?>