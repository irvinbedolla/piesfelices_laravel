<x-app-layout>
    {{-- FullCalendar v6 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core/locales/es.global.min.js"></script>

    <div class="container-fluid py-3">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            
            {{-- Encabezado con Control de Permisos --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h4 class="fw-bold text-primary m-0">
                        <i class="fa-solid fa-calendar-check me-2"></i>Agenda de Citas Podológicas
                    </h4>
                    <small class="text-muted">
                        @if(in_array($userRole, [1, 3]))
                            Vista Administrador General (Todas las sucursales)
                        @elseif($userRole == 3)
                            Vista Administrador de Centro (Sucursal: {{ $selectedBranch }})
                        @else
                            Mis Citas Asignadas (Podólogo: {{ $user->name ?? $user->username }})
                        @endif
                    </small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    {{-- Solo Administrador General (Rol 1 o 3) puede cambiar de sucursal --}}
                    @if(in_array($userRole, [1, 3]))
                        <form method="GET" data-loading-text="Cargando." action="{{ route('appointments.index') }}" id="branchForm">
                            <select name="branch" onchange="this.form.submit()" class="form-select form-select-sm rounded-pill fw-semibold">
                                <option value="TODOS" {{ $selectedBranch == 'TODOS' ? 'selected' : '' }}>-- TODAS LAS SUCURSALES --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->name }}" {{ $selectedBranch == $b->name ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    @else
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                            <i class="fa-solid fa-hospital me-1"></i> Sucursal: {{ $selectedBranch }}
                        </span>
                    @endif

                    <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#appointmentModal">
                        <i class="fa-solid fa-plus me-1"></i> Nueva Cita
                    </button>
                </div>
            </div>

            {{-- Contenedor del Calendario --}}
            <div id="calendar" style="min-height: 650px;"></div>

        </div>
    </div>

    {{-- MODAL PARA CREAR CITA --}}
    <div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-clock me-2"></i>Agendar Nueva Cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createAppointmentForm">
                    @csrf
                    <div class="modal-body">
                        
                        {{-- Selección de Sucursal si es Administrador General --}}
                        @if(in_array($userRole, [0, 3]))
                            <div class="mb-3">
                                <label class="form-label extra-small fw-bold text-muted">Sucursal de la Cita</label>
                                <select name="branch_name" class="form-select rounded-3" required>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->name }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Asunto / Título de la Cita</label>
                            <input type="text" name="title" id="app_title" class="form-control rounded-3" placeholder="Ej. Consulta Podológica Onicomicosis" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label extra-small fw-bold text-muted">Fecha y Hora Inicio</label>
                                <input type="datetime-local" name="start" id="app_start" class="form-control rounded-3" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label extra-small fw-bold text-muted">Fecha y Hora Fin</label>
                                <input type="datetime-local" name="end" id="app_end" class="form-control rounded-3" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Podólogo Asignado</label>
                            <select name="podiatrist_id" id="app_podiatrist" class="form-select rounded-3" required>
                                @foreach($podiatrists as $pod)
                                    <option value="{{ $pod->id }}" {{ $pod->id ==$user->id ? 'selected' : '' }}>
                                        {{ $pod->name ?? $pod->username }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Cliente / Paciente</label>
                            <select name="customer_id" id="app_customer" class="form-select rounded-3">
                                <option value="">-- Cliente General / Opcional --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label extra-small fw-bold text-muted">Color de Etiqueta</label>
                                <input type="color" name="color" value="#8e24aa" class="form-control form-control-color w-100 rounded-3">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Notas Adicionales</label>
                            <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="Ej. Trae expediente previo / Dolor agudo"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Agendar Cita</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- SCRIPT FULLCALENDAR --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'es',
                initialView: 'timeGridWeek',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                editable: true,
                selectable: true,
                events: "{{ route('appointments.events', ['branch' => $selectedBranch]) }}",

                select: function(info) {
                    document.getElementById('app_start').value = info.startStr.slice(0, 16);
                    document.getElementById('app_end').value = info.endStr.slice(0, 16);
                    var modal = new bootstrap.Modal(document.getElementById('appointmentModal'));
                    modal.show();
                },

                eventDrop: function(info) { updateEventDates(info.event); },
                eventResize: function(info) { updateEventDates(info.event); },

                eventClick: function(info) {
                    Swal.fire({
                        title: info.event.title,
                        html: `
                            <p class="text-start mb-1"><strong>Sucursal:</strong> ${info.event.extendedProps.branch || 'MATRIZ'}</p>
                            <p class="text-start mb-1"><strong>Podólogo:</strong> ${info.event.extendedProps.podiatrist || 'N/A'}</p>
                            <p class="text-start mb-1"><strong>Cliente:</strong> ${info.event.extendedProps.customer || 'N/A'}</p>
                            <p class="text-start mb-1"><strong>Notas:</strong> ${info.event.extendedProps.notes || 'Sin notas'}</p>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Eliminar Cita',
                        confirmButtonColor: '#d33',
                        cancelButtonText: 'Cerrar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            deleteAppointment(info.event.id);
                        }
                    });
                }
            });

            calendar.render();

            document.getElementById('createAppointmentForm').addEventListener('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);

                fetch("{{ route('appointments.store') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('appointmentModal')).hide();
                        this.reset();
                        calendar.refetchEvents();
                        Swal.fire('¡Éxito!', 'La cita ha sido agendada.', 'success');
                    }
                });
            });

            function updateEventDates(event) {
                fetch(`/appointments/${event.id}/dates`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ start: event.startStr, end: event.endStr })
                });
            }

            function deleteAppointment(id) {
                fetch(`/appointments/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        calendar.refetchEvents();
                        Swal.fire('Eliminada', 'La cita fue cancelada correctamente.', 'success');
                    }
                });
            }
        });
    </script>
</x-app-layout>