<x-app-layout>
    <div class="container-fluid py-4">

        <div class="card border-0 shadow-sm rounded-4 p-4">
            
            {{-- ENCABEZADO CON FILTROS Y BUSCADOR UNIFICADOS --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h4 class="fw-bold text-primary m-0">
                        <i class="fa-solid fa-credit-card me-2"></i>Control de Créditos Pendientes
                    </h4>
                    <small class="text-muted">Ventas a crédito registradas directamente en el sistema</small>
                </div>

                {{-- FORMULARIO DE FILTROS UNIFICADO --}}
                <form method="GET" action="{{ route('credits.index') }}" data-loading-text="Filtrando créditos..." class="d-flex flex-wrap align-items-center gap-2">
                    
                    {{-- Caja de Búsqueda --}}
                    <div class="input-group input-group-sm" style="min-width: 220px;">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted ps-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control form-control-sm border-start-0 rounded-end-pill pe-3" placeholder="Buscar cliente, N° venta...">
                    </div>

                    {{-- Selector de Sucursal --}}
                    @if($isAdmin ?? in_array($userRole, [0, 1, 3]))
                        <select name="branch" onchange="this.form.submit()" class="form-select form-select-sm rounded-pill fw-semibold border-primary">
                            <option value="TODOS" {{ (empty($selectedBranch) || $selectedBranch == 'TODOS') ? 'selected' : '' }}>-- TODAS LAS SUCURSALES --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->name }}" {{ $selectedBranch == $b->name ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fs-6">
                            <i class="fa-solid fa-hospital me-1"></i> Sucursal: {{ $userBranch }}
                        </span>
                    @endif

                    <button type="submit" class="btn btn-sm btn-primary rounded-pill fw-bold">
                        <i class="fa-solid fa-filter me-1"></i> Filtrar
                    </button>

                    @if(!empty($search) || ($selectedBranch && $selectedBranch !== 'TODOS'))
                        <a href="{{ route('credits.index') }}" class="btn btn-sm btn-light border rounded-circle text-muted" title="Limpiar Filtros">
                            <i class="fa-solid fa-arrows-rotate"></i>
                        </a>
                    @endif
                </form>
            </div>

            {{-- TABLA DE CRÉDITOS --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap">
                    <thead class="table-light">
                        <tr class="extra-small fw-bold text-uppercase">
                            <th>N° Venta</th>
                            <th>HORA</th>
                            <th>FECHA</th>
                            <th>VENDEDOR</th>
                            <th>CLIENTE</th>
                            <th>SUCURSAL</th>
                            <th class="text-end">TOTAL</th>
                            <th class="text-end">ABONO</th>
                            <th class="text-end">RESTANTE</th>
                            <th class="text-center">D.VENTA</th>
                            <th class="text-center">D.ABONOS</th>
                            <th class="text-center">ABONAR</th>
                            <th class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($credits as $c)
                            @php
                                $restanteCalculado = $c->venta_restante > 0 ? $c->venta_restante : ($c->venta_total - $c->venta_abono);
                                
                                $vendedorNombre = $c->seller->name 
                                    ?? $c->seller->username 
                                    ?? 'S/A';
                            @endphp
                            <tr>
                                <td class="fw-bold text-dark">{{ $c->venta_id }}</td>
                                <td>{{ $c->venta_hora }}</td>
                                <td>{{ $c->venta_fecha }}</td>
                                <td class="fw-semibold text-uppercase">{{ $vendedorNombre }}</td>
                                <td class="fw-semibold text-uppercase">{{ $c->nombre_cliente ?? 'CLIENTE GENERAL' }}</td>
                                <td class="text-uppercase">{{ $c->venta_sucursal }}</td>
                                <td class="text-end fw-bold">${{ number_format($c->venta_total, 2) }}</td>
                                <td class="text-end text-success fw-bold">${{ number_format($c->venta_abono, 2) }}</td>
                                <td class="text-end text-danger fw-bold">${{ number_format($restanteCalculado, 2) }}</td>

                                {{-- DETALLE VENTA --}}
                                <td class="text-center">
                                    <button class="btn btn-sm btn-link text-decoration-underline fw-bold p-0 text-primary" data-bs-toggle="modal" data-bs-target="#saleDetailModal{{ $c->venta_id }}">
                                        DETALLE
                                    </button>
                                </td>

                                {{-- DETALLE ABONOS --}}
                                <td class="text-center">
                                    <button class="btn btn-sm btn-link text-decoration-underline fw-bold p-0 text-info" data-bs-toggle="modal" data-bs-target="#paymentDetailModal{{ $c->venta_id }}">
                                        D.ABONO
                                    </button>
                                </td>

                                {{-- BOTÓN ABONAR --}}
                                <td class="text-center">
                                    <button class="btn btn-sm btn-link text-decoration-underline fw-bold p-0 text-success" data-bs-toggle="modal" data-bs-target="#addPaymentModal{{ $c->venta_id }}">
                                        ABONAR
                                    </button>
                                </td>

                                {{-- BORRADO CON ÍCONO ROJO --}}
                                <td class="text-center">
                                    <form action="{{ route('credits.destroy', $c->venta_id) }}" method="POST" data-loading-text="Cancelando Venta..." onsubmit="return confirm('¿Está seguro de cancelar esta venta a crédito?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm p-0 border-0 text-danger" title="Cancelar Venta">
                                            <i class="fa-solid fa-circle-xmark fs-5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            {{-- 1. MODAL DETALLE DE VENTA --}}
                            <div class="modal fade" id="saleDetailModal{{ $c->venta_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content border-0 rounded-4 shadow">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold text-primary">
                                                <i class="fa-solid fa-file-invoice me-2"></i>Detalle de Venta #{{ $c->venta_id }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body py-3">
                                            <p class="mb-1"><strong>Cliente:</strong> {{ $c->nombre_cliente ?? 'CLIENTE GENERAL' }}</p>
                                            <p class="mb-1"><strong>Fecha:</strong> {{ $c->venta_fecha }} {{ $c->venta_hora }}</p>
                                            <p class="mb-1"><strong>Sucursal:</strong> {{ $c->venta_sucursal }}</p>
                                            <hr>
                                            <div class="text-end fw-bold fs-5">
                                                Total: ${{ number_format($c->venta_total, 2) }}
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 pt-0">
                                            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. MODAL DETALLE DE ABONOS --}}
                            <div class="modal fade" id="paymentDetailModal{{ $c->venta_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 rounded-4 shadow">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold text-info">
                                                <i class="fa-solid fa-list-ol me-2"></i>Historial de Abonos - Venta #{{ $c->venta_id }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body py-3">
                                            <p class="mb-1"><strong>Total Abonado:</strong> ${{ number_format($c->venta_abono, 2) }}</p>
                                            <p class="mb-1"><strong>Restante:</strong> ${{ number_format($restanteCalculado, 2) }}</p>
                                        </div>
                                        <div class="modal-footer border-0 pt-0">
                                            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. MODAL REGISTRAR ABONO --}}
                            <div class="modal fade" id="addPaymentModal{{ $c->venta_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 rounded-4 shadow">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold text-success">
                                                <i class="fa-solid fa-money-bill-wave me-2"></i>Registrar Abono - Venta #{{ $c->venta_id }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('credits.payment', $c->venta_id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body py-3">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Monto del Abono ($)</label>
                                                    <input type="number" step="0.01" max="{{ $restanteCalculado }}" name="monto" class="form-control rounded-3" placeholder="0.00" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-success rounded-pill px-4">Guardar Abono</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="13" class="text-center py-4 text-muted">
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