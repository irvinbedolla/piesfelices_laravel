<x-app-layout>
    <div class="card border-0 shadow-sm rounded-4 p-4">
        
        {{-- ENCABEZADO Y BOTONES DE ACCIÓN --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark">
                    <i class="fa-solid fa-truck-field text-primary me-2"></i>Gestión de Proveedores
                </h4>
                <p class="text-muted small m-0">Administra el catálogo de proveedores y directorio de contactos</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('suppliers.pdf') }}" class="btn btn-outline-danger rounded-pill px-3 shadow-sm" target="_blank">
                    <i class="fa-solid fa-file-pdf me-2"></i>Exportar PDF
                </a>
                <a href="{{ route('suppliers.create') }}" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                    <i class="fa-solid fa-plus me-2"></i>Nuevo Proveedor
                </a>
            </div>
        </div>

        {{-- ALERTAS DE SESIÓN --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- CONTROLES DATATABLE: Registros por página y Buscador --}}
        <form method="GET" action="{{ route('suppliers.index') }}" id="dataTableForm">
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
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por empresa, contacto, teléfono, ciudad..." onchange="document.getElementById('dataTableForm').submit()">
                        @if(request('search'))
                            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary rounded-pill ms-2">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        {{-- TABLA CON ENCABEZADOS DE ORDENAMIENTO --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <a href="{{ route('suppliers.index', array_merge(request()->query(), ['sort_by' => 'company_name', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Empresa <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('suppliers.index', array_merge(request()->query(), ['sort_by' => 'contact_name', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Contacto / Atiende <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>Teléfono</th>
                        <th>
                            <a href="{{ route('suppliers.index', array_merge(request()->query(), ['sort_by' => 'city', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Ciudad <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('suppliers.index', array_merge(request()->query(), ['sort_by' => 'status', 'sort_order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Estado <i class="fa-solid fa-sort text-muted ms-1"></i>
                            </a>
                        </th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $s)
                        <tr>
                            <td>
                                <span class="fw-bold text-dark d-block">{{ $s->company_name }}</span>
                                @if($s->email)<span class="text-muted small">{{ $s->email }}</span>@endif
                            </td>
                            <td>{{ $s->contact_name ?? 'N/A' }}</td>
                            <td>
                                @if($s->phone)
                                    <div class="d-flex align-items-center gap-2">
                                        <span>{{ $s->phone }}</span>
                                        <a href="{{ $s->whatsapp_url }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-2 py-1" style="font-size: 0.75rem;" title="Enviar WhatsApp">
                                            <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
                                        </a>
                                    </div>
                                @else
                                    <span class="text-muted small">Sin teléfono</span>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $s->city ?? 'No especificada' }}</span></td>
                            <td>
                                @if($s->status)
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3">Activo</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('suppliers.edit', $s) }}" class="btn btn-sm btn-outline-primary rounded-circle" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('suppliers.destroy', $s) }}" onsubmit="return confirm('¿Confirmas eliminar este proveedor?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Eliminar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No se encontraron proveedores coincidentes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PIE DATATABLE: Información de conteo y paginación --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 gap-3">
            <div class="text-muted small">
                Mostrando del <strong>{{ $suppliers->firstItem() ?? 0 }}</strong> al <strong>{{ $suppliers->lastItem() ?? 0 }}</strong> de <strong>{{ $suppliers->total() }}</strong> registros
            </div>
            <div>
                {{ $suppliers->links() }}
            </div>
        </div>

    </div>
</x-app-layout>