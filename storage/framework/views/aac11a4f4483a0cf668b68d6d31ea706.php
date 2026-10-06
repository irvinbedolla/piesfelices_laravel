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

            
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
                <div>
                    <h4 class="fw-bold text-primary m-0">
                        <i class="fa-solid fa-magnifying-glass-dollar me-2"></i>Consulta General de Ventas
                    </h4>
                    <small class="text-muted">Consola de monitoreo de operaciones, facturas, cancelaciones y abonos</small>
                </div>

                <div>
                    <?php if(in_array($userRole, [1,2])): ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fs-6">
                            <i class="fa-solid fa-user-shield me-1"></i> Modo Administrador
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-3 py-2 fs-6">
                            <i class="fa-solid fa-hospital me-1"></i> Sucursal: <?php echo e($userBranch); ?>

                        </span>
                    <?php endif; ?>
                </div>
            </div>

            
            <ul class="nav nav-pills nav-fill bg-light p-1 rounded-pill mb-4 border">
                <li class="nav-item">
                    <a class="nav-link rounded-pill fw-bold <?php echo e($tab === 'general' ? 'active bg-primary' : 'text-secondary'); ?>" 
                       href="<?php echo e(route('sales.consultation.index', array_merge(request()->all(), ['tab' => 'general']))); ?>">
                        <i class="fa-solid fa-list me-1"></i> Ventas en General
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill fw-bold <?php echo e($tab === 'facturadas' ? 'active bg-success' : 'text-secondary'); ?>" 
                       href="<?php echo e(route('sales.consultation.index', array_merge(request()->all(), ['tab' => 'facturadas']))); ?>">
                        <i class="fa-solid fa-file-invoice-dollar me-1"></i> Facturadas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill fw-bold <?php echo e($tab === 'canceladas' ? 'active bg-danger' : 'text-secondary'); ?>" 
                       href="<?php echo e(route('sales.consultation.index', array_merge(request()->all(), ['tab' => 'canceladas']))); ?>">
                        <i class="fa-solid fa-ban me-1"></i> Canceladas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill fw-bold <?php echo e($tab === 'abonos' ? 'active bg-info text-white' : 'text-secondary'); ?>" 
                       href="<?php echo e(route('sales.consultation.index', array_merge(request()->all(), ['tab' => 'abonos']))); ?>">
                        <i class="fa-solid fa-hand-holding-dollar me-1"></i> Abonos en General
                    </a>
                </li>
            </ul>

            
            <form method="GET" action="<?php echo e(route('sales.consultation.index')); ?>" data-loading-text="Filtrando ventas..." class="row g-2 mb-4 align-items-end">
                <input type="hidden" name="tab" value="<?php echo e($tab); ?>">

                <div class="col-12 col-md-3">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted ps-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" value="<?php echo e($search); ?>" class="form-control form-control-sm border-start-0 rounded-end-pill" placeholder="Folio, cliente...">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Desde</label>
                    <input type="date" name="fecha1" value="<?php echo e($fecha1); ?>" class="form-control form-control-sm rounded-3">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Hasta</label>
                    <input type="date" name="fecha2" value="<?php echo e($fecha2); ?>" class="form-control form-control-sm rounded-3">
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Sucursal</label>
                    <?php if(in_array($userRole, [1,2])): ?>
                        <select name="branch" class="form-select form-select-sm rounded-3 border-primary">
                            <option value="TODOS" <?php echo e($selectedBranch == 'TODOS' ? 'selected' : ''); ?>>-- TODAS LAS SUCURSALES --</option>
                            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($b->name); ?>" <?php echo e($selectedBranch == $b->name ? 'selected' : ''); ?>><?php echo e($b->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php else: ?>
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light" value="<?php echo e($userBranch); ?>" readonly>
                        <input type="hidden" name="branch" value="<?php echo e($userBranch); ?>">
                    <?php endif; ?>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill fw-bold w-100">
                        <i class="fa-solid fa-filter me-1"></i> Filtrar
                    </button>
                    <a href="<?php echo e(route('sales.consultation.index', ['tab' => $tab])); ?>" class="btn btn-sm btn-light border rounded-circle text-muted" title="Limpiar Filtros">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </a>
                </div>
            </form>

            
            <div class="table-responsive">
                <?php if($tab !== 'abonos'): ?>
                    <table class="table table-hover align-middle text-nowrap">
                        <thead class="table-light">
                            <tr class="extra-small fw-bold text-uppercase">
                                <th>N° Venta</th>
                                <th>Fecha / Hora</th>
                                <th>Cliente</th>
                                <th>Sucursal</th>
                                <th>Tipo Pago</th>
                                <th>Factura</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Artículos</th>
                                <?php if(in_array($userRole, [1,2])): ?>
                                    <th class="text-center">Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="fw-bold text-primary">#<?php echo e($v->venta_id); ?></td>
                                    <td>
                                        <div class="fw-semibold"><?php echo e($v->venta_fecha); ?></div>
                                        <small class="text-muted"><?php echo e($v->venta_hora); ?></small>
                                    </td>
                                    <td class="fw-semibold"><?php echo e($v->nombre_cliente ?? 'Cliente General'); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo e($v->venta_sucursal); ?></span></td>
                                    <td><span class="badge bg-info-subtle text-info border border-info-subtle"><?php echo e($v->venta_tipopago); ?></span></td>
                                    <td>
                                        <?php if(in_array(strtoupper($v->venta_factura), ['SI', '1'])): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa-solid fa-check me-1"></i>SI</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">NO</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-bold fs-6">$<?php echo e(number_format($v->venta_total, 2)); ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-link text-decoration-none fw-bold p-0 text-primary" data-bs-toggle="modal" data-bs-target="#saleItemsModal<?php echo e($v->venta_id); ?>">
                                            Ver (<?php echo e(count($v->details)); ?>)
                                        </button>
                                    </td>
                                    <?php if(in_array($userRole, [1,2])): ?>
                                        <td class="text-center">
                                            <?php if($tab !== 'canceladas'): ?>
                                                <form action="<?php echo e(route('sales.consultation.destroy-sale', $v->venta_id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de cancelar esta venta?');">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1 px-2" title="Cancelar / Borrar Venta">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger border">CANCELADA</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>

                                
                                <div class="modal fade" id="saleItemsModal<?php echo e($v->venta_id); ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 border-0 p-3">
                                            <div class="modal-header border-0">
                                                <h5 class="fw-bold text-primary m-0">Detalle Venta #<?php echo e($v->venta_id); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <ul class="list-group list-group-flush">
                                                    <?php $__empty_2 = true; $__currentLoopData = $v->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <div class="fw-bold"><?php echo e($d->nombre ?? 'Producto Sin Nombre'); ?></div>
                                                                <small class="text-muted">Cant: <?php echo e($d->cantidad); ?> x $<?php echo e(number_format($d->precio, 2)); ?></small>
                                                            </div>
                                                            <span class="fw-bold">$<?php echo e(number_format($d->cantidad * $d->precio, 2)); ?></span>
                                                        </li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                                        <li class="list-group-item text-center text-muted">Sin artículos registrados.</li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="<?php echo e(in_array($userRole, [1,2]) ? '9' : '8'); ?>" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-circle-info me-1"></i> No se encontraron ventas para los criterios seleccionados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    
                    <table class="table table-hover align-middle text-nowrap">
                        <thead class="table-light">
                            <tr class="extra-small fw-bold text-uppercase">
                                <th># Abono</th>
                                <th>N° Venta Asociada</th>
                                <th>Fecha / Hora Abono</th>
                                <th>Sucursal</th>
                                <th class="text-end">Monto Abonado</th>
                                <?php if(in_array($userRole, [1,2])): ?>
                                    <th class="text-center">Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="fw-bold text-info">#<?php echo e($a->abono_id ?? $a->id); ?></td>
                                    <td class="fw-bold text-primary">Venta #<?php echo e($a->venta_id); ?></td>
                                    <td><?php echo e($a->abono_fecha ?? $a->created_at); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo e($a->abono_sucursal ?? 'MATRIZ'); ?></span></td>
                                    <td class="text-end fw-bold text-success fs-6">+$<?php echo e(number_format($a->abono_cantidad, 2)); ?></td>
                                    <?php if(in_array($userRole, [1,2])): ?>
                                        <td class="text-center">
                                            <form action="<?php echo e(route('sales.consultation.destroy-abono', $a->abono_id ?? $a->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de borrar este abono? El saldo pendiente de la venta será recalculado.');">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1 px-2" title="Eliminar Abono">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="<?php echo e(in_array($userRole, [1,2]) ? '6' : '5'); ?>" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-circle-info me-1"></i> No hay abonos registrados en el rango de fechas seleccionado.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 pt-2 border-top">
                <div class="small text-muted mb-2">
                    Mostrando del <strong><?php echo e($records->firstItem() ?? 0); ?></strong> al <strong><?php echo e($records->lastItem() ?? 0); ?></strong> de <strong><?php echo e($records->total()); ?></strong> registros
                </div>
                <div>
                    <?php echo e($records->links('pagination::bootstrap-5')); ?>

                </div>
            </div>

            
            <?php if($tab !== 'abonos'): ?>
                <div class="row g-3 mt-3 pt-3 border-top">
                    <div class="col-6 col-md-3">
                        <div class="card border rounded-3 p-3 text-center bg-white shadow-sm">
                            <small class="fw-bold text-muted extra-small text-uppercase d-block mb-1">MEDICAMENTO CRÉDITO</small>
                            <h5 class="fw-bold text-dark m-0">$<?php echo e(number_format($summary['medicamento_credito'], 2)); ?></h5>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border rounded-3 p-3 text-center bg-white shadow-sm">
                            <small class="fw-bold text-muted extra-small text-uppercase d-block mb-1">CONSULTA CRÉDITO</small>
                            <h5 class="fw-bold text-dark m-0">$<?php echo e(number_format($summary['consulta_credito'], 2)); ?></h5>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border border-danger-subtle rounded-3 p-3 text-center bg-danger-subtle shadow-sm">
                            <small class="fw-bold text-danger extra-small text-uppercase d-block mb-1">TOTAL MEDICAMENTOS</small>
                            <h5 class="fw-bold text-danger m-0">$<?php echo e(number_format($summary['total_medicamentos'], 2)); ?></h5>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border border-danger-subtle rounded-3 p-3 text-center bg-danger-subtle shadow-sm">
                            <small class="fw-bold text-danger extra-small text-uppercase d-block mb-1">TOTAL CONSULTAS</small>
                            <h5 class="fw-bold text-danger m-0">$<?php echo e(number_format($summary['total_consultas'], 2)); ?></h5>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <div class="card border-0 text-white rounded-4 p-3 shadow" style="background: linear-gradient(135deg, #d81b60 0%, #e91e63 100%); min-width: 320px;">
                        <div class="d-flex justify-content-between align-items-center mb-1 fs-6">
                            <span>Descuentos Totales:</span>
                            <strong>-$<?php echo e(number_format($summary['descuentos'], 2)); ?></strong>
                        </div>
                        <hr class="my-1 border-white opacity-50">
                        <div class="d-flex justify-content-between align-items-center fs-5 fw-bold">
                            <span>GRAN TOTAL VENTAS:</span>
                            <span class="fs-4">$<?php echo e(number_format($summary['gran_total'], 2)); ?></span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="d-flex justify-content-end mt-3">
                    <div class="card border-0 text-white rounded-4 p-3 shadow" style="background: linear-gradient(135deg, #0288d1 0%, #03a9f4 100%); min-width: 320px;">
                        <div class="d-flex justify-content-between align-items-center fs-5 fw-bold">
                            <span>TOTAL ABONOS REGISTRADOS:</span>
                            <span class="fs-4">$<?php echo e(number_format($summary['gran_total'], 2)); ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

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
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/sales_consultation/index.blade.php ENDPATH**/ ?>