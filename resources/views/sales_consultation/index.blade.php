<x-app-layout>
    <div class="container-fluid py-4">
        
        <div class="card border-0 shadow-sm rounded-4 p-4">
            
            {{-- MENSAJES DE SESIÓN --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- ENCABEZADO --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
                <div>
                    <h4 class="fw-bold text-primary m-0">
                        <i class="fa-solid fa-magnifying-glass-dollar me-2"></i>Consulta General de Ventas
                    </h4>
                    <small class="text-muted">Consola de monitoreo de operaciones, facturas, cancelaciones y abonos</small>
                </div>

                <div>
                    @if($isAdmin)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fs-6">
                            <i class="fa-solid fa-user-shield me-1"></i> Modo Administrador
                        </span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-3 py-2 fs-6">
                            <i class="fa-solid fa-hospital me-1"></i> Sucursal: {{ $userBranch }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- NAVEGACIÓN POR PESTAÑAS --}}
            <ul class="nav nav-pills nav-fill bg-light p-1 rounded-pill mb-4 border">
                <li class="nav-item">
                    <a class="nav-link rounded-pill fw-bold {{ $tab === 'general' ? 'active bg-primary' : 'text-secondary' }}" 
                       href="{{ route('sales.consultation.index', array_merge(request()->query(), ['tab' => 'general'])) }}">
                        <i class="fa-solid fa-list me-1"></i> Ventas en General
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill fw-bold {{ $tab === 'facturadas' ? 'active bg-success' : 'text-secondary' }}" 
                       href="{{ route('sales.consultation.index', array_merge(request()->query(), ['tab' => 'facturadas'])) }}">
                        <i class="fa-solid fa-file-invoice-dollar me-1"></i> Facturadas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill fw-bold {{ $tab === 'canceladas' ? 'active bg-danger' : 'text-secondary' }}" 
                       href="{{ route('sales.consultation.index', array_merge(request()->query(), ['tab' => 'canceladas'])) }}">
                        <i class="fa-solid fa-ban me-1"></i> Canceladas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill fw-bold {{ $tab === 'abonos' ? 'active bg-info text-white' : 'text-secondary' }}" 
                       href="{{ route('sales.consultation.index', array_merge(request()->query(), ['tab' => 'abonos'])) }}">
                        <i class="fa-solid fa-hand-holding-dollar me-1"></i> Abonos en General
                    </a>
                </li>
            </ul>

            {{-- FILTROS DE BÚSQUEDA, FECHA, SUCURSAL Y TRABAJADOR --}}
            <form method="GET" action="{{ route('sales.consultation.index') }}" data-loading-text="Filtrando ventas..." class="row g-2 mb-4 align-items-end">
                <input type="hidden" name="tab" value="{{ $tab }}">

                {{-- Buscar por Folio o Cliente --}}
                <div class="col-12 col-md-3 col-lg-2">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted ps-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm border-start-0 rounded-end-pill" placeholder="Folio, cliente...">
                    </div>
                </div>

                {{-- Fecha Desde --}}
                <div class="col-6 col-md-2 col-lg-2">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Desde</label>
                    <input type="date" name="fecha1" value="{{ $fecha1 }}" class="form-control form-control-sm rounded-3">
                </div>

                {{-- Fecha Hasta --}}
                <div class="col-6 col-md-2 col-lg-2">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Hasta</label>
                    <input type="date" name="fecha2" value="{{ $fecha2 }}" class="form-control form-control-sm rounded-3">
                </div>

                {{-- Seleccionar Sucursal --}}
                <div class="col-12 col-md-3 col-lg-2">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Sucursal</label>
                    @if($isAdmin)
                        <select name="branch" class="form-select form-select-sm rounded-3 border-primary">
                            <option value="TODOS" {{ (empty($selectedBranch) || $selectedBranch == 'TODOS') ? 'selected' : '' }}>-- TODAS --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->name }}" {{ $selectedBranch == $b->name ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light" value="{{ $userBranch }}" readonly>
                        <input type="hidden" name="branch" value="{{ $userBranch }}">
                    @endif
                </div>

                {{-- Seleccionar Trabajador / Empleado --}}
                <div class="col-12 col-md-3 col-lg-2">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Trabajador</label>
                    <select name="user_id" class="form-select form-select-sm rounded-3 border-info">
                        <option value="TODOS" {{ (empty($selectedUser) || $selectedUser == 'TODOS') ? 'selected' : '' }}>-- TODOS --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ $selectedUser == $u->id ? 'selected' : '' }}>
                                {{ $u->name ?? $u->username }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Botones Filtrar y Limpiar --}}
                <div class="col-12 col-md-2 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill fw-bold w-100">
                        <i class="fa-solid fa-filter me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('sales.consultation.index', ['tab' => $tab]) }}" class="btn btn-sm btn-light border rounded-circle text-muted" title="Limpiar Filtros">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </a>
                </div>
            </form>

            //Tabla de date_interval_create_from_date_string
            <div class="table-responsive">
                @if($tab !== 'abonos')
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
                                @if($isAdmin)
                                    <th class="text-center">Eliminar</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $v)
                                @php
                                    $isCred = in_array($v->venta_tipo, [2, '2', 'CREDITO']) || str_contains(strtoupper($v->venta_tipopago ?? ''), 'CREDITO');
                                @endphp
                                <tr>
                                    <td class="fw-bold text-primary">#{{ $v->venta_id }}</td>
                                    <td>{{ $v->venta_hora ?? '00:00:00' }}</td>
                                    <td>{{ $v->venta_fecha }}</td>
                                    <td class="fw-semibold text-uppercase">
                                        {{ $v->seller->name ?? $v->seller->username ?? $v->vendedor_nombre ?? ('Empleado #' . $v->venta_idempleado) }}
                                    </td>
                                    <td class="fw-semibold">{{ $v->nombre_cliente ?? 'Selecciona' }}</td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">
                                            {{ $v->venta_tipopago ?? 'Efectivo' }}
                                        </span>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $v->venta_sucursal }}</span></td>
                                    
                                    {{-- SUBTOTALES DESGLOSADOS POR FILA --}}
                                    <td class="text-end fw-semibold">${{ number_format($v->sum_medicamento ?? 0, 2) }}</td>
                                    <td class="text-end fw-semibold">${{ number_format($v->sum_consulta ?? 0, 2) }}</td>
                                    <td class="text-end fw-semibold">${{ number_format($v->sum_calzado ?? 0, 2) }}</td>
                                    <td class="text-end fw-semibold">${{ number_format($v->sum_general ?? 0, 2) }}</td>

                                    {{-- DESCUENTO --}}
                                    <td class="text-center">{{ (float)($v->venta_descuento ?? 0) }}%</td>
                                    
                                    {{-- TOTAL DE LA VENTA --}}
                                    <td class="text-end fw-bold text-dark fs-6">${{ number_format($v->venta_total, 2) }}</td>
                                    
                                    {{-- ÚLTIMO ABONO --}}
                                    <td class="text-end text-success fw-bold">${{ number_format($v->ultimo_abono ?? 0, 2) }}</td>
                                    
                                    {{-- TIPO DE VENTA (CONTADO / CRÉDITO) --}}
                                    <td class="text-center">
                                        @if($isCred)
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-bold">CRÉDITO</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">CONTADO</span>
                                        @endif
                                    </td>

                                    {{-- BOTÓN DETALLES --}}
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-link text-decoration-underline p-0 fw-bold" data-bs-toggle="modal" data-bs-target="#saleItemsModal{{ $v->venta_id }}">
                                            Detalles
                                        </button>
                                    </td>

                                    {{-- BOTÓN ELIMINAR / CANCELAR --}}
                                    @if($isAdmin)
                                        <td class="text-center">
                                            @if($tab !== 'canceladas')
                                                <form action="{{ route('sales.consultation.destroy-sale', $v->venta_id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de cancelar esta venta?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-link text-danger text-decoration-underline p-0 fw-bold" title="Eliminar Venta">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border">CANCELADA</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>

                                {{-- MODAL DETALLES DE VENTA --}}
                                <div class="modal fade" id="saleItemsModal{{ $v->venta_id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 border-0 p-3">
                                            <div class="modal-header border-0">
                                                <h5 class="fw-bold text-primary m-0">Detalle Venta #{{ $v->venta_id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <ul class="list-group list-group-flush">
                                                    @forelse($v->details as $d)
                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <div class="fw-bold">{{ $d->nombre ?? 'Producto Sin Nombre' }}</div>
                                                                <small class="text-muted">Cant: {{ $d->cantidad }} x ${{ number_format($d->precio, 2) }}</small>
                                                            </div>
                                                            <span class="fw-bold">${{ number_format($d->cantidad * $d->precio, 2) }}</span>
                                                        </li>
                                                    @empty
                                                        <li class="list-group-item text-center text-muted">Sin artículos registrados.</li>
                                                    @endforelse
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @empty
                                <tr>
                                    <td colspan="{{ $isAdmin ? '16' : '15' }}" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-circle-info me-1"></i> No se encontraron ventas para los criterios seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    {{-- TABLA DE ABONOS EN GENERAL --}}
                    <table class="table table-hover align-middle text-nowrap extra-small">
                        <thead class="table-light">
                            <tr class="fw-bold text-uppercase border-bottom">
                                <th># Abono</th>
                                <th>N° Venta Asociada</th>
                                <th>Fecha / Hora Abono</th>
                                <th>Sucursal</th>
                                <th class="text-end">Monto Abonado</th>
                                @if($isAdmin)
                                    <th class="text-center">Acciones</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $a)
                                <tr>
                                    <td class="fw-bold text-info">#{{ $a->abono_id ?? $a->id }}</td>
                                    <td class="fw-bold text-primary">Venta #{{ $a->abono_venta ?? $a->venta_id }}</td>
                                    <td>{{ $a->abono_fecha ?? $a->created_at }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $a->abono_sucursal ?? 'MATRIZ' }}</span></td>
                                    <td class="text-end fw-bold text-success fs-6">+${{ number_format($a->abono_cantidad, 2) }}</td>
                                    @if($isAdmin)
                                        <td class="text-center">
                                            <form action="{{ route('sales.consultation.destroy-abono', $a->abono_id ?? $a->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de borrar este abono?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger text-decoration-underline p-0 fw-bold" title="Eliminar Abono">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isAdmin ? '6' : '5' }}" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-circle-info me-1"></i> No hay abonos registrados en el rango de fechas seleccionado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- PAGINACIÓN --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 pt-2 border-top">
                <div class="small text-muted mb-2">
                    Mostrando del <strong>{{ $records->firstItem() ?? 0 }}</strong> al <strong>{{ $records->lastItem() ?? 0 }}</strong> de <strong>{{ $records->total() }}</strong> registros
                </div>
                <div>
                    {{ $records->links('pagination::bootstrap-5') }}
                </div>
            </div>

            {{-- BLOQUE DE TARJETAS DE RESUMEN AL PIE --}}
            @if($tab !== 'abonos')
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold text-secondary m-0">
                            <i class="fa-solid fa-chart-line me-2 text-primary"></i>Resumen Desglosado de Ventas 
                            <span class="badge bg-light text-primary border ms-1">Sucursal: {{ $selectedBranch ?: 'TODAS' }}</span>
                        </h6>
                    </div>

                    <div class="row g-3">
                        {{-- BLOQUE DE VENTAS A CONTADO --}}
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-light border-start border-4 border-success h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-success"><i class="fa-solid fa-money-bill-wave me-1"></i> Ventas a Contado</span>
                                    <span class="badge bg-success text-white rounded-pill">${{ number_format($summary['total_contado'], 2) }}</span>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6 col-sm-3">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">CALZADO</small>
                                            <span class="fw-bold text-dark fs-6">${{ number_format($summary['contado_calzado'], 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">MEDICAMENTO</small>
                                            <span class="fw-bold text-dark fs-6">${{ number_format($summary['contado_medicamento'], 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">GENERAL</small>
                                            <span class="fw-bold text-dark fs-6">${{ number_format($summary['contado_general'], 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">CONSULTA</small>
                                            <span class="fw-bold text-dark fs-6">${{ number_format($summary['contado_consulta'], 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- BLOQUE DE VENTAS A CRÉDITO --}}
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-light border-start border-4 border-warning h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-warning-emphasis"><i class="fa-solid fa-credit-card me-1"></i> Ventas a Crédito</span>
                                    <span class="badge bg-warning text-dark rounded-pill">${{ number_format($summary['total_credito'], 2) }}</span>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6 col-sm-3">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">CALZADO</small>
                                            <span class="fw-bold text-dark fs-6">${{ number_format($summary['credito_calzado'], 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">MEDICAMENTO</small>
                                            <span class="fw-bold text-dark fs-6">${{ number_format($summary['credito_medicamento'], 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">GENERAL</small>
                                            <span class="fw-bold text-dark fs-6">${{ number_format($summary['credito_general'], 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="bg-white p-2 rounded-3 text-center border">
                                            <small class="text-muted extra-small d-block fw-semibold">CONSULTA</small>
                                            <span class="fw-bold text-dark fs-6">${{ number_format($summary['credito_consulta'], 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- CARTA TOTALES Y GRAN TOTAL --}}
                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-2">
                        <div class="d-flex gap-3 text-muted extra-small fw-semibold">
                            <span>Total Calzado: <strong class="text-dark">${{ number_format($summary['total_calzado'], 2) }}</strong></span>
                            <span>|</span>
                            <span>Total Medicamento: <strong class="text-dark">${{ number_format($summary['total_medicamento'], 2) }}</strong></span>
                            <span>|</span>
                            <span>Total General: <strong class="text-dark">${{ number_format($summary['total_general'], 2) }}</strong></span>
                            <span>|</span>
                            <span>Total Consultas: <strong class="text-dark">${{ number_format($summary['total_consulta'], 2) }}</strong></span>
                        </div>

                        <div class="card border-0 text-white rounded-4 p-3 shadow ms-auto mt-2 mt-md-0" style="background: linear-gradient(135deg, #d81b60 0%, #e91e63 100%); min-width: 320px;">
                            <div class="d-flex justify-content-between align-items-center mb-1 fs-6">
                                <span>Descuentos Totales:</span>
                                <strong>-${{ number_format($summary['descuentos'], 2) }}</strong>
                            </div>
                            <hr class="my-1 border-white opacity-50">
                            <div class="d-flex justify-content-between align-items-center fs-5 fw-bold">
                                <span>GRAN TOTAL VENTAS:</span>
                                <span class="fs-4">${{ number_format($summary['gran_total'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- RESUMEN EN PESTAÑA ABONOS --}}
                <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                    <div class="card border-0 text-white rounded-4 p-3 shadow" style="background: linear-gradient(135deg, #0288d1 0%, #03a9f4 100%); min-width: 320px;">
                        <div class="d-flex justify-content-between align-items-center fs-5 fw-bold">
                            <span>TOTAL ABONOS REGISTRADOS:</span>
                            <span class="fs-4">${{ number_format($summary['gran_total'], 2) }}</span>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>