<x-app-layout>
    <div class="card border-0 shadow-sm rounded-4 p-4">
        
        {{-- ENCABEZADO Y BOTÓN DE ACCIÓN --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark">
                    <i class="fa-solid fa-users-gear text-primary me-2"></i>Gestión de Usuarios
                </h4>
                <p class="text-muted small m-0">Administra cuentas, sucursales asignadas y roles de acceso</p>
            </div>
            <a href="{{ route('users.create') }}" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                <i class="fa-solid fa-user-plus me-2"></i>Nuevo Usuario
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
        <form method="GET" data-loading-text="Cargando." action="{{ route('users.index') }}" id="dataTableForm">
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
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por usuario, correo, sucursal, rol..." onchange="document.getElementById('dataTableForm').submit()">
                        @if(request('search'))
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary rounded-pill ms-2">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        {{-- TABLA DE DATOS CON ORDENAMIENTO EN ENCABEZADOS --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <a href="{{ route('users.index', array_merge(request()->query(), ['sort_by' => 'username', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Usuario <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>Sucursal</th>
                        <th>Rol Asignado</th>
                        <th>
                            <a href="{{ route('users.index', array_merge(request()->query(), ['sort_by' => 'user_type', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Tipo <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('users.index', array_merge(request()->query(), ['sort_by' => 'status', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Estado <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>Médico</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td>
                                <span class="fw-bold text-dark d-block">{{ $u->username }}</span>
                                <small class="text-muted">{{ $u->email ?? 'Sin correo' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 fw-semibold">
                                    <i class="fa-solid fa-store me-1"></i> {{ $u->branch->name ?? $u->branch_name ?? 'MATRIZ' }}
                                </span>
                            </td>
                            <td>
                                @if($u->role)
                                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background-color: #f3e5f5; color: #7b1fa2;">
                                        <i class="fa-solid fa-user-shield me-1"></i> {{ $u->role->name }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border rounded-pill px-3">Personalizado</span>
                                @endif
                            </td>
                            <td>
                                @if($u->user_type == 0)
                                    <span class="badge bg-primary text-white rounded-pill px-3">Administrador</span>
                                @else
                                    <span class="badge bg-info-subtle text-info rounded-pill px-3">Operador</span>
                                @endif
                            </td>
                            <td>
                                @if($u->status === 'activo')
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3">Activo</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                @if($u->is_doctor)
                                    <i class="fa-solid fa-user-doctor text-primary fs-5" title="Podólogo / Médico"></i>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary rounded-circle" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    
                                    @if($u->id !== auth()->id())
                                        <form method="POST" data-loading-text="Cargando." action="{{ route('users.destroy', $u) }}" onsubmit="return confirm('¿Confirmas desactivar a este usuario?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Desactivar">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No se encontraron usuarios coincidentes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PIE DATATABLE: Información de recuento y paginación --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 gap-3">
            <div class="text-muted small">
                Mostrando del <strong>{{ $users->firstItem() ?? 0 }}</strong> al <strong>{{ $users->lastItem() ?? 0 }}</strong> de <strong>{{ $users->total() }}</strong> registros
            </div>
            <div>
                {{ $users->links() }}
            </div>
        </div>

    </div>
</x-app-layout>