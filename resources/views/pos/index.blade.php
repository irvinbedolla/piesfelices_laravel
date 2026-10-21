<x-app-layout>
    @php
        $userRole = (int) (auth()->user()->role_id ?? auth()->user()->type ?? 2);
        // Permitir cambiar vendedor y fecha a Admin Global (rol 1) y Admin de Centro (rol 2)
        $canManageSellers = auth()->user()->isAdmin() || in_array($userRole, [1, 2]);
    @endphp
    <div class="container-fluid px-3 py-3">
        
        {{-- TOOLBAR SUPERIOR DE ACCIONES RÁPIDAS --}}
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                
                <div class="w-100">
                    <!-- Encabezado con número de venta -->
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h4 class="fw-bold text-primary m-0">
                            <i class="fa-solid fa-cart-shopping me-1"></i> Venta #{{ $sale->venta_id ?? $venta_id ?? '30756' }}
                        </h4>

                        {{-- BADGE DE SUCURSAL ASIGNADA --}}
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fs-6">
                            <i class="fa-solid fa-hospital me-1"></i> {{ $sale->venta_sucursal ?? $selectedBranch ?? $userBranch ?? 'HUETAMO' }}
                        </span>
                    </div>

                    <!-- Grilla de Botones Responsiva -->
                    <div class="row g-2">
                        
                        {{-- Botón Vendedor (Solo administradores pueden reasignar) --}}
                        <div class="col-6 col-sm-auto">
                            @if($canManageSellers)
                                <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold w-100 text-truncate" data-bs-toggle="modal" data-bs-target="#sellerModal">
                                    <i class="fa-solid fa-id-badge me-1 text-success"></i> 
                                    {{ $sale->seller->name ?? $sale->seller->username ?? 'Empleado' }}
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold w-100 text-truncate" disabled>
                                    <i class="fa-solid fa-id-badge me-1 text-secondary"></i> 
                                    {{ $sale->seller->name ?? $sale->seller->username ?? auth()->user()->name }}
                                </button>
                            @endif
                        </div>

                        {{-- Botón Seleccionar Cliente --}}
                        <div class="col-6 col-sm-auto">
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold w-100 text-truncate" data-bs-toggle="modal" data-bs-target="#customerModal">
                                <i class="fa-solid fa-user me-1 text-primary"></i> 
                                {{ $sale->customer->nombre ?? $sale->nombre_cliente ?: 'Cliente General' }}
                            </button>
                        </div>

                        {{-- BOTÓN CAMBIAR FECHA (EXCLUSIVO ADMINISTRADOR) --}}
                        <div class="col-6 col-sm-auto">
                            @if(auth()->user()->isAdmin())
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill fw-semibold w-100 text-truncate" data-bs-toggle="modal" data-bs-target="#saleDateModal">
                                    <i class="fa-solid fa-calendar-day me-1"></i> Fecha: {{ Carbon\Carbon::parse($sale->venta_fecha)->format('d/m/Y') }}
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold w-100 text-truncate" disabled>
                                    <i class="fa-solid fa-calendar-day me-1"></i> Fecha: {{ Carbon\Carbon::parse($sale->venta_fecha)->format('d/m/Y') }}
                                </button>
                            @endif
                        </div>

                        {{-- BOTÓN AGREGAR SERVICIO --}}
                        <div class="col-6 col-sm-auto">
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold w-100 text-truncate" data-bs-toggle="modal" data-bs-target="#serviceModal">
                                <i class="fa-solid fa-screwdriver-wrench me-1 text-primary"></i> Servicio
                            </button>
                        </div>

                        {{-- Botón Tipo Pago --}}
                        <div class="col-6 col-sm-auto">
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold w-100 text-truncate" data-bs-toggle="modal" data-bs-target="#paymentTypeModal">
                                <i class="fa-solid fa-credit-card me-1 text-info"></i> {{ $sale->venta_tipopago }}
                            </button>
                        </div>

                        {{-- Botón Tipo Venta --}}
                        <div class="col-6 col-sm-auto">
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold w-100 text-truncate" data-bs-toggle="modal" data-bs-target="#saleTypeModal">
                                <i class="fa-solid fa-tags me-1 text-warning"></i> {{ $sale->venta_tipo == '1' ? 'Contado' : 'Crédito' }}
                            </button>
                        </div>

                        {{-- BOTÓN FACTURA --}}
                        <div class="col-6 col-sm-auto">
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold w-100 text-truncate" data-bs-toggle="modal" data-bs-target="#invoiceModal">
                                <i class="fa-solid fa-file-invoice me-1 text-danger"></i> Factura: {{ $sale->venta_factura }}
                            </button>
                        </div>

                        {{-- Botón Descuento --}}
                        <div class="col-6 col-sm-auto">
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold w-100 text-truncate" data-bs-toggle="modal" data-bs-target="#discountModal">
                                <i class="fa-solid fa-percent me-1 text-danger"></i> Desc: ${{ number_format($sale->venta_descuento, 2) }}
                            </button>
                        </div>

                    </div>
                </div>

                {{-- Escáner Código de Barras --}}
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-muted small"><i class="fa-solid fa-barcode me-1"></i>CB:</span>
                    <input type="text" id="barcodeReader" class="form-control form-control-sm rounded-pill border-primary" placeholder="Escanear producto..." autofocus style="width: 180px;">
                </div>

            </div>
        </div>

        {{-- CONTENIDO DIVIDIDO: CATÁLOGO VS CARRITO --}}
        <div class="row g-3">
            
            {{-- LADO IZQUIERDO: CATÁLOGO DE PRODUCTOS EN SUCURSAL DEL VENDEDOR --}}
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                    <div class="mb-3">
                        <input type="text" id="searchCatalog" class="form-control rounded-pill" placeholder="Buscar por nombre, código o modelo...">
                    </div>

                    <div class="row g-2 overflow-auto" style="max-height: 550px;" id="catalogGrid">
                        @forelse($products as $p)
                            @php
                                $pivot = $p->branches->first()?->pivot;
                                $stock = $pivot ? $pivot->stock_current : 0;
                            @endphp
                            <div class="col-6 col-md-4 catalog-item" data-name="{{ strtolower($p->name) }}" data-code="{{ strtolower($p->barcode ?? '') }}">
                                <div class="card h-100 border-0 shadow-sm rounded-3 text-center p-2 hover-card cursor-pointer add-to-cart-btn" data-id="{{ $p->id }}">
                                    <div class="fw-bold text-dark text-truncate small mb-1">{{ $p->name }}</div>
                                    <span class="badge bg-light text-secondary border extra-small mb-1">{{ $p->barcode ?? 'S/C' }}</span>
                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <span class="fw-bold text-success">${{ number_format($p->sale_price, 2) }}</span>
                                        <span class="badge bg-success-subtle text-success small">Stock: {{ $stock }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5 text-muted">
                                <i class="fa-solid fa-boxes-stacked fa-2x mb-2 d-block"></i>
                                No hay artículos con existencias disponibles en la sucursal del vendedor seleccionado ({{ $sale->venta_sucursal }}).
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- LADO DERECHO: CARRITO Y TOTALES --}}
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100 d-flex flex-column justify-content-between">
                    
                    {{-- TABLA CARRITO DE COMPRA --}}
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
                                @forelse($sale->items as $item)
                                    <tr>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-item-btn" data-id="{{ $item->id }}">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <strong class="text-dark d-block text-truncate" style="max-width: 180px;">{{ $item->nombre }}</strong>
                                            <small class="text-muted">{{ $item->sku }}</small>
                                        </td>
                                        <td class="text-center">
                                            <input type="number" value="{{ $item->cantidad }}" min="1" class="form-control form-control-sm text-center fw-bold update-qty-input rounded-3" data-id="{{ $item->id }}" style="width: 65px;">
                                        </td>
                                        <td class="text-end">${{ number_format($item->precio, 2) }}</td>
                                        <td class="text-end fw-bold text-success">${{ number_format($item->cantidad * $item->precio, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="fa-solid fa-cart-flatbed fa-2x mb-2 d-block"></i>
                                            Carrito de venta vacío
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- RESUMEN Y COBRO --}}
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Descuento:</span>
                            <span class="fw-semibold text-danger">-${{ number_format($sale->venta_descuento, 2) }}</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="fw-bold text-dark m-0">TOTAL:</h4>
                            <h2 class="fw-bold text-success m-0">${{ number_format($sale->venta_total, 2) }}</h2>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success btn-lg w-100 rounded-pill fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#payModal">
                                <i class="fa-solid fa-cash-register me-2"></i> Cobrar / Finalizar
                            </button>
                            <a href="{{ route('pos.cancel', $sale->venta_id) }}" class="btn btn-outline-danger btn-lg rounded-pill px-4" onclick="return confirm('¿Cancelar esta venta?')">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- MODAL COBRAR / CALCULAR CAMBIO -->
    <div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 text-center p-3">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-coins text-success me-2"></i>Finalizar Cobro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <h3 class="fw-bold text-dark mb-3">Total: <span class="text-success">${{ number_format($sale->venta_total, 2) }}</span></h3>
                    
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

    <!-- MODAL SELECCIONAR O REGISTRAR CLIENTE (#customerModal) -->
    <div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="modal-title fw-bold text-dark m-0">
                        <i class="fa-solid fa-user text-primary me-2"></i>Seleccionar Cliente
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#fullCustomerModal">
                            <i class="fa-solid fa-user-plus me-1"></i> + Registro Completo
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                
                <div class="modal-body py-3">
                    {{-- REGISTRO RÁPIDO --}}
                    <div class="card border-0 bg-light p-3 rounded-4 mb-3">
                        <h6 class="fw-bold text-dark small mb-2">
                            <i class="fa-solid fa-bolt me-1 text-warning"></i> Nuevo Cliente Rápido
                        </h6>
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-md-8">
                                <input type="text" id="fastCustomerName" class="form-control rounded-3" placeholder="Nombre completo del cliente (*)">
                                <input type="hidden" id="fastCustomerBranch" value="{{ $sale->venta_sucursal ?? $userBranchName ?? 'MATRIZ' }}">
                            </div>
                            <div class="col-12 col-md-4 text-end">
                                <button type="button" id="saveFastCustomerBtn" class="btn btn-primary w-100 rounded-pill fw-semibold">
                                    <i class="fa-solid fa-plus me-1"></i> Guardar y Asignar
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- BUSCADOR EN VIVO --}}
                    <div class="mb-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                <i class="fa-solid fa-magnifying-glass text-muted"></i>
                            </span>
                            <input type="text" id="searchCustomerInput" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por nombre de cliente...">
                        </div>
                    </div>

                    {{-- TABLA DE CLIENTES --}}
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
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 extra-small">
                                            {{ $sale->venta_sucursal }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 assign-customer-btn" data-id="0" data-name="Cliente General">
                                            Seleccionar
                                        </button>
                                    </td>
                                </tr>

                                @forelse($customers as $c)
                                    <tr class="customer-row" data-name="{{ strtolower($c->nombre) }}">
                                        <td><strong class="text-dark d-block">{{ $c->nombre }}</strong></td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 extra-small">
                                                {{ $c->sucursal ?? $sale->venta_sucursal }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 assign-customer-btn" data-id="{{ $c->cliente_id }}" data-name="{{ $c->nombre }}">
                                                Seleccionar
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted">No hay clientes registrados en esta sucursal.</td>
                                    </tr>
                                @endforelse
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
                                    @foreach($branches as $b)
                                        <option value="{{ $b->name }}" {{ ($sale->venta_sucursal == $b->name) ? 'selected' : '' }}>{{ $b->name }}</option>
                                    @endforeach
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

    <!-- MODAL SELECCIONAR EMPLEADO VENDEDOR (#sellerModal - EXCLUSIVO ADMIN) -->
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
                            @foreach($sellers as $s)
                                @php
                                    $sellerName = $s->name ?? $s->username ?? 'Empleado #' . $s->id;
                                @endphp
                                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center assign-seller-btn" data-id="{{ $s->id }}" data-name="{{ $sellerName }}">
                                    <span class="fw-semibold text-dark">
                                        <i class="fa-solid fa-user-circle me-2 text-secondary"></i>{{ $sellerName }}
                                    </span>
                                    @if($sale->venta_idempleado == $s->id)
                                        <span class="badge bg-success rounded-pill">Activo</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>


    <!-- MODAL TIPO DE PAGO (#paymentTypeModal) -->
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

    <!-- MODAL TIPO DE VENTA (#saleTypeModal) -->
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

    <!-- MODAL APLICAR DESCUENTO (#discountModal) -->
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
                        <input type="number" step="0.01" id="discountInput" class="form-control rounded-3 text-center fw-bold" value="{{ $sale->venta_descuento }}" placeholder="0.00">
                    </div>
                    <button type="button" id="saveDiscountBtn" class="btn btn-danger w-100 rounded-pill fw-semibold">
                        Aplicar Descuento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL AGREGAR SERVICIO (#serviceModal) -->
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
                        <label class="form-label fw-semibold">Concepto o Descripción del Servicio (*)</label>
                        <input type="text" id="serviceConcept" class="form-control rounded-3" placeholder="Ej. Consulta / Fabricación de Plantillas">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Monto / Precio ($) (*)</label>
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

    <!-- MODAL FACTURACIÓN (#invoiceModal) -->
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
                            <option value="NO" {{ $sale->venta_factura == 'NO' ? 'selected' : '' }}>NO</option>
                            <option value="SI" {{ $sale->venta_factura == 'SI' ? 'selected' : '' }}>SI</option>
                        </select>
                    </div>

                    <div id="invoiceFieldsContainer" class="{{ $sale->venta_factura == 'SI' ? '' : 'd-none' }}">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Número / Folio de Factura</label>
                            <input type="text" id="invoiceNumberInput" class="form-control rounded-3" value="{{ $sale->numero_factura }}" placeholder="Ej. A-1001">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Uso de CFDI</label>
                            <select id="invoiceCfdiSelect" class="form-select rounded-3">
                                <option value="G03" {{ $sale->venta_cfdi == 'G03' ? 'selected' : '' }}>G03 - Gastos en general</option>
                                <option value="P01" {{ $sale->venta_cfdi == 'P01' ? 'selected' : '' }}>P01 - Por definir</option>
                                <option value="S01" {{ $sale->venta_cfdi == 'S01' ? 'selected' : '' }}>S01 - Sin efectos fiscales</option>
                                <option value="D01" {{ $sale->venta_cfdi == 'D01' ? 'selected' : '' }}>D01 - Honorarios médicos, dentales y gastos hospitalarios</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tipo de Factura</label>
                            <select id="invoiceTypeSelect" class="form-select rounded-3">
                                <option value="Ingreso" {{ $sale->venta_tipo_factura == 'Ingreso' ? 'selected' : '' }}>Ingreso</option>
                                <option value="PPD" {{ $sale->venta_tipo_factura == 'PPD' ? 'selected' : '' }}>PPD - Pago en parcialidades o diferido</option>
                                <option value="PUE" {{ $sale->venta_tipo_factura == 'PUE' ? 'selected' : '' }}>PUE - Pago en una sola exhibición</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="saveInvoiceBtn" class="btn btn-danger rounded-pill px-4 fw-semibold">
                        Guardar Factura
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    {{-- OVERLAY / MODAL DE ESPERA AJAX --}}
    <div id="loadingOverlay" 
        class="position-fixed top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center bg-dark bg-opacity-50" 
        style="z-index: 9999;">
        <div class="card border-0 shadow-lg rounded-4 p-4 text-center bg-white" style="max-width: 300px;">
            <div class="spinner-border text-primary mx-auto mb-3" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <h6 class="fw-bold m-0 text-dark">Procesando Selección...</h6>
            <small class="text-muted extra-small">Por favor espera un momento</small>
        </div>
    </div>

    <!-- MODAL CAMBIAR FECHA DE VENTA (#saleDateModal - EXCLUSIVO ADMIN) -->
    @if(auth()->user()->isAdmin())
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
                            <input type="date" id="saleDateInput" class="form-control rounded-3 text-center fw-bold" value="{{ $sale->venta_fecha }}">
                        </div>
                        <button type="button" id="saveSaleDateBtn" class="btn btn-danger w-100 rounded-pill fw-semibold">
                            Guardar Nueva Fecha
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- SCRIPTS JS AJAX DE CONTROL DEL POS --}}
    <script>
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

        document.addEventListener('DOMContentLoaded', function () {
            const saleId = "{{ $sale->venta_id }}";
            const totalSale = parseFloat("{{ $sale->venta_total }}");

            // Escáner de Código de Barras
            const barcodeInput = document.getElementById('barcodeReader');
            if (barcodeInput) {
                barcodeInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const barcode = this.value.trim();
                        if (barcode) {
                            addProductAjax({ barcode: barcode });
                            this.value = '';
                        }
                    }
                });
            }

            // Agregar al Carrito desde el Catálogo
            document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const productId = this.getAttribute('data-id');
                    addProductAjax({ product_id: productId });
                });
            });

            // Función AJAX para agregar producto/servicio
            function addProductAjax(payload) {
                showLoading();

                fetch("{{ url('pos') }}/" + saleId + "/add-product", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json().then(data => ({ status: res.status, body: data })))
                .then(res => {
                    if (res.body.success) {
                        location.reload();
                    } else {
                        hideLoading();
                        alert(res.body.message || 'Error al agregar el producto');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error AJAX:', error);
                    alert('Ocurrió un error de conexión con el servidor.');
                });
            }

            // Cambio de Cantidad
            document.querySelectorAll('.update-qty-input').forEach(input => {
                input.addEventListener('change', function () {
                    const itemId = this.getAttribute('data-id');
                    const qty = this.value;

                    fetch(`{{ url('pos/item') }}/${itemId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ cantidad: qty })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message);
                            location.reload();
                        }
                    });
                });
            });

            // Eliminar Producto
            document.querySelectorAll('.remove-item-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const itemId = this.getAttribute('data-id');
                    fetch(`{{ url('pos/item') }}/${itemId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
                });
            });

            // Calculadora de Cambio
            const receivedInput = document.getElementById('receivedInput');
            const changeOutput = document.getElementById('changeOutput');

            if (receivedInput) {
                receivedInput.addEventListener('input', function () {
                    const received = parseFloat(this.value) || 0;
                    const change = received - totalSale;
                    changeOutput.value = change >= 0 ? `$${change.toFixed(2)}` : '$0.00';
                });
            }

            // Confirmar Pago y Finalizar Venta (VERSIÓN CORREGIDA Y ROBUSTA)
            const confirmPayBtn = document.getElementById('confirmPayBtn');
            if (confirmPayBtn) {
                confirmPayBtn.addEventListener('click', function () {
                    // Validar que exista la entrada de pago recibido
                    const receivedInput = document.getElementById('receivedInput');
                    const received = receivedInput ? (parseFloat(receivedInput.value) || totalSale) : totalSale;
                    const printTicket = document.getElementById('printTicketCheck') ? document.getElementById('printTicketCheck').checked : true;

                    if (typeof saleId === 'undefined' || !saleId) {
                        alert('Error: No hay una venta activa seleccionada.');
                        return;
                    }

                    showLoading();

                    fetch("{{ url('pos') }}/" + saleId + "/finish", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json', // Forzar a Laravel a responder siempre en JSON
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ 
                            abono: received,
                            print_ticket: printTicket
                        })
                    })
                    .then(async res => {
                        const isJson = res.headers.get('content-type')?.includes('application/json');
                        const data = isJson ? await res.json() : null;

                        if (!res.ok) {
                            // Si el servidor devolvió un error HTTP (500, 422, etc.)
                            const errorMsg = data?.message || data?.error || 'Error interno en el servidor (' + res.status + ')';
                            throw new Error(errorMsg);
                        }

                        return data;
                    })
                    .then(data => {
                        if (data && data.success) {
                            if (printTicket && data.redirect) {
                                window.open(data.redirect, '_blank');
                            }
                            window.location.href = "{{ url('pos') }}";
                        } else {
                            hideLoading();
                            alert(data?.message || 'No se pudo completar la venta');
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        console.error('Error al finalizar venta:', error);
                        alert('Error al procesar el cobro: ' + error.message);
                    });
                });
            }

            // Filtro rápido de Catálogo
            const searchCatalog = document.getElementById('searchCatalog');
            if (searchCatalog) {
                searchCatalog.addEventListener('keyup', function () {
                    const q = this.value.toLowerCase();
                    document.querySelectorAll('.catalog-item').forEach(item => {
                        const name = item.getAttribute('data-name');
                        const code = item.getAttribute('data-code');
                        if (name.includes(q) || code.includes(q)) {
                            item.classList.remove('d-none');
                        } else {
                            item.classList.add('d-none');
                        }
                    });
                });
            }

            // 1. Filtrar tabla de clientes en vivo (CORREGIDO Y SEGURO)
            const searchCustomerInput = document.getElementById('searchCustomerInput');
            if (searchCustomerInput) {
                searchCustomerInput.addEventListener('keyup', function () {
                    const q = this.value.toLowerCase().trim();
                    
                    document.querySelectorAll('#customersPosTable tbody tr.customer-row').forEach(row => {
                        const nameAttr = row.getAttribute('data-name') || '';
                        const phoneAttr = row.getAttribute('data-phone') || '';
                        
                        if (nameAttr.toLowerCase().includes(q) || phoneAttr.toLowerCase().includes(q)) {
                            row.classList.remove('d-none');
                        } else {
                            row.classList.add('d-none');
                        }
                    });
                });
            }

            // 2. Asignar cliente seleccionado
            document.querySelectorAll('.assign-customer-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const customerId = this.getAttribute('data-id');
                    const customerName = this.getAttribute('data-name');
                    showLoading();

                    fetch("{{ url('pos') }}/" + saleId + "/header", {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            venta_idcliente: customerId,
                            nombre_cliente: customerName
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            hideLoading();
                            alert('Error al asignar el cliente');
                        }
                    });
                });
            });

            // 3. Guardado Rápido de Nuevo Cliente
            const saveFastCustomerBtn = document.getElementById('saveFastCustomerBtn');
            if (saveFastCustomerBtn) {
                saveFastCustomerBtn.addEventListener('click', function () {
                    const name = document.getElementById('fastCustomerName').value.trim();
                    const branch = document.getElementById('fastCustomerBranch').value || '{{ $sale->venta_sucursal }}';

                    if (!name) {
                        alert('Por favor ingresa el nombre completo del cliente');
                        return;
                    }

                    showLoading();

                    fetch("{{ url('clientes') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            nombre: name,
                            sucursal: branch // Se envía siempre la sucursal activa de la venta
                        })
                    })
                    .then(res => res.json().then(data => ({ status: res.status, data: data })))
                    .then(res => {
                        if (res.status === 200 || res.status === 201 || res.data.success) {
                            const newCustomerId = res.data.cliente_id || res.data.id || 0;

                            // Asignar inmediatamente el cliente a la venta actual
                            return fetch("{{ url('pos') }}/" + saleId + "/header", {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    venta_idcliente: newCustomerId,
                                    nombre_cliente: name
                                })
                            });
                        } else {
                            hideLoading();
                            const msg = res.data.message || 'Error al registrar el cliente';
                            alert(msg);
                            throw new Error(msg);
                        }
                    })
                    .then(res => {
                        if (res) return res.json();
                    })
                    .then(data => {
                        if (data && data.success) {
                            location.reload();
                        }
                    })
                    .catch(err => {
                        hideLoading();
                        console.error(err);
                    });
                });
            }

            // 4. Asignar Empleado Vendedor (AJAX REASIGNACIÓN DE SUCURSAL Y CATÁLOGO)
            document.querySelectorAll('.assign-seller-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const sellerId = this.getAttribute('data-id');
                    showLoading();

                    fetch("{{ url('pos') }}/" + saleId + "/header", {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            venta_idempleado: sellerId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            hideLoading();
                            alert('Error al reasignar el vendedor');
                        }
                    })
                    .catch(() => hideLoading());
                });
            });

            // 5. Buscador en vivo de Empleados
            const searchSellerInput = document.getElementById('searchSellerInput');
            if (searchSellerInput) {
                searchSellerInput.addEventListener('keyup', function () {
                    const q = this.value.toLowerCase();
                    document.querySelectorAll('.assign-seller-btn').forEach(btn => {
                        const name = btn.getAttribute('data-name').toLowerCase();
                        if (name.includes(q)) {
                            btn.classList.remove('d-none');
                        } else {
                            btn.classList.add('d-none');
                        }
                    });
                });
            }

            // 6. Asignar Valores de Encabezado Genéricos
            document.querySelectorAll('.set-header-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const field = this.getAttribute('data-field');
                    const val = this.getAttribute('data-val');

                    let payload = {};
                    payload[field] = val;

                    fetch("{{ url('pos') }}/" + saleId + "/header", {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
                });
            });

            // 7. Aplicar Descuento
            const saveDiscountBtn = document.getElementById('saveDiscountBtn');
            if (saveDiscountBtn) {
                saveDiscountBtn.addEventListener('click', function () {
                    const discountVal = parseFloat(document.getElementById('discountInput').value) || 0;

                    fetch("{{ url('pos') }}/" + saleId + "/header", {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            venta_descuento: discountVal
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
                });
            }

            // 8. LÓGICA DE AGREGAR SERVICIO
            const saveServiceBtn = document.getElementById('saveServiceBtn');
            if (saveServiceBtn) {
                saveServiceBtn.addEventListener('click', function () {
                    const concept = document.getElementById('serviceConcept').value.trim();
                    const amount = parseFloat(document.getElementById('serviceAmount').value) || 0;

                    if (!concept || amount <= 0) {
                        alert('Por favor ingresa un concepto válido y un monto mayor a 0');
                        return;
                    }

                    showLoading();

                    fetch("{{ url('pos') }}/" + saleId + "/add-service", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ concept: concept, amount: amount })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            hideLoading();
                            alert(data.message);
                        }
                    })
                    .catch(() => hideLoading());
                });
            }

            // 9. LÓGICA DE FACTURACIÓN Y CONSECUTIVO AUTOMÁTICO
            const invoiceRequiredSelect = document.getElementById('invoiceRequiredSelect');
            const invoiceFieldsContainer = document.getElementById('invoiceFieldsContainer');
            const invoiceNumberInput = document.getElementById('invoiceNumberInput');

            if (invoiceRequiredSelect) {
                invoiceRequiredSelect.addEventListener('change', function () {
                    if (this.value === 'SI') {
                        if (invoiceFieldsContainer) invoiceFieldsContainer.classList.remove('d-none');
                        
                        if (invoiceNumberInput && !invoiceNumberInput.value) {
                            fetch("{{ url('pos') }}/" + saleId + "/next-invoice")
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

            // 10. GUARDAR FACTURA
            const saveInvoiceBtn = document.getElementById('saveInvoiceBtn');
            if (saveInvoiceBtn) {
                saveInvoiceBtn.addEventListener('click', function () {
                    const isRequired = invoiceRequiredSelect ? invoiceRequiredSelect.value : 'NO';
                    const numInvoice = invoiceNumberInput ? invoiceNumberInput.value : '';
                    const cfdi = document.getElementById('invoiceCfdiSelect') ? document.getElementById('invoiceCfdiSelect').value : null;
                    const invoiceType = document.getElementById('invoiceTypeSelect') ? document.getElementById('invoiceTypeSelect').value : null;

                    fetch("{{ url('pos') }}/" + saleId + "/header", {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
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

            // 11. CAMBIAR FECHA DE VENTA (EXCLUSIVO ADMIN)
            const saveSaleDateBtn = document.getElementById('saveSaleDateBtn');
            if (saveSaleDateBtn) {
                saveSaleDateBtn.addEventListener('click', function () {
                    const newDate = document.getElementById('saleDateInput').value;

                    if (!newDate) {
                        alert('Por favor selecciona una fecha válida');
                        return;
                    }

                    showLoading();

                    fetch("{{ url('pos') }}/" + saleId + "/header", {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            venta_fecha: newDate
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            hideLoading();
                            alert('Ocurrió un error al actualizar la fecha');
                        }
                    })
                    .catch(() => hideLoading());
                });
            }

            // Guardar Cliente Completo desde #fullCustomerModal
            const saveFullCustomerBtn = document.getElementById('saveFullCustomerBtn');
            if (saveFullCustomerBtn) {
                saveFullCustomerBtn.addEventListener('click', function () {
                    const name = document.getElementById('fullCustomerName').value.trim();
                    const branch = document.getElementById('fullCustomerBranch').value || '{{ $sale->venta_sucursal }}';

                    if (!name) {
                        alert('Por favor ingresa el nombre completo del cliente');
                        return;
                    }

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

                    fetch("{{ url('clientes') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json().then(data => ({ status: res.status, data: data })))
                    .then(res => {
                        if (res.status === 200 || res.status === 201 || res.data.success) {
                            const newCustomerId = res.data.cliente_id || res.data.id || 0;

                            // Asignar inmediatamente al POS de la venta activa
                            return fetch("{{ url('pos') }}/" + saleId + "/header", {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    venta_idcliente: newCustomerId,
                                    nombre_cliente: name
                                })
                            });
                        } else {
                            hideLoading();
                            const msg = res.data.message || 'Error al registrar el cliente';
                            alert(msg);
                            throw new Error(msg);
                        }
                    })
                    .then(res => {
                        if (res) return res.json();
                    })
                    .then(data => {
                        if (data && data.success) {
                            location.reload();
                        }
                    })
                    .catch(err => {
                        hideLoading();
                        console.error(err);
                    });
                });
            }
        });
    </script>
</x-app-layout>