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
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark"><i class="fa-solid fa-user-pen text-primary me-2"></i>Editar Usuario: <?php echo e($user->username); ?></h4>
                <p class="text-muted small m-0">Actualiza las credenciales, la sucursal asignada o el rol de acceso</p>
            </div>
            <a href="<?php echo e(route('users.index')); ?>" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa-solid fa-arrow-left me-2"></i>Volver
            </a>
        </div>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger rounded-3 mb-4">
                <ul class="m-0 ps-3">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('users.update', $user)); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <?php $perm = $user->permission; ?>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Nombre de Usuario (*)</label>
                    <input type="text" name="username" class="form-control rounded-3" value="<?php echo e(old('username', $user->username)); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Correo Electrónico</label>
                    <input type="email" name="email" class="form-control rounded-3" value="<?php echo e(old('email', $user->email)); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Nueva Contraseña (Opcional)</label>
                    <input type="password" name="password" class="form-control rounded-3" placeholder="Dejar en blanco para conservar la actual">
                </div>

                <!-- SUCURSAL -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Sucursal Asignada (*)</label>
                    <select name="branch_name" class="form-select rounded-3" required>
                        <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($b->name); ?>" <?php echo e($user->branch_name === $b->name ? 'selected' : ''); ?>><?php echo e($b->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- ROL -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Rol Predefinido</label>
                    <select name="role_id" class="form-select rounded-3">
                        <option value="">-- Sin Rol (Matriz Manual) --</option>
                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($r->id); ?>" <?php echo e($user->role_id == $r->id ? 'selected' : ''); ?>><?php echo e($r->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Tipo / Nivel (*)</label>
                    <select name="user_type" class="form-select rounded-3" required>
                        <option value="1" <?php echo e($user->user_type == 1 ? 'selected' : ''); ?>>Operador / Vendedor</option>
                        <option value="0" <?php echo e($user->user_type == 0 ? 'selected' : ''); ?>>Administrador General</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Estado (*)</label>
                    <select name="status" class="form-select rounded-3" required>
                        <option value="activo" <?php echo e($user->status === 'activo' ? 'selected' : ''); ?>>Activo</option>
                        <option value="inactivo" <?php echo e($user->status === 'inactivo' ? 'selected' : ''); ?>>Inactivo</option>
                    </select>
                </div>

                <div class="col-md-12 d-flex gap-4 mt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_doctor" id="is_doctor" value="1" <?php echo e($user->is_doctor ? 'checked' : ''); ?>>
                        <label class="form-check-input-label fw-semibold" for="is_doctor">¿Es Podólogo / Médico?</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="has_permission" id="has_permission" value="1" <?php echo e($user->has_permission ? 'checked' : ''); ?>>
                        <label class="form-check-input-label fw-semibold" for="has_permission">Activar Permisos Personalizados (Sobrescribe al Rol)</label>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <h5 class="fw-bold mb-3"><i class="fa-solid fa-shield-halved text-primary me-2"></i>Matriz de Permisos Específicos</h5>
            <div class="row g-3 bg-light p-3 rounded-4 mb-4">
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_sales" value="1" id="can_sales" <?php echo e($perm?->can_sales ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-medium" for="can_sales">Vender / Punto de Venta</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_inventory" value="1" id="can_inventory" <?php echo e($perm?->can_inventory ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-medium" for="can_inventory">Inventario</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_clients" value="1" id="can_clients" <?php echo e($perm?->can_clients ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-medium" for="can_clients">Clientes / Pacientes</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_credit" value="1" id="can_credit" <?php echo e($perm?->can_credit ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-medium" for="can_credit">Módulo de Créditos</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_expenses" value="1" id="can_expenses" <?php echo e($perm?->can_expenses ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-medium" for="can_expenses">Gastos</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_cash_closing" value="1" id="can_cash_closing" <?php echo e($perm?->can_cash_closing ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-medium" for="can_cash_closing">Cierre de Caja</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_suppliers" value="1" id="can_suppliers" <?php echo e($perm?->can_suppliers ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-medium" for="can_suppliers">Proveedores</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_employees" value="1" id="can_employees" <?php echo e($perm?->can_employees ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-medium" for="can_employees">Empleados</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_users" value="1" id="can_users" <?php echo e($perm?->can_users ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-medium" for="can_users">Gestión de Usuarios</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_prescriptions" value="1" id="can_prescriptions" <?php echo e($perm?->can_prescriptions ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-medium" for="can_prescriptions">Multimedia / Recetas</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?php echo e(route('users.index')); ?>" class="btn btn-light rounded-pill px-4">Cancelar</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Actualizar Usuario</button>
            </div>
        </form>
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
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/users/edit.blade.php ENDPATH**/ ?>