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
                <h4 class="fw-bold m-0 text-dark"><i class="fa-solid fa-user-pen text-primary me-2"></i>Editar Empleado: <?php echo e($employee->name); ?></h4>
                <p class="text-muted small m-0">Actualiza los datos de contacto, salarios y sucursal</p>
            </div>
            <a href="<?php echo e(route('employees.index')); ?>" class="btn btn-outline-secondary rounded-pill px-4">
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

        <form method="POST" data-loading-text="Cargando." action="<?php echo e(route('employees.update', $employee)); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nombre Completo (*)</label>
                    <input type="text" name="name" class="form-control rounded-3" value="<?php echo e(old('name', $employee->name)); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Teléfono</label>
                    <input type="text" name="phone" class="form-control rounded-3" value="<?php echo e(old('phone', $employee->phone)); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Puesto / Cargo</label>
                    <input type="text" name="position" class="form-control rounded-3" value="<?php echo e(old('position', $employee->position)); ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Salario Base ($) (*)</label>
                    <input type="number" step="0.01" name="salary" class="form-control rounded-3" value="<?php echo e(old('salary', $employee->salary)); ?>" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Comisión (%)</label>
                    <input type="number" step="0.01" name="commission_rate" class="form-control rounded-3" value="<?php echo e(old('commission_rate', $employee->commission_rate)); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Sucursal Asignada</label>
                    <select name="branch_id" class="form-select rounded-3">
                        <option value="">-- Seleccionar Sucursal --</option>
                        <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($b->id); ?>" <?php echo e($employee->branch_id == $b->id ? 'selected' : ''); ?>><?php echo e($b->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Usuario de Sistema (Opcional)</label>
                    <select name="user_id" class="form-select rounded-3">
                        <option value="">-- Sin Usuario Asociado --</option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($u->id); ?>" <?php echo e($employee->user_id == $u->id ? 'selected' : ''); ?>><?php echo e($u->username); ?> (<?php echo e($u->email); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Fecha de Contratación</label>
                    <input type="date" name="hired_at" class="form-control rounded-3" value="<?php echo e(old('hired_at', $employee->hired_at?->format('Y-m-d'))); ?>">
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="status" id="status" value="1" <?php echo e($employee->status ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-semibold" for="status">Empleado Activo</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?php echo e(route('employees.index')); ?>" class="btn btn-light rounded-pill px-4">Cancelar</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Actualizar Empleado</button>
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
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/employees/edit.blade.php ENDPATH**/ ?>