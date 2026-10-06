<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Citas</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; border-bottom: 3px solid #8e24aa; padding-bottom: 8px; }
        .header-table td { vertical-align: middle; }
        .logo-img { height: 50px; width: auto; max-width: 160px; }
        .header-title { text-align: center; }
        .header-title h2 { color: #8e24aa; margin: 0; font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .header-title p { margin: 2px 0 0 0; font-weight: bold; color: #555; font-size: 11px; }

        .info { margin-bottom: 12px; font-size: 11px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        table.data-table th { background-color: #8e24aa; color: white; padding: 8px 5px; font-size: 10px; text-transform: uppercase; border: 1px solid #7b1fa2; }
        table.data-table td { padding: 6px 5px; border-bottom: 1px solid #e0e0e0; font-size: 10px; }
        .text-center { text-align: center; }
        .badge { background: #f0f0f0; padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; border: 1px solid #ccc; }
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
        <strong>Sucursal:</strong> <?php echo e($sucursal ?? 'TODAS'); ?>

    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="6%"># ID</th>
                <th width="12%">FECHA</th>
                <th width="8%">HORA</th>
                <th width="22%">PACIENTE / CLIENTE</th>
                <th width="12%">TELÉFONO</th>
                <th width="18%">ATENDIÓ / PODÓLOGO</th>
                <th width="14%">SERVICIO / MOTIVO</th>
                <th width="8%">SUCURSAL</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $app): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="text-center"><strong>#<?php echo e($app['id']); ?></strong></td>
                    <td class="text-center"><?php echo e($app['fecha']); ?></td>
                    <td class="text-center"><?php echo e($app['hora']); ?></td>
                    <td><strong><?php echo e($app['cliente']); ?></strong></td>
                    <td><?php echo e($app['telefono']); ?></td>
                    <td><?php echo e($app['podologo']); ?></td>
                    <td><?php echo e($app['servicio']); ?></td>
                    <td class="text-center"><span class="badge"><?php echo e($app['sucursal']); ?></span></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #777;">
                        No se encontraron citas registradas en el periodo seleccionado.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/reports/pdf_citas.blade.php ENDPATH**/ ?>