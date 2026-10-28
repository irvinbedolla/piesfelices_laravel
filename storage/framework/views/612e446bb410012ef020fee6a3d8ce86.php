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
                    <?php if($isAdmin): ?>
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
                       href="<?php echo e(route('sales.consultation.index', array_merge(request()->query(), ['tab' => 'general']))); ?>">
                        <i class="fa-solid fa-list me-1"></i> Ventas en General
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill fw-bold <?php echo e($tab === 'facturadas' ? 'active bg-success' : 'text-secondary'); ?>" 
                       href="<?php echo e(route('sales.consultation.index', array_merge(request()->query(), ['tab' => 'facturadas']))); ?>">
                        <i class="fa-solid fa-file-invoice-dollar me-1"></i> Facturadas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill fw-bold <?php echo e($tab === 'canceladas' ? 'active bg-danger' : 'text-secondary'); ?>" 
                       href="<?php echo e(route('sales.consultation.index', array_merge(request()->query(), ['tab' => 'canceladas']))); ?>">
                        <i class="fa-solid fa-ban me-1"></i> Canceladas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill fw-bold <?php echo e($tab === 'abonos' ? 'active bg-info text-white' : 'text-secondary'); ?>" 
                       href="<?php echo e(route('sales.consultation.index', array_merge(request()->query(), ['tab' => 'abonos']))); ?>">
                        <i class="fa-solid fa-hand-holding-dollar me-1"></i> Abonos en General
                    </a>
                </li>
            </ul>

            
            <form method="GET" action="<?php echo e(route('sales.consultation.index')); ?>" data-loading-text="Filtrando ventas..." class="row g-2 mb-4 align-items-end">
                <input type="hidden" name="tab" value="<?php echo e($tab); ?>">

                
                <div class="col-12 col-md-3 col-lg-2">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted ps-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" value="<?php echo e($search); ?>" class="form-control form-control-sm border-start-0 rounded-end-pill" placeholder="Folio, cliente...">
                    </div>
                </div>

                
                <div class="col-6 col-md-2 col-lg-2">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Desde</label>
                    <input type="date" name="fecha1" value="<?php echo e($fecha1); ?>" class="form-control form-control-sm rounded-3">
                </div>

                
                <div class="col-6 col-md-2 col-lg-2">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Hasta</label>
                    <input type="date" name="fecha2" value="<?php echo e($fecha2); ?>" class="form-control form-control-sm rounded-3">
                </div>

                
                <div class="col-12 col-md-3 col-lg-2">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Sucursal</label>
                    <?php if($isAdmin): ?>
                        <select name="branch" class="form-select form-select-sm rounded-3 border-primary">
                            <option value="TODOS" <?php echo e((empty($selectedBranch) || $selectedBranch == 'TODOS') ? 'selected' : ''); ?>>-- TODAS --</option>
                            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($b->name); ?>" <?php echo e($selectedBranch == $b->name ? 'selected' : ''); ?>>
                                    <?php echo e($b->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php else: ?>
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light" value="<?php echo e($userBranch); ?>" readonly>
                        <input type="hidden" name="branch" value="<?php echo e($userBranch); ?>">
                    <?php endif; ?>
                </div>

                
                <div class="col-12 col-md-3 col-lg-2">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Trabajador</label>
                    <select name="user_id" class="form-select form-select-sm rounded-3 border-info">
                        <option value="TODOS" <?php echo e((empty($selectedUser) || $selectedUser == 'TODOS') ? 'selected' : ''); ?>>-- TODOS --</option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($u->id); ?>" <?php echo e($selectedUser == $u->id ? 'selected' : ''); ?>>
                                <?php echo e($u->name ?? $u->username); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="col-12 col-md-2 col-lg-2 d-flex gap-2">
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
                    <table class="table table-hover align-middle text-nowrap extra-small">
                        <thead class="table-light">
                            <tr class="fw-bold text-uppercase border-bottom">
                                <th>N° Venta</th>
                                <th>Hora</th>
                                <th>Fecha</th>
                                <th>Vendedor</th>
                                <th>Cliente</th>
                                <th>Tipo Pago</th>
                                <th>Sucursal</th>
                                <th class="text-end">Medicamento</th>
                                <th class="text-end">Consulta</th>
                                <th class="text-end">Calzado</th>
                                <th class="text-end">General</th>
                                <th class="text-center">Desc.</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Último Abono</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Detalles</th>
                                <?php if($isAdmin): ?>
                                    <th class="text-center">Eliminar</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $isCred = in_array($v->venta_tipo, [2, '2', 'CREDITO']) || str_contains(strtoupper($v->venta_tipopago ?? ''), 'CREDITO');
                                ?>
                                <tr>
                                    <td class="fw-bold text-primary">#<?php echo e($v->venta_id); ?></td>
                                    <td><?php echo e($v->venta_hora ?? '00:00:00'); ?></td>
                                    <td><?php echo e($v->venta_fecha); ?></td>
                                    <td class="fw-semibold text-uppercase">
                                        <?php
                                            // Intentar obtener el usuario mediante la relación o buscando por ID en la colección $users
                                            $sellerUser = $v->seller ?? $users->firstWhere('id', $v->venta_idempleado);
                                        ?>
                                        <?php echo e($sellerUser->name ?? $sellerUser->username ?? $v->vendedor_nombre ?? ('Usuario #' . $v->venta_idempleado)); ?>

                                    </td>
                                    <td class="fw-semibold"><?php echo e($v->nombre_cliente ?? 'Selecciona'); ?></td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">
                                            <?php echo e($v->venta_tipopago ?? 'Efectivo'); ?>

                                        </span>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo e($v->venta_sucursal); ?></span></td>
                                    
                                    
                                    <td class="text-end fw-semibold">$<?php echo e(number_format($v->sum_medicamento ?? 0, 2)); ?></td>
                                    <td class="text-end fw-semibold">$<?php echo e(number_format($v->sum_consulta ?? 0, 2)); ?></td>
                                    <td class="text-end fw-semibold">$<?php echo e(number_format($v->sum_calzado ?? 0, 2)); ?></td>
                                    <td class="text-end fw-semibold">$<?php echo e(number_format($v->sum_general ?? 0, 2)); ?></td>

                                    
                                    <td class="text-center">$<?php echo e(number_format($v->venta_descuento ?? 0)); ?></td>
                                    
                                    
                                    <td class="text-end fw-bold text-dark fs-6">$<?php echo e(number_format($v->venta_total, 2)); ?></td>
                                    
                                    
                                    <td class="text-end text-success fw-bold">$<?php echo e(number_format($v->ultimo_abono ?? 0, 2)); ?></td>
                                    
                                    
                                    <td class="text-center">
                                        <?php if($isCred): ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-bold">CRÉDITO</span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">CONTADO</span>
                                        <?php endif; ?>
                                    </td>

                                    
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-link text-decoration-underline p-0 fw-bold" data-bs-toggle="modal" data-bs-target="#saleItemsModal<?php echo e($v->venta_id); ?>">
                                            Detalles
                                        </button>
                                    </td>

                                    
                                    <?php if($isAdmin || in_array((int)(auth()->user()->role_id ?? auth()->user()->user_type ?? 2), [1, 2])): ?>
                                        <td class="text-center">
                                            <?php if($tab !== 'canceladas'): ?>
                                                <form action="<?php echo e(route('sales.consultation.destroy-sale', $v->venta_id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de cancelar esta venta?');">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-sm btn-link text-danger text-decoration-underline p-0 fw-bold" title="Cancelar / Eliminar Venta">
                                                        Eliminar
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
                                    <td colspan="<?php echo e($isAdmin ? '16' : '15'); ?>" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-circle-info me-1"></i> No se encontraron ventas para los criterios seleccionados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    
                    <table class="table table-hover align-middle text-nowrap extra-small">
                        <thead class="table-light">
                            <tr class="fw-bold text-uppercase border-bottom">
                                <th># Abono</th>
                                <th>N° Venta Asociada</th>
                                <th>Fecha / Hora Abono</th>
                                <th>Sucursal</th>
                                <th class="text-end">Monto Abonado</th>
                                <?php if($isAdmin): ?>
                                    <th class="text-center">Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="fw-bold text-info">#<?php echo e($a->abono_id ?? $a->id); ?></td>
                                    <td class="fw-bold text-primary">Venta #<?php echo e($a->abono_venta ?? $a->venta_id); ?></td>
                                    <td><?php echo e($a->abono_fecha ?? $a->created_at); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo e($a->abono_sucursal ?? 'MATRIZ'); ?></span></td>
                                    <td class="text-end fw-bold text-success fs-6">+$<?php echo e(number_format($a->abono_cantidad, 2)); ?></td>
                                    <?php if($isAdmin): ?>
                                        <td class="text-center">
                                            <form action="<?php echo e(route('sales.consultation.destroy-abono', $a->abono_id ?? $a->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de borrar este abono?');">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-sm btn-link text-danger text-decoration-underline p-0 fw-bold" title="Eliminar Abono">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="<?php echo e($isAdmin ? '6' : '5'); ?>" class="text-center py-4 text-muted">
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
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold text-secondary m-0">
                            <i class="fa-solid fa-chart-line me-2 text-primary"></i>Resumen Desglosado de Ventas 
                            <span class="badge bg-light text-primary border ms-1">Sucursal: <?php echo e($selectedBranch ?: 'TODAS'); ?></span>
                        </h6>
                    </div>

                    <div class="row g-3">
                        
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-light border-start border-4 border-success h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-success"><i class="fa-solid fa-money-bill-wave me-1"></i> Ventas a Contado</span>
                                    <span class="badge bg-success text-white rounded-pill">$<?php echo e(number_format($summary['total_contado'], 2)); ?></span>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6 col-sm-3">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">CALZADO</small>
                                            <span class="fw-bold text-dark fs-6">$<?php echo e(number_format($summary['contado_calzado'], 2)); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">MEDICAMENTO</small>
                                            <span class="fw-bold text-dark fs-6">$<?php echo e(number_format($summary['contado_medicamento'], 2)); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">GENERAL</small>
                                            <span class="fw-bold text-dark fs-6">$<?php echo e(number_format($summary['contado_general'], 2)); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">CONSULTA</small>
                                            <span class="fw-bold text-dark fs-6">$<?php echo e(number_format($summary['contado_consulta'], 2)); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-light border-start border-4 border-warning h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-warning-emphasis"><i class="fa-solid fa-credit-card me-1"></i> Ventas a Crédito</span>
                                    <span class="badge bg-warning text-dark rounded-pill">$<?php echo e(number_format($summary['total_credito'], 2)); ?></span>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6 col-sm-3">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">CALZADO</small>
                                            <span class="fw-bold text-dark fs-6">$<?php echo e(number_format($summary['credito_calzado'], 2)); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">MEDICAMENTO</small>
                                            <span class="fw-bold text-dark fs-6">$<?php echo e(number_format($summary['credito_medicamento'], 2)); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">GENERAL</small>
                                            <span class="fw-bold text-dark fs-6">$<?php echo e(number_format($summary['credito_general'], 2)); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">CONSULTA</small>
                                            <span class="fw-bold text-dark fs-6">$<?php echo e(number_format($summary['credito_consulta'], 2)); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="row g-3 mt-1">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-light border-start border-4 border-info">
                                <?php
                                    $totalFormasPago = $summary['pago_efectivo'] + $summary['pago_tarjeta_credito'] + $summary['pago_tarjeta_debito'] + $summary['pago_transferencia'] + ($summary['pago_otros'] ?? 0);
                                ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-info-emphasis">
                                        <i class="fa-solid fa-wallet me-1"></i> Desglose por Forma de Pago
                                    </span>
                                    <span class="badge bg-info text-white rounded-pill px-3">
                                        Total Formas de Pago: $<?php echo e(number_format($totalFormasPago, 2)); ?>

                                    </span>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6 col-md-2-4 col-lg">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">
                                                <i class="fa-solid fa-money-bill text-success me-1"></i>EFECTIVO
                                            </small>
                                            <span class="fw-bold text-dark fs-6">$<?php echo e(number_format($summary['pago_efectivo'], 2)); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-2-4 col-lg">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">
                                                <i class="fa-solid fa-credit-card text-primary me-1"></i>T. CRÉDITO
                                            </small>
                                            <span class="fw-bold text-dark fs-6">$<?php echo e(number_format($summary['pago_tarjeta_credito'], 2)); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-2-4 col-lg">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">
                                                <i class="fa-solid fa-credit-card text-info me-1"></i>T. DÉBITO
                                            </small>
                                            <span class="fw-bold text-dark fs-6">$<?php echo e(number_format($summary['pago_tarjeta_debito'], 2)); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-2-4 col-lg">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">
                                                <i class="fa-solid fa-building-columns text-warning me-1"></i>TRANSFERENCIA
                                            </small>
                                            <span class="fw-bold text-dark fs-6">$<?php echo e(number_format($summary['pago_transferencia'], 2)); ?></span>
                                        </div>
                                    </div>
                                    <?php if(($summary['pago_otros'] ?? 0) > 0): ?>
                                        <div class="col-6 col-md-2-4 col-lg">
                                            <div class="bg-white p-2 rounded-3 text-center border">
                                                <small class="text-muted extra-small d-block fw-semibold">
                                                    <i class="fa-solid fa-circle-ellipsis text-secondary me-1"></i>OTROS
                                                </small>
                                                <span class="fw-bold text-dark fs-6">$<?php echo e(number_format($summary['pago_otros'], 2)); ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-2">
                        <div class="d-flex flex-wrap gap-3 text-muted extra-small fw-semibold">
                            <span>Total Calzado: <strong class="text-dark">$<?php echo e(number_format($summary['total_calzado'], 2)); ?></strong></span>
                            <span>|</span>
                            <span>Total Medicamento: <strong class="text-dark">$<?php echo e(number_format($summary['total_medicamento'], 2)); ?></strong></span>
                            <span>|</span>
                            <span>Total General: <strong class="text-dark">$<?php echo e(number_format($summary['total_general'], 2)); ?></strong></span>
                            <span>|</span>
                            <span>Total Consultas: <strong class="text-dark">$<?php echo e(number_format($summary['total_consulta'], 2)); ?></strong></span>
                        </div>

                        <div class="card border-0 text-white rounded-4 p-3 shadow ms-auto mt-2 mt-md-0" style="background: linear-gradient(135deg, #d81b60 0%, #e91e63 100%); min-width: 320px;">
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
                </div>
            <?php else: ?>
                
                <div class="d-flex justify-content-end mt-4 pt-3 border-top">
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\PiesFelices\resources\views/sales_consultation/index.blade.php ENDPATH**/ ?>