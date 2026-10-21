<x-app-layout>
    <div class="container-fluid py-4" style="max-width: 1200px;">
        
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold text-primary m-0">
                    <i class="fa-solid fa-chart-line me-2"></i>Centro Unificado de Reportes
                </h4>
                <small class="text-muted">Generación de reportes de Facturación, Citas y Artículos en PDF y Excel</small>
            </div>
            
            <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-semibold">
                <i class="fa-solid fa-store text-primary me-1"></i>
                {{ $userRole === 1 ? 'Administración Global' : 'Sucursal: ' . $userBranch }}
            </span>
        </div>

        {{-- TARJETA PRINCIPAL CON PESTAÑAS --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            
            {{-- ENCABEZADO DE PESTAÑAS --}}
            <div class="card-header bg-white border-bottom p-3">
                <ul class="nav nav-pills card-header-pills gap-2" id="reportTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-bold px-4" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab">
                            <i class="fa-solid fa-file-invoice-dollar me-2"></i>Facturación
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-bold px-4" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments" type="button" role="tab">
                            <i class="fa-solid fa-calendar-check me-2"></i>Citas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-bold px-4" id="items-tab" data-bs-toggle="tab" data-bs-target="#items" type="button" role="tab">
                            <i class="fa-solid fa-boxes-packing me-2"></i>Ventas por Artículo
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="reportTabsContent">
                    
                    {{-- PESTAÑA 1: FACTURACIÓN --}}
                    <div class="tab-pane fade show active" id="invoices" role="tabpanel">
                        <div class="row align-items-center">
                            <div class="col-md-5 mb-3 mb-md-0 border-end pe-md-4">
                                <div class="p-3 bg-success-subtle text-success rounded-4 d-inline-block mb-3 fs-3">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                </div>
                                <h5 class="fw-bold text-dark">Reporte de Facturación</h5>
                                <p class="text-muted small">
                                    Exporta el desglose de ventas con factura solicitada (`SI`), incluyendo el folio interno, el número de factura fiscal, importes y tipo de pago.
                                </p>
                            </div>

                            <div class="col-md-7 ps-md-4">
                                <form action="{{ route('reports.invoices.excel') }}" method="GET" target="_blank">
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Fecha Inicio</label>
                                            <input type="date" name="fecha1" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Fecha Fin</label>
                                            <input type="date" name="fecha2" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label extra-small fw-bold text-muted">Sucursal</label>
                                        @if($userRole === 1)
                                            <select name="sucursal" class="form-select rounded-3">
                                                <option value="TODOS">Todas las Sucursales</option>
                                                @foreach($branches as $b)
                                                    <option value="{{ $b->name }}">{{ $b->name }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" class="form-control rounded-3 bg-light text-muted fw-bold" value="{{ $userBranch }}" readonly>
                                            <input type="hidden" name="sucursal" value="{{ $userBranch }}">
                                        @endif
                                    </div>

                                    <div class="mb-4 bg-light p-3 rounded-3">
                                        <label class="form-label extra-small fw-bold text-muted d-block mb-2">Formato de Descarga</label>
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtPdfInv" value="pdf">
                                                <label class="form-check-label fw-bold text-danger" for="fmtPdfInv">
                                                    <i class="fa-solid fa-file-pdf me-1"></i> Documento PDF
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtExcelInv" value="excel" checked>
                                                <label class="form-check-label fw-bold text-success" for="fmtExcelInv">
                                                    <i class="fa-solid fa-file-excel me-1"></i> Hoja Excel (.xlsx)
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold py-2 shadow-sm">
                                        <i class="fa-solid fa-download me-1"></i> Generar Reporte de Facturas
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- PESTAÑA 2: CITAS --}}
                    <div class="tab-pane fade" id="appointments" role="tabpanel">
                        <div class="row align-items-center">
                            <div class="col-md-5 mb-3 mb-md-0 border-end pe-md-4">
                                <div class="p-3 bg-primary-subtle text-primary rounded-4 d-inline-block mb-3 fs-3">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </div>
                                <h5 class="fw-bold text-dark">Reporte de Citas Podológicas</h5>
                                <p class="text-muted small">
                                    Genera listas detalladas de consultas agendadas, filtrando por rango de fechas, sucursal de atención y profesional asignado.
                                </p>
                            </div>

                            <div class="col-md-7 ps-md-4">
                                <form action="{{ route('reports.appointments') }}" method="GET" target="_blank">
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Fecha Inicio</label>
                                            <input type="date" name="fecha1" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Fecha Fin</label>
                                            <input type="date" name="fecha2" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Sucursal</label>
                                            @if($userRole === 1)
                                                <select name="sucursal" class="form-select rounded-3">
                                                    <option value="TODOS">Todas las Sucursales</option>
                                                    @foreach($branches as $b)
                                                        <option value="{{ $b->name }}">{{ $b->name }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="text" class="form-control rounded-3 bg-light text-muted fw-bold" value="{{ $userBranch }}" readonly>
                                                <input type="hidden" name="sucursal" value="{{ $userBranch }}">
                                            @endif
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Podólogo</label>
                                            <select name="podologo_id" class="form-select rounded-3">
                                                <option value="TODOS">Todos</option>
                                                @foreach($podologists as $p)
                                                    <option value="{{ $p->id }}">{{ $p->name ?? $p->username }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4 bg-light p-3 rounded-3">
                                        <label class="form-label extra-small fw-bold text-muted d-block mb-2">Formato de Descarga</label>
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtPdfApp" value="pdf" checked>
                                                <label class="form-check-label fw-bold text-danger" for="fmtPdfApp">
                                                    <i class="fa-solid fa-file-pdf me-1"></i> Documento PDF
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtExcelApp" value="excel">
                                                <label class="form-check-label fw-bold text-success" for="fmtExcelApp">
                                                    <i class="fa-solid fa-file-excel me-1"></i> Hoja Excel (.xlsx)
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">
                                        <i class="fa-solid fa-download me-1"></i> Generar Reporte de Citas
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- PESTAÑA 3: VENTAS POR ARTÍCULO --}}
                    <div class="tab-pane fade" id="items" role="tabpanel">
                        <div class="row align-items-center">
                            <div class="col-md-5 mb-3 mb-md-0 border-end pe-md-4">
                                <div class="p-3 bg-warning-subtle text-warning rounded-4 d-inline-block mb-3 fs-3">
                                    <i class="fa-solid fa-boxes-packing"></i>
                                </div>
                                <h5 class="fw-bold text-dark">Ventas por Artículo</h5>
                                <p class="text-muted small">
                                    Analiza el volumen de ventas filtrado por productos, medicamentos o modelos de calzado específicos, detallando cantidades y montos acumulados.
                                </p>
                            </div>

                            <div class="col-md-7 ps-md-4">
                                <form action="{{ route('reports.sales.items') }}" method="GET" target="_blank">
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Desde</label>
                                            <input type="date" name="fecha1" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Hasta</label>
                                            <input type="date" name="fecha2" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label extra-small fw-bold text-muted">Buscar Artículo / Producto</label>
                                        <input class="form-control rounded-3" list="productsList" name="articulo" placeholder="Escribe el nombre o SKU del producto...">
                                        <datalist id="productsList">
                                            @foreach($products as $prod)
                                                <option value="{{ $prod->nombre }}">{{ $prod->sku ? 'SKU: ' . $prod->sku . ' - ' : '' }}{{$prod->nombre }}</option>
                                            @endforeach
                                        </datalist>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Sucursal</label>
                                            @if($userRole === 1)
                                                <select name="sucursal" class="form-select rounded-3">
                                                    <option value="TODOS">Todas las Sucursales</option>
                                                    @foreach($branches as $b)
                                                        <option value="{{ $b->name }}">{{ $b->name }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="text" class="form-control rounded-3 bg-light text-muted fw-bold" value="{{ $userBranch }}" readonly>
                                                <input type="hidden" name="sucursal" value="{{ $userBranch }}">
                                            @endif
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label extra-small fw-bold text-muted">Atendió / Vendedor</label>
                                            <select name="usuario_id" class="form-select rounded-3">
                                                <option value="TODOS">Todos</option>
                                                @foreach($podologists as $p)
                                                    <option value="{{ $p->id }}">{{ $p->name ?? $p->username }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4 bg-light p-3 rounded-3">
                                        <label class="form-label extra-small fw-bold text-muted d-block mb-2">Formato de Descarga</label>
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtPdfSales" value="pdf" checked>
                                                <label class="form-check-label fw-bold text-danger" for="fmtPdfSales">
                                                    <i class="fa-solid fa-file-pdf me-1"></i> Documento PDF
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="formato" id="fmtExcelSales" value="excel">
                                                <label class="form-check-label fw-bold text-success" for="fmtExcelSales">
                                                    <i class="fa-solid fa-file-excel me-1"></i> Hoja Excel (.xlsx)
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-warning text-dark w-100 rounded-pill fw-bold py-2 shadow-sm">
                                        <i class="fa-solid fa-download me-1"></i> Generar Reporte de Ventas
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>