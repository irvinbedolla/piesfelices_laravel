<x-app-layout>
    <div class="card border-0 shadow-sm rounded-4 p-4">
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

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $b)
                        <tr>
                            <td class="fw-bold text-dark">{{ $b->name }}</td>
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
                                <form method="POST" action="{{ route('branches.destroy', $b) }}" onsubmit="return confirm('¿Eliminar sucursal?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No hay sucursales registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL CREAR SUCURSAL -->
    <div class="modal fade" id="createBranchModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <form method="POST" action="{{ route('branches.store') }}">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold modal-title"><i class="fa-solid fa-store text-primary me-2"></i>Nueva Sucursal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body py-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre de la Sucursal</label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="Ej. MATRIZ, SUCURSAL 1" required>
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
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>