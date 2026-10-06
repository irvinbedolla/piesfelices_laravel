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
        
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold text-primary m-0">
                    <i class="fa-solid fa-file-invoice-dollar me-2"></i>Centro de Reportes
                </h4>
                <small class="text-muted">Generación de reportes en Excel (.xlsx) y PDF</small>
            </div>
        </div>

        <div class="row g-4">
            
            
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-success-subtle text-success p-3 rounded-circle fs-4">
                            <i class="fa-solid fa-file-excel"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold m-0 text-dark">Facturación Excel</h6>
                            <small class="text-muted">Exporta ventas facturadas</small>
                        </div>
                    </div>

                    <form action="<?php echo e(route('reports.invoices.excel')); ?>" method="GET" data-no-loading target="_blank">
                        <div class="mb-2">
                            <label class="form-label extra-small fw-bold text-muted">Fecha Inicio</label>
                            <input type="date" name="fecha1" value="<?php echo e(date('Y-m-01')); ?>" class="form-control form-control-sm rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Fecha Fin</label>
                            <input type="date" name="fecha2" value="<?php echo e(date('Y-m-d')); ?>" class="form-control form-control-sm rounded-3" required>
                        </div>

                        <?php if(in_array($userRole, [0, 3])): ?>
                            <div class="mb-3">
                                <label class="form-label extra-small fw-bold text-muted">Sucursal</label>
                                <select name="sucursal" class="form-select form-select-sm rounded-3">
                                    <option value="TODOS">Todas las Sucursales</option>
                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($b->name); ?>"><?php echo e($b->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold btn-sm py-2">
                            <i class="fa-solid fa-download me-1"></i> Generar Excel
                        </button>
                    </form>
                </div>
            </div>

            
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-primary-subtle text-primary p-3 rounded-circle fs-4">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold m-0 text-dark">Reporte de Citas</h6>
                            <small class="text-muted">Desglose por Fecha, Sucursal y Podólogo</small>
                        </div>
                    </div>

                    <form action="<?php echo e(route('reports.appointments')); ?>" method="GET" data-no-loading target="_blank">
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label extra-small fw-bold text-muted">Fecha Inicio</label>
                                <input type="date" name="fecha1" value="<?php echo e(date('Y-m-01')); ?>" class="form-control form-control-sm rounded-3" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label extra-small fw-bold text-muted">Fecha Fin</label>
                                <input type="date" name="fecha2" value="<?php echo e(date('Y-m-d')); ?>" class="form-control form-control-sm rounded-3" required>
                            </div>
                        </div>

                        <?php if(in_array($userRole, [0, 3])): ?>
                            <div class="mb-2">
                                <label class="form-label extra-small fw-bold text-muted">Sucursal</label>
                                <select name="sucursal" class="form-select form-select-sm rounded-3">
                                    <option value="TODOS">Todas las Sucursales</option>
                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($b->name); ?>"><?php echo e($b->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Podólogo</label>
                            <select name="podologo_id" class="form-select form-select-sm rounded-3">
                                <option value="TODOS">Todos los Podólogos</option>
                                <?php $__currentLoopData = $podologists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($p->id); ?>"><?php echo e($p->name ?? $p->username); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        
                        <div class="mb-3 bg-light p-2 rounded-3">
                            <label class="form-label extra-small fw-bold text-muted d-block mb-1">Formato de Descarga</label>
                            <div class="d-flex justify-content-around">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="formato" id="fmtPdf" value="pdf" checked>
                                    <label class="form-check-input-label small fw-bold text-danger" for="fmtPdf">
                                        <i class="fa-solid fa-file-pdf me-1"></i>PDF
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="formato" id="fmtExcel" value="excel">
                                    <label class="form-check-input-label small fw-bold text-success" for="fmtExcel">
                                        <i class="fa-solid fa-file-excel me-1"></i>Excel
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold btn-sm py-2">
                            <i class="fa-solid fa-download me-1"></i> Generar Reporte
                        </button>
                    </form>
                </div>
            </div>


            
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-warning-subtle text-warning p-3 rounded-circle fs-4">
                            <i class="fa-solid fa-boxes-packing"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold m-0 text-dark">Ventas por Artículo</h6>
                            <small class="text-muted">Filtro por producto y sucursal principal</small>
                        </div>
                    </div>

                    <form action="<?php echo e(route('reports.sales.items')); ?>" method="GET" data-no-loading target="_blank">
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label extra-small fw-bold text-muted">Desde</label>
                                <input type="date" name="fecha1" value="<?php echo e(date('Y-m-01')); ?>" class="form-control form-control-sm rounded-3" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label extra-small fw-bold text-muted">Hasta</label>
                                <input type="date" name="fecha2" value="<?php echo e(date('Y-m-d')); ?>" class="form-control form-control-sm rounded-3" required>
                            </div>
                        </div>

                        
                        <div class="mb-2">
                            <label class="form-label extra-small fw-bold text-muted">Buscar Artículo / Producto</label>
                            <input class="form-control form-control-sm rounded-3" list="productsList" name="articulo" placeholder="Escribe el nombre o SKU...">
                            <datalist id="productsList">
                                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($prod->nombre); ?>"><?php echo e($prod->sku ? 'SKU: ' . $prod->sku . ' - ' : ''); ?><?php echo e($prod->nombre); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </datalist>
                        </div>

                        
                        <div class="mb-2">
                            <label class="form-label extra-small fw-bold text-muted">Sucursal</label>
                            <select name="sucursal" class="form-select form-select-sm rounded-3">
                                <option value="MATRIZ" selected>MATRIZ (Sucursal Principal)</option>
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($b->name !== 'MATRIZ'): ?>
                                        <option value="<?php echo e($b->name); ?>"><?php echo e($b->name); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <option value="TODOS">-- TODAS LAS SUCURSALES --</option>
                            </select>
                        </div>

                        
                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Atendió / Usuario</label>
                            <select name="usuario_id" class="form-select form-select-sm rounded-3">
                                <option value="TODOS">Todos los usuarios</option>
                                <?php $__currentLoopData = $podologists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($p->id); ?>"><?php echo e($p->name ?? $p->username); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        
                        <div class="mb-3 bg-light p-2 rounded-3">
                            <label class="form-label extra-small fw-bold text-muted d-block mb-1">Formato de Descarga</label>
                            <div class="d-flex justify-content-around">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="formato" id="fmtPdfSales" value="pdf" checked>
                                    <label class="form-check-input-label small fw-bold text-danger" for="fmtPdfSales">
                                        <i class="fa-solid fa-file-pdf me-1"></i>PDF
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="formato" id="fmtExcelSales" value="excel">
                                    <label class="form-check-input-label small fw-bold text-success" for="fmtExcelSales">
                                        <i class="fa-solid fa-file-excel me-1"></i>Excel
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning text-dark w-100 rounded-pill fw-bold btn-sm py-2">
                            <i class="fa-solid fa-download me-1"></i> Generar Reporte
                        </button>
                    </form>
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
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/reports/index.blade.php ENDPATH**/ ?>