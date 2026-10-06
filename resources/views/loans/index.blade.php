<x-app-layout>
    <div class="container-fluid py-4">
        
        {{-- BOTONERA PRINCIPAL DE ADMINISTRACIÓN Y PODÓLOGOS --}}
        <div class="d-flex flex-wrap gap-2 mb-4">
            @if(in_array($userRole, [0, 1, 3]))
                <button class="btn btn-primary rounded-3 px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#newLoanModal" style="background-color: #ce93d8; border:none; color:#000;">Generar</button>
                <a href="{{ route('loans.index', ['mode' => 'historial']) }}" class="btn btn-primary rounded-3 px-3 fw-bold" style="background-color: #ce93d8; border:none; color:#000;">Historial</a>
                <a href="{{ route('loans.index', ['mode' => 'activos']) }}" class="btn btn-primary rounded-3 px-3 fw-bold" style="background-color: #ce93d8; border:none; color:#000;">Activos</a>
            @endif
            <a href="{{ route('loans.index', ['mode' => 'mis_prestamos']) }}" class="btn btn-primary rounded-3 px-3 fw-bold" style="background-color: #ce93d8; border:none; color:#000;">Mis Préstamos</a>
        </div>

        {{-- TABLA DE PRÉSTAMOS --}}
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-hand-holding-dollar me-2 text-primary"></i>Listado de Préstamos</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>N° Gasto</th>
                            <th>Fecha</th>
                            <th>Usuario / Empleado</th>
                            <th>Descripción</th>
                            <th>Sucursal</th>
                            <th class="text-end">Costo Total</th>
                            <th class="text-end">Restante</th>
                            <th class="text-center">Historial</th>
                            <th class="text-center">Abonar</th>
                            <th class="text-center">Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $l)
                            <tr>
                                <td><span class="badge bg-light text-primary border">#{{ $l->pres_id }}</span></td>
                                <td>{{ $l->pres_fecha }}</td>
                                <td class="fw-bold">{{ $l->usuario }}</td>
                                <td>{{ $l->pres_descripcion }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $l->sucursal }}</span></td>
                                <td class="text-end fw-bold">${{ number_format($l->pres_costo, 2) }}</td>
                                <td class="text-end fw-bold text-danger">${{ number_format($l->pres_restante, 2) }}</td>
                                
                                {{-- Ver Historial de Abonos --}}
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info text-white rounded-3" data-bs-toggle="modal" data-bs-target="#historyModal{{ $l->pres_id }}">
                                        <i class="fa-solid fa-receipt"></i>
                                    </button>
                                </td>

                                {{-- Botón Abonar --}}
                                <td class="text-center">
                                    @if($l->pres_restante > 0)
                                        <button class="btn btn-sm btn-primary rounded-3" data-bs-toggle="modal" data-bs-target="#payModal{{ $l->pres_id }}">
                                            <i class="fa-solid fa-sack-dollar"></i>
                                        </button>
                                    @else
                                        <span class="badge bg-success">Liquidado</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <form action="{{ route('loans.destroy', $l->pres_id) }}" method="POST" data-loading-text="Cargando." onsubmit="return confirm('¿Borrar préstamo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm text-danger border-0"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>

                            {{-- MODAL PARA REGISTRAR ABONO --}}
                            <div class="modal fade" id="payModal{{ $l->pres_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 p-3">
                                        <div class="modal-header border-0">
                                            <h5 class="fw-bold text-primary">Abonar a Préstamo #{{ $l->pres_id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('loans.payment', $l->pres_id) }}" method="POST" data-loading-text="Cargando.">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="row g-2 mb-3 fs-6">
                                                    <div class="col-6">Total: <strong>${{ number_format($l->pres_costo, 2) }}</strong></div>
                                                    <div class="col-6 text-danger">Restante: <strong>${{ number_format($l->pres_restante, 2) }}</strong></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label extra-small fw-bold text-muted">Monto a Abonar ($)</label>
                                                    <input type="number" step="0.01" name="monto" max="{{ $l->pres_restante }}" class="form-control rounded-3" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label extra-small fw-bold text-muted">Fecha Abono</label>
                                                    <input type="date" name="fecha" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="submit" class="btn btn-primary rounded-pill px-4">GUARDAR <i class="fa-solid fa-paper-plane ms-1"></i></button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- MODAL HISTORIAL DE ABONOS --}}
                            <div class="modal fade" id="historyModal{{ $l->pres_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 p-3">
                                        <div class="modal-header border-0">
                                            <h5 class="fw-bold text-dark">Historial de Abonos - {{ $l->usuario }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <ul class="list-group list-group-flush">
                                                @forelse($l->payments as $p)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <span><i class="fa-solid fa-calendar-day me-2 text-muted"></i>{{ $p->fechaPretamo }}</span>
                                                        <span class="fw-bold text-success">+${{ number_format($p->monto, 2) }}</span>
                                                    </li>
                                                @empty
                                                    <li class="list-group-item text-muted text-center">No hay abonos registrados aún.</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr><td colspan="10" class="text-center py-4 text-muted">No se encontraron préstamos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL CREAR NUEVO PRÉSTAMO --}}
    <div class="modal fade" id="newLoanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 p-3">
                <div class="modal-header border-0">
                    <h5 class="fw-bold text-primary">Registrar Préstamo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('loans.store') }}" method="POST" data-loading-text="Cargando.">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Descripción</label>
                            <input type="text" name="pres_descripcion" class="form-control rounded-3" placeholder="Ej. Préstamo Personal / Certificación" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Costo / Monto ($)</label>
                            <input type="number" step="0.01" name="pres_costo" class="form-control rounded-3" placeholder="0.00" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Fecha</label>
                            <input type="date" name="pres_fecha" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Usuario / Empleado</label>
                            <select name="user_id" class="form-select rounded-3" required>
                                <option value="">-- Seleccionar Usuario --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name ?? $u->username }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Sucursal</label>
                            <select name="sucursal" class="form-select rounded-3">
                                @foreach($branches as $b)
                                    <option value="{{ $b->name }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-primary rounded-pill px-4" style="background-color: #e040fb; border:none;">Crear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>