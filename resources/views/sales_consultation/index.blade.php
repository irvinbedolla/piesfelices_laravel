<x-app-layout>
    <div class="container-fluid py-4">
        
        <div class="card border-0 shadow-sm rounded-4 p-4">
            
            {{-- MENSAGE DE ALERTA DE ÉXITO O ERROR --}}
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
                    @if(in_array($userRole, [1,2]))
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
                       href="{{ route('sales.consultation.index', array_merge(request()->all(), ['tab' => 'general'])) }}">
                        <i class="fa-solid fa-list me-1"></i> Ventas en General
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill fw-bold {{ $tab === 'facturadas' ? 'active bg-success' : 'text-secondary' }}" 
                       href="{{ route('sales.consultation.index', array_merge(request()->all(), ['tab' => 'facturadas'])) }}">
                        <i class="fa-solid fa-file-invoice-dollar me-1"></i> Facturadas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill fw-bold {{ $tab === 'canceladas' ? 'active bg-danger' : 'text-secondary' }}" 
                       href="{{ route('sales.consultation.index', array_merge(request()->all(), ['tab' => 'canceladas'])) }}">
                        <i class="fa-solid fa-ban me-1"></i> Canceladas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill fw-bold {{ $tab === 'abonos' ? 'active bg-info text-white' : 'text-secondary' }}" 
                       href="{{ route('sales.consultation.index', array_merge(request()->all(), ['tab' => 'abonos'])) }}">
                        <i class="fa-solid fa-hand-holding-dollar me-1"></i> Abonos en General
                    </a>
                </li>
            </ul>

            {{-- FILTROS DE BÚSQUEDA Y FECHA --}}
            <form method="GET" action="{{ route('sales.consultation.index') }}" data-loading-text="Filtrando ventas..." class="row g-2 mb-4 align-items-end">
                <input type="hidden" name="tab" value="{{ $tab }}">

                <div class="col-12 col-md-3">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted ps-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm border-start-0 rounded-end-pill" placeholder="Folio, cliente...">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Desde</label>
                    <input type="date" name="fecha1" value="{{ $fecha1 }}" class="form-control form-control-sm rounded-3">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Hasta</label>
                    <input type="date" name="fecha2" value="{{ $fecha2 }}" class="form-control form-control-sm rounded-3">
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label extra-small fw-bold text-muted mb-1">Sucursal</label>
                    @if(in_array($userRole, [1,2]))
                        <select name="branch" class="form-select form-select-sm rounded-3 border-primary">
                            <option value="TODOS" {{ $selectedBranch == 'TODOS' ? 'selected' : '' }}>-- TODAS LAS SUCURSALES --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->name }}" {{ $selectedBranch == $b->name ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light" value="{{ $userBranch }}" readonly>
                        <input type="hidden" name="branch" value="{{ $userBranch }}">
                    @endif
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill fw-bold w-100">
                        <i class="fa-solid fa-filter me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('sales.consultation.index', ['tab' => $tab]) }}" class="btn btn-sm btn-light border rounded-circle text-muted" title="Limpiar Filtros">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </a>
                </div>
            </form>

            {{-- TABLA DE RESULTADOS --}}
            <div class="table-responsive">
                @if($tab !== 'abonos')
                    <table class="table table-hover align-middle text-nowrap">
                        <thead class="table-light">
                            <tr class="extra-small fw-bold text-uppercase">
                                <th>N° Venta</th>
                                <th>Fecha / Hora</th>
                                <th>Cliente</th>
                                <th>Sucursal</th>
                                <th>Tipo Pago</th>
                                <th>Factura</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Artículos</th>
                                @if(in_array($userRole, [1,2]))
                                    <th class="text-center">Acciones</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $v)
                                <tr>
                                    <td class="fw-bold text-primary">#{{ $v->venta_id }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $v->venta_fecha }}</div>
                                        <small class="text-muted">{{ $v->venta_hora }}</small>
                                    </td>
                                    <td class="fw-semibold">{{ $v->nombre_cliente ?? 'Cliente General' }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $v->venta_sucursal }}</span></td>
                                    <td><span class="badge bg-info-subtle text-info border border-info-subtle">{{ $v->venta_tipopago }}</span></td>
                                    <td>
                                        @if(in_array(strtoupper($v->venta_factura), ['SI', '1']))
                                            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa-solid fa-check me-1"></i>SI</span>
                                        @else
                                            <span class="badge bg-light text-muted border">NO</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold fs-6">${{ number_format($v->venta_total, 2) }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-link text-decoration-none fw-bold p-0 text-primary" data-bs-toggle="modal" data-bs-target="#saleItemsModal{{ $v->venta_id }}">
                                            Ver ({{ count($v->details) }})
                                        </button>
                                    </td>
                                    @if(in_array($userRole, [1,2]))
                                        <td class="text-center">
                                            @if($tab !== 'canceladas')
                                                <form action="{{ route('sales.consultation.destroy-sale', $v->venta_id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de cancelar esta venta?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1 px-2" title="Cancelar / Borrar Venta">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border">CANCELADA</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>

                                {{-- MODAL ARTÍCULOS --}}
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
                                    <td colspan="{{ in_array($userRole, [1,2]) ? '9' : '8' }}" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-circle-info me-1"></i> No se encontraron ventas para los criterios seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    {{-- TABLA DE ABONOS EN GENERAL --}}
                    <table class="table table-hover align-middle text-nowrap">
                        <thead class="table-light">
                            <tr class="extra-small fw-bold text-uppercase">
                                <th># Abono</th>
                                <th>N° Venta Asociada</th>
                                <th>Fecha / Hora Abono</th>
                                <th>Sucursal</th>
                                <th class="text-end">Monto Abonado</th>
                                @if(in_array($userRole, [1,2]))
                                    <th class="text-center">Acciones</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $a)
                                <tr>
                                    <td class="fw-bold text-info">#{{ $a->abono_id ?? $a->id }}</td>
                                    <td class="fw-bold text-primary">Venta #{{ $a->venta_id }}</td>
                                    <td>{{ $a->abono_fecha ?? $a->created_at }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $a->abono_sucursal ?? 'MATRIZ' }}</span></td>
                                    <td class="text-end fw-bold text-success fs-6">+${{ number_format($a->abono_cantidad, 2) }}</td>
                                    @if(in_array($userRole, [1,2]))
                                        <td class="text-center">
                                            <form action="{{ route('sales.consultation.destroy-abono', $a->abono_id ?? $a->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de borrar este abono? El saldo pendiente de la venta será recalculado.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1 px-2" title="Eliminar Abono">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ in_array($userRole, [1,2]) ? '6' : '5' }}" class="text-center py-4 text-muted">
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
                <div class="row g-3 mt-3 pt-3 border-top">
                    <div class="col-6 col-md-3">
                        <div class="card border rounded-3 p-3 text-center bg-white shadow-sm">
                            <small class="fw-bold text-muted extra-small text-uppercase d-block mb-1">MEDICAMENTO CRÉDITO</small>
                            <h5 class="fw-bold text-dark m-0">${{ number_format($summary['medicamento_credito'], 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border rounded-3 p-3 text-center bg-white shadow-sm">
                            <small class="fw-bold text-muted extra-small text-uppercase d-block mb-1">CONSULTA CRÉDITO</small>
                            <h5 class="fw-bold text-dark m-0">${{ number_format($summary['consulta_credito'], 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border border-danger-subtle rounded-3 p-3 text-center bg-danger-subtle shadow-sm">
                            <small class="fw-bold text-danger extra-small text-uppercase d-block mb-1">TOTAL MEDICAMENTOS</small>
                            <h5 class="fw-bold text-danger m-0">${{ number_format($summary['total_medicamentos'], 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border border-danger-subtle rounded-3 p-3 text-center bg-danger-subtle shadow-sm">
                            <small class="fw-bold text-danger extra-small text-uppercase d-block mb-1">TOTAL CONSULTAS</small>
                            <h5 class="fw-bold text-danger m-0">${{ number_format($summary['total_consultas'], 2) }}</h5>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <div class="card border-0 text-white rounded-4 p-3 shadow" style="background: linear-gradient(135deg, #d81b60 0%, #e91e63 100%); min-width: 320px;">
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
            @else
                <div class="d-flex justify-content-end mt-3">
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