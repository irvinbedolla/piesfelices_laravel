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
    <div class="container-fluid py-4">
        
        
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
            <h5 class="fw-bold text-primary mb-3">
                <i class="fa-solid fa-plus-circle me-2"></i>Registrar Nuevo Gasto
            </h5>
            <form action="<?php echo e(route('expenses.store')); ?>" method="POST" data-loading-text="Guardando Gasto..." id="expenseForm" class="row g-3">
                <?php echo csrf_field(); ?>
                <div class="col-12 col-md-3">
                    <label class="form-label extra-small fw-bold text-muted">Concepto</label>
                    <input type="text" name="concepto" class="form-control rounded-3" placeholder="Ej. GARRAFON DE AGUA" required>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label extra-small fw-bold text-muted">Cantidad ($)</label>
                    <input type="number" step="0.01" name="cantida" class="form-control rounded-3" placeholder="0.00" required>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label extra-small fw-bold text-muted">Fecha</label>
                    <input type="date" name="fecha" value="<?php echo e(date('Y-m-d')); ?>" class="form-control rounded-3" required>
                </div>
                
                
                <div class="col-12 col-md-2">
                    <label class="form-label extra-small fw-bold text-muted">Sucursal</label>
                    <?php if(in_array($userRole, [0, 3])): ?>
                        <select name="sucursal" class="form-select rounded-3">
                            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($b->name); ?>"><?php echo e($b->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php else: ?>
                        <input type="text" value="<?php echo e($userBranch); ?>" class="form-control rounded-3 bg-light" readonly>
                        <input type="hidden" name="sucursal" value="<?php echo e($userBranch); ?>">
                    <?php endif; ?>
                </div>

                <div class="col-12 col-md-2">
                    <label class="form-label extra-small fw-bold text-muted">Tipo de Gasto</label>
                    <select name="tipo" class="form-select rounded-3">
                        <option value="2">CONSULTORIO</option>
                        <option value="1">PROVEEDOR</option>
                        <option value="3">SUELDOS</option>
                    </select>
                </div>
                <div class="col-12 col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary rounded-3 w-100 fw-bold" style="background-color: #e040fb; border: none;">Crear</button>
                </div>
            </form>
        </div>

        
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <h5 class="fw-bold text-dark m-0">
                    <i class="fa-solid fa-receipt me-2 text-primary"></i>Historial de Gastos
                </h5>
                
                
                <form method="GET" action="<?php echo e(route('expenses.index')); ?>" data-loading-text="Filtrando Gastos..." class="d-flex flex-wrap gap-2 align-items-center">
                    <input type="date" name="fecha1" value="<?php echo e($fecha1); ?>" class="form-control form-control-sm rounded-3">
                    <input type="date" name="fecha2" value="<?php echo e($fecha2); ?>" class="form-control form-control-sm rounded-3">
                    
                    <?php if(in_array($userRole, [0, 3])): ?>
                        <select name="sucursal" class="form-select form-select-sm rounded-3">
                            <option value="TODOS" <?php echo e($selectedBranch == 'TODOS' ? 'selected' : ''); ?>>Todas las Sucursales</option>
                            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($b->name); ?>" <?php echo e($selectedBranch == $b->name ? 'selected' : ''); ?>><?php echo e($b->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php else: ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                            <i class="fa-solid fa-hospital me-1"></i> <?php echo e($userBranch); ?>

                        </span>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-sm btn-dark rounded-pill px-3">Filtrar</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>CONCEPTO</th>
                            <th>FECHA</th>
                            <th class="text-end">CANTIDAD</th>
                            <th>SUCURSAL</th>
                            <th>TIPO</th>
                            <th class="text-center">ELIMINAR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="fw-semibold"><?php echo e($e->concepto); ?></td>
                                <td><?php echo e($e->fecha); ?></td>
                                <td class="text-end fw-bold">$<?php echo e(number_format($e->cantida, 2)); ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo e($e->sucursal); ?></span></td>
                                <td>
                                    <?php if($e->tipo == 2): ?> 
                                        <span class="badge bg-info-subtle text-info border">CONSULTORIO</span>
                                    <?php elseif($e->tipo == 1): ?> 
                                        <span class="badge bg-warning-subtle text-warning border">PROVEEDOR</span>
                                    <?php else: ?> 
                                        <span class="badge bg-success-subtle text-success border">SUELDOS</span> 
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <form action="<?php echo e(route('expenses.destroy', $e->id)); ?>" method="POST" data-loading-text="Eliminando Gasto..." onsubmit="return confirm('¿Eliminar gasto?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-sm text-danger border-0"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No hay gastos registrados en esta sede/periodo.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <div class="text-center mt-4 p-3 bg-light rounded-4">
                <div class="row g-2 fs-6 fw-bold">
                    <div class="col-12 col-md-3">CONSULTORIO: <span class="text-info">$<?php echo e(number_format($totalConsultorio, 2)); ?></span></div>
                    <div class="col-12 col-md-3">PROVEEDOR: <span class="text-warning">$<?php echo e(number_format($totalProveedor, 2)); ?></span></div>
                    <div class="col-12 col-md-3">SUELDOS: <span class="text-success">$<?php echo e(number_format($totalSueldos, 2)); ?></span></div>
                    <div class="col-12 col-md-3 fs-5 text-primary">TOTAL: $<?php echo e(number_format($totalGeneral, 2)); ?></div>
                </div>
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
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/expenses/index.blade.php ENDPATH**/ ?>