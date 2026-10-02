<x-app-layout>
    <div class="card border-0 shadow-sm rounded-4 p-4" style="max-width: 800px; margin: 0 auto;">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark"><i class="fa-solid fa-plus text-primary me-2"></i>Registrar Proveedor</h4>
                <p class="text-muted small m-0">Ingresa la información comercial y datos de contacto</p>
            </div>
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa-solid fa-arrow-left me-2"></i>Volver
            </a>
        </div>

        <form method="POST" action="{{ route('suppliers.store') }}">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nombre de la Empresa (*)</label>
                    <input type="text" name="company_name" class="form-control rounded-3" value="{{ old('company_name') }}" required placeholder="Ej. Distribuidora Médica S.A.">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nombre del Trabajador / Contacto</label>
                    <input type="text" name="contact_name" class="form-control rounded-3" value="{{ old('contact_name') }}" placeholder="Ej. Carlos Gómez">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Teléfono / WhatsApp</label>
                    <input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone') }}" placeholder="Ej. 4431234567">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ciudad</label>
                    <input type="text" name="city" class="form-control rounded-3" value="{{ old('city') }}" placeholder="Ej. Morelia, Michoacán">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Correo Electrónico</label>
                    <input type="email" name="email" class="form-control rounded-3" value="{{ old('email') }}" placeholder="proveedor@ejemplo.com">
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="status" id="status" value="1" checked>
                        <label class="form-check-label fw-semibold" for="status">Proveedor Activo</label>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Dirección</label>
                    <textarea name="address" class="form-control rounded-3" rows="2" placeholder="Calle, número, colonia...">{{ old('address') }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="{{ route('suppliers.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar Proveedor</button>
            </div>
        </form>
    </div>
</x-app-layout>