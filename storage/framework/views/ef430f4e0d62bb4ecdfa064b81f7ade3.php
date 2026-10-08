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
    <div class="card border-0 shadow-sm rounded-4 p-4">
        
        
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark">
                    <i class="fa-solid fa-list-check text-primary me-2"></i>Movimientos de Inventario
                </h4>
                <p class="text-muted small m-0">Historial inmutable de entradas, salidas, traspasos y ajustes de stock</p>
            </div>
            <a href="<?php echo e(route('inventory.index')); ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold shadow-sm">
                <i class="fa-solid fa-arrow-left me-2"></i>Volver al Inventario
            </a>
        </div>

        
        <form method="GET" data-loading-text="Cargando." action="<?php echo e(route('inventory.movements')); ?>" id="dataTableForm">
            <input type="hidden" name="sort_by" value="<?php echo e($sortBy); ?>">
            <input type="hidden" name="sort_order" value="<?php echo e($sortOrder); ?>">

            <div class="row align-items-center mb-3 g-3">
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <span class="text-muted small">Mostrar</span>
                    <select name="per_page" class="form-select form-select-sm rounded-3" style="width: 80px;" onchange="document.getElementById('dataTableForm').submit()">
                        <option value="10" <?php echo e($perPage == 10 ? 'selected' : ''); ?>>10</option>
                        <option value="25" <?php echo e($perPage == 25 ? 'selected' : ''); ?>>25</option>
                        <option value="50" <?php echo e($perPage == 50 ? 'selected' : ''); ?>>50</option>
                        <option value="100" <?php echo e($perPage == 100 ? 'selected' : ''); ?>>100</option>
                    </select>
                    <span class="text-muted small">registros por página</span>
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por producto, tipo o notas..." onchange="document.getElementById('dataTableForm').submit()">
                        <?php if(request('search')): ?>
                            <a href="<?php echo e(route('inventory.movements')); ?>" class="btn btn-outline-secondary rounded-pill ms-2">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>

        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha / Hora</th>
                        <th>Producto</th>
                        <th>Movimiento</th>
                        <th>Cantidad</th>
                        <th>Stock Anterior</th>
                        <th>Nuevo Stock</th>
                        <th>Usuario / Responsable</th>
                        <th>Motivo / Referencia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <span class="fw-semibold text-dark d-block"><?php echo e($m->created_at->format('d/m/Y')); ?></span>
                                <small class="text-muted"><?php echo e($m->created_at->format('h:i A')); ?></small>
                            </td>
                            <td>
                                <strong class="text-dark d-block"><?php echo e($m->product->name ?? 'Producto eliminado'); ?></strong>
                                <small class="text-muted"><?php echo e($m->branch->name ?? 'MATRIZ'); ?></small>
                            </td>
                            <td>
                                <?php if($m->type === 'ENTRADA'): ?>
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3">ENTRADA</span>
                                <?php elseif($m->type === 'SALIDA'): ?>
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3">SALIDA</span>
                                <?php elseif($m->type === 'TRASPASO'): ?>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3">TRASPASO</span>
                                <?php elseif($m->type === 'DEVOLUCION'): ?>
                                    <span class="badge bg-info-subtle text-info rounded-pill px-3">DEVOLUCIÓN</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3"><?php echo e($m->type); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold fs-6"><?php echo e($m->quantity); ?></td>
                            <td class="text-muted"><?php echo e($m->previous_stock); ?></td>
                            <td class="fw-bold text-dark"><?php echo e($m->new_stock); ?></td>
                            <td>
                                <span class="badge bg-light text-dark border rounded-pill px-3">
                                    <i class="fa-solid fa-user me-1 text-secondary"></i> <?php echo e($m->user->name ?? $m->user->username ?? 'Sistema'); ?>

                                </span>
                            </td>
                            <td>
                                <span class="d-block text-dark fw-semibold small"><?php echo e($m->reference ?? 'Sin referencia'); ?></span>
                                <span class="text-muted extra-small"><?php echo e($m->notes); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No se encontraron movimientos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 gap-3">
            <div class="text-muted small">
                Mostrando del <strong><?php echo e($movements->firstItem() ?? 0); ?></strong> al <strong><?php echo e($movements->lastItem() ?? 0); ?></strong> de <strong><?php echo e($movements->total()); ?></strong> movimientos
            </div>
            <div>
                <?php echo e($movements->links()); ?>

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
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/inventory/movements.blade.php ENDPATH**/ ?>