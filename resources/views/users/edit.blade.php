<x-app-layout>
    <div class="card border-0 shadow-sm rounded-4 p-4" style="max-width: 900px; margin: 0 auto;">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark"><i class="fa-solid fa-user-pen text-primary me-2"></i>Editar Usuario: {{ $user->username }}</h4>
                <p class="text-muted small m-0">Actualiza las credenciales, sucursal o el rol asignado</p>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa-solid fa-arrow-left me-2"></i>Volver
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger rounded-3 mb-4">
                <ul class="m-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nombre de Usuario (*)</label>
                    <input type="text" name="username" class="form-control rounded-3" value="{{ old('username', $user->username) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Correo Electrónico</label>
                    <input type="email" name="email" class="form-control rounded-3" value="{{ old('email', $user->email) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nueva Contraseña (Opcional)</label>
                    <input type="password" name="password" class="form-control rounded-3" placeholder="Dejar en blanco para conservar la actual">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Sucursal Asignada (*)</label>
                    <select name="branch_name" class="form-select rounded-3" required>
                        @foreach($branches as $b)
                            <option value="{{ $b->name }}" {{ $user->branch_name === $b->name ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Rol de Acceso (*)</label>
                    <select name="role_id" class="form-select rounded-3" required>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ $user->role_id == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tipo / Nivel (*)</label>
                    <select name="user_type" class="form-select rounded-3" required>
                        <option value="1" {{ $user->user_type == 1 ? 'selected' : '' }}>Operador / Vendedor</option>
                        <option value="0" {{ $user->user_type == 0 ? 'selected' : '' }}>Administrador General</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Estado (*)</label>
                    <select name="status" class="form-select rounded-3" required>
                        <option value="activo" {{ $user->status === 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ $user->status === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>

                <div class="col-md-12 mt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_doctor" id="is_doctor" value="1" {{ $user->is_doctor ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_doctor">¿Es Podólogo / Médico?</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="{{ route('users.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Actualizar Usuario</button>
            </div>
        </form>
    </div>
</x-app-layout>