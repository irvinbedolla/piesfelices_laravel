<x-app-layout>
    <div class="card border-0 shadow-sm rounded-4 p-4" style="max-width: 900px; margin: 0 auto;">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark"><i class="fa-solid fa-user-pen text-primary me-2"></i>Editar Empleado: {{ $employee->name }}</h4>
                <p class="text-muted small m-0">Actualiza los datos de contacto, salarios y sucursal</p>
            </div>
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
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

        <form method="POST" action="{{ route('employees.update', $employee) }}">
            @csrf
            @method('PUT')

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nombre Completo (*)</label>
                    <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $employee->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Teléfono</label>
                    <input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone', $employee->phone) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Puesto / Cargo</label>
                    <input type="text" name="position" class="form-control rounded-3" value="{{ old('position', $employee->position) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Salario Base ($) (*)</label>
                    <input type="number" step="0.01" name="salary" class="form-control rounded-3" value="{{ old('salary', $employee->salary) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Comisión (%)</label>
                    <input type="number" step="0.01" name="commission_rate" class="form-control rounded-3" value="{{ old('commission_rate', $employee->commission_rate) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Sucursal Asignada</label>
                    <select name="branch_id" class="form-select rounded-3">
                        <option value="">-- Seleccionar Sucursal --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $employee->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Usuario de Sistema (Opcional)</label>
                    <select name="user_id" class="form-select rounded-3">
                        <option value="">-- Sin Usuario Asociado --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ $employee->user_id == $u->id ? 'selected' : '' }}>{{ $u->username }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Fecha de Contratación</label>
                    <input type="date" name="hired_at" class="form-control rounded-3" value="{{ old('hired_at', $employee->hired_at?->format('Y-m-d')) }}">
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ $employee->status ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="status">Empleado Activo</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="{{ route('employees.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Actualizar Empleado</button>
            </div>
        </form>
    </div>
</x-app-layout>