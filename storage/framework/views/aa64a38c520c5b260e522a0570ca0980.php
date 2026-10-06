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
                    <i class="fa-solid fa-store text-primary me-2"></i>Gestión de Sucursales
                </h4>
                <p class="text-muted small m-0">Registra y administra las sucursales del sistema</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#createBranchModal">
                <i class="fa-solid fa-plus me-2"></i>Nueva Sucursal
            </button>
        </div>

        
        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i><?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        
        <form method="GET" data-loading-text="Cargando." action="<?php echo e(route('branches.index')); ?>" id="dataTableForm">
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
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por nombre, teléfono, dirección..." onchange="document.getElementById('dataTableForm').submit()">
                        <?php if(request('search')): ?>
                            <a href="<?php echo e(route('branches.index')); ?>" class="btn btn-outline-secondary rounded-pill ms-2">
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
                        <th>
                            <a href="<?php echo e(route('branches.index', array_merge(request()->query(), ['sort_by' => 'name', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc']))); ?>" class="text-dark text-decoration-none">
                                Nombre <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo e(route('branches.index', array_merge(request()->query(), ['sort_by' => 'phone', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc']))); ?>" class="text-dark text-decoration-none">
                                Teléfono <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo e(route('branches.index', array_merge(request()->query(), ['sort_by' => 'address', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc']))); ?>" class="text-dark text-decoration-none">
                                Dirección <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo e(route('branches.index', array_merge(request()->query(), ['sort_by' => 'status', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc']))); ?>" class="text-dark text-decoration-none">
                                Estado <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <span class="fw-bold text-dark"><?php echo e($b->name); ?></span>
                                <?php if($b->is_matrix): ?>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill ms-2 small">Matriz Central</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($b->phone ?? 'Sin teléfono'); ?></td>
                            <td><?php echo e($b->address ?? 'Sin dirección'); ?></td>
                            <td>
                                <?php if($b->status): ?>
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3">Activa</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    
                                        <?php if(!$b->is_matrix): ?>
                                            <form method="POST" data-loading-text="Cargando." action="<?php echo e(route('branches.set-matrix', $b)); ?>" onsubmit="return confirm('¿Deseas establecer <?php echo e($b->name); ?> como la nueva Matriz Central?');">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-2 py-1 small fw-semibold" title="Establecer como Matriz Central">
                                                    <i class="fa-solid fa-star me-1"></i> Asignar Matriz
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-semibold small">
                                                <i class="fa-solid fa-crown me-1"></i> Matriz Actual
                                            </span>
                                        <?php endif; ?>
                                    <form method="POST" data-loading-text="Cargando." action="<?php echo e(route('branches.destroy', $b)); ?>" onsubmit="return confirm('¿Eliminar sucursal?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Eliminar Sucursal" <?php echo e($b->is_matrix ? 'disabled' : ''); ?>>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No se encontraron sucursales coincidentes.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 gap-3">
            <div class="text-muted small">
                Mostrando del <strong><?php echo e($branches->firstItem() ?? 0); ?></strong> al <strong><?php echo e($branches->lastItem() ?? 0); ?></strong> de <strong><?php echo e($branches->total()); ?></strong> registros
            </div>
            <div>
                <?php echo e($branches->links()); ?>

            </div>
        </div>

    </div>

    <!-- MODAL CREAR SUCURSAL -->
    <div class="modal fade" id="createBranchModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <form method="POST" data-loading-text="Cargando." action="<?php echo e(route('branches.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold modal-title"><i class="fa-solid fa-store text-primary me-2"></i>Nueva Sucursal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body py-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre de la Sucursal (*)</label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="Ej. ALTOZANO, CENTRO" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" name="phone" class="form-control rounded-3" placeholder="Ej. 4431234567">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Dirección</label>
                            <textarea name="address" class="form-control rounded-3" rows="2" placeholder="Calle, Número, Colonia"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Guardar</button>
                    </div>
                </form>
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
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/branches/index.blade.php ENDPATH**/ ?>