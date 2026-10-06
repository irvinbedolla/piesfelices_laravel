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
            <form method="GET" data-loading-text="Cargando." action="{{ route('prescriptions.index') }}" class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label extra-small fw-bold text-muted">Seleccionar Sucursal:</label>
                    <select name="branch" onchange="this.form.submit()" class="form-select rounded-3">
                        <option value="TODOS" {{ $selectedBranch == 'TODOS' ? 'selected' : '' }}>-- TODAS LAS SUCURSALES --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->name }}" {{ $selectedBranch == $b->name ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
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
                                <td class="text-end">
                                    <a href="{{ route('prescriptions.pdf', $p->id) }}" target="_blank" class="btn btn-sm btn-danger rounded-3" title="Descargar PDF">
                                        <i class="fa-solid fa-file-pdf"></i> PDF
                                    </a>
                                    <a href="{{ route('prescriptions.edit', $p->id) }}" class="btn btn-sm btn-primary rounded-3" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('prescriptions.destroy', $p->id) }}" method="POST" data-loading-text="Cargando." class="d-inline" onsubmit="return confirm('¿Seguro que deseas borrar esta receta?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Borrar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
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