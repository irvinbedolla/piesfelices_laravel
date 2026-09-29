<x-app-layout>
    <div class="card border-0 shadow-sm rounded-4 p-4">
        
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

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Sucursal</th>
                        <th>Rol Asignado</th>
                        <th>Tipo</th>
                        <th>Estado</th>
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
                                    <i class="fa-solid fa-store me-1"></i> {{ $u->branch_name ?? 'MATRIZ' }}
                                </span>
                            </td>
                            <td>
                                @if($u->role)
                                    <span class="badge bg-purple-subtle text-purple rounded-pill px-3 py-1 fw-semibold" style="background-color: #f3e5f5; color: #7b1fa2;">
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
                                        <form method="POST" action="{{ route('users.destroy', $u) }}" onsubmit="return confirm('¿Confirmas desactivar a este usuario?');">
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
                            <td colspan="7" class="text-center py-4 text-muted">No hay usuarios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>