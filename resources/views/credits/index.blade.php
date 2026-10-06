<x-app-layout>
    <div class="container-fluid py-4">
        
        <div class="card border-0 shadow-sm rounded-4 p-4">
            
            {{-- ENCABEZADO CON FILTROS Y BUSCADOR --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h4 class="fw-bold text-primary m-0">
                        <i class="fa-solid fa-credit-card me-2"></i>Control de Créditos Pendientes
                    </h4>
                    <small class="text-muted">Ventas a crédito registradas directamente en el sistema</small>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    {{-- Buscador --}}
                    <form method="GET" action="{{ route('credits.index') }}" data-loading-text="Buscando..." class="d-flex align-items-center gap-2">
                        @if($selectedBranch)
                            <input type="hidden" name="branch" value="{{ $selectedBranch }}">
                        @endif
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted ps-3">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control form-control-sm border-start-0 rounded-end-pill pe-3" placeholder="Buscar cliente, N° venta...">
                        </div>
                        @if(!empty($search))
                            <a href="{{ route('credits.index', ['branch' => $selectedBranch]) }}" class="btn btn-sm btn-light rounded-circle text-muted" title="Limpiar Búsqueda">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </form>

                    {{-- Selector de Sucursal --}}
                    @if(in_array($userRole, [1, 2]))
                        <form method="GET" action="{{ route('credits.index') }}" data-loading-text="Cargando Sucursal...">
                            @if(!empty($search))
                                <input type="hidden" name="search" value="{{ $search }}">
                            @endif
                            <select name="branch" onchange="this.form.submit()" class="form-select form-select-sm rounded-pill fw-semibold border-primary">
                                <option value="TODOS" {{ $selectedBranch == 'TODOS' ? 'selected' : '' }}>-- TODAS LAS SUCURSALES --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->name }}" {{ $selectedBranch == $b->name ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    @else
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fs-6">
                            <i class="fa-solid fa-hospital me-1"></i> Sucursal: {{ $userBranch }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- TABLA DE CRÉDITOS --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap">
                    <thead class="table-light">
                        <tr class="extra-small fw-bold text-uppercase">
                            <th>N° Venta</th>
                            <th>Hora</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Sucursal</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Abono</th>
                            <th class="text-end">Restante</th>
                            <th class="text-center">D.Venta</th>
                            <th class="text-center">D.Abonos</th>
                            <th class="text-center">Abonar</th>
                            <th class="text-center">Borrar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($credits as $c)
                            @php
                                $restanteCalculado = $c->venta_restante > 0 ? $c->venta_restante : ($c->venta_total - $c->venta_abono);
                            @endphp
                            <tr>
                                <td class="fw-bold text-primary">#{{ $c->venta_id }}</td>
                                <td>{{ $c->venta_hora }}</td>
                                <td>{{ $c->venta_fecha }}</td>
                                <td class="fw-semibold">{{ $c->nombre_cliente ?? 'Cliente General' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $c->venta_sucursal }}</span></td>
                                <td class="text-end fw-bold">${{ number_format($c->venta_total, 2) }}</td>
                                <td class="text-end text-success fw-bold">${{ number_format($c->venta_abono, 2) }}</td>
                                <td class="text-end text-danger fw-bold">${{ number_format($restanteCalculado, 2) }}</td>

                                {{-- DETALLE VENTA --}}
                                <td class="text-center">
                                    <button class="btn btn-sm btn-link text-decoration-none fw-bold p-0" data-bs-toggle="modal" data-bs-target="#saleDetailModal{{ $c->venta_id }}">
                                        DETALLE
                                    </button>
                                </td>

                                {{-- DETALLE ABONOS --}}
                                <td class="text-center">
                                    <button class="btn btn-sm btn-link text-decoration-none fw-bold p-0 text-info" data-bs-toggle="modal" data-bs-target="#paymentDetailModal{{ $c->venta_id }}">
                                        D.ABONO
                                    </button>
                                </td>

                                {{-- BOTÓN ABONAR --}}
                                <td class="text-center">
                                    <button class="btn btn-sm btn-link text-decoration-none fw-bold p-0 text-success" data-bs-toggle="modal" data-bs-target="#addPaymentModal{{ $c->venta_id }}">
                                        ABONAR
                                    </button>
                                </td>

                                {{-- BORRADO LÓGICO --}}
                                <td class="text-center">
                                    <form action="{{ route('credits.destroy', $c->venta_id) }}" method="POST" data-loading-text="Cancelando Venta..." onsubmit="return confirm('¿Está seguro de cancelar esta venta a crédito?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm p-0 border-0 text-danger">
                                            <i class="fa-solid fa-circle-xmark fs-5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            {{-- MODAL DETALLE DE VENTA --}}
                            <div class="modal fade" id="saleDetailModal{{ $c->venta_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 p-3">
                                        <div class="modal-header border-0">
                                            <h5 class="fw-bold text-primary m-0">Artículos Vendidos - Venta #{{ $c->venta_id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <ul class="list-group list-group-flush">
                                                @forelse($c->details as $d)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <div class="fw-bold">{{ $d->nombre ?? 'Producto Sin Nombre' }}</div>
                                                            <small class="text-muted">Cant: {{ $d->cantidad }} x ${{ number_format($d->precio, 2) }}</small>
                                                        </div>
                                                        <span class="fw-bold">${{ number_format($d->cantidad * $d->precio, 2) }}</span>
                                                    </li>
                                                @empty
                                                    <li class="list-group-item text-center text-muted">No hay productos registrados en el detalle.</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- MODAL HISTORIAL DE ABONOS --}}
                            <div class="modal fade" id="paymentDetailModal{{ $c->venta_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 p-3">
                                        <div class="modal-header border-0">
                                            <h5 class="fw-bold text-info m-0">Historial de Abonos - Venta #{{ $c->venta_id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <ul class="list-group list-group-flush">
                                                @forelse($c->payments as $p)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <div class="fw-bold text-success">+${{ number_format($p->abono_cantidad, 2) }}</div>
                                                            <small class="text-muted">{{ $p->abono_fecha }}</small>
                                                        </div>
                                                        <span class="badge bg-light text-dark border">{{ $p->abono_sucursal }}</span>
                                                    </li>
                                                @empty
                                                    <li class="list-group-item text-center text-muted">No hay abonos registrados para esta venta.</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- MODAL REGISTRAR ABONO --}}
                            <div class="modal fade" id="addPaymentModal{{ $c->venta_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 p-3">
                                        <div class="modal-header border-0">
                                            <h5 class="fw-bold text-success m-0">Abonar a Venta #{{ $c->venta_id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('credits.payment', $c->venta_id) }}" method="POST" data-loading-text="Guardando Abono...">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="row mb-3 bg-light p-3 rounded-3 g-2 fs-6">
                                                    <div class="col-6">Total Venta: <strong>${{ number_format($c->venta_total, 2) }}</strong></div>
                                                    <div class="col-6 text-danger">Restante: <strong>${{ number_format($restanteCalculado, 2) }}</strong></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label extra-small fw-bold text-muted">Monto a Abonar ($)</label>
                                                    <input type="number" step="0.01" name="monto" max="{{ $restanteCalculado }}" class="form-control rounded-3 fs-5 text-center fw-bold text-success" placeholder="0.00" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Guardar Abono</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-check-circle me-1 text-success"></i> No se encontraron ventas a crédito.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINACIÓN Y RESUMEN DE REGISTROS --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-3 border-top">
                <div class="small text-muted">
                    Mostrando del <strong>{{ $credits->firstItem() ?? 0 }}</strong> al <strong>{{ $credits->lastItem() ?? 0 }}</strong> de <strong>{{ $credits->total() }}</strong> créditos pendientes
                </div>
                <div>
                    {{ $credits->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>