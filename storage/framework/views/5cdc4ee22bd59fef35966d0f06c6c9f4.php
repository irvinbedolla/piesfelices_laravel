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
    <div class="container-fluid px-2 px-md-4 py-3" style="max-width: 100%; overflow-x: hidden;">

        <?php
            // Determinación estricta usando la variable enviada por el controlador o la relación del usuario
            $userRoleType = $userType ?? (int) (auth()->user()->tipo_seccion ?? auth()->user()->type ?? 2);
        ?>

        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
            
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h4 class="fw-bold m-0 text-primary">
                        <i class="fa-solid fa-boxes-stacked me-2"></i>Gestión de Inventario
                    </h4>
                    <small class="text-muted">Consulta y administra existencias en tiempo real</small>
                </div>

                
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    
                    
                    <?php if(in_array($userRoleType, [1, 3])): ?>
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#newProductModal">
                            <i class="fa-solid fa-plus me-1"></i> + Nuevo
                        </button>
                    <?php endif; ?>

                    
                    <?php if(in_array($userRoleType, [1, 3])): ?>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#transferModal">
                            <i class="fa-solid fa-rotate me-1"></i> Movimiento
                        </button>
                    <?php endif; ?>

                    
                    <?php if(in_array($userRoleType, [1, 2, 3])): ?>
                        <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#supplyOrderModal">
                            <i class="fa-solid fa-boxes-packing me-1"></i> Orden de Surtido
                        </button>
                    <?php endif; ?>

                    
                    <?php if(in_array($userRoleType, [1, 3])): ?>
                        <a href="<?php echo e(route('inventory.movements')); ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                            <i class="fa-solid fa-list-check me-1"></i> Movimientos
                        </a>
                    <?php endif; ?>

                </div>
            </div>

            <hr class="my-2 opacity-25">

            
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    
                    
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-secondary small"><i class="fa-solid fa-store me-1 text-primary"></i>Sucursal:</span>
                        
                        <?php if(in_array($userRoleType, [1, 3])): ?>
                            
                            <select class="form-select form-select-sm rounded-pill fw-semibold border-primary" 
                                    onchange="window.location.href='<?php echo e(route('inventory.index')); ?>?branch_id=' + this.value + '&type=<?php echo e($selectedType); ?>&low_stock=<?php echo e($onlyLowStock ? '1' : '0'); ?>'">
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($b->id); ?>" <?php echo e($selectedBranchId == $b->id ? 'selected' : ''); ?>>
                                        <?php echo e($b->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        <?php else: ?>
                            
                            <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-semibold">
                                <?php echo e($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Sucursal Asignada'); ?>

                            </span>
                        <?php endif; ?>
                    </div>

                    
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-secondary small"><i class="fa-solid fa-filter me-1 text-info"></i>Tipo:</span>
                        <select class="form-select form-select-sm rounded-pill" 
                                onchange="window.location.href='<?php echo e(route('inventory.index')); ?>?branch_id=<?php echo e($selectedBranchId); ?>&type=' + this.value + '&low_stock=<?php echo e($onlyLowStock ? '1' : '0'); ?>'">
                            <option value="">-- Todos --</option>
                            <option value="GENERAL" <?php echo e($selectedType == 'GENERAL' ? 'selected' : ''); ?>>GENERAL</option>
                            <option value="CALZADO" <?php echo e($selectedType == 'CALZADO' ? 'selected' : ''); ?>>CALZADO</option>
                            <option value="MEDICAMENTO" <?php echo e($selectedType == 'MEDICAMENTO' ? 'selected' : ''); ?>>MEDICAMENTO</option>
                        </select>
                    </div>

                    
                    <a href="<?php echo e(route('inventory.index')); ?>?branch_id=<?php echo e($selectedBranchId); ?>&type=<?php echo e($selectedType); ?>&low_stock=<?php echo e($onlyLowStock ? '0' : '1'); ?>" 
                       class="btn btn-sm <?php echo e($onlyLowStock ? 'btn-danger' : 'btn-outline-danger'); ?> rounded-pill px-3 fw-semibold">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Solo Bajo Stock
                    </a>

                </div>

                
                <div>
                    <a href="<?php echo e(route('inventory.pdf', ['branch_id' => $selectedBranchId])); ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold" target="_blank">
                        <i class="fa-solid fa-file-pdf me-1"></i> Exportar PDF
                    </a>
                </div>
            </div>
        </div>

        
        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i><?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        
        <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
            
            <form method="GET" action="<?php echo e(route('inventory.index')); ?>" id="dataTableForm">
                <input type="hidden" name="branch_id" value="<?php echo e($selectedBranchId); ?>">
                <input type="hidden" name="type" value="<?php echo e($selectedType); ?>">
                <input type="hidden" name="low_stock" value="<?php echo e($onlyLowStock ? '1' : ''); ?>">
                <input type="hidden" name="sort_by" value="<?php echo e($sortBy); ?>">
                <input type="hidden" name="sort_order" value="<?php echo e($sortOrder); ?>">

                <div class="row align-items-center mb-3 g-3">
                    <div class="col-12 col-md-6 d-flex align-items-center gap-2">
                        <span class="text-muted small">Mostrar</span>
                        <select name="per_page" class="form-select form-select-sm rounded-3" style="width: 80px;" onchange="document.getElementById('dataTableForm').submit()">
                            <option value="10" <?php echo e($perPage == 10 ? 'selected' : ''); ?>>10</option>
                            <option value="25" <?php echo e($perPage == 25 ? 'selected' : ''); ?>>25</option>
                            <option value="50" <?php echo e($perPage == 50 ? 'selected' : ''); ?>>50</option>
                            <option value="100" <?php echo e($perPage == 100 ? 'selected' : ''); ?>>100</option>
                        </select>
                        <span class="text-muted small">registros</span>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                <i class="fa-solid fa-magnifying-glass text-muted"></i>
                            </span>
                            <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por código, nombre, modelo..." onchange="document.getElementById('dataTableForm').submit()">
                            <?php if(request('search')): ?>
                                <a href="<?php echo e(route('inventory.index', ['branch_id' => $selectedBranchId, 'type' => $selectedType])); ?>" class="btn btn-outline-secondary rounded-pill ms-2">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">Img</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $branchData = $p->branches->firstWhere('id', $selectedBranchId);
                                $currentStock = $branchData ? $branchData->pivot->stock_current : 0;
                                $minStock = $branchData ? $branchData->pivot->stock_min : 1;
                            ?>
                            <tr class="<?php echo e($currentStock <= $minStock ? 'table-warning' : ''); ?>">
                                <td class="text-center">
                                    <?php if($p->image_url): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-circle p-1 view-image-btn" 
                                                data-img="<?php echo e($p->image_url); ?>" 
                                                data-title="<?php echo e($p->name); ?>" 
                                                title="Ver Fotografía del Producto">
                                            <i class="fa-solid fa-image"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted" title="Sin Imagen"><i class="fa-solid fa-box"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="fw-mono text-secondary small"><?php echo e($p->barcode ?? 'S/C'); ?></span></td>
                                <td>
                                    <strong class="text-dark d-block"><?php echo e($p->name); ?></strong>
                                    <?php if($p->type === 'CALZADO' && ($p->model || $p->color)): ?>
                                        <span class="text-muted extra-small">Mod: <?php echo e($p->model); ?> | Col: <?php echo e($p->color); ?></span>
                                    <?php elseif($p->type === 'MEDICAMENTO' && $p->substance): ?>
                                        <span class="text-muted extra-small">Sustancia: <?php echo e($p->substance); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?php echo e($p->type); ?></span></td>
                                <td><span class="fw-semibold">$<?php echo e(number_format($p->sale_price, 2)); ?></span></td>
                                <td><strong class="fs-6"><?php echo e($currentStock); ?></strong> <span class="text-muted small">/ Mín: <?php echo e($minStock); ?></span></td>
                                <td>
                                    <?php if($currentStock <= 0): ?>
                                        <span class="badge bg-danger rounded-pill px-2 py-1">Agotado</span>
                                    <?php elseif($currentStock <= $minStock): ?>
                                        <span class="badge bg-warning text-dark rounded-pill px-2 py-1">Bajo</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1">OK</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        
                                            <?php if(in_array($userRoleType, [1, 2, 3])): ?>
                                                
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success rounded-pill px-2 py-1 open-adjust-modal" 
                                                        data-id="<?php echo e($p->id); ?>" 
                                                        data-name="<?php echo e($p->name); ?>" 
                                                        data-stock="<?php echo e($currentStock); ?>"
                                                        title="Agregar/Ajustar Stock">
                                                    <i class="fa-solid fa-boxes-packing me-1"></i> Stock
                                                </button>
                                            <?php endif; ?>

                                            
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary rounded-circle open-edit-modal"
                                                    data-id="<?php echo e($p->id); ?>"
                                                    data-name="<?php echo e($p->name); ?>"
                                                    data-type="<?php echo e($p->type); ?>"
                                                    data-barcode="<?php echo e($p->barcode); ?>"
                                                    data-sale-price="<?php echo e($p->sale_price); ?>"
                                                    data-cost-price="<?php echo e($p->cost_price); ?>"
                                                    data-model="<?php echo e($p->model); ?>"
                                                    data-color="<?php echo e($p->color); ?>"
                                                    data-page="<?php echo e($p->page_number); ?>"
                                                    data-substance="<?php echo e($p->substance); ?>"
                                                    title="Editar Datos del Artículo">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No se encontraron productos coincidentes.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 gap-3">
                <div class="text-muted small">
                    Mostrando del <strong><?php echo e($products->firstItem() ?? 0); ?></strong> al <strong><?php echo e($products->lastItem() ?? 0); ?></strong> de <strong><?php echo e($products->total()); ?></strong> registros
                </div>
                <div>
                    <?php echo e($products->links()); ?>

                </div>
            </div>

        </div>

        <!-- RESUMEN DE INVENTARIO AMPLIADO (EXCLUSIVO ADMINISTRADOR) -->
        <?php if(isset($isAdmin) ? $isAdmin : auth()->user()->isAdmin()): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 mt-4 bg-light">
                <div class="d-flex align-items-center mb-3">
                    <h6 class="fw-bold m-0 text-secondary">
                        <i class="fa-solid fa-chart-pie me-2 text-primary"></i>Resumen General de Inventario
                    </h6>
                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 ms-auto">
                        <i class="fa-solid fa-user-shield me-1"></i>Vista Administrador
                    </span>
                </div>

                
                <div class="row g-3 mb-3">
                    
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 p-3 text-white h-100" style="background: linear-gradient(135deg, #0288d1, #26c6da);">
                            <div class="small fw-semibold text-white-50">Artículos Únicos</div>
                            <div class="fs-3 fw-bold my-1"><?php echo e(number_format($totalArticulos ?? 0)); ?></div>
                            <div class="extra-small text-white-50"><i class="fa-solid fa-boxes-stacked me-1"></i>En catálogo</div>
                        </div>
                    </div>

                    
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 p-3 text-white h-100" style="background: linear-gradient(135deg, #7b1fa2, #ab47bc);">
                            <div class="small fw-semibold text-white-50">Existencia Total</div>
                            <div class="fs-3 fw-bold my-1"><?php echo e(number_format($totalPiezasStock ?? 0)); ?></div>
                            <div class="extra-small text-white-50"><i class="fa-solid fa-cubes me-1"></i>Unidades físicas</div>
                        </div>
                    </div>

                    
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 p-3 text-white h-100" style="background: linear-gradient(135deg, #d81b60, #e91e63);">
                            <div class="small fw-semibold text-white-50">Inversión Total (Costo)</div>
                            <div class="fs-3 fw-bold my-1">$<?php echo e(number_format($inversionTotal ?? 0, 2)); ?></div>
                            <div class="extra-small text-white-50"><i class="fa-solid fa-sack-dollar me-1"></i>Valor en compras</div>
                        </div>
                    </div>

                    
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 p-3 text-white h-100" style="background: linear-gradient(135deg, #2e7d32, #4caf50);">
                            <div class="small fw-semibold text-white-50">Ganancia Estimada</div>
                            <div class="fs-3 fw-bold my-1">$<?php echo e(number_format($gananciaEstimada ?? 0, 2)); ?></div>
                            <div class="extra-small text-white-50"><i class="fa-solid fa-chart-line me-1"></i>Retorno proyectado</div>
                        </div>
                    </div>
                </div>

                <hr class="my-3 opacity-25">

                
                <h6 class="fw-bold mb-3 text-secondary small">
                    <i class="fa-solid fa-layer-group me-1 text-info"></i>Desglose por Categoría de Producto:
                </h6>

                <div class="row g-3">
                    
                    <div class="col-12 col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold text-dark small"><i class="fa-solid fa-box-open me-1 text-info"></i>General</span>
                                <span class="badge bg-info-subtle text-info rounded-pill px-2 py-1 small fw-semibold">
                                    <?php echo e(number_format($statsByType['GENERAL']['pieces'] ?? 0)); ?> pzas
                                </span>
                            </div>
                            <div class="fs-4 fw-bold text-dark">
                                $<?php echo e(number_format($statsByType['GENERAL']['investment'] ?? 0, 2)); ?>

                            </div>
                            <div class="extra-small text-muted mt-1">
                                Inversión estimada en artículos generales
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-12 col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold text-dark small"><i class="fa-solid fa-shoe-prints me-1 text-primary"></i>Calzado</span>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1 small fw-semibold">
                                    <?php echo e(number_format($statsByType['CALZADO']['pieces'] ?? 0)); ?> pzas
                                </span>
                            </div>
                            <div class="fs-4 fw-bold text-dark">
                                $<?php echo e(number_format($statsByType['CALZADO']['investment'] ?? 0, 2)); ?>

                            </div>
                            <div class="extra-small text-muted mt-1">
                                Inversión estimada en modelos de calzado
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-12 col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold text-dark small"><i class="fa-solid fa-pills me-1 text-success"></i>Medicamento</span>
                                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small fw-semibold">
                                    <?php echo e(number_format($statsByType['MEDICAMENTO']['pieces'] ?? 0)); ?> pzas
                                </span>
                            </div>
                            <div class="fs-4 fw-bold text-dark">
                                $<?php echo e(number_format($statsByType['MEDICAMENTO']['investment'] ?? 0, 2)); ?>

                            </div>
                            <div class="extra-small text-muted mt-1">
                                Inversión estimada en medicamentos / insumos
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </div>

    <!-- MODAL VER IMAGEN RÁPIDA -->
    <div class="modal fade" id="viewImageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 text-center">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modal_image_title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <img id="modal_image_src" src="" alt="Producto" class="img-fluid rounded-3 shadow-sm" style="max-height: 400px; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 1: NUEVO PRODUCTO -->
    <div class="modal fade" id="newProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-plus-circle text-success me-2"></i>Registrar Nuevo Artículo (Ingreso a Matriz)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?php echo e(route('inventory.store-product')); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    
                    
                    <input type="hidden" name="branch_id" value="<?php echo e($matrixBranch?->id ?? 1); ?>">
                    
                    
                    <input type="hidden" name="category_id" id="product_category_id" value="4">

                    <div class="modal-body py-3">
                        <div class="alert alert-info rounded-3 small py-2 mb-3">
                            <i class="fa-solid fa-info-circle me-1"></i> El stock inicial ingresará a la <strong>Sucursal Matriz (<?php echo e($matrixBranch?->name ?? 'Central'); ?>)</strong>.
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nombre del Producto (*)</label>
                                <input type="text" name="name" class="form-control rounded-3" required placeholder="Ej. Zapato Podológico Confort">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tipo (*)</label>
                                <select name="type" id="product_type_select" class="form-select rounded-3" required>
                                    <option value="GENERAL">General</option>
                                    <option value="CALZADO">Calzado</option>
                                    <option value="MEDICAMENTO">Medicamento</option>
                                </select>
                            </div>

                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Proveedor</label>
                                <select name="supplier_id" class="form-select rounded-3">
                                    <option value="">-- Seleccionar Proveedor --</option>
                                    <?php if(isset($suppliers)): ?>
                                        <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name ?? $supplier->company_name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Código de Barras / SKU</label>
                                <input type="text" name="barcode" class="form-control rounded-3" placeholder="750123456789">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Imagen del Producto (Opcional)</label>
                                <input type="file" name="image" class="form-control rounded-3" accept="image/*">
                            </div>

                            
                            <div id="footwear_fields" class="row g-3 m-0 p-0 d-none">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-primary">Modelo</label>
                                    <input type="text" name="model" class="form-control rounded-3" placeholder="Ej. Mod-2026">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-primary">Color</label>
                                    <input type="text" name="color" class="form-control rounded-3" placeholder="Ej. Negro / Blanco">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-primary">Nº de Página</label>
                                    <input type="number" name="page_number" class="form-control rounded-3" placeholder="Ej. 12">
                                </div>
                            </div>

                            
                            <div id="medicine_fields" class="row g-3 m-0 p-0 d-none">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold text-info">Sustancia Activa</label>
                                    <input type="text" name="substance" class="form-control rounded-3" placeholder="Ej. Mupirocina 2%">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Precio Compra (*)</label>
                                <input type="number" step="0.01" name="cost_price" class="form-control rounded-3" required value="0.00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Precio Venta (*)</label>
                                <input type="number" step="0.01" name="sale_price" class="form-control rounded-3" required value="0.00">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Stock Inicial (*)</label>
                                <input type="number" name="initial_stock" class="form-control rounded-3" required value="10" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Stock Mínimo  (*)</label>
                                <input type="number" name="stock_min" class="form-control rounded-3" required value="2" min="1">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">Guardar en Matriz</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 2: AJUSTE DIRECTO DE STOCK POR FILA (AJUSTAR O ORDEN DE SURTIDO) -->
    <div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-boxes-packing text-primary me-2"></i>Registrar Movimiento de Inventario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?php echo e(route('inventory.adjust')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="product_id" id="adjust_product_id">
                    <input type="hidden" name="branch_id" value="<?php echo e($selectedBranchId); ?>">

                    <div class="modal-body py-3">
                        <p class="mb-1">Producto: <strong id="adjust_product_name" class="text-dark"></strong></p>
                        <p class="small text-muted mb-3">Stock actual en esta sucursal: <strong id="adjust_product_stock"></strong></p>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tipo de Movimiento (*)</label>
                                <select name="type" class="form-select rounded-3" required>
                                    
                                    <?php if(in_array($userRoleType, [1, 3])): ?>
                                        <option value="ENTRADA">Agregar a Inventario General</option>
                                    <?php endif; ?>
                                    <option value="DEVOLUCION">Devolver a Inventario General</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cantidad (*)</label>
                                <input type="number" name="quantity" class="form-control rounded-3" min="1" value="1" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Referencia (Opcional)</label>
                            <input type="text" name="reference" class="form-control rounded-3" placeholder="Ej. Factura #102, Traspaso manual, etc.">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-danger">Motivo / Notas de Auditoría (*)</label>
                            <textarea name="notes" class="form-control rounded-3" rows="2" required placeholder="Justifica el movimiento para el Kárdex..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Guardar Movimiento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR PRODUCTO -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Editar Artículo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <form method="POST" id="editProductForm" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    
                    <div class="modal-body py-3">
                        <div class="alert alert-secondary rounded-3 small py-2 mb-3">
                            <i class="fa-solid fa-shield-halved me-1 text-primary"></i> Edición de datos descriptivos e imagen. <strong>No modifica existencias en inventario.</strong>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nombre del Producto (*)</label>
                                <input type="text" name="name" id="edit_name" class="form-control rounded-3" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tipo (*)</label>
                                <select name="type" id="edit_type" class="form-select rounded-3" required>
                                    <option value="GENERAL">General</option>
                                    <option value="CALZADO">Calzado</option>
                                    <option value="MEDICAMENTO">Medicamento</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold"><i class="fa-solid fa-image me-1 text-primary"></i> Cambiar / Subir Imagen del Producto</label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="file" name="image" id="edit_image_input" class="form-control rounded-3" accept="image/jpeg,image/png,image/jpg,image/webp">
                                </div>
                                <small class="text-muted">Formatos permitidos: JPG, PNG, WEBP (Máx. 2MB). Deja en blanco si no deseas cambiar la imagen actual.</small>
                            </div>

                            <div id="edit_footwear_fields" class="row g-3 m-0 p-0 d-none">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-primary">Modelo</label>
                                    <input type="text" name="model" id="edit_model" class="form-control rounded-3">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-primary">Color</label>
                                    <input type="text" name="color" id="edit_color" class="form-control rounded-3">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-primary">Nº de Página</label>
                                    <input type="number" name="page_number" id="edit_page_number" class="form-control rounded-3">
                                </div>
                            </div>

                            <div id="edit_medicine_fields" class="row g-3 m-0 p-0 d-none">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold text-info">Sustancia Activa</label>
                                    <input type="text" name="substance" id="edit_substance" class="form-control rounded-3">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Código de Barras / SKU</label>
                                <input type="text" name="barcode" id="edit_barcode" class="form-control rounded-3">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Precio Compra (*)</label>
                                <input type="number" step="0.01" name="cost_price" id="edit_cost_price" class="form-control rounded-3" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Precio Venta (*)</label>
                                <input type="number" step="0.01" name="sale_price" id="edit_sale_price" class="form-control rounded-3" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL TRASPASO / MOVIMIENTO -->
    <div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-arrows-rotate text-primary me-2"></i>Traspaso de Inventario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?php echo e(route('inventory.transfer')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body py-3">
                        <div class="mb-3 position-relative">
                            <label class="form-label fw-semibold">Producto a Traspasar (*)</label>
                            
                            <input type="hidden" name="product_id" id="transfer_product_id" required>

                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fa-solid fa-magnifying-glass text-muted"></i>
                                </span>
                                <input type="text" id="transfer_product_search" class="form-control border-start-0 rounded-end-3" 
                                       placeholder="Escribe nombre, modelo, código..." autocomplete="off" required>
                                <button type="button" class="btn btn-outline-secondary d-none" id="btn_clear_transfer_product">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <div id="transfer_search_results" class="list-group position-absolute w-100 shadow-lg rounded-3 mt-1 d-none overflow-auto" 
                                 style="max-height: 220px; z-index: 1060; background: #fff;">
                            </div>
                            
                            <div id="transfer_selected_info" class="mt-2 text-primary small d-none fw-semibold">
                                <i class="fa-solid fa-circle-check me-1 text-success"></i><span id="selected_product_text"></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sucursal Destino (*)</label>
                            <select name="destination_branch_id" class="form-select rounded-3" required>
                                <option value="">-- Selecciona sucursal destino --</option>
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(!$b->is_matrix): ?>
                                        <option value="<?php echo e($b->id); ?>"><?php echo e($b->name); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Cantidad a Traspasar (*)</label>
                            <input type="number" name="quantity" class="form-control rounded-3" min="1" value="1" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Motivo / Notas de Auditoría (*)</label>
                            <textarea name="notes" class="form-control rounded-3" rows="2" required placeholder="Ej. Reabastecimiento semanal..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Confirmar Traspaso</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: GENERAR ORDEN DE SURTIDO INTERACTIVA -->
    <div class="modal fade" id="supplyOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-boxes-packing text-warning me-2"></i>Generar Orden de Surtido
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <form method="POST" action="<?php echo e(route('supply-orders.store')); ?>" target="_blank" id="supplyOrderForm">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body py-3">
                        
                        
                        <div class="row g-3 mb-3 align-items-center">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold mb-1">Sucursal Destino (*)</label>
                                <select name="branch_id" id="so_branch_id" class="form-select rounded-3" required>
                                    <option value="">-- Selecciona sucursal --</option>
                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($b->id); ?>" <?php echo e($selectedBranchId == $b->id ? 'selected' : ''); ?>>
                                            <?php echo e($b->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-4 pt-4 text-end">
                                <button type="button" id="btnSelectAllSO" class="btn btn-sm btn-outline-primary rounded-pill">
                                    Marcar / Desmarcar Todos
                                </button>
                            </div>
                        </div>

                        
                        <div class="table-responsive rounded-3 border mb-3" style="max-height: 320px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0" id="soProductsTable">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">
                                            <input type="checkbox" id="so_check_master" checked style="cursor: pointer;">
                                        </th>
                                        <th>Producto</th>
                                        <th class="text-center" style="width: 100px;">Stock</th>
                                        <th class="text-center" style="width: 130px;">Cant. Surtir</th>
                                    </tr>
                                </thead>
                                <tbody id="so_products_tbody">
                                    
                                    <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <?php
                                            $branchData = $p->branches->firstWhere('id', $selectedBranchId);
                                            $currentStock = $branchData ? $branchData->pivot->stock_current : 0;
                                            $minStock = $branchData ? $branchData->pivot->stock_min : 1;
                                            $suggestedQty = max(1, ($minStock * 2) - $currentStock);
                                        ?>
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input so-item-check" name="items[<?php echo e($p->id); ?>][selected]" value="1" checked style="cursor: pointer;">
                                                <input type="hidden" name="items[<?php echo e($p->id); ?>][product_id]" value="<?php echo e($p->id); ?>">
                                            </td>
                                            <td>
                                                <strong class="d-block text-dark small"><?php echo e($p->name); ?></strong>
                                                <span class="text-muted extra-small"><?php echo e($p->barcode ?? 'S/C'); ?></span>
                                                <?php if($currentStock <= $minStock): ?>
                                                    <span class="badge bg-danger rounded-pill ms-1" style="font-size: 9px;">Stock Bajo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-danger-subtle text-danger fw-bold rounded-circle p-2 fs-6">
                                                    <?php echo e($currentStock); ?>

                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <input type="number" name="items[<?php echo e($p->id); ?>][quantity]" class="form-control form-control-sm text-center fw-bold rounded-3" value="<?php echo e($suggestedQty); ?>" min="1" style="width: 90px; margin: 0 auto;">
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-3 text-muted">No hay productos en bajo stock para esta sucursal.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        
                        <div class="mb-2">
                            <label class="form-label fw-semibold small">Observaciones o Notas de la Orden (Opcional)</label>
                            <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="Escribe detalles adicionales sobre el pedido..."></textarea>
                        </div>

                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold" style="background: #e91e63; border: none;">
                            <i class="fa-solid fa-paper-plane me-1"></i> Generar Orden de Surtido
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- SCRIPTS JS CORREGIDOS Y ROBUSTOS -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // 1. VISUALIZAR IMAGEN EN MODAL RÁPIDO
            const imgBtns = document.querySelectorAll('.view-image-btn');
            const imgModalElem = document.getElementById('viewImageModal');
            if (imgModalElem) {
                const imgModal = new bootstrap.Modal(imgModalElem);
                imgBtns.forEach(btn => {
                    btn.addEventListener('click', function () {
                        document.getElementById('modal_image_title').textContent = this.getAttribute('data-title');
                        document.getElementById('modal_image_src').src = this.getAttribute('data-img');
                        imgModal.show();
                    });
                });
            }

            // 2. MODAL AJUSTAR STOCK
            const adjustBtns = document.querySelectorAll('.open-adjust-modal');
            const adjustModalElem = document.getElementById('adjustStockModal');
            if (adjustModalElem) {
                const adjustModal = new bootstrap.Modal(adjustModalElem);
                adjustBtns.forEach(btn => {
                    btn.addEventListener('click', function () {
                        document.getElementById('adjust_product_id').value = this.getAttribute('data-id');
                        document.getElementById('adjust_product_name').textContent = this.getAttribute('data-name');
                        document.getElementById('adjust_product_stock').textContent = this.getAttribute('data-stock');
                        adjustModal.show();
                    });
                });
            }

            // 3. MODAL EDITAR ARTÍCULO
            const editBtns = document.querySelectorAll('.open-edit-modal');
            const editModalElem = document.getElementById('editProductModal');
            if (editModalElem) {
                const editModal = new bootstrap.Modal(editModalElem);
                const editForm = document.getElementById('editProductForm');
                const editTypeSelect = document.getElementById('edit_type');
                const editFootwear = document.getElementById('edit_footwear_fields');
                const editMedicine = document.getElementById('edit_medicine_fields');

                function toggleEditFields() {
                    const type = editTypeSelect.value;
                    if (type === 'CALZADO') {
                        editFootwear.classList.remove('d-none');
                        editMedicine.classList.add('d-none');
                    } else if (type === 'MEDICAMENTO') {
                        editMedicine.classList.remove('d-none');
                        editFootwear.classList.add('d-none');
                    } else {
                        editFootwear.classList.add('d-none');
                        editMedicine.classList.add('d-none');
                    }
                }

                if (editTypeSelect) {
                    editTypeSelect.addEventListener('change', toggleEditFields);
                }

                editBtns.forEach(btn => {
                    btn.addEventListener('click', function () {
                        const id = this.getAttribute('data-id');
                        editForm.action = `<?php echo e(url('inventory/products')); ?>/${id}`;

                        document.getElementById('edit_name').value = this.getAttribute('data-name');
                        document.getElementById('edit_type').value = this.getAttribute('data-type');
                        document.getElementById('edit_barcode').value = this.getAttribute('data-barcode') ?? '';
                        document.getElementById('edit_sale_price').value = this.getAttribute('data-sale-price');
                        document.getElementById('edit_cost_price').value = this.getAttribute('data-cost-price');
                        document.getElementById('edit_model').value = this.getAttribute('data-model') ?? '';
                        document.getElementById('edit_color').value = this.getAttribute('data-color') ?? '';
                        document.getElementById('edit_page_number').value = this.getAttribute('data-page') ?? '';
                        document.getElementById('edit_substance').value = this.getAttribute('data-substance') ?? '';

                        toggleEditFields();
                        editModal.show();
                    });
                });
            }

            // MAPEO AUTOMÁTICO DE CATEGORÍAS SEGÚN TIPO DE PRODUCTO
            const typeSelect = document.getElementById('product_type_select');
            const categoryInput = document.getElementById('product_category_id');
            const footwearFields = document.getElementById('footwear_fields');
            const medicineFields = document.getElementById('medicine_fields');

            if (typeSelect) {
                function handleTypeChange() {
                    const selectedType = typeSelect.value;

                    if (selectedType === 'CALZADO') {
                        categoryInput.value = 3; // Categoría 3 = Calzado
                        if (footwearFields) footwearFields.classList.remove('d-none');
                        if (medicineFields) medicineFields.classList.add('d-none');
                    } else if (selectedType === 'MEDICAMENTO') {
                        categoryInput.value = 2; // Categoría 2 = Medicamento
                        if (medicineFields) medicineFields.classList.remove('d-none');
                        if (footwearFields) footwearFields.classList.add('d-none');
                    } else {
                        categoryInput.value = 4; // Categoría 4 = General
                        if (footwearFields) footwearFields.classList.add('d-none');
                        if (medicineFields) medicineFields.classList.add('d-none');
                    }
                }

                typeSelect.addEventListener('change', handleTypeChange);
                handleTypeChange(); // Ejecutar al cargar para inicializar el estado
            }
            
            // 5. BUSCADOR TRASPASOS
            const searchInput = document.getElementById('transfer_product_search');
            const hiddenInput = document.getElementById('transfer_product_id');
            const resultsContainer = document.getElementById('transfer_search_results');
            const clearBtn = document.getElementById('btn_clear_transfer_product');
            const selectedInfo = document.getElementById('transfer_selected_info');
            const selectedText = document.getElementById('selected_product_text');

            const productsData = <?php echo json_encode($productsJson, 15, 512) ?>;

            if (searchInput && resultsContainer) {
                searchInput.addEventListener('input', function () {
                    const query = this.value.trim().toLowerCase();
                    if (query.length < 1) {
                        resultsContainer.classList.add('d-none');
                        return;
                    }

                    const filtered = productsData.filter(p => 
                        p.name.toLowerCase().includes(query) ||
                        p.barcode.toLowerCase().includes(query) ||
                        p.model.toLowerCase().includes(query)
                    );

                    resultsContainer.innerHTML = '';

                    if (filtered.length === 0) {
                        resultsContainer.innerHTML = `<div class="list-group-item text-muted small py-2">No se encontraron productos</div>`;
                    } else {
                        filtered.forEach(p => {
                            const item = document.createElement('button');
                            item.type = 'button';
                            item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 text-start';
                            
                            item.innerHTML = `
                                <div>
                                    <strong class="d-block text-dark small">${p.name}</strong>
                                    <span class="text-muted extra-small">${p.type}</span>
                                </div>
                                <span class="badge ${p.stock > 0 ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger'} rounded-pill">
                                    Stock: ${p.stock}
                                </span>
                            `;

                            item.addEventListener('click', function (e) {
                                e.preventDefault();
                                selectProduct(p);
                            });

                            resultsContainer.appendChild(item);
                        });
                    }

                    resultsContainer.classList.remove('d-none');
                });

                function selectProduct(p) {
                    hiddenInput.value = p.id;
                    searchInput.value = p.name;
                    searchInput.readOnly = true;
                    selectedText.textContent = `Seleccionado: ${p.name} (Stock: ${p.stock})`;
                    selectedInfo.classList.remove('d-none');
                    if (clearBtn) clearBtn.classList.remove('d-none');
                    resultsContainer.classList.add('d-none');
                }

                if (clearBtn) {
                    clearBtn.addEventListener('click', function () {
                        hiddenInput.value = '';
                        searchInput.value = '';
                        searchInput.readOnly = false;
                        clearBtn.classList.add('d-none');
                        selectedInfo.classList.add('d-none');
                        searchInput.focus();
                    });
                }
            }
        });
        // CONTROLADOR DE CASILLAS PARA ORDEN DE SURTIDO
        const masterCheck = document.getElementById('so_check_master');
        const btnSelectAll = document.getElementById('btnSelectAllSO');

        if (masterCheck) {
            masterCheck.addEventListener('change', function () {
                const itemChecks = document.querySelectorAll('.so-item-check');
                itemChecks.forEach(chk => chk.checked = this.checked);
            });
        }

        if (btnSelectAll) {
            btnSelectAll.addEventListener('click', function () {
                const itemChecks = document.querySelectorAll('.so-item-check');
                const anyUnchecked = Array.from(itemChecks).some(chk => !chk.checked);
                itemChecks.forEach(chk => chk.checked = anyUnchecked);
                if (masterCheck) masterCheck.checked = anyUnchecked;
            });
        }
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\PiesFelices\resources\views/inventory/index.blade.php ENDPATH**/ ?>