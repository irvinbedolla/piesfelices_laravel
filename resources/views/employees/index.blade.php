<x-app-layout>
    <div class="card border-0 shadow-sm rounded-4 p-4">
        
        {{-- ENCABEZADO Y BOTÓN DE ACCIÓN --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark">
                    <i class="fa-solid fa-id-card text-primary me-2"></i>Gestión de Empleados
                </h4>
                <p class="text-muted small m-0">Administra la plantilla del personal, sueldos y comisiones</p>
            </div>
            <a href="{{ route('employees.create') }}" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                <i class="fa-solid fa-user-plus me-2"></i>Nuevo Empleado
            </a>
        </div>

        {{-- ALERTAS DE SESIÓN --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- CONTROLES DATATABLE: Registros por página y Buscador --}}
        <form method="GET" action="{{ route('employees.index') }}" id="dataTableForm">
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
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por nombre, teléfono, puesto, sucursal, usuario..." onchange="document.getElementById('dataTableForm').submit()">
                        @if(request('search'))
                            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary rounded-pill ms-2">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        {{-- TABLA DE DATOS CON ENCABEZADOS ORDENABLES --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <a href="{{ route('employees.index', array_merge(request()->query(), ['sort_by' => 'name', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Nombre <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>Teléfono</th>
                        <th>
                            <a href="{{ route('employees.index', array_merge(request()->query(), ['sort_by' => 'position', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Puesto / Cargo <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>Sucursal</th>
                        <th>
                            <a href="{{ route('employees.index', array_merge(request()->query(), ['sort_by' => 'salary', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Salario <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('employees.index', array_merge(request()->query(), ['sort_by' => 'commission_rate', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Comisión (%) <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>Usuario de Sistema</th>
                        <th>
                            <a href="{{ route('employees.index', array_merge(request()->query(), ['sort_by' => 'status', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Estado <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $e)
                        <tr>
                            <td>
                                <span class="fw-bold text-dark d-block">{{ $e->name }}</span>
                            </td>
                            <td>{{ $e->phone ?? 'Sin teléfono' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $e->position ?? 'Sin puesto' }}</span></td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">
                                    <i class="fa-solid fa-store me-1"></i> {{ $e->branch->name ?? 'MATRIZ' }}
                                </span>
                            </td>
                            <td class="fw-semibold text-success">${{ number_format($e->salary, 2) }}</td>
                            <td><span class="badge bg-info-subtle text-info rounded-pill px-3">{{ $e->commission_rate }}%</span></td>
                            <td>
                                @if($e->user)
                                    <span class="badge rounded-pill px-3 py-1" style="background-color: #f3e5f5; color: #7b1fa2;">
                                        <i class="fa-solid fa-user me-1"></i> {{ $e->user->username }}
                                    </span>
                                @else
                                    <span class="text-muted small">Sin Usuario</span>
                                @endif
                            </td>
                            <td>
                                @if($e->status)
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3">Activo</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('employees.edit', $e) }}" class="btn btn-sm btn-outline-primary rounded-circle" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('employees.destroy', $e) }}" onsubmit="return confirm('¿Confirmas eliminar a este empleado?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Desactivar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No se encontraron empleados coincidentes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PIE DATATABLE: Información de recuento y paginación --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 gap-3">
            <div class="text-muted small">
                Mostrando del <strong>{{ $employees->firstItem() ?? 0 }}</strong> al <strong>{{ $employees->lastItem() ?? 0 }}</strong> de <strong>{{ $employees->total() }}</strong> registros
            </div>
            <div>
                {{ $employees->links() }}
            </div>
        </div>

    </div>
</x-app-layout>