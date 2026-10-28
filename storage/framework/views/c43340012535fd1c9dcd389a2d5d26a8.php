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
    <div class="container-fluid py-4" style="max-width: 1200px;">
        
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold text-primary m-0">
                    <i class="fa-solid fa-chart-line me-2"></i>Centro Unificado de Reportes
                </h4>
                <small class="text-muted">Generación de reportes generales, ventas, créditos y préstamos en PDF y Excel</small>
            </div>
            
            <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-semibold">
                <i class="fa-solid fa-store text-primary me-1"></i>
                <?php echo e($userRole === 1 ? 'Administración Global' : 'Sucursal: ' . $userBranch); ?>

            </span>
        </div>

        
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            
            
            <div class="card-header bg-white border-bottom p-3">
                <ul class="nav nav-pills card-header-pills gap-2" id="reportTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-bold px-3" id="sales-normal-tab" data-bs-toggle="tab" data-bs-target="#sales-normal" type="button" role="tab">
                            <i class="fa-solid fa-receipt me-2"></i>Ventas Contado
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-bold px-3" id="sales-credit-tab" data-bs-toggle="tab" data-bs-target="#sales-credit" type="button" role="tab">
                            <i class="fa-solid fa-credit-card me-2"></i>Ventas Crédito
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-bold px-3" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab">
                            <i class="fa-solid fa-file-invoice-dollar me-2"></i>Facturación
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-bold px-3" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments" type="button" role="tab">
                            <i class="fa-solid fa-calendar-check me-2"></i>Citas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-bold px-3" id="items-tab" data-bs-toggle="tab" data-bs-target="#items" type="button" role="tab">
                            <i class="fa-solid fa-boxes-packing me-2"></i>Artículos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-bold px-3" id="loans-tab" data-bs-toggle="tab" data-bs-target="#loans" type="button" role="tab" style="color: #d81b60;">
                            <i class="fa-solid fa-hand-holding-dollar me-2"></i>Préstamos
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="reportTabsContent">
                    
                    
                    <div class="tab-pane fade show active" id="sales-normal" role="tabpanel">
                        <div class="row align-items-center">
                            <div class="col-md-5 mb-3 mb-md-0 border-end pe-md-4">
                                <div class="p-3 bg-primary-subtle text-primary rounded-4 d-inline-block mb-3 fs-3">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                                <h5 class="fw-bold text-dark">Reporte de Ventas al Contado</h5>
                                <p class="text-muted small">
                                    Genera el listado consolidado de ventas liquidadas de contado, filtrando independientemente por sucursal y por vendedor.
                                </p>
                            </div>

                            <div class="col-md-7 ps-md-4">
                                <form action="<?php echo e(route('reports.sales.normal')); ?>" method="GET" target="_blank">
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Fecha Inicio</label>
                                            <input type="date" name="fecha1" value="<?php echo e(date('Y-m-d')); ?>" class="form-control rounded-3" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Fecha Fin</label>
                                            <input type="date" name="fecha2" value="<?php echo e(date('Y-m-d')); ?>" class="form-control rounded-3" required>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Filtro Sucursal</label>
                                            <?php if($userRole === 1): ?>
                                                <select name="sucursal" class="form-select rounded-3">
                                                    <option value="TODOS">Todas las Sucursales</option>
                                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($b->name); ?>"><?php echo e($b->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            <?php else: ?>
                                                <input type="text" class="form-control rounded-3 bg-light text-muted fw-bold" value="<?php echo e($userBranch); ?>" readonly>
                                                <input type="hidden" name="sucursal" value="<?php echo e($userBranch); ?>">
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Filtro Atendió / Vendedor</label>
                                            <select name="usuario_id" class="form-select rounded-3">
                                                <option value="TODOS">Todos los Vendedores</option>
                                                <?php $__currentLoopData = $podologists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($p->id); ?>"><?php echo e($p->name ?? $p->username); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4 bg-light p-3 rounded-3">
                                        <label class="form-label extra-small fw-bold text-muted d-block mb-2">Formato de Descarga</label>
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtPdfNorm" value="pdf" checked>
                                                <label class="form-check-label fw-bold text-danger" for="fmtPdfNorm">
                                                    <i class="fa-solid fa-file-pdf me-1"></i> Documento PDF
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtExcelNorm" value="excel">
                                                <label class="form-check-label fw-bold text-success" for="fmtExcelNorm">
                                                    <i class="fa-solid fa-file-excel me-1"></i> Hoja Excel (.xlsx)
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">
                                        <i class="fa-solid fa-download me-1"></i> Generar Ventas Contado
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    
                    <div class="tab-pane fade" id="sales-credit" role="tabpanel">
                        <div class="row align-items-center">
                            <div class="col-md-5 mb-3 mb-md-0 border-end pe-md-4">
                                <div class="p-3 bg-warning-subtle text-warning rounded-4 d-inline-block mb-3 fs-3">
                                    <i class="fa-solid fa-credit-card"></i>
                                </div>
                                <h5 class="fw-bold text-dark">Reporte de Ventas a Crédito</h5>
                                <p class="text-muted small">
                                    Desglose de créditos otorgados con filtros independientes por tienda y vendedor, detallando montos totales, abonos y saldos.
                                </p>
                            </div>

                            <div class="col-md-7 ps-md-4">
                                <form action="<?php echo e(route('reports.sales.credit')); ?>" method="GET" target="_blank">
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Fecha Inicio</label>
                                            <input type="date" name="fecha1" value="<?php echo e(date('Y-m-d')); ?>" class="form-control rounded-3" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Fecha Fin</label>
                                            <input type="date" name="fecha2" value="<?php echo e(date('Y-m-d')); ?>" class="form-control rounded-3" required>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Filtro Sucursal</label>
                                            <?php if($userRole === 1): ?>
                                                <select name="sucursal" class="form-select rounded-3">
                                                    <option value="TODOS">Todas las Sucursales</option>
                                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($b->name); ?>"><?php echo e($b->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            <?php else: ?>
                                                <input type="text" class="form-control rounded-3 bg-light text-muted fw-bold" value="<?php echo e($userBranch); ?>" readonly>
                                                <input type="hidden" name="sucursal" value="<?php echo e($userBranch); ?>">
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Filtro Atendió / Vendedor</label>
                                            <select name="usuario_id" class="form-select rounded-3">
                                                <option value="TODOS">Todos los Vendedores</option>
                                                <?php $__currentLoopData = $podologists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($p->id); ?>"><?php echo e($p->name ?? $p->username); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4 bg-light p-3 rounded-3">
                                        <label class="form-label extra-small fw-bold text-muted d-block mb-2">Formato de Descarga</label>
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtPdfCred" value="pdf" checked>
                                                <label class="form-check-label fw-bold text-danger" for="fmtPdfCred">
                                                    <i class="fa-solid fa-file-pdf me-1"></i> Documento PDF
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtExcelCred" value="excel">
                                                <label class="form-check-label fw-bold text-success" for="fmtExcelCred">
                                                    <i class="fa-solid fa-file-excel me-1"></i> Hoja Excel (.xlsx)
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-warning text-dark w-100 rounded-pill fw-bold py-2 shadow-sm">
                                        <i class="fa-solid fa-download me-1"></i> Generar Ventas Crédito
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    
                    <div class="tab-pane fade" id="invoices" role="tabpanel">
                        <div class="row align-items-center">
                            <div class="col-md-5 mb-3 mb-md-0 border-end pe-md-4">
                                <div class="p-3 bg-success-subtle text-success rounded-4 d-inline-block mb-3 fs-3">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                </div>
                                <h5 class="fw-bold text-dark">Reporte de Facturación</h5>
                                <p class="text-muted small">Exporta el desglose de ventas con factura solicitada (`SI`).</p>
                            </div>
                            <div class="col-md-7 ps-md-4">
                                <form action="<?php echo e(route('reports.invoices.excel')); ?>" method="GET" target="_blank">
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Fecha Inicio</label>
                                            <input type="date" name="fecha1" value="<?php echo e(date('Y-m-d')); ?>" class="form-control rounded-3" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Fecha Fin</label>
                                            <input type="date" name="fecha2" value="<?php echo e(date('Y-m-d')); ?>" class="form-control rounded-3" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label extra-small fw-bold text-muted">Sucursal</label>
                                        <?php if($userRole === 1): ?>
                                            <select name="sucursal" class="form-select rounded-3">
                                                <option value="TODOS">Todas las Sucursales</option>
                                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($b->name); ?>"><?php echo e($b->name); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        <?php else: ?>
                                            <input type="text" class="form-control rounded-3 bg-light text-muted fw-bold" value="<?php echo e($userBranch); ?>" readonly>
                                            <input type="hidden" name="sucursal" value="<?php echo e($userBranch); ?>">
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-4 bg-light p-3 rounded-3">
                                        <label class="form-label extra-small fw-bold text-muted d-block mb-2">Formato de Descarga</label>
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtPdfInv" value="pdf">
                                                <label class="form-check-label fw-bold text-danger" for="fmtPdfInv"><i class="fa-solid fa-file-pdf me-1"></i> PDF</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtExcelInv" value="excel" checked>
                                                <label class="form-check-label fw-bold text-success" for="fmtExcelInv"><i class="fa-solid fa-file-excel me-1"></i> Excel</label>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold py-2 shadow-sm"><i class="fa-solid fa-download me-1"></i> Generar Reporte</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    
                    <div class="tab-pane fade" id="appointments" role="tabpanel">
                        <div class="row align-items-center">
                            <div class="col-md-5 mb-3 mb-md-0 border-end pe-md-4">
                                <div class="p-3 bg-primary-subtle text-primary rounded-4 d-inline-block mb-3 fs-3">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </div>
                                <h5 class="fw-bold text-dark">Reporte de Citas Podológicas</h5>
                                <p class="text-muted small">Genera listas detalladas de consultas agendadas.</p>
                            </div>
                            <div class="col-md-7 ps-md-4">
                                <form action="<?php echo e(route('reports.appointments')); ?>" method="GET" target="_blank">
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Fecha Inicio</label>
                                            <input type="date" name="fecha1" value="<?php echo e(date('Y-m-d')); ?>" class="form-control rounded-3" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Fecha Fin</label>
                                            <input type="date" name="fecha2" value="<?php echo e(date('Y-m-d')); ?>" class="form-control rounded-3" required>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Sucursal</label>
                                            <?php if($userRole === 1): ?>
                                                <select name="sucursal" class="form-select rounded-3">
                                                    <option value="TODOS">Todas las Sucursales</option>
                                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($b->name); ?>"><?php echo e($b->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            <?php else: ?>
                                                <input type="text" class="form-control rounded-3 bg-light text-muted fw-bold" value="<?php echo e($userBranch); ?>" readonly>
                                                <input type="hidden" name="sucursal" value="<?php echo e($userBranch); ?>">
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Podólogo</label>
                                            <select name="podologo_id" class="form-select rounded-3">
                                                <option value="TODOS">Todos</option>
                                                <?php $__currentLoopData = $podologists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($p->id); ?>"><?php echo e($p->name ?? $p->username); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-4 bg-light p-3 rounded-3">
                                        <label class="form-label extra-small fw-bold text-muted d-block mb-2">Formato de Descarga</label>
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtPdfApp" value="pdf" checked>
                                                <label class="form-check-label fw-bold text-danger" for="fmtPdfApp"><i class="fa-solid fa-file-pdf me-1"></i> PDF</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtExcelApp" value="excel">
                                                <label class="form-check-label fw-bold text-success" for="fmtExcelApp"><i class="fa-solid fa-file-excel me-1"></i> Excel</label>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm"><i class="fa-solid fa-download me-1"></i> Generar Citas</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    
                    <div class="tab-pane fade" id="items" role="tabpanel">
                        <div class="row align-items-center">
                            <div class="col-md-5 mb-3 mb-md-0 border-end pe-md-4">
                                <div class="p-3 bg-warning-subtle text-warning rounded-4 d-inline-block mb-3 fs-3">
                                    <i class="fa-solid fa-boxes-packing"></i>
                                </div>
                                <h5 class="fw-bold text-dark">Ventas por Artículo</h5>
                                <p class="text-muted small">Analiza el volumen de ventas por productos o artículos específicos.</p>
                            </div>
                            <div class="col-md-7 ps-md-4">
                                <form action="<?php echo e(route('reports.sales.items')); ?>" method="GET" target="_blank">
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Desde</label>
                                            <input type="date" name="fecha1" value="<?php echo e(date('Y-m-d')); ?>" class="form-control rounded-3" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Hasta</label>
                                            <input type="date" name="fecha2" value="<?php echo e(date('Y-m-d')); ?>" class="form-control rounded-3" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label extra-small fw-bold text-muted">Buscar Artículo / Producto</label>
                                        <input class="form-control rounded-3" list="productsList" name="articulo" placeholder="Nombre o SKU...">
                                        <datalist id="productsList">
                                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($prod->nombre); ?>"><?php echo e($prod->sku ? 'SKU: ' . $prod->sku . ' - ' : ''); ?><?php echo e($prod->nombre); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </datalist>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Sucursal</label>
                                            <?php if($userRole === 1): ?>
                                                <select name="sucursal" class="form-select rounded-3">
                                                    <option value="TODOS">Todas las Sucursales</option>
                                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($b->name); ?>"><?php echo e($b->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            <?php else: ?>
                                                <input type="text" class="form-control rounded-3 bg-light text-muted fw-bold" value="<?php echo e($userBranch); ?>" readonly>
                                                <input type="hidden" name="sucursal" value="<?php echo e($userBranch); ?>">
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Atendió / Vendedor</label>
                                            <select name="usuario_id" class="form-select rounded-3">
                                                <option value="TODOS">Todos</option>
                                                <?php $__currentLoopData = $podologists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($p->id); ?>"><?php echo e($p->name ?? $p->username); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-4 bg-light p-3 rounded-3">
                                        <label class="form-label extra-small fw-bold text-muted d-block mb-2">Formato de Descarga</label>
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtPdfSales" value="pdf" checked>
                                                <label class="form-check-label fw-bold text-danger" for="fmtPdfSales"><i class="fa-solid fa-file-pdf me-1"></i> PDF</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtExcelSales" value="excel">
                                                <label class="form-check-label fw-bold text-success" for="fmtExcelSales"><i class="fa-solid fa-file-excel me-1"></i> Excel</label>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-warning text-dark w-100 rounded-pill fw-bold py-2 shadow-sm"><i class="fa-solid fa-download me-1"></i> Generar Artículos</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    
                    <div class="tab-pane fade" id="loans" role="tabpanel">
                        <div class="row align-items-center">
                            <div class="col-md-5 mb-3 mb-md-0 border-end pe-md-4">
                                <div class="p-3 bg-danger-subtle rounded-4 d-inline-block mb-3 fs-3" style="color: #e040fb;">
                                    <i class="fa-solid fa-hand-holding-dollar"></i>
                                </div>
                                <h5 class="fw-bold text-dark">Reporte de Préstamos y Abonos</h5>
                                <p class="text-muted small">Genera el estado de préstamos otorgados y sus balances por sucursal.</p>
                            </div>
                            <div class="col-md-7 ps-md-4">
                                <form action="<?php echo e(route('reports.loans')); ?>" method="GET" target="_blank">
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Fecha Inicio</label>
                                            <input type="date" name="fecha1" value="<?php echo e(date('Y-m-d')); ?>" class="form-control rounded-3" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Fecha Fin</label>
                                            <input type="date" name="fecha2" value="<?php echo e(date('Y-m-d')); ?>" class="form-control rounded-3" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label extra-small fw-bold text-muted text-center d-block">Sucursal</label>
                                        <?php if($userRole === 1): ?>
                                            <select name="sucursal" class="form-select rounded-3 text-center fw-bold">
                                                <option value="TODOS">Todas las Sucursales</option>
                                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($b->name); ?>" <?php echo e($b->name == 'ALTOZANO' ? 'selected' : ''); ?>><?php echo e($b->name); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        <?php else: ?>
                                            <input type="text" class="form-control rounded-3 bg-light text-muted fw-bold text-center" value="<?php echo e($userBranch); ?>" readonly>
                                            <input type="hidden" name="sucursal" value="<?php echo e($userBranch); ?>">
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-4 bg-light p-3 rounded-3">
                                        <label class="form-label extra-small fw-bold text-muted d-block mb-2">Formato de Descarga</label>
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtPdfLoan" value="pdf" checked>
                                                <label class="form-check-label fw-bold text-danger" for="fmtPdfLoan"><i class="fa-solid fa-file-pdf me-1"></i> PDF</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtExcelLoan" value="excel">
                                                <label class="form-check-label fw-bold text-success" for="fmtExcelLoan"><i class="fa-solid fa-file-excel me-1"></i> Excel</label>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn text-white w-100 rounded-3 fw-bold py-2 shadow-sm" style="background-color: #e040fb; border: none;">
                                        Préstamos
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\PiesFelices\resources\views/reports/index.blade.php ENDPATH**/ ?>