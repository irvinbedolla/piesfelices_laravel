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
    <div class="card border-0 shadow-sm rounded-4 p-4" style="max-width: 900px; margin: 0 auto;">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark"><i class="fa-solid fa-user-plus text-primary me-2"></i>Registrar Nuevo Usuario</h4>
                <p class="text-muted small m-0">Ingresa las credenciales, sucursal y rol asignado</p>
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

        <form method="POST" action="<?php echo e(route('users.store')); ?>">
            <?php echo csrf_field(); ?>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nombre de Usuario (*)</label>
                    <input type="text" name="username" class="form-control rounded-3" value="<?php echo e(old('username')); ?>" required placeholder="Ej. juanperez">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Correo Electrónico</label>
                    <input type="email" name="email" class="form-control rounded-3" value="<?php echo e(old('email')); ?>" placeholder="juan@piesfelices.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Contraseña (*)</label>
                    <input type="password" name="password" class="form-control rounded-3" required placeholder="******">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Sucursal Asignada (*)</label>
                    <select name="branch_name" class="form-select rounded-3" required>
                        <option value="">-- Seleccionar Sucursal --</option>
                        <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($b->name); ?>" <?php echo e(old('branch_name') == $b->name ? 'selected' : ''); ?>><?php echo e($b->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Rol de Acceso (*)</label>
                    <select name="role_id" class="form-select rounded-3" required>
                        <option value="">-- Seleccionar Rol --</option>
                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($r->id); ?>" <?php echo e(old('role_id') == $r->id ? 'selected' : ''); ?>><?php echo e($r->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tipo / Nivel (*)</label>
                    <select name="user_type" class="form-select rounded-3" required>
                        <option value="1">Operador / Vendedor</option>
                        <option value="0">Administrador General</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Estado (*)</label>
                    <select name="status" class="form-select rounded-3" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>

                <div class="col-md-12 mt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_doctor" id="is_doctor" value="1">
                        <label class="form-check-label fw-semibold" for="is_doctor">¿Es Podólogo / Médico?</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?php echo e(route('users.index')); ?>" class="btn btn-light rounded-pill px-4">Cancelar</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar Usuario</button>
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
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/users/create.blade.php ENDPATH**/ ?>