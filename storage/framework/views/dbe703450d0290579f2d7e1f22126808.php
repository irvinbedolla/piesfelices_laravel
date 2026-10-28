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
    <?php
        $userRole = (int) (auth()->user()->role_id ?? auth()->user()->type ?? 2);
        $canManageSellers = auth()->user()->isAdmin() || in_array($userRole, [1, 2]);
    ?>
    <div class="container-fluid px-3 py-3">
        
        
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="w-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h4 class="fw-bold text-primary m-0">
                            <i class="fa-solid fa-cart-shopping me-1"></i> Venta #<?php echo e($sale->venta_id); ?>

                        </h4>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fs-6">
                            <i class="fa-solid fa-hospital me-1"></i> <?php echo e($sale->venta_sucursal); ?>

                        </span>
                    </div>

                    <div class="row g-2">
                        
                        <div class="col-6 col-sm-auto">
                            <?php if($canManageSellers): ?>
                                <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold w-100 text-truncate open-modal-btn" data-target="#sellerModal">
                                    <i class="fa-solid fa-id-badge me-1 text-success"></i> 
                                    <?php echo e($sale->seller->name ?? $sale->seller->username ?? 'Empleado'); ?>

                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold w-100 text-truncate" disabled>
                                    <i class="fa-solid fa-id-badge me-1 text-secondary"></i> 
                                    <?php echo e($sale->seller->name ?? $sale->seller->username ?? auth()->user()->name); ?>

                                </button>
                            <?php endif; ?>
                        </div>

                        
                        <div class="col-6 col-sm-auto">
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold w-100 text-truncate open-modal-btn" data-target="#customerModal">
                                <i class="fa-solid fa-user me-1 text-primary"></i> 
                                <?php echo e($sale->customer->nombre ?? $sale->nombre_cliente ?: 'Cliente General'); ?>

                            </button>
                        </div>

                        
                        <div class="col-6 col-sm-auto">
                            <?php if(auth()->user()->isAdmin()): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill fw-semibold w-100 text-truncate open-modal-btn" data-target="#saleDateModal">
                                    <i class="fa-solid fa-calendar-day me-1"></i> Fecha: <?php echo e(\Carbon\Carbon::parse($sale->venta_fecha)->format('d/m/Y')); ?>

                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold w-100 text-truncate" disabled>
                                    <i class="fa-solid fa-calendar-day me-1"></i> Fecha: <?php echo e(\Carbon\Carbon::parse($sale->venta_fecha)->format('d/m/Y')); ?>

                                </button>
                            <?php endif; ?>
                        </div>

                        
                        <div class="col-6 col-sm-auto">
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold w-100 text-truncate open-modal-btn" data-target="#serviceModal">
                                <i class="fa-solid fa-screwdriver-wrench me-1 text-primary"></i> Servicio
                            </button>
                        </div>

                        
                        <div class="col-6 col-sm-auto">
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold w-100 text-truncate open-modal-btn" data-target="#paymentTypeModal">
                                <i class="fa-solid fa-credit-card me-1 text-info"></i> <?php echo e($sale->venta_tipopago); ?>

                            </button>
                        </div>

                        
                        <div class="col-6 col-sm-auto">
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold w-100 text-truncate open-modal-btn" data-target="#saleTypeModal">
                                <i class="fa-solid fa-tags me-1 text-warning"></i> <?php echo e($sale->venta_tipo == '1' ? 'Contado' : 'Crédito'); ?>

                            </button>
                        </div>

                        
                        <div class="col-6 col-sm-auto">
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold w-100 text-truncate open-modal-btn" data-target="#invoiceModal">
                                <i class="fa-solid fa-file-invoice me-1 text-danger"></i> Factura: <?php echo e($sale->venta_factura); ?>

                            </button>
                        </div>

                        
                        <div class="col-6 col-sm-auto">
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold w-100 text-truncate open-modal-btn" data-target="#discountModal">
                                <i class="fa-solid fa-percent me-1 text-danger"></i> Desc: $<?php echo e(number_format($sale->venta_descuento, 2)); ?>

                            </button>
                        </div>
                    </div>
                </div>

                
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-muted small"><i class="fa-solid fa-barcode me-1"></i>CB:</span>
                    <input type="text" id="barcodeReader" class="form-control form-control-sm rounded-pill border-primary" placeholder="Escanear producto..." autofocus style="width: 180px;">
                </div>
            </div>
        </div>

        
        <div class="row g-3">
            
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                    <div class="mb-3">
                        <input type="text" id="searchCatalog" class="form-control rounded-pill" placeholder="Buscar por nombre, código o modelo...">
                    </div>

                    <div class="row g-2 overflow-auto" style="max-height: 550px;" id="catalogGrid">
                        <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $pivot = $p->branches->first()?->pivot;
                                $stock = $pivot ? $pivot->stock_current : 0;
                            ?>
                            <div class="col-6 col-md-4 catalog-item" data-name="<?php echo e(strtolower($p->name)); ?>" data-code="<?php echo e(strtolower($p->barcode ?? '')); ?>">
                                <div class="card h-100 border-0 shadow-sm rounded-3 text-center p-2 hover-card cursor-pointer add-to-cart-btn" data-id="<?php echo e($p->id); ?>">
                                    <div class="fw-bold text-dark extra-small mb-1 text-wrap" title="<?php echo e($p->name); ?>">
                                        <?php echo e($p->name); ?>

                                    </div>
                                    <span class="badge bg-light text-secondary border extra-small mb-1"><?php echo e($p->barcode ?? 'S/C'); ?></span>
                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <span class="fw-bold text-success">$<?php echo e(number_format($p->sale_price, 2)); ?></span>
                                        <span class="badge bg-success-subtle text-success small">Stock: <?php echo e($stock); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="col-12 text-center py-5 text-muted">
                                <i class="fa-solid fa-boxes-stacked fa-2x mb-2 d-block"></i>
                                No hay artículos con existencias disponibles en la sucursal (<?php echo e($sale->venta_sucursal); ?>).
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100 d-flex flex-column justify-content-between">
                    <div class="table-responsive mb-3" style="max-height: 380px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th>Producto</th>
                                    <th class="text-center" style="width: 100px;">Cant.</th>
                                    <th class="text-end">P.Unit</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="cartTableBody">
                                <?php $__empty_1 = true; $__currentLoopData = $sale->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-item-btn" data-id="<?php echo e($item->id); ?>">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <strong class="text-dark d-block text-truncate" style="max-width: 180px;"><?php echo e($item->nombre); ?></strong>
                                            <small class="text-muted"><?php echo e($item->sku); ?></small>
                                        </td>
                                        <td class="text-center">
                                            <input type="number" value="<?php echo e($item->cantidad); ?>" min="1" class="form-control form-control-sm text-center fw-bold update-qty-input rounded-3" data-id="<?php echo e($item->id); ?>" style="width: 65px;">
                                        </td>
                                        <td class="text-end">$<?php echo e(number_format($item->precio, 2)); ?></td>
                                        <td class="text-end fw-bold text-success">$<?php echo e(number_format($item->cantidad * $item->precio, 2)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="fa-solid fa-cart-flatbed fa-2x mb-2 d-block"></i>
                                            Carrito de venta vacío
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Descuento:</span>
                            <span class="fw-semibold text-danger">-$<?php echo e(number_format($sale->venta_descuento, 2)); ?></span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="fw-bold text-dark m-0">TOTAL:</h4>
                            <h2 class="fw-bold text-success m-0">$<?php echo e(number_format($sale->venta_total, 2)); ?></h2>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success btn-lg w-100 rounded-pill fw-semibold shadow-sm open-modal-btn" data-target="#payModal">
                                <i class="fa-solid fa-cash-register me-2"></i> Cobrar / Finalizar
                            </button>
                            <a href="<?php echo e(route('pos.cancel', $sale->venta_id)); ?>" class="btn btn-outline-danger btn-lg rounded-pill px-4" onclick="return confirm('¿Cancelar esta venta?')">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL COBRAR -->
    <div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 text-center p-3">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-coins text-success me-2"></i>Finalizar Cobro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <h3 class="fw-bold text-dark mb-3">Total: <span class="text-success">$<?php echo e(number_format($sale->venta_total, 2)); ?></span></h3>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Efectivo Recibido ($):</label>
                        <input type="number" step="0.01" id="receivedInput" class="form-control form-control-lg text-center fw-bold border-primary rounded-3" placeholder="0.00" autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Cambio ($):</label>
                        <input type="text" id="changeOutput" class="form-control form-control-lg text-center fw-bold bg-light text-primary rounded-3" value="$0.00" readonly>
                    </div>

                    <div class="form-check form-switch d-flex align-items-center justify-content-center gap-2 mb-3 fs-6">
                        
                        <input class="form-check-input" type="checkbox" id="printTicketCheck" style="cursor: pointer; width: 2.5em; height: 1.25em;">
                        <label class="form-check-label fw-bold text-dark" for="printTicketCheck" style="cursor: pointer;">
                            <i class="fa-solid fa-print me-1 text-primary"></i> Imprimir Ticket al finalizar
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmPayBtn" class="btn btn-success rounded-pill px-5 fw-semibold fs-5">
                        <i class="fa-solid fa-check me-2"></i> Confirmar Pago
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL SELECCIONAR CLIENTE (#customerModal) -->
    <div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="modal-title fw-bold text-dark m-0">
                        <i class="fa-solid fa-user text-primary me-2"></i>Seleccionar Cliente
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold open-modal-btn" data-target="#fullCustomerModal">
                            <i class="fa-solid fa-user-plus me-1"></i> + Registro Completo
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                
                <div class="modal-body py-3">
                    
                    <div class="card border-0 bg-light p-3 rounded-4 mb-3">
                        <h6 class="fw-bold text-dark small mb-2">
                            <i class="fa-solid fa-bolt me-1 text-warning"></i> Nuevo Cliente Rápido
                        </h6>
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-md-8">
                                <input type="text" id="fastCustomerName" class="form-control rounded-3" placeholder="Nombre completo del cliente (*)">
                                <input type="hidden" id="fastCustomerBranch" value="<?php echo e($sale->venta_sucursal); ?>">
                            </div>
                            <div class="col-12 col-md-4 text-end">
                                <button type="button" id="saveFastCustomerBtn" class="btn btn-primary w-100 rounded-pill fw-semibold">
                                    <i class="fa-solid fa-plus me-1"></i> Guardar y Asignar
                                </button>
                            </div>
                        </div>
                    </div>

                    
                    <div class="mb-3">
                        <input type="text" id="searchCustomerInput" class="form-control rounded-pill" placeholder="Buscar por nombre de cliente...">
                    </div>

                    
                    <div class="table-responsive" style="max-height: 280px;">
                        <table class="table table-hover align-middle mb-0" id="customersPosTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Sucursal Asignada</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <strong class="text-dark d-block">Cliente General / Público</strong>
                                        <small class="text-muted">Venta mostrador general</small>
                                    </td>
                                    <td><span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 extra-small"><?php echo e($sale->venta_sucursal); ?></span></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 assign-customer-btn" data-id="0" data-name="Cliente General">Seleccionar</button>
                                    </td>
                                </tr>

                                <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr class="customer-row" data-name="<?php echo e(strtolower($c->nombre)); ?>">
                                        <td><strong class="text-dark d-block"><?php echo e($c->nombre); ?></strong></td>
                                        <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 extra-small"><?php echo e($c->sucursal ?? $sale->venta_sucursal); ?></span></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 assign-customer-btn" data-id="<?php echo e($c->cliente_id); ?>" data-name="<?php echo e($c->nombre); ?>">Seleccionar</button>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="3" class="text-center py-3 text-muted">No hay clientes registrados en esta sucursal.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL REGISTRAR NUEVO CLIENTE COMPLETO (#fullCustomerModal) -->
    <div class="modal fade" id="fullCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-user-plus text-primary me-2"></i>Registrar Nuevo Cliente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <form id="fullCustomerForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nombre Completo (*)</label>
                                <input type="text" id="fullCustomerName" class="form-control rounded-3" placeholder="Ej. Juan Pérez" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Teléfono</label>
                                <input type="text" id="fullCustomerPhone" class="form-control rounded-3" placeholder="4431234567">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Correo Electrónico</label>
                                <input type="email" id="fullCustomerEmail" class="form-control rounded-3" placeholder="correo@ejemplo.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">RFC</label>
                                <input type="text" id="fullCustomerRfc" class="form-control rounded-3" placeholder="XAXX010101000">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Dirección</label>
                                <input type="text" id="fullCustomerAddress" class="form-control rounded-3" placeholder="Calle, Número, Colonia">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Código Postal</label>
                                <input type="text" id="fullCustomerCp" class="form-control rounded-3" placeholder="58000">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Localidad / Ciudad</label>
                                <input type="text" id="fullCustomerCity" class="form-control rounded-3" placeholder="Morelia">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Sucursal (*)</label>
                                <select id="fullCustomerBranch" class="form-select rounded-3" required>
                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($b->name); ?>" <?php echo e(($sale->venta_sucursal == $b->name) ? 'selected' : ''); ?>><?php echo e($b->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Fecha Nacimiento</label>
                                <input type="date" id="fullCustomerBirthdate" class="form-control rounded-3">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="saveFullCustomerBtn" class="btn btn-primary rounded-pill px-5 fw-semibold">
                        Guardar Cliente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL FACTURACIÓN -->
    <div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-file-invoice text-danger me-2"></i>Datos de Facturación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">¿Requiere Factura?</label>
                        <select id="invoiceRequiredSelect" class="form-select rounded-3">
                            <option value="NO" <?php echo e($sale->venta_factura == 'NO' ? 'selected' : ''); ?>>NO</option>
                            <option value="SI" <?php echo e($sale->venta_factura == 'SI' ? 'selected' : ''); ?>>SI</option>
                        </select>
                    </div>

                    <div id="invoiceFieldsContainer" class="<?php echo e($sale->venta_factura == 'SI' ? '' : 'd-none'); ?>">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Número / Folio de Factura (Consecutivo Global)</label>
                            <input type="text" id="invoiceNumberInput" class="form-control rounded-3" value="<?php echo e($sale->numero_factura); ?>" placeholder="Ej. 1001">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Uso de CFDI</label>
                            <select id="invoiceCfdiSelect" class="form-select rounded-3">
                                <option value="G03" <?php echo e($sale->venta_cfdi == 'G03' ? 'selected' : ''); ?>>G03 - Gastos en general</option>
                                <option value="P01" <?php echo e($sale->venta_cfdi == 'P01' ? 'selected' : ''); ?>>P01 - Por definir</option>
                                <option value="S01" <?php echo e($sale->venta_cfdi == 'S01' ? 'selected' : ''); ?>>S01 - Sin efectos fiscales</option>
                                <option value="D01" <?php echo e($sale->venta_cfdi == 'D01' ? 'selected' : ''); ?>>D01 - Honorarios médicos, dentales y gastos hospitalarios</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tipo de Factura</label>
                            <select id="invoiceTypeSelect" class="form-select rounded-3">
                                <option value="Ingreso" <?php echo e($sale->venta_tipo_factura == 'Ingreso' ? 'selected' : ''); ?>>Ingreso</option>
                                <option value="PPD" <?php echo e($sale->venta_tipo_factura == 'PPD' ? 'selected' : ''); ?>>PPD - Pago en parcialidades o diferido</option>
                                <option value="PUE" <?php echo e($sale->venta_tipo_factura == 'PUE' ? 'selected' : ''); ?>>PUE - Pago en una sola exhibición</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="saveInvoiceBtn" class="btn btn-danger rounded-pill px-4 fw-semibold">Guardar Factura</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL VENDEDOR -->
    <div class="modal fade" id="sellerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-id-badge text-success me-2"></i>Seleccionar Empleado Vendedor
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <input type="text" id="searchSellerInput" class="form-control form-control-sm rounded-pill" placeholder="Buscar empleado...">
                    </div>
                    <div class="list-group rounded-3 shadow-sm overflow-auto" style="max-height: 250px;" id="sellersList">
                        <?php $__currentLoopData = $sellers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $sellerName = $s->name ?? $s->username ?? 'Empleado #' . $s->id; ?>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center assign-seller-btn" data-id="<?php echo e($s->id); ?>" data-name="<?php echo e($sellerName); ?>">
                                <span class="fw-semibold text-dark">
                                    <i class="fa-solid fa-user-circle me-2 text-secondary"></i><?php echo e($sellerName); ?>

                                </span>
                                <?php if($sale->venta_idempleado == $s->id): ?>
                                    <span class="badge bg-success rounded-pill">Activo</span>
                                <?php endif; ?>
                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TIPO DE PAGO -->
    <div class="modal fade" id="paymentTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg rounded-4 text-center">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-dark w-100">Tipo de Pago</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body d-grid gap-2 py-3">
                    <button type="button" class="btn btn-outline-primary rounded-pill set-header-btn" data-field="venta_tipopago" data-val="Efectivo">
                        <i class="fa-solid fa-money-bill-wave me-1"></i> Efectivo
                    </button>
                    <button type="button" class="btn btn-outline-primary rounded-pill set-header-btn" data-field="venta_tipopago" data-val="Tarjeta Credito">
                        <i class="fa-solid fa-credit-card me-1"></i> Tarjeta Crédito
                    </button>
                    <button type="button" class="btn btn-outline-primary rounded-pill set-header-btn" data-field="venta_tipopago" data-val="Tarjeta Debito">
                        <i class="fa-solid fa-credit-card me-1"></i> Tarjeta Débito
                    </button>
                    <button type="button" class="btn btn-outline-primary rounded-pill set-header-btn" data-field="venta_tipopago" data-val="Transferencia">
                        <i class="fa-solid fa-building-columns me-1"></i> Transferencia
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL MODALIDAD DE VENTA -->
    <div class="modal fade" id="saleTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg rounded-4 text-center">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-dark w-100">Modalidad de Venta</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body d-grid gap-2 py-3">
                    <button type="button" class="btn btn-outline-success rounded-pill set-header-btn" data-field="venta_tipo" data-val="1">
                        <i class="fa-solid fa-hand-holding-dollar me-1"></i> Contado
                    </button>
                    <button type="button" class="btn btn-outline-warning text-dark rounded-pill set-header-btn" data-field="venta_tipo" data-val="2">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i> Crédito
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DESCUENTO -->
    <div class="modal fade" id="discountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-dark">Monto de Descuento</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Descuento Global ($)</label>
                        <input type="number" step="0.01" id="discountInput" class="form-control rounded-3 text-center fw-bold" value="<?php echo e($sale->venta_descuento); ?>" placeholder="0.00">
                    </div>
                    <button type="button" id="saveDiscountBtn" class="btn btn-danger w-100 rounded-pill fw-semibold">
                        Aplicar Descuento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL SERVICIO -->
    <div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-screwdriver-wrench text-primary me-2"></i>Agregar Servicio
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Concepto / Descripción (*)</label>
                        <input type="text" id="serviceConcept" class="form-control rounded-3" placeholder="Ej. Consulta / Plantillas">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Monto ($) (*)</label>
                        <input type="number" step="0.01" id="serviceAmount" class="form-control rounded-3 fw-bold fs-5 text-success" placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="saveServiceBtn" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        Agregar al Carrito
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CAMBIAR FECHA -->
    <?php if(auth()->user()->isAdmin()): ?>
        <div class="modal fade" id="saleDateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-0 pb-0">
                        <h6 class="modal-title fw-bold text-dark">
                            <i class="fa-solid fa-calendar-days text-danger me-2"></i>Cambiar Fecha de Venta
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body py-3">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Fecha de Emisión</label>
                            <input type="date" id="saleDateInput" class="form-control rounded-3 text-center fw-bold" value="<?php echo e($sale->venta_fecha); ?>">
                        </div>
                        <button type="button" id="saveSaleDateBtn" class="btn btn-danger w-100 rounded-pill fw-semibold">
                            Guardar Nueva Fecha
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    
    <div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center bg-dark bg-opacity-50" style="z-index: 9999;">
        <div class="card border-0 shadow-lg rounded-4 p-4 text-center bg-white" style="max-width: 300px;">
            <div class="spinner-border text-primary mx-auto mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
            <h6 class="fw-bold m-0 text-dark">Procesando...</h6>
            <small class="text-muted extra-small">Por favor espera un momento</small>
        </div>
    </div>

    
    <script>
        let isProcessing = false;

        function showLoading() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.classList.remove('d-none');
                overlay.classList.add('d-flex');
            }
        }

        function hideLoading() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.classList.remove('d-flex');
                overlay.classList.add('d-none');
            }
        }

        function forceCleanBackdrops() {
            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }

        document.addEventListener('DOMContentLoaded', function () {
            const saleId = "<?php echo e($sale->venta_id); ?>";
            const totalSale = parseFloat("<?php echo e($sale->venta_total); ?>");

            // CONTROL SEGURO DE APERTURA DE MODALES
            document.querySelectorAll('.open-modal-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    forceCleanBackdrops();
                    const targetSelector = this.getAttribute('data-target');
                    const modalEl = document.querySelector(targetSelector);

                    if (modalEl) {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            const modalObj = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modalObj.show();
                        } else {
                            modalEl.classList.add('show');
                            modalEl.style.display = 'block';
                        }
                    }
                });
            });

            // 1. GUARDAR CLIENTE RÁPIDO
            const saveFastCustomerBtn = document.getElementById('saveFastCustomerBtn');
            if (saveFastCustomerBtn) {
                saveFastCustomerBtn.addEventListener('click', function () {
                    if (isProcessing) return;
                    const name = document.getElementById('fastCustomerName').value.trim();
                    const branch = document.getElementById('fastCustomerBranch').value || '<?php echo e($sale->venta_sucursal); ?>';

                    if (!name) {
                        alert('Por favor ingresa el nombre completo del cliente');
                        return;
                    }

                    isProcessing = true;
                    showLoading();

                    fetch("<?php echo e(url('clientes')); ?>", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({ nombre: name, sucursal: branch })
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success || res.cliente_id) {
                            const newCustomerId = res.cliente_id || res.id;
                            const newCustomerName = res.nombre || name;

                            return fetch("<?php echo e(url('pos')); ?>/" + saleId + "/header", {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                                },
                                body: JSON.stringify({
                                    venta_idcliente: newCustomerId,
                                    nombre_cliente: newCustomerName
                                })
                            });
                        } else {
                            throw new Error(res.message || 'Error al registrar cliente');
                        }
                    })
                    .then(res => res ? res.json() : null)
                    .then(data => {
                        if (data && data.success) {
                            location.reload();
                        }
                    })
                    .catch(err => {
                        isProcessing = false;
                        hideLoading();
                        alert(err.message || 'Error de conexión al registrar cliente.');
                    });
                });
            }

            // 2. GUARDAR CLIENTE COMPLETO
            const saveFullCustomerBtn = document.getElementById('saveFullCustomerBtn');
            if (saveFullCustomerBtn) {
                saveFullCustomerBtn.addEventListener('click', function () {
                    if (isProcessing) return;
                    const name = document.getElementById('fullCustomerName').value.trim();
                    const branch = document.getElementById('fullCustomerBranch').value || '<?php echo e($sale->venta_sucursal); ?>';

                    if (!name) {
                        alert('Por favor ingresa el nombre completo del cliente');
                        return;
                    }

                    isProcessing = true;
                    showLoading();

                    const payload = {
                        nombre: name,
                        telefono: document.getElementById('fullCustomerPhone').value.trim(),
                        correo: document.getElementById('fullCustomerEmail').value.trim(),
                        rfc: document.getElementById('fullCustomerRfc').value.trim(),
                        direccion: document.getElementById('fullCustomerAddress').value.trim(),
                        cp: document.getElementById('fullCustomerCp').value.trim(),
                        localidad: document.getElementById('fullCustomerCity').value.trim(),
                        sucursal: branch,
                        fecha_nacimiento: document.getElementById('fullCustomerBirthdate').value
                    };

                    fetch("<?php echo e(url('clientes')); ?>", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json().then(data => ({ status: res.status, data: data })))
                    .then(res => {
                        if (res.status === 200 || res.status === 201 || res.data.success) {
                            const newCustomerId = res.data.cliente_id || res.data.id || 0;

                            return fetch("<?php echo e(url('pos')); ?>/" + saleId + "/header", {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                                },
                                body: JSON.stringify({
                                    venta_idcliente: newCustomerId,
                                    nombre_cliente: name
                                })
                            });
                        } else {
                            throw new Error(res.data.message || 'Error al registrar el cliente');
                        }
                    })
                    .then(res => res ? res.json() : null)
                    .then(data => {
                        if (data && data.success) {
                            location.reload();
                        }
                    })
                    .catch(err => {
                        isProcessing = false;
                        hideLoading();
                        alert(err.message || 'Error al guardar cliente completo.');
                    });
                });
            }

            // 3. CAMBIAR FECHA DE VENTA
            const saveSaleDateBtn = document.getElementById('saveSaleDateBtn');
            if (saveSaleDateBtn) {
                saveSaleDateBtn.addEventListener('click', function () {
                    if (isProcessing) return;
                    const newDate = document.getElementById('saleDateInput').value;

                    if (!newDate) {
                        alert('Por favor selecciona una fecha válida');
                        return;
                    }

                    isProcessing = true;
                    showLoading();

                    fetch("<?php echo e(url('pos')); ?>/" + saleId + "/header", {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({ venta_fecha: newDate })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            isProcessing = false;
                            hideLoading();
                            alert(data.message || 'Error al actualizar fecha');
                        }
                    })
                    .catch(() => {
                        isProcessing = false;
                        hideLoading();
                    });
                });
            }

            // 4. AGREGAR SERVICIO AL CARRITO
            const saveServiceBtn = document.getElementById('saveServiceBtn');
            if (saveServiceBtn) {
                saveServiceBtn.addEventListener('click', function () {
                    if (isProcessing) return;

                    const concept = document.getElementById('serviceConcept').value.trim();
                    const amount = parseFloat(document.getElementById('serviceAmount').value) || 0;

                    if (!concept || amount <= 0) {
                        alert('Ingresa un concepto y un monto mayor a $0.00');
                        return;
                    }

                    isProcessing = true;
                    showLoading();

                    fetch("<?php echo e(url('pos')); ?>/" + saleId + "/add-service", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({ concept: concept, amount: amount })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            isProcessing = false;
                            hideLoading();
                            alert(data.message || 'Error al agregar servicio');
                        }
                    })
                    .catch(() => {
                        isProcessing = false;
                        hideLoading();
                    });
                });
            }

            // 5. AGREGAR PRODUCTO AL CARRITO
            document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    if (isProcessing) return;
                    forceCleanBackdrops();
                    const productId = this.getAttribute('data-id');
                    addProductAjax({ product_id: productId });
                });
            });

            function addProductAjax(payload) {
                isProcessing = true;
                showLoading();

                fetch("<?php echo e(url('pos')); ?>/" + saleId + "/add-product", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        isProcessing = false;
                        hideLoading();
                        alert(data.message || 'Error al agregar el producto');
                    }
                })
                .catch(err => {
                    isProcessing = false;
                    hideLoading();
                    console.error(err);
                });
            }

            // 6. CAMBIO DE CANTIDAD
            document.querySelectorAll('.update-qty-input').forEach(input => {
                input.addEventListener('change', function () {
                    if (isProcessing) return;
                    isProcessing = true;
                    showLoading();

                    const itemId = this.getAttribute('data-id');
                    const qty = this.value;

                    fetch(`<?php echo e(url('pos/item')); ?>/${itemId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({ cantidad: qty })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            isProcessing = false;
                            hideLoading();
                            alert(data.message || 'Error al actualizar cantidad');
                            location.reload();
                        }
                    });
                });
            });

            // 7. ELIMINAR ITEM
            document.querySelectorAll('.remove-item-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    if (isProcessing) return;
                    isProcessing = true;
                    showLoading();

                    const itemId = this.getAttribute('data-id');
                    fetch(`<?php echo e(url('pos/item')); ?>/${itemId}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
                });
            });

            // 8. ASIGNAR CLIENTE EXISTENTE
            document.querySelectorAll('.assign-customer-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    if (isProcessing) return;
                    isProcessing = true;
                    showLoading();

                    const customerId = this.getAttribute('data-id');
                    const customerName = this.getAttribute('data-name');

                    fetch("<?php echo e(url('pos')); ?>/" + saleId + "/header", {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({
                            venta_idcliente: customerId,
                            nombre_cliente: customerName
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
                });
            });

            // 9. APLICAR DESCUENTO
            const saveDiscountBtn = document.getElementById('saveDiscountBtn');
            if (saveDiscountBtn) {
                saveDiscountBtn.addEventListener('click', function () {
                    if (isProcessing) return;
                    isProcessing = true;
                    showLoading();

                    const discountVal = parseFloat(document.getElementById('discountInput').value) || 0;

                    fetch("<?php echo e(url('pos')); ?>/" + saleId + "/header", {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({ venta_descuento: discountVal })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
                });
            }

            // 10. ASIGNAR VENDEDOR
            document.querySelectorAll('.assign-seller-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    if (isProcessing) return;
                    isProcessing = true;
                    showLoading();

                    const sellerId = this.getAttribute('data-id');

                    fetch("<?php echo e(url('pos')); ?>/" + saleId + "/header", {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({ venta_idempleado: sellerId })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
                });
            });

            // 11. FACTURACIÓN
            const invoiceRequiredSelect = document.getElementById('invoiceRequiredSelect');
            const invoiceFieldsContainer = document.getElementById('invoiceFieldsContainer');
            const invoiceNumberInput = document.getElementById('invoiceNumberInput');

            if (invoiceRequiredSelect) {
                invoiceRequiredSelect.addEventListener('change', function () {
                    if (this.value === 'SI') {
                        if (invoiceFieldsContainer) invoiceFieldsContainer.classList.remove('d-none');
                        if (invoiceNumberInput && !invoiceNumberInput.value) {
                            fetch("<?php echo e(url('pos')); ?>/" + saleId + "/next-invoice")
                                .then(res => res.json())
                                .then(data => {
                                    if (data.success) {
                                        invoiceNumberInput.value = data.next_invoice;
                                    }
                                });
                        }
                    } else {
                        if (invoiceFieldsContainer) invoiceFieldsContainer.classList.add('d-none');
                    }
                });
            }

            const saveInvoiceBtn = document.getElementById('saveInvoiceBtn');
            if (saveInvoiceBtn) {
                saveInvoiceBtn.addEventListener('click', function () {
                    if (isProcessing) return;
                    isProcessing = true;
                    showLoading();

                    const isRequired = invoiceRequiredSelect ? invoiceRequiredSelect.value : 'NO';
                    const numInvoice = invoiceNumberInput ? invoiceNumberInput.value : '';
                    const cfdi = document.getElementById('invoiceCfdiSelect') ? document.getElementById('invoiceCfdiSelect').value : null;
                    const invoiceType = document.getElementById('invoiceTypeSelect') ? document.getElementById('invoiceTypeSelect').value : null;

                    fetch("<?php echo e(url('pos')); ?>/" + saleId + "/header", {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({
                            venta_factura: isRequired,
                            numero_factura: isRequired === 'SI' ? numInvoice : null,
                            venta_cfdi: isRequired === 'SI' ? cfdi : null,
                            venta_tipo_factura: isRequired === 'SI' ? invoiceType : null
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
                });
            }

            // 12. CONFIRMAR PAGO
            const confirmPayBtn = document.getElementById('confirmPayBtn');
            if (confirmPayBtn) {
                confirmPayBtn.addEventListener('click', function () {
                    if (isProcessing) return;
                    isProcessing = true;
                    confirmPayBtn.disabled = true;
                    showLoading();

                    const receivedInput = document.getElementById('receivedInput');
                    const received = receivedInput ? (parseFloat(receivedInput.value) || totalSale) : totalSale;
                    const printTicket = document.getElementById('printTicketCheck') ? document.getElementById('printTicketCheck').checked : true;

                    fetch("<?php echo e(url('pos')); ?>/" + saleId + "/finish", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({ abono: received, print_ticket: printTicket })
                    })
                    .then(async res => {
                        const data = await res.json();
                        if (!res.ok) throw new Error(data.message || 'Error en el servidor');
                        return data;
                    })
                    .then(data => {
                        if (data && data.success) {
                            if (printTicket && data.redirect) {
                                window.open(data.redirect, '_blank');
                            }
                            window.location.href = "<?php echo e(url('pos')); ?>";
                        } else {
                            throw new Error(data?.message || 'No se pudo completar el cobro');
                        }
                    })
                    .catch(error => {
                        isProcessing = false;
                        confirmPayBtn.disabled = false;
                        hideLoading();
                        alert('Error: ' + error.message);
                    });
                });
            }

            // 13. ENCABEZADO GENÉRICO (TIPO PAGO Y TIPO VENTA)
            document.querySelectorAll('.set-header-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    if (isProcessing) return;
                    isProcessing = true;
                    showLoading();

                    const field = this.getAttribute('data-field');
                    const val = this.getAttribute('data-val');

                    let payload = {};
                    payload[field] = val;

                    fetch("<?php echo e(url('pos')); ?>/" + saleId + "/header", {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const totalSale = parseFloat("<?php echo e($sale->venta_total); ?>") || 0;
            const receivedInput = document.getElementById('receivedInput');
            const changeOutput = document.getElementById('changeOutput');

            // CÁLCULO DINÁMICO DEL CAMBIO
            if (receivedInput && changeOutput) {
                receivedInput.addEventListener('input', function () {
                    const received = parseFloat(this.value) || 0;
                    const change = received - totalSale;

                    if (change >= 0) {
                        changeOutput.value = '$' + change.toFixed(2);
                        changeOutput.classList.remove('text-danger');
                        changeOutput.classList.add('text-primary');
                    } else {
                        changeOutput.value = 'Faltan $' + Math.abs(change).toFixed(2);
                        changeOutput.classList.remove('text-primary');
                        changeOutput.classList.add('text-danger');
                    }
                });
            }

            // CONFIRMAR PAGO Y ACTUALIZAR
            const confirmPayBtn = document.getElementById('confirmPayBtn');
            if (confirmPayBtn) {
                confirmPayBtn.addEventListener('click', function () {
                    if (isProcessing) return;

                    const receivedVal = parseFloat(receivedInput.value) || 0;
                    if (receivedVal < totalSale) {
                        if (!confirm('El efectivo recibido es menor al total de la venta. ¿Deseas continuar?')) {
                            return;
                        }
                    }

                    isProcessing = true;
                    confirmPayBtn.disabled = true;
                    showLoading();

                    const printTicket = document.getElementById('printTicketCheck') ? document.getElementById('printTicketCheck').checked : false;

                    fetch("<?php echo e(url('pos')); ?>/" + saleId + "/finish", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({ 
                            abono: receivedVal, 
                            print_ticket: printTicket 
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (data.print && data.redirect) {
                                window.open(data.redirect, '_blank');
                            }
                            // Redirigir para abrir una nueva venta en limpio
                            window.location.href = "<?php echo e(url('pos')); ?>";
                        } else {
                            throw new Error(data.message || 'No se pudo completar el cobro');
                        }
                    })
                    .catch(err => {
                        isProcessing = false;
                        confirmPayBtn.disabled = false;
                        hideLoading();
                        alert(err.message || 'Error de comunicación al cobrar.');
                    });
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\PiesFelices\resources\views/pos/index.blade.php ENDPATH**/ ?>