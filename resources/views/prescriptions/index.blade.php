<x-app-layout>
    <div class="container py-4">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold text-primary m-0">
                        <i class="fa-solid fa-notes-medical me-2"></i>Expedientes y Recetas Médicas
                    </h4>
                    <small class="text-muted">Selecciona una sucursal para consultar sus recetas</small>
                </div>
                <a href="{{ route('prescriptions.create') }}" class="btn btn-primary rounded-pill px-3">
                    <i class="fa-solid fa-plus me-1"></i> Nueva Receta
                </a>
            </div>

            {{-- Filtro por Sucursal --}}
            <form method="GET" action="{{ route('prescriptions.index') }}" class="mb-4">
                <label class="form-label fw-bold text-muted small text-uppercase">Seleccionar Sucursal:</label>
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-4">
                        <select name="branch_id" class="form-select rounded-pill" onchange="this.form.submit()">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>

            {{-- Tabla de Registros --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Cliente / Paciente</th>
                            <th>Diagnóstico</th>
                            <th>Receta / Tratamiento</th>
                            <th>Fecha</th>
                            <th>Próxima Cita</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prescriptions as $p)
                            <tr>
                                <td><span class="badge bg-light text-primary border">#{{ $p->id }}</span></td>
                                <td>
                                    <div class="fw-bold text-dark"><i class="fa-solid fa-user me-1 text-muted"></i> {{ $p->patient_name }}</div>
                                </td>
                                <td>{{ Str::limit($p->diagnosis, 35) }}</td>
                                <td>{{ Str::limit($p->indications, 45) }}</td>
                                <td>{{ $p->created_at->format('d/m/Y') }}</td>
                                <td>
                                    @if($p->next_appointment)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="fa-regular fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($p->next_appointment)->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1 flex-nowrap">
                                        
                                        {{-- Botón PDF --}}
                                        <a href="{{ route('prescriptions.pdf', $p->id) }}" 
                                        class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1 lh-1" 
                                        target="_blank">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                            <span class="small">PDF</span>
                                        </a>

                                        {{-- Botón Editar --}}
                                        <a href="{{ route('prescriptions.edit', $p->id) }}" 
                                        class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1 lh-1">
                                            <i class="bi bi-pencil"></i>
                                            <span class="small">Editar</span>
                                        </a>

                                        {{-- Botón Eliminar --}}
                                        <form id="delete-form-{{ $p->id }}" 
                                            action="{{ route('prescriptions.destroy', $p->id) }}" 
                                            method="POST" 
                                            class="m-0 p-0 d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1 lh-1" 
                                                    onclick="confirmDelete({{ $p->id }})">
                                                <i class="bi bi-trash"></i>
                                                <span class="small">Eliminar</span>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No se encontraron recetas en esta sucursal.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $prescriptions->appends(request()->query())->links() }}
            </div>

        </div>
    </div>
</x-app-layout>

<script>
    function confirmDelete(id) {
        if (confirm('¿Estás seguro de eliminar esta receta?')) {
            // Muestra el loader solo si el usuario hizo clic en Aceptar
            if (typeof showLoader === 'function') {
                showLoader();
            }
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>