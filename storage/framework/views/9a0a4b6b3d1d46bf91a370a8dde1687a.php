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
    <div class="container-fluid py-4">

        <div class="card border-0 shadow-sm rounded-4 p-4">
            
            
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h4 class="fw-bold text-primary m-0">
                        <i class="fa-solid fa-credit-card me-2"></i>Control de Créditos Pendientes
                    </h4>
                    <small class="text-muted">Ventas a crédito registradas directamente en el sistema</small>
                </div>

                
                <form method="GET" action="<?php echo e(route('credits.index')); ?>" data-loading-text="Filtrando créditos..." class="d-flex flex-wrap align-items-center gap-2">
                    
                    
                    <div class="input-group input-group-sm" style="min-width: 220px;">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted ps-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" value="<?php echo e($search ?? ''); ?>" class="form-control form-control-sm border-start-0 rounded-end-pill pe-3" placeholder="Buscar cliente, N° venta...">
                    </div>

                    
                    <?php if($isAdmin ?? in_array($userRole, [0, 1, 3])): ?>
                        <select name="branch" onchange="this.form.submit()" class="form-select form-select-sm rounded-pill fw-semibold border-primary">
                            <option value="TODOS" <?php echo e((empty($selectedBranch) || $selectedBranch == 'TODOS') ? 'selected' : ''); ?>>-- TODAS LAS SUCURSALES --</option>
                            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($b->name); ?>" <?php echo e($selectedBranch == $b->name ? 'selected' : ''); ?>><?php echo e($b->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php else: ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fs-6">
                            <i class="fa-solid fa-hospital me-1"></i> Sucursal: <?php echo e($userBranch); ?>

                        </span>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-sm btn-primary rounded-pill fw-bold">
                        <i class="fa-solid fa-filter me-1"></i> Filtrar
                    </button>

                    <?php if(!empty($search) || ($selectedBranch && $selectedBranch !== 'TODOS')): ?>
                        <a href="<?php echo e(route('credits.index')); ?>" class="btn btn-sm btn-light border rounded-circle text-muted" title="Limpiar Filtros">
                            <i class="fa-solid fa-arrows-rotate"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap">
                    <thead class="table-light">
                        <tr class="extra-small fw-bold text-uppercase">
                            <th>N° Venta</th>
                            <th>HORA</th>
                            <th>FECHA</th>
                            <th>VENDEDOR</th>
                            <th>CLIENTE</th>
                            <th>SUCURSAL</th>
                            <th class="text-end">TOTAL</th>
                            <th class="text-end">ABONO</th>
                            <th class="text-end">RESTANTE</th>
                            <th class="text-center">D.VENTA</th>
                            <th class="text-center">D.ABONOS</th>
                            <th class="text-center">ABONAR</th>
                            <th class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $credits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $restanteCalculado = $c->venta_restante > 0 ? $c->venta_restante : ($c->venta_total - $c->venta_abono);
                                
                                // Extrae el nombre del usuario asignado en 'venta_idempleado'
                                $vendedorNombre = $c->seller->name 
                                    ?? $c->seller->username 
                                    ?? 'S/A';
                            ?>
                            <tr>
                                <td class="fw-bold text-dark"><?php echo e($c->venta_id); ?></td>
                                <td><?php echo e($c->venta_hora); ?></td>
                                <td><?php echo e($c->venta_fecha); ?></td>
                                <td class="fw-semibold text-uppercase"><?php echo e($vendedorNombre); ?></td>
                                <td class="fw-semibold text-uppercase"><?php echo e($c->nombre_cliente ?? 'CLIENTE GENERAL'); ?></td>
                                <td class="text-uppercase"><?php echo e($c->venta_sucursal); ?></td>
                                <td class="text-end fw-bold">$<?php echo e(number_format($c->venta_total, 2)); ?></td>
                                <td class="text-end text-success fw-bold">$<?php echo e(number_format($c->venta_abono, 2)); ?></td>
                                <td class="text-end text-danger fw-bold">$<?php echo e(number_format($restanteCalculado, 2)); ?></td>

                                
                                <td class="text-center">
                                    <button class="btn btn-sm btn-link text-decoration-underline fw-bold p-0 text-primary" data-bs-toggle="modal" data-bs-target="#saleDetailModal<?php echo e($c->venta_id); ?>">
                                        DETALLE
                                    </button>
                                </td>

                                
                                <td class="text-center">
                                    <button class="btn btn-sm btn-link text-decoration-underline fw-bold p-0 text-info" data-bs-toggle="modal" data-bs-target="#paymentDetailModal<?php echo e($c->venta_id); ?>">
                                        D.ABONO
                                    </button>
                                </td>

                                
                                <td class="text-center">
                                    <button class="btn btn-sm btn-link text-decoration-underline fw-bold p-0 text-success" data-bs-toggle="modal" data-bs-target="#addPaymentModal<?php echo e($c->venta_id); ?>">
                                        ABONAR
                                    </button>
                                </td>

                                
                                <td class="text-center">
                                    <form action="<?php echo e(route('credits.destroy', $c->venta_id)); ?>" method="POST" data-loading-text="Cancelando Venta..." onsubmit="return confirm('¿Está seguro de cancelar esta venta a crédito?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm p-0 border-0 text-danger" title="Cancelar Venta">
                                            <i class="fa-solid fa-circle-xmark fs-5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="13" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-check-circle me-1 text-success"></i> No se encontraron ventas a crédito.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-3 border-top">
                <div class="small text-muted">
                    Mostrando del <strong><?php echo e($credits->firstItem() ?? 0); ?></strong> al <strong><?php echo e($credits->lastItem() ?? 0); ?></strong> de <strong><?php echo e($credits->total()); ?></strong> créditos pendientes
                </div>
                <div>
                    <?php echo e($credits->links('pagination::bootstrap-5')); ?>

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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\PiesFelices\resources\views/credits/index.blade.php ENDPATH**/ ?>