<x-app-layout>
    <div class="container-fluid px-4 py-4">
        
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold text-dark m-0">
                <i class="fa-solid fa-vault me-2 text-primary"></i>Cierres de Caja & Reportes
            </h4>
            
            @if(!$canAccessAll)
                <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-3 py-2">
                    <i class="fa-solid fa-hospital me-1"></i> Sucursal: {{ $userBranchName }}
                </span>
            @endif
        </div>

        {{-- TARJETAS / PESTAÑAS DE REPORTES ESTANDARIZADAS --}}
        <div class="row g-4 align-items-stretch">
            
            {{-- 1. CIERRE DIARIO POR SUCURSAL --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="p-3 bg-primary-subtle text-primary rounded-4">
                                <i class="fa-solid fa-receipt fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark m-0">Cierre Diario</h5>
                                <small class="text-muted">Por sucursal o general</small>
                            </div>
                        </div>

                        <form id="formCierreDiario" action="{{ route('cash-closing.daily') }}" method="GET" data-loading-text="Cargando." target="_blank" class="d-flex flex-column h-100">
                            <div class="mb-2">
                                <label class="form-label extra-small fw-bold text-muted mb-1">Fecha Inicial</label>
                                <input type="date" name="fecha1" value="{{ date('Y-m-d') }}" 
                                       @if(!$canAccessAll) min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" @endif 
                                       class="form-control form-control-sm rounded-3" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label extra-small fw-bold text-muted mb-1">Fecha Final</label>
                                <input type="date" name="fecha2" value="{{ date('Y-m-d') }}" 
                                       @if(!$canAccessAll) min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" @endif 
                                       class="form-control form-control-sm rounded-3" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label extra-small fw-bold text-muted mb-1">Sucursal</label>
                                <select name="sucursal" class="form-select form-select-sm rounded-3">
                                    @if($canAccessAll)
                                        <option value="TODOS">Todas las Sucursales</option>
                                    @endif
                                    @foreach($branches as $b)
                                        <option value="{{ $b->name }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="pt-2">
                        <button type="submit" form="formCierreDiario" class="btn btn-primary btn-sm w-100 rounded-pill fw-semibold">
                            Generar Cierre Diario PDF
                        </button>
                    </div>
                </div>
            </div>

            {{-- 2. CIERRE COLECTIVO (MÚLTIPLES SUCURSALES) --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="p-3 bg-success-subtle text-success rounded-4">
                                <i class="fa-solid fa-store fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark m-0">Cierre Colectivo</h5>
                                <small class="text-muted">Selección múltiple</small>
                            </div>
                        </div>

                        <form id="formCierreColectivo" action="{{ route('cash-closing.daily') }}" method="GET" data-loading-text="Cargando." target="_blank" class="d-flex flex-column h-100">
                            <div class="mb-2">
                                <label class="form-label extra-small fw-bold text-muted mb-1">Rango de Fechas</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" name="fecha1" value="{{ date('Y-m-d') }}" 
                                           @if(!$canAccessAll) min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" @endif 
                                           class="form-control rounded-start-3" required>
                                    <input type="date" name="fecha2" value="{{ date('Y-m-d') }}" 
                                           @if(!$canAccessAll) min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" @endif 
                                           class="form-control rounded-end-3" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label extra-small fw-bold text-muted d-block mb-1">Selecciona Sucursales:</label>
                                <div class="overflow-auto p-2 border rounded-3 bg-light-subtle" style="height: 115px;">
                                    @foreach($branches as $b)
                                        <div class="form-check extra-small mb-1">
                                            {{-- Se quitó el atributo 'checked' para que queden desmarcados por defecto --}}
                                            <input class="form-check-input" type="checkbox" name="sucursales[]" value="{{ $b->name }}" id="b_{{ $b->id }}">
                                            <label class="form-check-label text-dark fw-semibold" for="b_{{ $b->id }}">{{ $b->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="pt-2">
                        <button type="submit" form="formCierreColectivo" class="btn btn-success btn-sm w-100 rounded-pill fw-semibold">
                            Generar Colectivo PDF
                        </button>
                    </div>
                </div>
            </div>

            {{-- 3. COMISIONES POR EMPLEADO --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="p-3 bg-warning-subtle text-warning rounded-4">
                                <i class="fa-solid fa-user-gear fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark m-0">Comisiones</h5>
                                <small class="text-muted">Reporte por Empleado</small>
                            </div>
                        </div>

                        <form id="formComisiones" action="{{ route('cash-closing.commissions') }}" method="GET" data-loading-text="Cargando." target="_blank" class="d-flex flex-column h-100">
                            <div class="mb-2">
                                <label class="form-label extra-small fw-bold text-muted mb-1">Rango de Fechas</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" name="fecha1" value="{{ date('Y-m-d') }}" 
                                           @if(!$canAccessAll) min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" @endif 
                                           class="form-control rounded-start-3" required>
                                    <input type="date" name="fecha2" value="{{ date('Y-m-d') }}" 
                                           @if(!$canAccessAll) min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" @endif 
                                           class="form-control rounded-end-3" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label extra-small fw-bold text-muted mb-1">Sucursal</label>
                                <select name="sucursal" class="form-select form-select-sm rounded-3">
                                    @if($canAccessAll)
                                        <option value="TODOS">Todas las Sucursales</option>
                                    @endif
                                    @foreach($branches as $b)
                                        <option value="{{ $b->name }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label extra-small fw-bold text-muted mb-1">Empleado</label>
                                <select name="usuario" class="form-select form-select-sm rounded-3" required>
                                    <option value="">-- Seleccionar Empleado --</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name ?? $u->username }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="pt-2">
                        <button type="submit" form="formComisiones" class="btn btn-warning btn-sm w-100 rounded-pill fw-semibold text-dark">
                            Calcular Comisión
                        </button>
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>