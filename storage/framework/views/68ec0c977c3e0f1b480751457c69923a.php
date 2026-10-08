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
            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-calculator me-2 text-warning"></i>Reporte de Comisiones</h5>
            <p class="text-muted small"><strong>Empleado:</strong> <?php echo e($employeeName); ?> | <strong>Periodo:</strong> <?php echo e($fecha1); ?> al <?php echo e($fecha2); ?> | <strong>Sucursal:</strong> <?php echo e($sucursal); ?></p>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Concepto</th>
                            <th class="text-end">Venta Total</th>
                            <th class="text-center">% Comisión</th>
                            <th class="text-end">Comisión Ganada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Venta General</td>
                            <td class="text-end">$<?php echo e(number_format($totalGeneral, 2)); ?></td>
                            <td class="text-center">10%</td>
                            <td class="text-end fw-bold text-success">$<?php echo e(number_format($comisionGeneral, 2)); ?></td>
                        </tr>
                        <tr>
                            <td>Venta de Calzado</td>
                            <td class="text-end">$<?php echo e(number_format($totalCalzado, 2)); ?></td>
                            <td class="text-center">10%</td>
                            <td class="text-end fw-bold text-success">$<?php echo e(number_format($comisionCalzado, 2)); ?></td>
                        </tr>
                        <tr>
                            <td>Venta de Medicamento</td>
                            <td class="text-end">$<?php echo e(number_format($totalMedicamento, 2)); ?></td>
                            <td class="text-center">10%</td>
                            <td class="text-end fw-bold text-success">$<?php echo e(number_format($comisionMedicamento, 2)); ?></td>
                        </tr>
                        <tr>
                            <td>Consultas y Servicios</td>
                            <td class="text-end">$<?php echo e(number_format($totalConsulta, 2)); ?></td>
                            <td class="text-center">Personalizado</td>
                            <td class="text-end fw-bold text-success">$<?php echo e(number_format($comisionConsulta, 2)); ?></td>
                        </tr>
                        <tr class="table-light fw-bold fs-6">
                            <td colspan="3" class="text-end">TOTAL A PAGAR:</td>
                            <td class="text-end text-primary">$<?php echo e(number_format($totalComisiones, 2)); ?></td>
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
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/cash_closing/commissions_view.blade.php ENDPATH**/ ?>