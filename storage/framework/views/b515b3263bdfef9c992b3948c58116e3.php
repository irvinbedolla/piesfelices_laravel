<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket Venta #<?php echo e($sale->venta_id); ?></title>
    <style>
        @page { margin: 5px; }
        body {
            font-family: 'Courier', 'Arial', sans-serif;
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 5px;
            width: 100%;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .table th, .table td { font-size: 9px; padding: 2px 0; text-align: left; }

        /* Estilos de la barra flotante de botones (No se imprime) */
        .no-print-bar {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 12px;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }

        .btn-action {
            padding: 6px 12px;
            font-size: 11px;
            font-weight: bold;
            font-family: 'Arial', sans-serif;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-decoration: none;
        }

        .btn-print {
            background-color: #198754;
            color: #ffffff;
        }

        .btn-close-tab {
            background-color: #dc3545;
            color: #ffffff;
        }

        /* OCULTAR LOS BOTONES AL IMPRIMIR EL TICKET */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <!-- TICKET DE VENTA -->
    <div class="text-center">
        <h2 style="font-size: 14px; margin: 0; text-transform: uppercase;">PIES FELICES</h2>
        <div style="font-size: 9px;">Sucursal: <?php echo e($sale->venta_sucursal); ?></div>
        <div style="font-size: 9px;">Fecha: <?php echo e(\Carbon\Carbon::parse($sale->venta_fecha)->format('d/m/Y')); ?> <?php echo e($sale->venta_hora); ?></div>
        <div class="fw-bold" style="font-size: 11px; margin-top: 3px;">TICKET DE VENTA #<?php echo e($sale->venta_id); ?></div>
    </div>

    <div class="divider"></div>

    <div style="font-size: 9px;">
        <div><strong>Cliente:</strong> <?php echo e($sale->customer->nombre ?? $sale->nombre_cliente ?: 'Cliente General'); ?></div>
        <div><strong>Vendedor:</strong> <?php echo e($sale->seller->name ?? $sale->seller->username ?? 'Empleado'); ?></div>
        <div><strong>Tipo Pago:</strong> <?php echo e($sale->venta_tipopago); ?> (<?php echo e($sale->venta_tipo == '1' ? 'Contado' : 'Crédito'); ?>)</div>
    </div>

    <div class="divider"></div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 15%;">Cant</th>
                <th style="width: 55%;">Producto/Servicio</th>
                <th style="width: 30%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $sale->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($item->cantidad); ?></td>
                    <td><?php echo e($item->nombre); ?></td>
                    <td class="text-right">$<?php echo e(number_format($item->cantidad * $item->precio, 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <div class="divider"></div>

    <table style="width: 100%; font-size: 10px;">
        <?php if($sale->venta_descuento > 0): ?>
            <tr>
                <td class="text-right">Descuento:</td>
                <td class="text-right">-$<?php echo e(number_format($sale->venta_descuento, 2)); ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <td class="text-right fw-bold" style="font-size: 12px;">TOTAL:</td>
            <td class="text-right fw-bold" style="font-size: 12px;">$<?php echo e(number_format($sale->venta_total, 2)); ?></td>
        </tr>
        <?php if($sale->venta_abono > 0): ?>
            <tr>
                <td class="text-right">Abono / Efectivo:</td>
                <td class="text-right">$<?php echo e(number_format($sale->venta_abono, 2)); ?></td>
            </tr>
        <?php endif; ?>
    </table>

    <div class="divider"></div>

    <div class="text-center" style="font-size: 8px; margin-top: 8px;">
        ¡Gracias por su preferencia!<br>
        Pies Felices - Salud y Cuidado para tus Pies
    </div>

    <!-- SCRIPT PARA DISPARAR LA IMPRESIÓN AUTOMÁTICA AL ABRIR LA PESTAÑA -->
    <script class="no-print">
        window.onload = function() {
            window.print();
        };
    </script>

</body>
</html><?php /**PATH C:\xampp\htdocs\PiesFelices\resources\views/pos/ticket.blade.php ENDPATH**/ ?>