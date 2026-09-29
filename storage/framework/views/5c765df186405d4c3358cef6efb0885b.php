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
        
        <!-- ENCABEZADO -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark">
                    <i class="fa-solid fa-user-shield text-primary me-2"></i>Gestión de Roles y Permisos
                </h4>
                <p class="text-muted small m-0">Crea plantillas de accesos estandarizadas para el personal de la clínica</p>
            </div>
            <a href="<?php echo e(route('roles.create')); ?>" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                <i class="fa-solid fa-plus me-2"></i>Nuevo Rol
            </a>
        </div>

        <!-- NOTIFICACIONES -->
        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i><?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- TABLA DE ROLES -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Rol</th>
                        <th>Descripción</th>
                        <th class="text-center">Usuarios Asignados</th>
                        <th>Permisos Habilitados</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $perm = $role->permission; ?>
                        <tr>
                            <td>
                                <span class="fw-bold text-dark d-block fs-6"><?php echo e($role->name); ?></span>
                                <small class="text-muted">ID: #<?php echo e($role->id); ?></small>
                            </td>
                            <td>
                                <span class="text-secondary"><?php echo e($role->description ?? 'Sin descripción'); ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-purple-subtle text-purple rounded-pill px-3 py-2 fw-semibold" style="background-color: #f3e5f5; color: #7b1fa2;">
                                    <i class="fa-solid fa-users me-1"></i> <?php echo e($role->users_count); ?> usuarios
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1" style="max-width: 320px;">
                                    <?php if($perm?->can_sales): ?> <span class="badge bg-success-subtle text-success rounded-pill">Ventas</span> <?php endif; ?>
                                    <?php if($perm?->can_inventory): ?> <span class="badge bg-info-subtle text-info rounded-pill">Inventario</span> <?php endif; ?>
                                    <?php if($perm?->can_clients): ?> <span class="badge bg-primary-subtle text-primary rounded-pill">Clientes</span> <?php endif; ?>
                                    <?php if($perm?->can_credit): ?> <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Crédito</span> <?php endif; ?>
                                    <?php if($perm?->can_expenses): ?> <span class="badge bg-danger-subtle text-danger rounded-pill">Gastos</span> <?php endif; ?>
                                    <?php if($perm?->can_cash_closing): ?> <span class="badge bg-dark-subtle text-dark rounded-pill">Cierre</span> <?php endif; ?>
                                    <?php if($perm?->can_users): ?> <span class="badge bg-secondary-subtle text-secondary rounded-pill">Usuarios</span> <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="<?php echo e(route('roles.edit', $role)); ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="Editar Rol">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <form method="POST" action="<?php echo e(route('roles.destroy', $role)); ?>" onsubmit="return confirm('¿Confirmas eliminar este rol? Solo se puede eliminar si no tiene usuarios activos.');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Eliminar Rol" <?php echo e($role->users_count > 0 ? 'disabled' : ''); ?>>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-user-shield fs-1 text-secondary opacity-50 mb-3 d-block"></i>
                                No hay roles registrados todavía. Haz clic en <strong>Nuevo Rol</strong> para agregar el primero.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <?php echo e($roles->links()); ?>

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
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/roles/index.blade.php ENDPATH**/ ?>