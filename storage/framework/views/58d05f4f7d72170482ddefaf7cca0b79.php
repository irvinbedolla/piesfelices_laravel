<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="container py-4">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            
            
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 pb-3 border-bottom gap-2">
                <div>
                    <h5 class="fw-bold text-dark m-0">
                        <i class="fa-solid fa-calculator me-2 text-warning"></i>Reporte de Comisiones por Empleado
                    </h5>
                    <p class="text-muted small m-0 mt-1">
                        <strong>Empleado:</strong> <span class="text-primary fw-bold"><?php echo e($employeeName); ?></span> | 
                        <strong>Periodo:</strong> <?php echo e($fecha1); ?> al <?php echo e($fecha2); ?> | 
                        <strong>Sucursal:</strong> <?php echo e($sucursal ?: 'TODOS'); ?>

                    </p>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    
                    <a href="<?php echo e(route('cash-closing.commissions-pdf', ['usuario' => $userId, 'fecha1' => $fecha1, 'fecha2' => $fecha2, 'sucursal' => $sucursal])); ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fa-solid fa-print me-1"></i> Imprimir PDF
                    </a>

                    
                    <?php
                        $pdfUrl = route('cash-closing.commissions-pdf', ['usuario' => $userId, 'fecha1' => $fecha1, 'fecha2' => $fecha2, 'sucursal' => $sucursal]);
                        $mensajeWS = "Hola *{$employeeName}*, te compartimos tu reporte de comisiones del período *{$fecha1} al {$fecha2}*.\n\n*Total a pagar:* $" . number_format($totalComisiones, 2) . "\n\nPuedes ver o descargar tu reporte en PDF aquí:\n" . $pdfUrl;
                        $urlWhatsApp = "https://api.whatsapp.com/send?phone=" . ($employeePhoneClean ? '52' . $employeePhoneClean : '') . "&text=" . urlencode($mensajeWS);
                    ?>

                    <a href="<?php echo e($urlWhatsApp); ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                        <i class="fa-brands fa-whatsapp me-1"></i> Enviar a WhatsApp
                    </a>
                </div>
            </div>

            
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr class="extra-small fw-bold text-uppercase">
                            <th>Concepto / Categoría</th>
                            <th class="text-end">Venta Total</th>
                            <th class="text-center">% Comisión</th>
                            <th class="text-end">Comisión Ganada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-semibold text-dark"><i class="fa-solid fa-box-open me-2 text-info"></i>Venta General</td>
                            <td class="text-end">$<?php echo e(number_format($totalGeneral, 2)); ?></td>
                            <td class="text-center"><span class="badge bg-light text-dark border"><?php echo e($pctGeneral); ?>%</span></td>
                            <td class="text-end fw-bold text-success">$<?php echo e(number_format($comisionGeneral, 2)); ?></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-dark"><i class="fa-solid fa-shoe-prints me-2 text-primary"></i>Venta de Calzado</td>
                            <td class="text-end">$<?php echo e(number_format($totalCalzado, 2)); ?></td>
                            <td class="text-center"><span class="badge bg-light text-dark border"><?php echo e($pctCalzado); ?>%</span></td>
                            <td class="text-end fw-bold text-success">$<?php echo e(number_format($comisionCalzado, 2)); ?></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-dark"><i class="fa-solid fa-pills me-2 text-danger"></i>Venta de Medicamento</td>
                            <td class="text-end">$<?php echo e(number_format($totalMedicamento, 2)); ?></td>
                            <td class="text-center"><span class="badge bg-light text-dark border"><?php echo e($pctMedicamento); ?>%</span></td>
                            <td class="text-end fw-bold text-success">$<?php echo e(number_format($comisionMedicamento, 2)); ?></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-dark"><i class="fa-solid fa-user-doctor me-2 text-warning"></i>Consultas y Servicios</td>
                            <td class="text-end">$<?php echo e(number_format($totalConsulta, 2)); ?></td>
                            <td class="text-center"><span class="badge bg-warning-subtle text-dark border border-warning"><?php echo e($pctConsulta); ?>%</span></td>
                            <td class="text-end fw-bold text-success">$<?php echo e(number_format($comisionConsulta, 2)); ?></td>
                        </tr>
                        <tr class="table-light fw-bold">
                            <td class="text-end">TOTALES:</td>
                            <td class="text-end text-dark">$<?php echo e(number_format($totalVentas, 2)); ?></td>
                            <td class="text-center">-</td>
                            <td class="text-end text-primary fs-5">$<?php echo e(number_format($totalComisiones, 2)); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\PiesFelices\resources\views/cash_closing/commissions_view.blade.php ENDPATH**/ ?>