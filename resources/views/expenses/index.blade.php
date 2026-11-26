<x-app-layout>
    <div class="container-fluid py-4">
        
        {{-- MENSAGE DE ALERTA DE ÉXITO O ERROR --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif 

        {{-- PANEL DE REGISTRO RÁPIDO --}}
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
            <h5 class="fw-bold text-primary mb-3">
                <i class="fa-solid fa-plus-circle me-2"></i>Registrar Nuevo Gasto
            </h5>
            <form action="{{ route('expenses.store') }}" method="POST" data-loading-text="Guardando Gasto..." id="expenseForm" class="row g-3">
                @csrf
                <div class="col-12 col-md-3">
                    <label class="form-label extra-small fw-bold text-muted">Concepto</label>
                    <input type="text" name="concepto" class="form-control rounded-3" placeholder="Ej. GARRAFON DE AGUA" required>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label extra-small fw-bold text-muted">Cantidad ($)</label>
                    <input type="number" step="0.01" name="cantidad" class="form-control rounded-3" placeholder="0.00" required>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label extra-small fw-bold text-muted">Fecha</label>
                    <input type="date" name="fecha" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                </div>
                
                {{-- Selector de Sucursal en Registro --}}
                <div class="col-12 col-md-2">
                    <label class="form-label extra-small fw-bold text-muted">Sucursal</label>
                    @if($isAdmin)
                        <select name="sucursal" class="form-select rounded-3">
                            @foreach($branches as $b)
                                <option value="{{ $b->name }}" {{ (isset($selectedBranch) && $selectedBranch == $b->name) ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" value="{{ $userBranch }}" class="form-control rounded-3 bg-light" readonly>
                        <input type="hidden" name="sucursal" value="{{ $userBranch }}">
                    @endif
                </div>

                <div class="col-12 col-md-2">
                    <label class="form-label extra-small fw-bold text-muted">Tipo de Gasto</label>
                    <select name="tipo" class="form-select rounded-3">
                        <option value="2">CONSULTORIO</option>
                        <option value="1">PROVEEDOR</option>
                        <option value="3">SUELDOS</option>
                    </select>
                </div>
                <div class="col-12 col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary rounded-3 w-100 fw-bold" style="background-color: #e040fb; border: none;">Crear</button>
                </div>
            </form>
        </div>

        {{-- INDEX CON FILTROS INTEGRADOS --}}
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
                <h5 class="fw-bold text-dark m-0">
                    <i class="fa-solid fa-receipt me-2 text-primary"></i>Historial de Gastos
                </h5>
                
                {{-- Filtro de Búsqueda, Fechas, Tipo y Sucursal --}}
                <form method="GET" action="{{ route('expenses.index') }}" id="expensesFilterForm" data-loading-text="Filtrando Gastos..." class="d-flex flex-wrap gap-2 align-items-center">
                    
                    {{-- Selector de Registros por Página --}}
                    <div class="d-flex align-items-center gap-1 me-1">
                        <span class="text-muted small">Mostrar</span>
                        <select name="per_page" class="form-select form-select-sm rounded-3" style="width: 85px;" onchange="document.getElementById('expensesFilterForm').submit()">
                            @php $currentPerPage = request('per_page', $perPage ?? 500); @endphp
                            <option value="10" {{ $currentPerPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $currentPerPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $currentPerPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $currentPerPage == 100 ? 'selected' : '' }}>100</option>
                            <option value="500" {{ $currentPerPage == 500 ? 'selected' : '' }}>500</option>
                            <option value="1000" {{ $currentPerPage == 1000 ? 'selected' : '' }}>1000</option>
                        </select>
                    </div>

                    {{-- Campo de Búsqueda por Concepto --}}
                    <div class="input-group input-group-sm" style="max-width: 190px;">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted ps-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control form-control-sm border-start-0 rounded-end-pill" placeholder="Buscar concepto...">
                    </div>

                    <input type="date" name="fecha1" value="{{ $fecha1 }}" class="form-control form-control-sm rounded-3" style="max-width: 135px;">
                    <input type="date" name="fecha2" value="{{ $fecha2 }}" class="form-control form-control-sm rounded-3" style="max-width: 135px;">

                    {{-- Filtro por Tipo de Gasto --}}
                    <select name="tipo" class="form-select form-select-sm rounded-3" style="max-width: 155px;" onchange="document.getElementById('expensesFilterForm').submit()">
                        <option value="TODOS" {{ request('tipo', 'TODOS') == 'TODOS' ? 'selected' : '' }}>Todos los Tipos</option>
                        <option value="2" {{ request('tipo') == '2' ? 'selected' : '' }}>CONSULTORIO</option>
                        <option value="1" {{ request('tipo') == '1' ? 'selected' : '' }}>PROVEEDOR</option>
                        <option value="3" {{ request('tipo') == '3' ? 'selected' : '' }}>SUELDOS</option>
                    </select>
                    
                    @if($isAdmin)
                        <select name="sucursal" class="form-select form-select-sm rounded-3 border-primary" style="max-width: 175px;" onchange="document.getElementById('expensesFilterForm').submit()">
                            <option value="TODOS" {{ $selectedBranch == 'TODOS' ? 'selected' : '' }}>Todas las Sucursales</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->name }}" {{ $selectedBranch == $b->name ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                            <i class="fa-solid fa-hospital me-1"></i> {{ $userBranch }}
                        </span>
                        <input type="hidden" name="sucursal" value="{{ $userBranch }}">
                    @endif

                    <button type="submit" class="btn btn-sm btn-dark rounded-pill px-3">Filtrar</button>
                    <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-light border rounded-circle text-muted" title="Limpiar Filtros">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </a>
                </form>
            </div>

            {{-- TABLA DE REGISTROS CON SCROLL Y ENCABEZADO FIJO --}}
            <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                <table class="table table-hover align-middle text-nowrap">
                    <thead class="table-light sticky-top">
                        <tr class="extra-small fw-bold text-uppercase">
                            <th>CONCEPTO</th>
                            <th>FECHA</th>
                            <th class="text-end">CANTIDAD</th>
                            <th>SUCURSAL</th>
                            <th>TIPO</th>
                            <th class="text-center">ELIMINAR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $e)
                            <tr>
                                <td class="fw-semibold">{{ $e->concepto }}</td>
                                <td>{{ $e->fecha }}</td>
                                <td class="text-end fw-bold">${{ number_format($e->cantidad, 2) }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $e->sucursal }}</span></td>
                                <td>
                                    @if($e->tipo == 2) 
                                        <span class="badge bg-info-subtle text-info border">CONSULTORIO</span>
                                    @elseif($e->tipo == 1) 
                                        <span class="badge bg-warning-subtle text-warning border">PROVEEDOR</span>
                                    @else 
                                        <span class="badge bg-success-subtle text-success border">SUELDOS</span> 
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('expenses.destroy', $e->id) }}" method="POST" data-loading-text="Eliminando Gasto..." onsubmit="return confirm('¿Eliminar gasto?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm text-danger border-0"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No hay gastos registrados en esta sede/periodo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- NAVEGACIÓN Y PAGINACIÓN --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-3 border-top">
                <div class="small text-muted mb-2">
                    Mostrando del <strong>{{ $expenses->firstItem() ?? 0 }}</strong> al <strong>{{ $expenses->lastItem() ?? 0 }}</strong> de <strong>{{ $expenses->total() }}</strong> registros
                </div>
                <div>
                    {{ $expenses->links('pagination::bootstrap-5') }}
                </div>
            </div>

            {{-- RESUMEN DE TOTALES --}}
            <div class="text-center mt-4 p-3 bg-light rounded-4">
                <div class="row g-2 fs-6 fw-bold">
                    <div class="col-12 col-md-3">CONSULTORIO: <span class="text-info">${{ number_format($totalConsultorio, 2) }}</span></div>
                    <div class="col-12 col-md-3">PROVEEDOR: <span class="text-warning">${{ number_format($totalProveedor, 2) }}</span></div>
                    <div class="col-12 col-md-3">SUELDOS: <span class="text-success">${{ number_format($totalSueldos, 2) }}</span></div>
                    <div class="col-12 col-md-3 fs-5 text-primary">TOTAL: ${{ number_format($totalGeneral, 2) }}</div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>