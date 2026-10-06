<x-app-layout>
    <div class="container-fluid px-2 px-md-4 py-3" style="max-width: 100%; overflow-x: hidden;">

        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
            {{-- FILA 1: ENCABEZADO Y ACCIONES PRINCIPALES --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                <div>
                    <h5 class="fw-bold m-0 text-dark">
                        <i class="fa-solid fa-boxes-stacked text-primary me-2"></i>Gestión de Inventario
                    </h5>
                    <small class="text-muted">Consulta y administra existencias en tiempo real</small>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if (in_array($user->type, [0, 3]))
                        <button type="button" class="btn btn-success rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#newProductModal">
                            <i class="fa-solid fa-plus me-1"></i> Nuevo
                        </button>
                        <button type="button" class="btn btn-primary rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#transferModal">
                            <i class="fa-solid fa-arrows-rotate me-1"></i> Traspaso
                        </button>
                    @endif

                    <a href="{{ route('supply-orders.create', ['branch_id' => $selectedBranchId]) }}" class="btn btn-warning rounded-pill px-3 fw-semibold shadow-sm text-dark">
                        <i class="fa-solid fa-boxes-packing me-1"></i> Orden de Surtido
                    </a>

                    <a href="{{ route('inventory.movements') }}" class="btn btn-light border rounded-pill px-3 text-dark fw-semibold">
                        <i class="fa-solid fa-list-check text-primary me-1"></i> Movimientos
                    </a>
                </div>
            </div>

            {{-- FILA 2: BARRA DE FILTROS Y EXPORTACIÓN --}}
            <form method="GET" data-loading-text="Cargando." action="{{ route('inventory.index') }}" class="d-flex flex-wrap align-items-center justify-content-between gap-3 bg-light p-2 rounded-4">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    {{-- Filtro Sucursal --}}
                    <div class="d-flex align-items-center gap-2">
                        <label class="fw-bold text-secondary small text-nowrap"><i class="fa-solid fa-store text-primary me-1"></i>Sucursal:</label>
                        <select name="branch_id" class="form-select form-select-sm rounded-pill border-0 shadow-sm px-3" onchange="this.form.submit()" {{ $user->type == 1 ? 'disabled' : '' }}>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }} {{ $b->is_matrix ? '(Matriz)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro Tipo --}}
                    <div class="d-flex align-items-center gap-2">
                        <label class="fw-bold text-secondary small text-nowrap"><i class="fa-solid fa-filter text-primary me-1"></i>Tipo:</label>
                        <select name="type" class="form-select form-select-sm rounded-pill border-0 shadow-sm px-3" onchange="this.form.submit()">
                            <option value="">-- Todos --</option>
                            <option value="GENERAL" {{ $selectedType == 'GENERAL' ? 'selected' : '' }}>General</option>
                            <option value="CALZADO" {{ $selectedType == 'CALZADO' ? 'selected' : '' }}>Calzado</option>
                            <option value="MEDICAMENTO" {{ $selectedType == 'MEDICAMENTO' ? 'selected' : '' }}>Medicamento</option>
                        </select>
                    </div>

                    {{-- Toggle Bajo Stock --}}
                    <div class="d-flex align-items-center">
                        <input type="checkbox" name="low_stock" value="1" id="lowStockCheck" class="btn-check" onchange="this.form.submit()" {{ $onlyLowStock ? 'checked' : '' }}>
                        <label class="btn btn-sm {{ $onlyLowStock ? 'btn-danger' : 'btn-outline-danger' }} rounded-pill px-3 fw-semibold shadow-sm" for="lowStockCheck">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Solo Bajo Stock
                        </label>
                    </div>
                </div>

                {{-- Botón Exportar PDF a la derecha del bloque de filtros --}}
                <div>
                    <a href="{{ route('inventory.pdf', request()->query()) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold shadow-sm bg-white">
                        <i class="fa-solid fa-file-pdf me-1"></i> Exportar PDF
                    </a>
                </div>
            </form>
        </div>

        {{-- ALERTAS DE SESIÓN --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- CONTENEDOR DATATABLE --}}
        <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
            
            <form method="GET" data-loading-text="Cargando." action="{{ route('inventory.index') }}" id="dataTableForm">
                <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                <input type="hidden" name="type" value="{{ $selectedType }}">
                <input type="hidden" name="low_stock" value="{{ $onlyLowStock ? '1' : '' }}">
                <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                <input type="hidden" name="sort_order" value="{{ $sortOrder }}">

                <div class="row align-items-center mb-3 g-3">
                    <div class="col-12 col-md-6 d-flex align-items-center gap-2">
                        <span class="text-muted small">Mostrar</span>
                        <select name="per_page" class="form-select form-select-sm rounded-3" style="width: 80px;" onchange="document.getElementById('dataTableForm').submit()">
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="text-muted small">registros</span>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                <i class="fa-solid fa-magnifying-glass text-muted"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por código, nombre, modelo..." onchange="document.getElementById('dataTableForm').submit()">
                            @if(request('search'))
                                <a href="{{ route('inventory.index', ['branch_id' => $selectedBranchId, 'type' => $selectedType]) }}" class="btn btn-outline-secondary rounded-pill ms-2">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            @endif
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
                        @forelse ($products as $p)
                            @php
                                $branchData = $p->branches->first();
                                $currentStock = $branchData ? $branchData->pivot->stock_current : 0;
                                $minStock = $branchData ? $branchData->pivot->stock_min : 1;
                            @endphp
                            <tr class="{{ $currentStock <= $minStock ? 'table-warning' : '' }}">
                                <td class="text-center">
                                    @if($p->image_url)
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-circle p-1 view-image-btn" 
                                                data-img="{{ $p->image_url }}" 
                                                data-title="{{ $p->name }}" 
                                                title="Ver Fotografía del Producto">
                                            <i class="fa-solid fa-image"></i>
                                        </button>
                                    @else
                                        <span class="text-muted" title="Sin Imagen"><i class="fa-solid fa-box"></i></span>
                                    @endif
                                </td>
                                <td><span class="fw-mono text-secondary small">{{ $p->barcode ?? 'S/C' }}</span></td>
                                <td>
                                    <strong class="text-dark d-block">{{ $p->name }}</strong>
                                    @if($p->type === 'CALZADO' && ($p->model || $p->color))
                                        <span class="text-muted extra-small">Mod: {{ $p->model }} | Col: {{ $p->color }}</span>
                                    @elseif($p->type === 'MEDICAMENTO' && $p->substance)
                                        <span class="text-muted extra-small">Sustancia: {{ $p->substance }}</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $p->type }}</span></td>
                                <td><span class="fw-semibold">${{ number_format($p->sale_price, 2) }}</span></td>
                                <td><strong class="fs-6">{{ $currentStock }}</strong> <span class="text-muted small">/ Mín: {{ $minStock }}</span></td>
                                <td>
                                    @if ($currentStock <= 0)
                                        <span class="badge bg-danger rounded-pill px-2 py-1">Agotado</span>
                                    @elseif ($currentStock <= $minStock)
                                        <span class="badge bg-warning text-dark rounded-pill px-2 py-1">Bajo</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1">OK</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        
                                        @if (in_array($user->type, [0, 3]))
                                            {{-- BOTÓN STOCK --}}
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-success rounded-pill px-2 py-1 open-adjust-modal" 
                                                    data-id="{{ $p->id }}" 
                                                    data-name="{{ $p->name }}" 
                                                    data-stock="{{ $currentStock }}"
                                                    title="Agregar/Ajustar Stock">
                                                <i class="fa-solid fa-boxes-packing me-1"></i> Stock
                                            </button>

                                            {{-- BOTÓN EDITAR ARTÍCULO --}}
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary rounded-circle open-edit-modal"
                                                    data-id="{{ $p->id }}"
                                                    data-name="{{ $p->name }}"
                                                    data-type="{{ $p->type }}"
                                                    data-barcode="{{ $p->barcode }}"
                                                    data-sale-price="{{ $p->sale_price }}"
                                                    data-cost-price="{{ $p->cost_price }}"
                                                    data-model="{{ $p->model }}"
                                                    data-color="{{ $p->color }}"
                                                    data-page="{{ $p->page_number }}"
                                                    data-substance="{{ $p->substance }}"
                                                    title="Editar Datos del Artículo">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                        @endif

                                        @if ($user->type == 1 && $selectedBranchId == $user->branch_id)
                                            @if ($currentStock > 0)
                                                <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2 py-1 fw-semibold open-return-modal"
                                                        data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-stock="{{ $currentStock }}">
                                                    <i class="fa-solid fa-rotate-left me-1"></i> Devolver
                                                </button>
                                            @else
                                                <span class="text-muted small">Sin stock</span>
                                            @endif
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No se encontraron productos coincidentes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PIE CON PAGINACIÓN --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 gap-3">
                <div class="text-muted small">
                    Mostrando del <strong>{{ $products->firstItem() ?? 0 }}</strong> al <strong>{{ $products->lastItem() ?? 0 }}</strong> de <strong>{{ $products->total() }}</strong> registros
                </div>
                <div>
                    {{ $products->links() }}
                </div>
            </div>

        </div>
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

    <!-- MODAL 1: NUEVO PRODUCTO (MATRIZ) -->
    <div class="modal fade" id="newProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-plus-circle text-success me-2"></i>Registrar Nuevo Artículo (Ingreso a Matriz)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" data-loading-text="Cargando." action="{{ route('inventory.store-product') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body py-3">
                        <div class="alert alert-info rounded-3 small py-2 mb-3">
                            <i class="fa-solid fa-info-circle me-1"></i> El stock inicial ingresará a la <strong>Sucursal Matriz</strong>.
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

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Código de Barras / SKU</label>
                                <input type="text" name="barcode" class="form-control rounded-3" placeholder="750123456789">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Precio Compra (*)</label>
                                <input type="number" step="0.01" name="cost_price" class="form-control rounded-3" required value="0.00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Precio Venta (*)</label>
                                <input type="number" step="0.01" name="sale_price" class="form-control rounded-3" required value="0.00">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Stock Inicial en Matriz (*)</label>
                                <input type="number" name="initial_stock" class="form-control rounded-3" required value="10" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Stock Mínimo Alerta (*)</label>
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

    <!-- MODAL 2: AJUSTE DIRECTO DE STOCK POR FILA -->
    <div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-boxes-packing text-primary me-2"></i>Registrar Movimiento de Inventario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" data-loading-text="Cargando." action="{{ route('inventory.adjust') }}">
                    @csrf
                    <input type="hidden" name="product_id" id="adjust_product_id">
                    <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">

                    <div class="modal-body py-3">
                        <p class="mb-1">Producto: <strong id="adjust_product_name" class="text-dark"></strong></p>
                        <p class="small text-muted mb-3">Stock actual en esta sucursal: <strong id="adjust_product_stock"></strong></p>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tipo de Movimiento (*)</label>
                                <select name="type" class="form-select rounded-3" required>
                                    <option value="ENTRADA">Agregar a Inventario General</option>
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
                
                {{-- IMPORTANTE: enctype="multipart/form-data" PARA PERMITIR SUBIR LA IMAGEN --}}
                <form method="POST" data-loading-text="Cargando." id="editProductForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
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

                            <!-- CAMPO DE SUBIDA Y VISTA PREVIA DE IMAGEN -->
                            <div class="col-md-12">
                                <label class="form-label fw-semibold"><i class="fa-solid fa-image me-1 text-primary"></i> Cambiar / Subir Imagen del Producto</label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="file" name="image" id="edit_image_input" class="form-control rounded-3" accept="image/jpeg,image/png,image/jpg,image/webp">
                                </div>
                                <small class="text-muted">Formatos permitidos: JPG, PNG, WEBP (Máx. 2MB). Deja en blanco si no deseas cambiar la imagen actual.</small>
                            </div>

                            <!-- CAMPOS DINÁMICOS SEGÚN TIPO DE PRODUCTO -->
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

    <!-- MODAL TRASPASO -->
    <div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-arrows-rotate text-primary me-2"></i>Traspaso de Inventario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" data-loading-text="Cargando." action="{{ route('inventory.transfer') }}">
                    @csrf
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
                                @foreach ($branches as $b)
                                    @if (!$b->is_matrix)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endif
                                @endforeach
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
                        editForm.action = `/inventory/products/${id}`;

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

            // 4. FORMULARIO NUEVO PRODUCTO
            const typeSelect = document.getElementById('product_type_select');
            const footwearFields = document.getElementById('footwear_fields');
            const medicineFields = document.getElementById('medicine_fields');

            if (typeSelect) {
                function toggleFields() {
                    const selectedType = typeSelect.value;
                    if (selectedType === 'CALZADO') {
                        footwearFields.classList.remove('d-none');
                        medicineFields.classList.add('d-none');
                    } else if (selectedType === 'MEDICAMENTO') {
                        medicineFields.classList.remove('d-none');
                        footwearFields.classList.add('d-none');
                    } else {
                        footwearFields.classList.add('d-none');
                        medicineFields.classList.add('d-none');
                    }
                }
                typeSelect.addEventListener('change', toggleFields);
                toggleFields();
            }

            // 5. BUSCADOR TRASPASOS
            const searchInput = document.getElementById('transfer_product_search');
            const hiddenInput = document.getElementById('transfer_product_id');
            const resultsContainer = document.getElementById('transfer_search_results');
            const clearBtn = document.getElementById('btn_clear_transfer_product');
            const selectedInfo = document.getElementById('transfer_selected_info');
            const selectedText = document.getElementById('selected_product_text');

            const productsData = @json($productsJson);

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
                            const item = document.createElement('a');
                            item.href = '#';
                            item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2';
                            
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
    </script>
</x-app-layout>