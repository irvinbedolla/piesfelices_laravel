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
    <div class="card border-0 shadow-sm rounded-4 p-4" style="max-width: 800px; margin: 0 auto;">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Editar Proveedor: <?php echo e($supplier->company_name); ?></h4>
                <p class="text-muted small m-0">Actualiza los datos del proveedor</p>
            </div>
            <a href="<?php echo e(route('suppliers.index')); ?>" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa-solid fa-arrow-left me-2"></i>Volver
            </a>
        </div>

        <form method="POST" data-loading-text="Cargando." action="<?php echo e(route('suppliers.update', $supplier)); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nombre de la Empresa (*)</label>
                    <input type="text" name="company_name" class="form-control rounded-3" value="<?php echo e(old('company_name', $supplier->company_name)); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nombre del Trabajador / Contacto</label>
                    <input type="text" name="contact_name" class="form-control rounded-3" value="<?php echo e(old('contact_name', $supplier->contact_name)); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Teléfono / WhatsApp</label>
                    <input type="text" name="phone" class="form-control rounded-3" value="<?php echo e(old('phone', $supplier->phone)); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ciudad</label>
                    <input type="text" name="city" class="form-control rounded-3" value="<?php echo e(old('city', $supplier->city)); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Correo Electrónico</label>
                    <input type="email" name="email" class="form-control rounded-3" value="<?php echo e(old('email', $supplier->email)); ?>">
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="status" id="status" value="1" <?php echo e($supplier->status ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-semibold" for="status">Proveedor Activo</label>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Dirección</label>
                    <textarea name="address" class="form-control rounded-3" rows="2"><?php echo e(old('address', $supplier->address)); ?></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?php echo e(route('suppliers.index')); ?>" class="btn btn-light rounded-pill px-4">Cancelar</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Actualizar Proveedor</button>
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\PiesFelices\resources\views/suppliers/edit.blade.php ENDPATH**/ ?>