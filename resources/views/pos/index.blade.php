<x-app-layout>
    <div class="container-fluid px-3 py-3">
        
        {{-- TOOLBAR SUPERIOR DE ACCIONES RÁPIDAS --}}
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="fw-bold m-0 text-primary me-3">
                        <i class="fa-solid fa-cart-shopping me-2"></i>Venta #{{ $sale->venta_id }}
                    </h4>

                    {{-- Botón Seleccionar Cliente --}}
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#customerModal">
                        <i class="fa-solid fa-user me-1 text-primary"></i> 
                        {{ $sale->customer->nombre ?? $sale->nombre_cliente ?: 'Cliente General' }}
                    </button>

                    {{-- Botón Vendedor --}}
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#sellerModal">
                        <i class="fa-solid fa-id-badge me-1 text-success"></i> 
                        {{ $sale->seller->name ?? 'Empleado' }}
                    </button>

                    {{-- Botón Tipo Pago --}}
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#paymentTypeModal">
                        <i class="fa-solid fa-credit-card me-1 text-info"></i> {{ $sale->venta_tipopago }}
                    </button>

                    {{-- Botón Tipo Venta (Contado/Crédito) --}}
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#saleTypeModal">
                        <i class="fa-solid fa-tags me-1 text-warning"></i> {{ $sale->venta_tipo == '1' ? 'Contado' : 'Crédito' }}
                    </button>

                    {{-- Botón Descuento --}}
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#discountModal">
                        <i class="fa-solid fa-percent me-1 text-danger"></i> Desc: ${{ number_format($sale->venta_descuento, 2) }}
                    </button>

                    {{-- BOTÓN AGREGAR SERVICIO --}}
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#serviceModal">
                        <i class="fa-solid fa-screwdriver-wrench me-1 text-primary"></i> Servicio
                    </button>

                    {{-- BOTÓN FACTURA --}}
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#invoiceModal">
                        <i class="fa-solid fa-file-invoice me-1 text-danger"></i> Factura: {{ $sale->venta_factura }}
                    </button>
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
            
            {{-- LADO IZQUIERDO: CATÁLOGO DE PRODUCTOS EN SUCURSAL --}}
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                    <div class="mb-3">
                        <input type="text" id="searchCatalog" class="form-control rounded-pill" placeholder="Buscar por nombre, código o modelo...">
                    </div>

                    {{-- LADO IZQUIERDO: CATÁLOGO DE PRODUCTOS DE LA SUCURSAL --}}
                    <div class="row g-2 overflow-auto" style="max-height: 550px;" id="catalogGrid">
                        @forelse($products as $p)
                            @php
                                // Obtener el pivot específico de la sucursal filtrada
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
                                No hay artículos con existencias disponibles en esta sucursal.
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
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-user text-primary me-2"></i>Seleccionar Cliente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body py-3">
                    
                    {{-- FORMULARIO DE REGISTRO RÁPIDO DE CLIENTE --}}
                    <div class="card border-0 bg-light p-3 rounded-4 mb-3">
                        <h6 class="fw-bold text-dark small mb-2"><i class="fa-solid fa-user-plus me-1 text-success"></i> Registro Rápido</h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="text" id="fastCustomerName" class="form-control form-control-sm rounded-3" placeholder="Nombre completo (*)">
                            </div>
                            <div class="col-md-6">
                                <input type="text" id="fastCustomerPhone" class="form-control form-control-sm rounded-3" placeholder="Teléfono">
                            </div>
                            <div class="col-12 text-end mt-2">
                                <button type="button" id="saveFastCustomerBtn" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                                    Guardar y Asignar
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- BUSCADOR EN VIVO DE CLIENTES --}}
                    <div class="mb-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                <i class="fa-solid fa-magnifying-glass text-muted"></i>
                            </span>
                            <input type="text" id="searchCustomerInput" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por nombre o teléfono...">
                        </div>
                    </div>

                    {{-- TABLA DE CLIENTES DE LA SUCURSAL --}}
                    <div class="table-responsive" style="max-height: 280px;">
                        <table class="table table-hover align-middle mb-0" id="customersPosTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Teléfono</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- OPCIÓN CLIENTE GENERAL / MOSTRADOR --}}
                                <tr>
                                    <td>
                                        <strong class="text-dark d-block">Cliente General / Público</strong>
                                        <small class="text-muted">Venta mostrador general</small>
                                    </td>
                                    <td><span class="text-muted small">N/A</span></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 assign-customer-btn" data-id="0" data-name="Cliente General">
                                            Seleccionar
                                        </button>
                                    </td>
                                </tr>

                                @forelse($customers as $c)
                                    <tr class="customer-row" data-name="{{ strtolower($c->nombre) }}" data-phone="{{ strtolower($c->telefono) }}">
                                        <td>
                                            <strong class="text-dark d-block">{{ $c->nombre }}</strong>
                                            <small class="text-muted">{{ $c->correo ?? 'Sin correo' }}</small>
                                        </td>
                                        <td><span class="small fw-semibold">{{ $c->telefono ?? 'N/A' }}</span></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 assign-customer-btn" data-id="{{ $c->cliente_id }}" data-name="{{ $c->nombre }}">
                                                Seleccionar
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted">No hay clientes registrados para esta sucursal.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- MODAL SELECCIONAR EMPLEADO VENDEDOR (#sellerModal) -->
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

    <!-- MODAL DATOS DE FACTURACIÓN (#invoiceModal) -->
    <div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i>Datos de Facturación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    
                    {{-- SWITCH FACTURAR SÍ / NO --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">¿Requiere Factura?</label>
                        <select id="invoiceRequiredSelect" class="form-select rounded-3">
                            <option value="NO" {{ $sale->venta_factura == 'NO' ? 'selected' : '' }}>NO</option>
                            <option value="SI" {{ $sale->venta_factura == 'SI' ? 'selected' : '' }}>SÍ</option>
                        </select>
                    </div>

                    {{--CAMPOS ADICIONALES (SE MUESTRAN SOLO SI FACTURA ES SÍ) --}}
                    <div id="invoiceFieldsContainer" class="{{ $sale->venta_factura == 'SI' ? '' : 'd-none' }}">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Número de Factura (Consecutivo)</label>
                            <input type="number" id="invoiceNumberInput" class="form-control rounded-3 fw-bold text-primary" value="{{ $sale->numero_factura }}" placeholder="Cargando consecutivo...">
                            <small class="text-muted">Calculado automáticamente. Puedes modificarlo manualmente si lo requieres.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Uso de CFDI</label>
                            <select id="invoiceCfdiSelect" class="form-select rounded-3">
                                <option value="G01" {{ $sale->venta_cfdi == 'G01' ? 'selected' : '' }}>G01 - Adquisición de mercancías</option>
                                <option value="G03" {{ $sale->venta_cfdi == 'G03' ? 'selected' : '' }}>G03 - Gastos en general</option>
                                <option value="P01" {{ $sale->venta_cfdi == 'P01' ? 'selected' : '' }}>P01 - Por definir</option>
                                <option value="D01" {{ $sale->venta_cfdi == 'D01' ? 'selected' : '' }}>D01 - Honorarios médicos</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tipo de Factura</label>
                            <select id="invoiceTypeSelect" class="form-select rounded-3">
                                <option value="PG" {{ $sale->venta_tipo_factura == 'PG' ? 'selected' : '' }}>PG (Público en General)</option>
                                <option value="Factura" {{ $sale->venta_tipo_factura == 'Factura' ? 'selected' : '' }}>Factura Nominal</option>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="saveInvoiceBtn" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        Guardar Datos
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS JS AJAX DE CONTROL DEL POS --}}
    <script>
        const saleId = "{{ $sale->venta_id }}";
        const totalSale = parseFloat("{{ $sale->venta_total }}");

        document.addEventListener('DOMContentLoaded', function () {
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

            // Función AJAX Agregar Producto
            function addProductAjax(payload) {
                fetch(`/pos/${saleId}/add-product`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
            }

            // Cambio de Cantidad
            document.querySelectorAll('.update-qty-input').forEach(input => {
                input.addEventListener('change', function () {
                    const itemId = this.getAttribute('data-id');
                    const qty = this.value;

                    fetch(`/pos/item/${itemId}`, {
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
                    fetch(`/pos/item/${itemId}`, {
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

            // Confirmar Pago y Finalizar Venta
            const confirmPayBtn = document.getElementById('confirmPayBtn');
            if (confirmPayBtn) {
                confirmPayBtn.addEventListener('click', function () {
                    const received = parseFloat(receivedInput.value) || totalSale;

                    fetch(`/pos/${saleId}/finish`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ abono: received })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.open(data.redirect, '_blank');
                            window.location.href = '/pos';
                        } else {
                            alert(data.message);
                        }
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
        });

        // 1. Filtrar tabla de clientes en vivo
        const searchCustomerInput = document.getElementById('searchCustomerInput');
        if (searchCustomerInput) {
            searchCustomerInput.addEventListener('keyup', function () {
                const q = this.value.toLowerCase();
                document.querySelectorAll('.customer-row').forEach(row => {
                    const name = row.getAttribute('data-name');
                    const phone = row.getAttribute('data-phone');
                    if (name.includes(q) || phone.includes(q)) {
                        row.classList.remove('d-none');
                    } else {
                        row.classList.add('d-none');
                    }
                });
            });
        }

        // 2. Asignar cliente seleccionado a la venta activa mediante AJAX
        document.querySelectorAll('.assign-customer-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const customerId = this.getAttribute('data-id');
                const customerName = this.getAttribute('data-name');

                fetch(`/pos/${saleId}/header`, {
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
                        alert('Error al asignar el cliente');
                    }
                });
            });
        });

        // 3. Guardado Rápido de Nuevo Cliente desde el Modal
        const saveFastCustomerBtn = document.getElementById('saveFastCustomerBtn');
        if (saveFastCustomerBtn) {
            saveFastCustomerBtn.addEventListener('click', function () {
                const name = document.getElementById('fastCustomerName').value.trim();
                const phone = document.getElementById('fastCustomerPhone').value.trim();

                if (!name) {
                    alert('Ingresa al menos el nombre del cliente');
                    return;
                }

                fetch('/customers', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        nombre: name,
                        telefono: phone,
                        sucursal: '{{ $sale->venta_sucursal }}'
                    })
                })
                .then(res => {
                    if (res.ok) {
                        // Asignar el nuevo cliente guardado directamente
                        fetch(`/pos/${saleId}/header`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                nombre_cliente: name
                            })
                        })
                        .then(() => location.reload());
                    } else {
                        alert('Ocurrió un error al registrar el cliente');
                    }
                });
            });
        }

        // 1. Asignar Empleado Vendedor a la venta
        document.querySelectorAll('.assign-seller-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const sellerId = this.getAttribute('data-id');

                fetch(`/pos/${saleId}/header`, {
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
                    if (data.success) location.reload();
                });
            });
        });

        // 2. Buscador en vivo de Empleados
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

        // 3. Asignar Valores de Encabezado Genéricos (Tipo de Pago / Tipo de Venta)
        document.querySelectorAll('.set-header-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const field = this.getAttribute('data-field');
                const val = this.getAttribute('data-val');

                let payload = {};
                payload[field] = val;

                fetch(`/pos/${saleId}/header`, {
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

        // 4. Aplicar Descuento
        const saveDiscountBtn = document.getElementById('saveDiscountBtn');
        if (saveDiscountBtn) {
            saveDiscountBtn.addEventListener('click', function () {
                const discountVal = parseFloat(document.getElementById('discountInput').value) || 0;

                fetch(`/pos/${saleId}/header`, {
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

        // 1. LÓGICA DE AGREGAR SERVICIO
        const saveServiceBtn = document.getElementById('saveServiceBtn');
        if (saveServiceBtn) {
            saveServiceBtn.addEventListener('click', function () {
                const concept = document.getElementById('serviceConcept').value.trim();
                const amount = parseFloat(document.getElementById('serviceAmount').value) || 0;

                if (!concept || amount <= 0) {
                    alert('Por favor ingresa un concepto válido y un monto mayor a 0');
                    return;
                }

                fetch(`/pos/${saleId}/add-service`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ concept: concept, amount: amount })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) location.reload();
                });
            });
        }

        // 2. LÓGICA DE FACTURACIÓN Y CONSECUTIVO AUTOMÁTICO
        const invoiceRequiredSelect = document.getElementById('invoiceRequiredSelect');
        const invoiceFieldsContainer = document.getElementById('invoiceFieldsContainer');
        const invoiceNumberInput = document.getElementById('invoiceNumberInput');

        if (invoiceRequiredSelect) {
            invoiceRequiredSelect.addEventListener('change', function () {
                if (this.value === 'SI') {
                    invoiceFieldsContainer.classList.remove('d-none');
                    
                    // Si el campo de número de factura está vacío, consultar el consecutivo en MySQL
                    if (!invoiceNumberInput.value) {
                        fetch(`/pos/${saleId}/next-invoice`)
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    invoiceNumberInput.value = data.next_invoice;
                                }
                            });
                    }
                } else {
                    invoiceFieldsContainer.classList.add('d-none');
                }
            });
        }

        // 3. GUARDAR FACTURA
        const saveInvoiceBtn = document.getElementById('saveInvoiceBtn');
        if (saveInvoiceBtn) {
            saveInvoiceBtn.addEventListener('click', function () {
                const isRequired = invoiceRequiredSelect.value;
                const numInvoice = invoiceNumberInput.value;
                const cfdi = document.getElementById('invoiceCfdiSelect').value;
                const invoiceType = document.getElementById('invoiceTypeSelect').value;

                fetch(`/pos/${saleId}/header`, {
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


    </script>
</x-app-layout>