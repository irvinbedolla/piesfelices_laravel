<x-app-layout>
    <div class="card border-0 shadow-sm rounded-4 p-4">
        
        {{-- ENCABEZADO Y BOTÓN VOLVER --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark">
                    <i class="fa-solid fa-list-check text-primary me-2"></i>Movimientos de Inventario
                </h4>
                <p class="text-muted small m-0">Historial inmutable de entradas, salidas, traspasos y ajustes de stock</p>
            </div>
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold shadow-sm">
                <i class="fa-solid fa-arrow-left me-2"></i>Volver al Inventario
            </a>
        </div>

        {{-- CONTROLES DE DATATABLE: REGISTROS POR PÁGINA Y BUSCADOR --}}
        <form method="GET" data-loading-text="Cargando." action="{{ route('inventory.movements') }}" id="dataTableForm">
            <input type="hidden" name="sort_by" value="{{ $sortBy }}">
            <input type="hidden" name="sort_order" value="{{ $sortOrder }}">

            <div class="row align-items-center mb-3 g-3">
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <span class="text-muted small">Mostrar</span>
                    <select name="per_page" class="form-select form-select-sm rounded-3" style="width: 80px;" onchange="document.getElementById('dataTableForm').submit()">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span class="text-muted small">registros por página</span>
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por producto, tipo o notas..." onchange="document.getElementById('dataTableForm').submit()">
                        @if(request('search'))
                            <a href="{{ route('inventory.movements') }}" class="btn btn-outline-secondary rounded-pill ms-2">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        {{-- TABLA DE MOVIMIENTOS --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha / Hora</th>
                        <th>Producto</th>
                        <th>Movimiento</th>
                        <th>Cantidad</th>
                        <th>Stock Anterior</th>
                        <th>Nuevo Stock</th>
                        <th>Usuario / Responsable</th>
                        <th>Motivo / Referencia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                        <tr>
                            <td>
                                <span class="fw-semibold text-dark d-block">{{ $m->created_at->format('d/m/Y') }}</span>
                                <small class="text-muted">{{ $m->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <strong class="text-dark d-block">{{ $m->product->name ?? 'Producto eliminado' }}</strong>
                                <small class="text-muted">{{ $m->branch->name ?? 'MATRIZ' }}</small>
                            </td>
                            <td>
                                @if($m->type === 'ENTRADA')
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3">ENTRADA</span>
                                @elseif($m->type === 'SALIDA')
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3">SALIDA</span>
                                @elseif($m->type === 'TRASPASO')
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3">TRASPASO</span>
                                @elseif($m->type === 'DEVOLUCION')
                                    <span class="badge bg-info-subtle text-info rounded-pill px-3">DEVOLUCIÓN</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3">{{ $m->type }}</span>
                                @endif
                            </td>
                            <td class="fw-bold fs-6">{{ $m->quantity }}</td>
                            <td class="text-muted">{{ $m->previous_stock }}</td>
                            <td class="fw-bold text-dark">{{ $m->new_stock }}</td>
                            <td>
                                <span class="badge bg-light text-dark border rounded-pill px-3">
                                    <i class="fa-solid fa-user me-1 text-secondary"></i> {{ $m->user->name ?? $m->user->username ?? 'Sistema' }}
                                </span>
                            </td>
                            <td>
                                <span class="d-block text-dark fw-semibold small">{{ $m->reference ?? 'Sin referencia' }}</span>
                                <span class="text-muted extra-small">{{ $m->notes }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No se encontraron movimientos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PIE DE TABLA: RECUENTO Y ENLACES DE PAGINACIÓN --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 gap-3">
            <div class="text-muted small">
                Mostrando del <strong>{{ $movements->firstItem() ?? 0 }}</strong> al <strong>{{ $movements->lastItem() ?? 0 }}</strong> de <strong>{{ $movements->total() }}</strong> movimientos
            </div>
            <div>
                {{ $movements->links() }}
            </div>
        </div>

    </div>
</x-app-layout>