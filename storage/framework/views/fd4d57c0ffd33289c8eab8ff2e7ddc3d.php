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
    <div class="container-fluid px-4 py-4">
        
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold text-dark m-0">
                <i class="fa-solid fa-vault me-2 text-primary"></i>Cierres de Caja & Reportes
            </h4>
            
            <?php if(!$canAccessAll): ?>
                <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-3 py-2">
                    <i class="fa-solid fa-hospital me-1"></i> Sucursal: <?php echo e($userBranchName); ?>

                </span>
            <?php endif; ?>
        </div>

        
        <div class="row g-4 align-items-stretch">
            
            
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="p-3 bg-primary-subtle text-primary rounded-4">
                                <i class="fa-solid fa-receipt fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark m-0">Cierre Diario</h5>
                                <small class="text-muted">Por sucursal o general</small>
                            </div>
                        </div>

                        <form id="formCierreDiario" action="<?php echo e(route('cash-closing.daily')); ?>" method="GET" data-loading-text="Cargando." target="_blank" class="d-flex flex-column h-100">
                            <div class="mb-2">
                                <label class="form-label extra-small fw-bold text-muted mb-1">Fecha Inicial</label>
                                <input type="date" name="fecha1" value="<?php echo e(date('Y-m-d')); ?>" 
                                       <?php if(!$canAccessAll): ?> min="<?php echo e(date('Y-m-d')); ?>" max="<?php echo e(date('Y-m-d')); ?>" <?php endif; ?> 
                                       class="form-control form-control-sm rounded-3" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label extra-small fw-bold text-muted mb-1">Fecha Final</label>
                                <input type="date" name="fecha2" value="<?php echo e(date('Y-m-d')); ?>" 
                                       <?php if(!$canAccessAll): ?> min="<?php echo e(date('Y-m-d')); ?>" max="<?php echo e(date('Y-m-d')); ?>" <?php endif; ?> 
                                       class="form-control form-control-sm rounded-3" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label extra-small fw-bold text-muted mb-1">Sucursal</label>
                                <select name="sucursal" class="form-select form-select-sm rounded-3">
                                    <?php if($canAccessAll): ?>
                                        <option value="TODOS">Todas las Sucursales</option>
                                    <?php endif; ?>
                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($b->name); ?>"><?php echo e($b->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="pt-2">
                        <button type="submit" form="formCierreDiario" class="btn btn-primary btn-sm w-100 rounded-pill fw-semibold">
                            Generar Cierre Diario PDF
                        </button>
                    </div>
                </div>
            </div>

            
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="p-3 bg-success-subtle text-success rounded-4">
                                <i class="fa-solid fa-store fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark m-0">Cierre Colectivo</h5>
                                <small class="text-muted">Selección múltiple</small>
                            </div>
                        </div>

                        <form id="formCierreColectivo" action="<?php echo e(route('cash-closing.daily')); ?>" method="GET" data-loading-text="Cargando." target="_blank" class="d-flex flex-column h-100">
                            <div class="mb-2">
                                <label class="form-label extra-small fw-bold text-muted mb-1">Rango de Fechas</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" name="fecha1" value="<?php echo e(date('Y-m-d')); ?>" 
                                           <?php if(!$canAccessAll): ?> min="<?php echo e(date('Y-m-d')); ?>" max="<?php echo e(date('Y-m-d')); ?>" <?php endif; ?> 
                                           class="form-control rounded-start-3" required>
                                    <input type="date" name="fecha2" value="<?php echo e(date('Y-m-d')); ?>" 
                                           <?php if(!$canAccessAll): ?> min="<?php echo e(date('Y-m-d')); ?>" max="<?php echo e(date('Y-m-d')); ?>" <?php endif; ?> 
                                           class="form-control rounded-end-3" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label extra-small fw-bold text-muted d-block mb-1">Selecciona Sucursales:</label>
                                <div class="overflow-auto p-2 border rounded-3 bg-light-subtle" style="height: 115px;">
                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="form-check extra-small mb-1">
                                            
                                            <input class="form-check-input" type="checkbox" name="sucursales[]" value="<?php echo e($b->name); ?>" id="b_<?php echo e($b->id); ?>">
                                            <label class="form-check-label text-dark fw-semibold" for="b_<?php echo e($b->id); ?>"><?php echo e($b->name); ?></label>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="pt-2">
                        <button type="submit" form="formCierreColectivo" class="btn btn-success btn-sm w-100 rounded-pill fw-semibold">
                            Generar Colectivo PDF
                        </button>
                    </div>
                </div>
            </div>

            
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="p-3 bg-warning-subtle text-warning rounded-4">
                                <i class="fa-solid fa-user-gear fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark m-0">Comisiones</h5>
                                <small class="text-muted">Reporte por Empleado</small>
                            </div>
                        </div>

                        <form id="formComisiones" action="<?php echo e(route('cash-closing.commissions')); ?>" method="GET" data-loading-text="Cargando." target="_blank" class="d-flex flex-column h-100">
                            <div class="mb-2">
                                <label class="form-label extra-small fw-bold text-muted mb-1">Rango de Fechas</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" name="fecha1" value="<?php echo e(date('Y-m-d')); ?>" 
                                           <?php if(!$canAccessAll): ?> min="<?php echo e(date('Y-m-d')); ?>" max="<?php echo e(date('Y-m-d')); ?>" <?php endif; ?> 
                                           class="form-control rounded-start-3" required>
                                    <input type="date" name="fecha2" value="<?php echo e(date('Y-m-d')); ?>" 
                                           <?php if(!$canAccessAll): ?> min="<?php echo e(date('Y-m-d')); ?>" max="<?php echo e(date('Y-m-d')); ?>" <?php endif; ?> 
                                           class="form-control rounded-end-3" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label extra-small fw-bold text-muted mb-1">Sucursal</label>
                                <select name="sucursal" class="form-select form-select-sm rounded-3">
                                    <?php if($canAccessAll): ?>
                                        <option value="TODOS">Todas las Sucursales</option>
                                    <?php endif; ?>
                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($b->name); ?>"><?php echo e($b->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label extra-small fw-bold text-muted mb-1">Empleado</label>
                                <select name="usuario" class="form-select form-select-sm rounded-3" required>
                                    <option value="">-- Seleccionar Empleado --</option>
                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($u->id); ?>"><?php echo e($u->name ?? $u->username); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="pt-2">
                        <button type="submit" form="formComisiones" class="btn btn-warning btn-sm w-100 rounded-pill fw-semibold text-dark">
                            Calcular Comisión
                        </button>
                    </div>
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\PiesFelices\resources\views/cash_closing/index.blade.php ENDPATH**/ ?>