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
    <div class="container-fluid px-2 px-md-4 py-3">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h3 class="fw-bold text-dark m-0">
                        <i class="fa-solid fa-boxes-packing text-primary me-2"></i>Orden de Surtido de Materiales
                    </h3>
                    <small class="text-muted">Genera un pedido para abastecer el stock de tu sucursal</small>
                </div>
                
                
                <form method="GET" data-loading-text="Cargando." action="<?php echo e(route('supply-orders.create')); ?>" class="d-flex align-items-center gap-2">
                    <label class="fw-bold text-secondary mb-0 text-nowrap"><i class="fa-solid fa-store text-primary me-1"></i> Sucursal:</label>
                    <select name="branch_id" class="form-select rounded-pill border-primary shadow-sm" onchange="this.form.submit()" <?php echo e($user->type == 1 ? 'disabled' : ''); ?>>
                        <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($b->id); ?>" <?php echo e($selectedBranchId == $b->id ? 'selected' : ''); ?>>
                                <?php echo e($b->name); ?> <?php echo e($b->is_matrix ? '(Matriz)' : ''); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </form>
            </div>

            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="<?php echo e(route('supply-orders.store')); ?>" method="POST" id="formOrdenSurtido" target="_blank">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="branch_id" value="<?php echo e($selectedBranchId); ?>">

                
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-dark btn-sm rounded-start-pill" id="btnMarcarTodos">
                            <i class="fa-solid fa-check-double me-1"></i> Marcar Todos
                        </button>
                        <button type="button" class="btn btn-outline-dark btn-sm rounded-end-pill" id="btnDesmarcarTodos">
                            <i class="fa-solid fa-xmark me-1"></i> Desmarcar Todos
                        </button>
                    </div>
                    <span class="text-muted small">Los productos marcados con <b>Bajo Stock</b> se seleccionan automáticamente.</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100 mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">
                                    <input type="checkbox" id="checkMaestro" class="form-check-input" title="Marcar / Desmarcar todos">
                                </th>
                                <th>Código</th>
                                <th>Material / Producto</th>
                                <th class="text-center">Stock Actual</th>
                                <th class="text-center" style="width: 180px;">Cantidad a Pedir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $pivot = $p->branches->first()?->pivot;
                                    $currentStock = $pivot ? $pivot->stock_current : 0;
                                    $minStock = $pivot ? $pivot->stock_min : 1;
                                    $isLowStock = $currentStock <= $minStock;
                                ?>
                                <tr class="<?php echo e($isLowStock ? 'table-warning' : ''); ?>">
                                    <td class="text-center">
                                        <input type="checkbox" name="products[]" value="<?php echo e($p->id); ?>" class="form-check-input check-item" <?php echo e($isLowStock ? 'checked' : ''); ?>>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo e($p->barcode ?? 'S/C'); ?></span></td>
                                    <td class="fw-bold">
                                        <?php echo e($p->name); ?>

                                        <?php if($isLowStock): ?>
                                            <span class="badge bg-danger ms-2" style="font-size: 10px;">Stock Bajo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge fs-6 <?php echo e($isLowStock ? 'bg-danger' : 'bg-success'); ?>">
                                            <?php echo e($currentStock); ?>

                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" name="quantity[<?php echo e($p->id); ?>]" value="5" min="1" class="form-control form-control-sm text-center fw-bold border-primary rounded-3">
                                        <input type="hidden" name="stock_actual[<?php echo e($p->id); ?>]" value="<?php echo e($currentStock); ?>">
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No hay productos registrados en esta sucursal.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mb-3 mt-4">
                    <label class="form-label fw-bold text-dark">Observaciones o Notas de la Orden:</label>
                    <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="Escribe detalles adicionales sobre el pedido..."></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo e(route('inventory.index')); ?>" class="btn btn-outline-secondary rounded-pill px-4">Regresar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm fw-semibold">
                        <i class="fa-solid fa-paper-plane me-1"></i> Generar Orden de Surtido
                    </button>
                </div>
            </form>

        </div>
    </div>

    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkMaestro = document.getElementById('checkMaestro');
            const checkItems = document.querySelectorAll('.check-item');
            const btnMarcar = document.getElementById('btnMarcarTodos');
            const btnDesmarcar = document.getElementById('btnDesmarcarTodos');

            if (checkMaestro) {
                checkMaestro.addEventListener('change', function () {
                    checkItems.forEach(item => item.checked = this.checked);
                });
            }

            if (btnMarcar) {
                btnMarcar.addEventListener('click', function () {
                    checkItems.forEach(item => item.checked = true);
                    if (checkMaestro) checkMaestro.checked = true;
                });
            }

            if (btnDesmarcar) {
                btnDesmarcar.addEventListener('click', function () {
                    checkItems.forEach(item => item.checked = false);
                    if (checkMaestro) checkMaestro.checked = false;
                });
            }
        });
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/supply_orders/create.blade.php ENDPATH**/ ?>