<x-app-layout>
    <div class="container-fluid px-4 py-4">
        
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold text-dark m-0">
                <i class="fa-solid fa-vault me-2 text-primary"></i>Cierres de Caja & Reportes
            </h4>
        </div>

        {{-- TARJETAS / PESTAÑAS DE REPORTES --}}
        <div class="row g-4">
            
            {{-- 1. CIERRE DIARIO POR SUCURSAL --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-primary-subtle text-primary rounded-4">
                            <i class="fa-solid fa-receipt fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark m-0">Cierre Diario</h5>
                            <small class="text-muted">Por sucursal o general</small>
                        </div>
                    </div>

                    <form action="{{ route('cash-closing.daily') }}" method="GET" data-loading-text="Cargando." target="_blank">
                        <div class="mb-2">
                            <label class="form-label extra-small fw-bold text-muted">Fecha Inicial</label>
                            <input type="date" name="fecha1" value="{{ date('Y-m-d') }}" class="form-control form-control-sm rounded-3" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label extra-small fw-bold text-muted">Fecha Final</label>
                            <input type="date" name="fecha2" value="{{ date('Y-m-d') }}" class="form-control form-control-sm rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Sucursal</label>
                            <select name="sucursal" class="form-select form-select-sm rounded-3">
                                <option value="TODOS">Todas las Sucursales</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->name }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill fw-semibold">
                            Generar Cierre Diario PDF
                        </button>
                    </form>
                </div>
            </div>

            {{-- 2. CIERRE COLECTIVO (MÚLTIPLES SUCURSALES) --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-success-subtle text-success rounded-4">
                            <i class="fa-solid fa-store fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark m-0">Cierre Colectivo</h5>
                            <small class="text-muted">Selección múltiple</small>
                        </div>
                    </div>

                    <form action="{{ route('cash-closing.daily') }}" method="GET" data-loading-text="Cargando." target="_blank">
                        <div class="mb-2">
                            <label class="form-label extra-small fw-bold text-muted">Rango de Fechas</label>
                            <div class="input-group input-group-sm mb-1">
                                <input type="date" name="fecha1" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                                <input type="date" name="fecha2" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                            </div>
                        </div>
                        
                        <div class="mb-3 overflow-auto p-2 border rounded-3" style="max-height: 110px;">
                            <label class="form-label extra-small fw-bold text-muted d-block mb-1">Selecciona Sucursales:</label>
                            @foreach($branches as $b)
                                <div class="form-check extra-small mb-1">
                                    <input class="form-check-input" type="checkbox" name="sucursales[]" value="{{ $b->name }}" id="b_{{ $b->id }}" checked>
                                    <label class="form-check-label" for="b_{{ $b->id }}">{{ $b->name }}</label>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-success btn-sm w-100 rounded-pill fw-semibold">
                            Generar Colectivo PDF
                        </button>
                    </form>
                </div>
            </div>

            {{-- 3. COMISIONES POR EMPLEADO --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-warning-subtle text-warning rounded-4">
                            <i class="fa-solid fa-user-gear fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark m-0">Comisiones</h5>
                            <small class="text-muted">Reporte por Empleado</small>
                        </div>
                    </div>

                    <form action="{{ route('cash-closing.commissions') }}" method="GET" data-loading-text="Cargando." target="_blank">
                        <div class="mb-2">
                            <div class="input-group input-group-sm mb-1">
                                <input type="date" name="fecha1" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                                <input type="date" name="fecha2" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <select name="sucursal" class="form-select form-select-sm rounded-3">
                                <option value="TODOS">Todas las Sucursales</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->name }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <select name="usuario" class="form-select form-select-sm rounded-3" required>
                                <option value="">-- Seleccionar Empleado --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name ?? $u->username }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-warning btn-sm w-100 rounded-pill fw-semibold text-dark">
                            Calcular Comisión
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>