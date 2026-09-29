<x-app-layout>
    <div class="card border-0 shadow-sm rounded-4 p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark">
                    <i class="fa-solid fa-user-pen text-primary me-2"></i>Editar Rol: {{ $role->name }}
                </h4>
                <p class="text-muted small m-0">Modifica el nombre, descripción o los permisos asignados a este rol</p>
            </div>
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
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

        <form method="POST" action="{{ route('roles.update', $role) }}">
            @csrf
            @method('PUT')

            @php $perm = $role->permission; @endphp

            <!-- DATOS BÁSICOS DEL ROL -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nombre del Rol (*)</label>
                    <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $role->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Descripción</label>
                    <input type="text" name="description" class="form-control rounded-3" value="{{ old('description', $role->description) }}">
                </div>
            </div>

            <hr class="my-4">

            <!-- MATRIZ DE PERMISOS DEL ROL -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold m-0"><i class="fa-solid fa-shield-halved text-primary me-2"></i>Matriz de Permisos Predeterminada</h5>
                <small class="text-muted">Los cambios se aplicarán a los usuarios que hereden este rol.</small>
            </div>

            <div class="row g-3 bg-light p-3 rounded-4 mb-4">
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_sales" value="1" id="can_sales" {{ $perm?->can_sales ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="can_sales">Vender / Punto de Venta</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_inventory" value="1" id="can_inventory" {{ $perm?->can_inventory ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="can_inventory">Inventario</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_clients" value="1" id="can_clients" {{ $perm?->can_clients ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="can_clients">Clientes / Pacientes</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_credit" value="1" id="can_credit" {{ $perm?->can_credit ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="can_credit">Módulo de Créditos</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_expenses" value="1" id="can_expenses" {{ $perm?->can_expenses ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="can_expenses">Gastos</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_cash_closing" value="1" id="can_cash_closing" {{ $perm?->can_cash_closing ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="can_cash_closing">Cierre de Caja</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_suppliers" value="1" id="can_suppliers" {{ $perm?->can_suppliers ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="can_suppliers">Proveedores</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_employees" value="1" id="can_employees" {{ $perm?->can_employees ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="can_employees">Empleados</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_users" value="1" id="can_users" {{ $perm?->can_users ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="can_users">Gestión de Usuarios</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_prescriptions" value="1" id="can_prescriptions" {{ $perm?->can_prescriptions ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="can_prescriptions">Multimedia / Recetas</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('roles.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Actualizar Rol</button>
            </div>
        </form>
    </div>
</x-app-layout>