<div class="col-md-4">
    <label class="form-label fw-semibold">Rol Asignado</label>
    <select name="role_id" class="form-select rounded-3">
        <option value="">-- Sin Rol (Solo Permisos Manuales) --</option>
        @foreach($roles as $role)
            <option value="{{ $role->id }}" {{ (isset($user) && $user->role_id == $role->id) ? 'selected' : '' }}>
                {{ $role->name }}
            </option>
        @endforeach
    </select>
</div>