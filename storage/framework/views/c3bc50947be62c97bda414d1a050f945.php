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

                <div class="d-flex flex-wrap align-items-center gap-2">
                    
                    <form method="GET" action="<?php echo e(route('credits.index')); ?>" data-loading-text="Buscando..." class="d-flex align-items-center gap-2">
                        <?php if($selectedBranch): ?>
                            <input type="hidden" name="branch" value="<?php echo e($selectedBranch); ?>">
                        <?php endif; ?>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted ps-3">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" name="search" value="<?php echo e($search ?? ''); ?>" class="form-control form-control-sm border-start-0 rounded-end-pill pe-3" placeholder="Buscar cliente, N° venta...">
                        </div>
                        <?php if(!empty($search)): ?>
                            <a href="<?php echo e(route('credits.index', ['branch' => $selectedBranch])); ?>" class="btn btn-sm btn-light rounded-circle text-muted" title="Limpiar Búsqueda">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        <?php endif; ?>
                    </form>

                    
                    <?php if(in_array($userRole, [1, 2])): ?>
                        <form method="GET" action="<?php echo e(route('credits.index')); ?>" data-loading-text="Cargando Sucursal...">
                            <?php if(!empty($search)): ?>
                                <input type="hidden" name="search" value="<?php echo e($search); ?>">
                            <?php endif; ?>
                            <select name="branch" onchange="this.form.submit()" class="form-select form-select-sm rounded-pill fw-semibold border-primary">
                                <option value="TODOS" <?php echo e($selectedBranch == 'TODOS' ? 'selected' : ''); ?>>-- TODAS LAS SUCURSALES --</option>
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($b->name); ?>" <?php echo e($selectedBranch == $b->name ? 'selected' : ''); ?>><?php echo e($b->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </form>
                    <?php else: ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fs-6">
                            <i class="fa-solid fa-hospital me-1"></i> Sucursal: <?php echo e($userBranch); ?>

                        </span>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap">
                    <thead class="table-light">
                        <tr class="extra-small fw-bold text-uppercase">
                            <th>N° Venta</th>
                            <th>Hora</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Sucursal</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Abono</th>
                            <th class="text-end">Restante</th>
                            <th class="text-center">D.Venta</th>
                            <th class="text-center">D.Abonos</th>
                            <th class="text-center">Abonar</th>
                            <th class="text-center">Borrar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $credits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $restanteCalculado = $c->venta_restante > 0 ? $c->venta_restante : ($c->venta_total - $c->venta_abono);
                            ?>
                            <tr>
                                <td class="fw-bold text-primary">#<?php echo e($c->venta_id); ?></td>
                                <td><?php echo e($c->venta_hora); ?></td>
                                <td><?php echo e($c->venta_fecha); ?></td>
                                <td class="fw-semibold"><?php echo e($c->nombre_cliente ?? 'Cliente General'); ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo e($c->venta_sucursal); ?></span></td>
                                <td class="text-end fw-bold">$<?php echo e(number_format($c->venta_total, 2)); ?></td>
                                <td class="text-end text-success fw-bold">$<?php echo e(number_format($c->venta_abono, 2)); ?></td>
                                <td class="text-end text-danger fw-bold">$<?php echo e(number_format($restanteCalculado, 2)); ?></td>

                                
                                <td class="text-center">
                                    <button class="btn btn-sm btn-link text-decoration-none fw-bold p-0" data-bs-toggle="modal" data-bs-target="#saleDetailModal<?php echo e($c->venta_id); ?>">
                                        DETALLE
                                    </button>
                                </td>

                                
                                <td class="text-center">
                                    <button class="btn btn-sm btn-link text-decoration-none fw-bold p-0 text-info" data-bs-toggle="modal" data-bs-target="#paymentDetailModal<?php echo e($c->venta_id); ?>">
                                        D.ABONO
                                    </button>
                                </td>

                                
                                <td class="text-center">
                                    <button class="btn btn-sm btn-link text-decoration-none fw-bold p-0 text-success" data-bs-toggle="modal" data-bs-target="#addPaymentModal<?php echo e($c->venta_id); ?>">
                                        ABONAR
                                    </button>
                                </td>

                                
                                <td class="text-center">
                                    <form action="<?php echo e(route('credits.destroy', $c->venta_id)); ?>" method="POST" data-loading-text="Cancelando Venta..." onsubmit="return confirm('¿Está seguro de cancelar esta venta a crédito?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm p-0 border-0 text-danger">
                                            <i class="fa-solid fa-circle-xmark fs-5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            
                            <div class="modal fade" id="saleDetailModal<?php echo e($c->venta_id); ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 p-3">
                                        <div class="modal-header border-0">
                                            <h5 class="fw-bold text-primary m-0">Artículos Vendidos - Venta #<?php echo e($c->venta_id); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <ul class="list-group list-group-flush">
                                                <?php $__empty_2 = true; $__currentLoopData = $c->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <div class="fw-bold"><?php echo e($d->nombre ?? 'Producto Sin Nombre'); ?></div>
                                                            <small class="text-muted">Cant: <?php echo e($d->cantidad); ?> x $<?php echo e(number_format($d->precio, 2)); ?></small>
                                                        </div>
                                                        <span class="fw-bold">$<?php echo e(number_format($d->cantidad * $d->precio, 2)); ?></span>
                                                    </li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                                    <li class="list-group-item text-center text-muted">No hay productos registrados en el detalle.</li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="modal fade" id="paymentDetailModal<?php echo e($c->venta_id); ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 p-3">
                                        <div class="modal-header border-0">
                                            <h5 class="fw-bold text-info m-0">Historial de Abonos - Venta #<?php echo e($c->venta_id); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <ul class="list-group list-group-flush">
                                                <?php $__empty_2 = true; $__currentLoopData = $c->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <div class="fw-bold text-success">+$<?php echo e(number_format($p->abono_cantidad, 2)); ?></div>
                                                            <small class="text-muted"><?php echo e($p->abono_fecha); ?></small>
                                                        </div>
                                                        <span class="badge bg-light text-dark border"><?php echo e($p->abono_sucursal); ?></span>
                                                    </li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                                    <li class="list-group-item text-center text-muted">No hay abonos registrados para esta venta.</li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="modal fade" id="addPaymentModal<?php echo e($c->venta_id); ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 p-3">
                                        <div class="modal-header border-0">
                                            <h5 class="fw-bold text-success m-0">Abonar a Venta #<?php echo e($c->venta_id); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?php echo e(route('credits.payment', $c->venta_id)); ?>" method="POST" data-loading-text="Guardando Abono...">
                                            <?php echo csrf_field(); ?>
                                            <div class="modal-body">
                                                <div class="row mb-3 bg-light p-3 rounded-3 g-2 fs-6">
                                                    <div class="col-6">Total Venta: <strong>$<?php echo e(number_format($c->venta_total, 2)); ?></strong></div>
                                                    <div class="col-6 text-danger">Restante: <strong>$<?php echo e(number_format($restanteCalculado, 2)); ?></strong></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label extra-small fw-bold text-muted">Monto a Abonar ($)</label>
                                                    <input type="number" step="0.01" name="monto" max="<?php echo e($restanteCalculado); ?>" class="form-control rounded-3 fs-5 text-center fw-bold text-success" placeholder="0.00" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Guardar Abono</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="12" class="text-center py-4 text-muted">
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
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/credits/index.blade.php ENDPATH**/ ?>