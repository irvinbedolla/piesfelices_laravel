<x-app-layout>
    <div class="card border-0 shadow-sm rounded-4 p-4" style="max-width: 900px; margin: 0 auto;">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark"><i class="fa-solid fa-user-plus text-primary me-2"></i>Registrar Nuevo Usuario</h4>
                <p class="text-muted small m-0">Ingresa las credenciales, sucursal y rol asignado</p>
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

        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nombre de Usuario (*)</label>
                    <input type="text" name="username" class="form-control rounded-3" value="{{ old('username') }}" required placeholder="Ej. juanperez">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Correo Electrónico</label>
                    <input type="email" name="email" class="form-control rounded-3" value="{{ old('email') }}" placeholder="juan@piesfelices.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Contraseña (*)</label>
                    <input type="password" name="password" class="form-control rounded-3" required placeholder="******">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Sucursal Asignada (*)</label>
                    <select name="branch_name" class="form-select rounded-3" required>
                        <option value="">-- Seleccionar Sucursal --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->name }}" {{ old('branch_name') == $b->name ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Rol de Acceso (*)</label>
                    <select name="role_id" class="form-select rounded-3" required>
                        <option value="">-- Seleccionar Rol --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ old('role_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Estado (*)</label>
                    <select name="status" class="form-select rounded-3" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>

                <div class="col-md-12 mt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_doctor" id="is_doctor" value="1">
                        <label class="form-check-label fw-semibold" for="is_doctor">¿Es Podólogo / Médico?</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="{{ route('users.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar Usuario</button>
            </div>
        </form>
    </div>
</x-app-layout>