<x-app-layout>
    <div class="card border-0 shadow-sm rounded-4 p-4">
        
        {{-- ENCABEZADO Y BOTÓN DE ACCIÓN --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark">
                    <i class="fa-solid fa-store text-primary me-2"></i>Gestión de Sucursales
                </h4>
                <p class="text-muted small m-0">Registra y administra las sucursales del sistema</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#createBranchModal">
                <i class="fa-solid fa-plus me-2"></i>Nueva Sucursal
            </button>
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
        <form method="GET" data-loading-text="Cargando." action="{{ route('branches.index') }}" id="dataTableForm">
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
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por nombre, teléfono, dirección..." onchange="document.getElementById('dataTableForm').submit()">
                        @if(request('search'))
                            <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary rounded-pill ms-2">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        {{-- TABLA DE SUCURSALES CON ENCABEZADOS ORDENABLES --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <a href="{{ route('branches.index', array_merge(request()->query(), ['sort_by' => 'name', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Nombre <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('branches.index', array_merge(request()->query(), ['sort_by' => 'phone', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Teléfono <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('branches.index', array_merge(request()->query(), ['sort_by' => 'address', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Dirección <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('branches.index', array_merge(request()->query(), ['sort_by' => 'status', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Estado <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $b)
                        <tr>
                            <td>
                                <span class="fw-bold text-dark">{{ $b->name }}</span>
                                @if($b->is_matrix)
                                    <span class="badge bg-primary-subtle text-primary rounded-pill ms-2 small">Sucursal Principal</span>
                                @endif
                            </td>
                            <td>{{ $b->phone ?? 'Sin teléfono' }}</td>
                            <td>{{ $b->address ?? 'Sin dirección' }}</td>
                            <td>
                                @if($b->status)
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3">Activa</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Inactiva</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    {{-- BOTÓN EDITAR --}}
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary rounded-pill px-3" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editBranchModal{{ $b->id }}">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Editar
                                    </button>

                                    {{-- MODAL PARA EDITAR SUCURSAL #{{ $b->id }} --}}
                                    <div class="modal fade text-start" id="editBranchModal{{ $b->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg rounded-4">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-dark">
                                                        <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Editar Sucursal
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>

                                                <form action="{{ route('branches.update', $b->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="modal-body py-3">
                                                        {{-- NOMBRE --}}
                                                        <div class="mb-3">
                                                            <label class="form-label extra-small fw-bold text-muted text-uppercase mb-1">Nombre de Sucursal (*)</label>
                                                            <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $b->name) }}" required>
                                                        </div>

                                                        {{-- DIRECCIÓN --}}
                                                        <div class="mb-3">
                                                            <label class="form-label extra-small fw-bold text-muted text-uppercase mb-1">Dirección</label>
                                                            <input type="text" name="address" class="form-control rounded-3" value="{{ old('address', $b->address) }}">
                                                        </div>

                                                        {{-- TELÉFONO --}}
                                                        <div class="mb-3">
                                                            <label class="form-label extra-small fw-bold text-muted text-uppercase mb-1">Teléfono de Contacto</label>
                                                            <input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone', $b->phone) }}">
                                                        </div>

                                                        {{-- ES MATRIZ --}}
                                                        <div class="form-check form-switch mt-3">
                                                            <input class="form-check-input" type="checkbox" name="is_matrix" value="1" id="is_matrix_{{ $b->id }}" {{ $b->is_matrix ? 'checked' : '' }}>
                                                            <label class="form-check-label small fw-semibold text-dark" for="is_matrix_{{ $b->id }}">
                                                                Establecer como Sucursal Matriz Principal
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                                                            <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios
                                                        </button>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                    {{-- FIN MODAL EDITAR --}}


                                    {{-- Botón para Establecer como Sucursal Principal --}}
                                        @if(!$b->is_matrix)
                                            <form method="POST" data-loading-text="Cargando." action="{{ route('branches.set-matrix', $b) }}" onsubmit="return confirm('¿Deseas establecer {{ $b->name }} como la nueva Sucursal Principal?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-2 py-1 small fw-semibold" title="Establecer como Sucursal Principal">
                                                    <i class="fa-solid fa-star me-1"></i> Asignar Sucursal Principal
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-semibold small">
                                                <i class="fa-solid fa-crown me-1"></i> Sucursal Principal
                                            </span>
                                        @endif
                                    <form method="POST" data-loading-text="Cargando." action="{{ route('branches.destroy', $b) }}" onsubmit="return confirm('¿Eliminar sucursal?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Eliminar Sucursal" {{ $b->is_matrix ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No se encontraron sucursales coincidentes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PIE DATATABLE: Información de recuento y paginación --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 gap-3">
            <div class="text-muted small">
                Mostrando del <strong>{{ $branches->firstItem() ?? 0 }}</strong> al <strong>{{ $branches->lastItem() ?? 0 }}</strong> de <strong>{{ $branches->total() }}</strong> registros
            </div>
            <div>
                {{ $branches->links() }}
            </div>
        </div>

    </div>

    <!-- MODAL CREAR SUCURSAL -->
    <div class="modal fade" id="createBranchModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <form method="POST" data-loading-text="Cargando." action="{{ route('branches.store') }}">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold modal-title"><i class="fa-solid fa-store text-primary me-2"></i>Nueva Sucursal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body py-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre de la Sucursal (*)</label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="Ej. ALTOZANO, CENTRO" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" name="phone" class="form-control rounded-3" placeholder="Ej. 4431234567">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Dirección</label>
                            <textarea name="address" class="form-control rounded-3" rows="2" placeholder="Calle, Número, Colonia"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>