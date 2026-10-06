<x-app-layout>
    <div class="card border-0 shadow-sm rounded-4 p-4">
        
        <!-- ENCABEZADO Y BOTÓN DE ACCIÓN -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark">
                    <i class="fa-solid fa-user-shield text-primary me-2"></i>Gestión de Roles y Permisos
                </h4>
                <p class="text-muted small m-0">Crea plantillas de accesos estandarizadas para el personal de la clínica</p>
            </div>
            <a href="{{ route('roles.create') }}" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                <i class="fa-solid fa-plus me-2"></i>Nuevo Rol
            </a>
        </div>

        <!-- NOTIFICACIONES -->
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

        <!-- CONTROLES DATATABLE: Registros por página y Buscador -->
        <form method="GET" data-loading-text="Cargando." action="{{ route('roles.index') }}" id="dataTableForm">
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
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por rol o descripción..." onchange="document.getElementById('dataTableForm').submit()">
                        @if(request('search'))
                            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary rounded-pill ms-2">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        <!-- TABLA DE ROLES CON ENCABEZADOS ORDENABLES -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <a href="{{ route('roles.index', array_merge(request()->query(), ['sort_by' => 'name', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Rol <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('roles.index', array_merge(request()->query(), ['sort_by' => 'description', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Descripción <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th class="text-center">
                            <a href="{{ route('roles.index', array_merge(request()->query(), ['sort_by' => 'users_count', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Usuarios Asignados <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>Permisos Habilitados</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        @php $perm = $role->permission; @endphp
                        <tr>
                            <td>
                                <span class="fw-bold text-dark d-block fs-6">{{ $role->name }}</span>
                                <small class="text-muted">ID: #{{ $role->id }}</small>
                            </td>
                            <td>
                                <span class="text-secondary">{{ $role->description ?? 'Sin descripción' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background-color: #f3e5f5; color: #7b1fa2;">
                                    <i class="fa-solid fa-users me-1"></i> {{ $role->users_count }} usuarios
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1" style="max-width: 320px;">
                                    @if($perm?->can_sales) <span class="badge bg-success-subtle text-success rounded-pill">Ventas</span> @endif
                                    @if($perm?->can_inventory) <span class="badge bg-info-subtle text-info rounded-pill">Inventario</span> @endif
                                    @if($perm?->can_clients) <span class="badge bg-primary-subtle text-primary rounded-pill">Clientes</span> @endif
                                    @if($perm?->can_credit) <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Crédito</span> @endif
                                    @if($perm?->can_expenses) <span class="badge bg-danger-subtle text-danger rounded-pill">Gastos</span> @endif
                                    @if($perm?->can_cash_closing) <span class="badge bg-dark-subtle text-dark rounded-pill">Cierre</span> @endif
                                    @if($perm?->can_users) <span class="badge bg-secondary-subtle text-secondary rounded-pill">Usuarios</span> @endif
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-primary rounded-circle" title="Editar Rol">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <form method="POST" data-loading-text="Cargando." action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('¿Confirmas eliminar este rol? Solo se puede eliminar si no tiene usuarios activos.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Eliminar Rol" {{ $role->users_count > 0 ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-user-shield fs-1 text-secondary opacity-50 mb-3 d-block"></i>
                                No se encontraron roles coincidentes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PIE DATATABLE: Información de recuento y paginación -->
        <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 gap-3">
            <div class="text-muted small">
                Mostrando del <strong>{{ $roles->firstItem() ?? 0 }}</strong> al <strong>{{ $roles->lastItem() ?? 0 }}</strong> de <strong>{{ $roles->total() }}</strong> registros
            </div>
            <div>
                {{ $roles->links() }}
            </div>
        </div>

    </div>
</x-app-layout>