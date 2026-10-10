<x-app-layout>
    <div class="container-fluid py-4">
        
        {{-- MENSAJES DE SESIÓN --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- BOTONERA PRINCIPAL CON INDICADOR VISUAL --}}
        <div class="d-flex flex-wrap gap-2 mb-4">
            @if($isAdmin)
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#newLoanModal" style="background-color: #e040fb; border:none;">
                    <i class="fa-solid fa-plus-circle me-1"></i> Generar
                </button>
                
                <a href="{{ route('loans.index', array_merge(request()->query(), ['mode' => 'activos'])) }}" 
                   class="btn rounded-pill px-4 fw-bold {{ $viewMode === 'activos' ? 'btn-primary shadow text-white' : 'btn-light text-dark border' }}">
                    <i class="fa-solid fa-clock-rotate-left me-1"></i> Activos (Pendientes)
                </a>

                <a href="{{ route('loans.index', array_merge(request()->query(), ['mode' => 'historial'])) }}" 
                   class="btn rounded-pill px-4 fw-bold {{ $viewMode === 'historial' ? 'btn-success shadow text-white' : 'btn-light text-dark border' }}">
                    <i class="fa-solid fa-circle-check me-1"></i> Historial (Liquidados)
                </a>
            @endif

            <a href="{{ route('loans.index', array_merge(request()->query(), ['mode' => 'mis_prestamos'])) }}" 
               class="btn rounded-pill px-4 fw-bold {{ $viewMode === 'mis_prestamos' ? 'btn-info shadow text-white' : 'btn-light text-dark border' }}">
                <i class="fa-solid fa-user me-1"></i> Mis Préstamos
            </a>
        </div>

        {{-- TABLA DE PRÉSTAMOS --}}
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <h5 class="fw-bold text-dark m-0">
                    <i class="fa-solid fa-hand-holding-dollar me-2 text-primary"></i>
                    @if($viewMode === 'historial')
                        Historial de Préstamos Liquidados
                    @elseif($viewMode === 'mis_prestamos')
                        Mis Préstamos Solicitados
                    @else
                        Préstamos Activos
                    @endif
                </h5>

                {{-- FILTRO DE BÚSQUEDA Y SUCURSAL --}}
                <form method="GET" action="{{ route('loans.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
                    <input type="hidden" name="mode" value="{{ $viewMode }}">

                    <div class="input-group input-group-sm" style="max-width: 200px;">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted ps-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control form-control-sm border-start-0 rounded-end-pill" placeholder="Buscar empleado...">
                    </div>

                    @if($isAdmin &&$viewMode !== 'mis_prestamos')
                        <select name="sucursal" class="form-select form-select-sm rounded-3 border-primary" style="max-width: 180px;">
                            <option value="TODOS" {{ (empty($selectedBranch) || $selectedBranch == 'TODOS') ? 'selected' : '' }}>Todas las Sucursales</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->name }}" {{ $selectedBranch ==$b->name ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    <button type="submit" class="btn btn-sm btn-dark rounded-pill px-3">Filtrar</button>
                    <a href="{{ route('loans.index', ['mode' => $viewMode]) }}" class="btn btn-sm btn-light border rounded-circle text-muted" title="Limpiar Filtros">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </a>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap">
                    <thead class="table-light">
                        <tr class="extra-small fw-bold text-uppercase">
                            <th>N° Gasto</th>
                            <th>Fecha</th>
                            <th>Usuario / Empleado</th>
                            <th>Descripción</th>
                            <th>Sucursal</th>
                            <th class="text-end">Costo Total</th>
                            <th class="text-end">Restante</th>
                            <th class="text-center">Historial</th>
                            <th class="text-center">Abonar</th>
                            @if($isAdmin)
                                <th class="text-center">Eliminar</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $l)
                            <tr>
                                <td><span class="badge bg-light text-primary border">#{{ $l->pres_id }}</span></td>
                                <td>{{ $l->pres_fecha }}</td>
                                <td class="fw-bold text-dark">
                                    {{ $l->user->name ?? $l->user->username ?? $l->usuario ?? 'Empleado #'.$l->user_id }}
                                </td>
                                <td>{{ $l->pres_descripcion }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $l->sucursal }}</span></td>
                                <td class="text-end fw-bold">${{ number_format($l->pres_costo, 2) }}</td>
                                <td class="text-end fw-bold {{ $l->pres_restante > 0 ? 'text-danger' : 'text-success' }}">
                                    ${{ number_format($l->pres_restante, 2) }}
                                </td>
                                
                                {{-- BOTÓN HISTORIAL AJAX --}}
                                <td class="text-center">
                                    <button type="button" 
                                            class="btn btn-sm btn-info text-white rounded-circle p-1 px-2 btn-fetch-history" 
                                            data-loan-id="{{ $l->pres_id }}" 
                                            data-employee="{{ $l->user->name ?? $l->user->username ?? $l->usuario }}"
                                            title="Ver Historial">
                                        <i class="fa-solid fa-receipt"></i>
                                    </button>
                                </td>

                                {{-- BOTÓN ABONAR --}}
                                <td class="text-center">
                                    @if($l->pres_restante > 0)
                                        <button type="button" 
                                                class="btn btn-sm btn-primary rounded-circle p-1 px-2" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#payModal{{ $l->pres_id }}"
                                                title="Abonar">
                                            <i class="fa-solid fa-sack-dollar"></i>
                                        </button>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fa-solid fa-check me-1"></i>Liquidado</span>
                                    @endif
                                </td>

                                @if($isAdmin)
                                    <td class="text-center">
                                        <form action="{{ route('loans.destroy', $l->pres_id) }}" method="POST" onsubmit="if(!confirm('¿Borrar préstamo?')) { event.preventDefault(); return false; }">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm text-danger border-0"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? '10' : '9' }}" class="text-center py-4 text-muted">
                                    No se encontraron préstamos en esta sección.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINACIÓN --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-3 border-top">
                <div class="small text-muted mb-2">
                    Mostrando del <strong>{{ $loans->firstItem() ?? 0 }}</strong> al <strong>{{ $loans->lastItem() ?? 0 }}</strong> de <strong>{{$loans->total() }}</strong> registros
                </div>
                <div>
                    {{ $loans->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>
    </div>

    {{-- MODALES DE ABONAR (UNO POR PRÉSTAMO) --}}
    @foreach($loans as $l)
        @if($l->pres_restante > 0)
            <div class="modal fade" id="payModal{{ $l->pres_id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 border-0 p-3">
                        <div class="modal-header border-0">
                            <h5 class="fw-bold text-primary">Abonar a Préstamo #{{ $l->pres_id }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('loans.payment', $l->pres_id) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="row g-2 mb-3 fs-6">
                                    <div class="col-6">Total: <strong>${{ number_format($l->pres_costo, 2) }}</strong></div>
                                    <div class="col-6 text-danger">Restante: <strong>${{ number_format($l->pres_restante, 2) }}</strong></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label extra-small fw-bold text-muted">Monto a Abonar (\$)</label>
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
        @endif
    @endforeach

    {{-- MODAL ÚNICO REUTILIZABLE PARA HISTORIAL AJAX --}}
    <div class="modal fade" id="ajaxHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 p-3">
                <div class="modal-header border-0">
                    <h5 class="fw-bold text-dark m-0" id="modalEmployeeName">Historial de Abonos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="paymentsLoader" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="small text-muted mt-2">Cargando abonos...</p>
                    </div>
                    <ul class="list-group list-group-flush" id="paymentsListContainer">
                        <!-- Se llena dinámicamente con JS -->
                    </ul>
                </div>
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
                <form action="{{ route('loans.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Descripción</label>
                            <input type="text" name="pres_descripcion" class="form-control rounded-3" placeholder="Ej. Préstamo Personal / Certificación" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Costo / Monto (\$)</label>
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
                                    <option value="{{ $b->name }}" {{ (isset($selectedBranch) && $selectedBranch ==$b->name) ? 'selected' : '' }}>
                                        {{ $b->name }}
                                    </option>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('paymentsListContainer');
            const loader = document.getElementById('paymentsLoader');
            const title = document.getElementById('modalEmployeeName');
            const modalElement = document.getElementById('ajaxHistoryModal');

            document.querySelectorAll('.btn-fetch-history').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    
                    const loanId = this.getAttribute('data-loan-id');
                    const employeeName = this.getAttribute('data-employee');

                    if (title) title.innerText = `Historial de Abonos - ${employeeName}`;
                    if (container) container.innerHTML = '';
                    if (loader) loader.classList.remove('d-none');

                    // Instanciar o recuperar el modal de Bootstrap
                    let historyModal = bootstrap.Modal.getInstance(modalElement);
                    if (!historyModal) {
                        historyModal = new bootstrap.Modal(modalElement);
                    }
                    historyModal.show();

                    // Petición AJAX
                    fetch(`{{ url('loans') }}/${loanId}/payments`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Error en la respuesta del servidor');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (loader) loader.classList.add('d-none');
                            if (!data || data.length === 0) {
                                container.innerHTML = `<li class="list-group-item text-muted text-center py-3"><i class="fa-solid fa-info-circle me-1"></i> No hay abonos registrados aún.</li>`;
                            } else {
                                data.forEach(p => {
                                    const fecha = p.fechaPretamo || p.fecha || p.created_at || 'S/F';
                                    const monto = parseFloat(p.monto || p.cantidad || 0).toFixed(2);
                                    container.innerHTML += `
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fa-solid fa-calendar-day me-2 text-muted"></i>${fecha}</span>
                                            <span class="fw-bold text-success">+$${monto}</span>
                                        </li>
                                    `;
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error AJAX:', error);
                            if (loader) loader.classList.add('d-none');
                            if (container) container.innerHTML = `<li class="list-group-item text-danger text-center py-3"><i class="fa-solid fa-triangle-exclamation me-1"></i> Error al cargar el historial.</li>`;
                        });
                });
            });
        });
    </script>
    
</x-app-layout>