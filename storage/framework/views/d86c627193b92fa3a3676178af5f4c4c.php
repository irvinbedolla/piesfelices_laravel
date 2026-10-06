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
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold text-primary m-0">
                        <i class="fa-solid fa-notes-medical me-2"></i>Expedientes y Recetas Médicas
                    </h4>
                    <small class="text-muted">Selecciona una sucursal para consultar sus recetas</small>
                </div>
                <a href="<?php echo e(route('prescriptions.create')); ?>" class="btn btn-primary rounded-pill px-3">
                    <i class="fa-solid fa-plus me-1"></i> Nueva Receta
                </a>
            </div>

            
            <form method="GET" data-loading-text="Cargando." action="<?php echo e(route('prescriptions.index')); ?>" class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label extra-small fw-bold text-muted">Seleccionar Sucursal:</label>
                    <select name="branch" onchange="this.form.submit()" class="form-select rounded-3">
                        <option value="TODOS" <?php echo e($selectedBranch == 'TODOS' ? 'selected' : ''); ?>>-- TODAS LAS SUCURSALES --</option>
                        <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($b->name); ?>" <?php echo e($selectedBranch == $b->name ? 'selected' : ''); ?>><?php echo e($b->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </form>

            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Cliente / Paciente</th>
                            <th>Diagnóstico</th>
                            <th>Receta / Tratamiento</th>
                            <th>Fecha</th>
                            <th>Próxima Cita</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $prescriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><span class="badge bg-light text-primary border">#<?php echo e($p->id); ?></span></td>
                                <td>
                                    <div class="fw-bold text-dark"><i class="fa-solid fa-user me-1 text-muted"></i> <?php echo e($p->patient_name); ?></div>
                                </td>
                                <td><?php echo e(Str::limit($p->diagnosis, 35)); ?></td>
                                <td><?php echo e(Str::limit($p->indications, 45)); ?></td>
                                <td><?php echo e($p->created_at->format('d/m/Y')); ?></td>
                                <td>
                                    <?php if($p->next_appointment): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="fa-regular fa-calendar me-1"></i><?php echo e(\Carbon\Carbon::parse($p->next_appointment)->format('d/m/Y')); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?php echo e(route('prescriptions.pdf', $p->id)); ?>" target="_blank" class="btn btn-sm btn-danger rounded-3" title="Descargar PDF">
                                        <i class="fa-solid fa-file-pdf"></i> PDF
                                    </a>
                                    <a href="<?php echo e(route('prescriptions.edit', $p->id)); ?>" class="btn btn-sm btn-primary rounded-3" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="<?php echo e(route('prescriptions.destroy', $p->id)); ?>" method="POST" data-loading-text="Cargando." class="d-inline" onsubmit="return confirm('¿Seguro que deseas borrar esta receta?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Borrar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No se encontraron recetas en esta sucursal.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <?php echo e($prescriptions->appends(request()->query())->links()); ?>

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
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/prescriptions/index.blade.php ENDPATH**/ ?>