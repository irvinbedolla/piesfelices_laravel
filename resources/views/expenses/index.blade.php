<x-app-layout>
    <div class="container-fluid py-4">
        
        {{-- PANEL DE REGISTRO RÁPIDO --}}
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
            <h5 class="fw-bold text-primary mb-3">
                <i class="fa-solid fa-plus-circle me-2"></i>Registrar Nuevo Gasto
            </h5>
            <form action="{{ route('expenses.store') }}" method="POST" data-loading-text="Guardando Gasto..." id="expenseForm" class="row g-3">
                @csrf
                <div class="col-12 col-md-3">
                    <label class="form-label extra-small fw-bold text-muted">Concepto</label>
                    <input type="text" name="concepto" class="form-control rounded-3" placeholder="Ej. GARRAFON DE AGUA" required>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label extra-small fw-bold text-muted">Cantidad ($)</label>
                    <input type="number" step="0.01" name="cantida" class="form-control rounded-3" placeholder="0.00" required>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label extra-small fw-bold text-muted">Fecha</label>
                    <input type="date" name="fecha" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                </div>
                
                {{-- Selector de Sucursal: Solo editable por Admin General --}}
                <div class="col-12 col-md-2">
                    <label class="form-label extra-small fw-bold text-muted">Sucursal</label>
                    @if(in_array($userRole, [0, 3]))
                        <select name="sucursal" class="form-select rounded-3">
                            @foreach($branches as $b)
                                <option value="{{ $b->name }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" value="{{ $userBranch }}" class="form-control rounded-3 bg-light" readonly>
                        <input type="hidden" name="sucursal" value="{{ $userBranch }}">
                    @endif
                </div>

                <div class="col-12 col-md-2">
                    <label class="form-label extra-small fw-bold text-muted">Tipo de Gasto</label>
                    <select name="tipo" class="form-select rounded-3">
                        <option value="2">CONSULTORIO</option>
                        <option value="1">PROVEEDOR</option>
                        <option value="3">SUELDOS</option>
                    </select>
                </div>
                <div class="col-12 col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary rounded-3 w-100 fw-bold" style="background-color: #e040fb; border: none;">Crear</button>
                </div>
            </form>
        </div>

        {{-- INDEX CON FILTROS INTEGRADOS --}}
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <h5 class="fw-bold text-dark m-0">
                    <i class="fa-solid fa-receipt me-2 text-primary"></i>Historial de Gastos
                </h5>
                
                {{-- Filtro de Fechas y Sucursal --}}
                <form method="GET" action="{{ route('expenses.index') }}" data-loading-text="Filtrando Gastos..." class="d-flex flex-wrap gap-2 align-items-center">
                    <input type="date" name="fecha1" value="{{ $fecha1 }}" class="form-control form-control-sm rounded-3">
                    <input type="date" name="fecha2" value="{{ $fecha2 }}" class="form-control form-control-sm rounded-3">
                    
                    @if(in_array($userRole, [0, 3]))
                        <select name="sucursal" class="form-select form-select-sm rounded-3">
                            <option value="TODOS" {{ $selectedBranch == 'TODOS' ? 'selected' : '' }}>Todas las Sucursales</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->name }}" {{ $selectedBranch == $b->name ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                            <i class="fa-solid fa-hospital me-1"></i> {{ $userBranch }}
                        </span>
                    @endif

                    <button type="submit" class="btn btn-sm btn-dark rounded-pill px-3">Filtrar</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>CONCEPTO</th>
                            <th>FECHA</th>
                            <th class="text-end">CANTIDAD</th>
                            <th>SUCURSAL</th>
                            <th>TIPO</th>
                            <th class="text-center">ELIMINAR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $e)
                            <tr>
                                <td class="fw-semibold">{{ $e->concepto }}</td>
                                <td>{{ $e->fecha }}</td>
                                <td class="text-end fw-bold">${{ number_format($e->cantida, 2) }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $e->sucursal }}</span></td>
                                <td>
                                    @if($e->tipo == 2) 
                                        <span class="badge bg-info-subtle text-info border">CONSULTORIO</span>
                                    @elseif($e->tipo == 1) 
                                        <span class="badge bg-warning-subtle text-warning border">PROVEEDOR</span>
                                    @else 
                                        <span class="badge bg-success-subtle text-success border">SUELDOS</span> 
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('expenses.destroy', $e->id) }}" method="POST" data-loading-text="Eliminando Gasto..." onsubmit="return confirm('¿Eliminar gasto?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm text-danger border-0"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No hay gastos registrados en esta sede/periodo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- RESUMEN DE TOTALES --}}
            <div class="text-center mt-4 p-3 bg-light rounded-4">
                <div class="row g-2 fs-6 fw-bold">
                    <div class="col-12 col-md-3">CONSULTORIO: <span class="text-info">${{ number_format($totalConsultorio, 2) }}</span></div>
                    <div class="col-12 col-md-3">PROVEEDOR: <span class="text-warning">${{ number_format($totalProveedor, 2) }}</span></div>
                    <div class="col-12 col-md-3">SUELDOS: <span class="text-success">${{ number_format($totalSueldos, 2) }}</span></div>
                    <div class="col-12 col-md-3 fs-5 text-primary">TOTAL: ${{ number_format($totalGeneral, 2) }}</div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>