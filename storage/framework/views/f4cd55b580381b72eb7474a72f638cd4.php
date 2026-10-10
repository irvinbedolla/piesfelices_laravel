<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core/locales/es.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php
        $isAdmin = in_array($userRole, [0, 1, 3]);$listPodiatrists = (isset($podiatrists) && count($podiatrists) > 0) ? $podiatrists : ($users ?? []);
    ?>

    <div class="container-fluid py-3">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            
            
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h4 class="fw-bold text-primary m-0">
                        <i class="fa-solid fa-calendar-check me-2"></i>Agenda de Citas Podológicas
                    </h4>
                    <small class="text-muted">
                        <?php if($isAdmin): ?>
                            Vista Administrador General (Gestión Total)
                        <?php else: ?>
                            Mis Citas Asignadas (Podólogo: <?php echo e($user?->name ?? $user?->username ?? 'Usuario'); ?>)
                        <?php endif; ?>
                    </small>
                </div>

                
                <div class="d-flex flex-wrap align-items-center gap-2">
                    
                    
                    <input class="form-control form-control-sm rounded-pill border-primary-subtle" 
                           list="customersList" 
                           id="customerSearchInput" 
                           placeholder="Buscar cliente..." 
                           style="max-width: 180px;">
                    <datalist id="customersList">
                        <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option data-id="<?php echo e($c->id); ?>" value="<?php echo e($c->nombre); ?>">
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </datalist>

                    
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold" id="btnViewCustomerHistory">
                        <i class="fa-solid fa-list-check me-1"></i> Historial de Citas
                    </button>

                    
                    <?php if($isAdmin): ?>
                        <form method="GET" action="<?php echo e(route('appointments.index')); ?>" id="branchForm" class="m-0">
                            <select name="branch" onchange="this.form.submit()" class="form-select form-select-sm rounded-pill fw-semibold">
                                <option value="TODOS" <?php echo e($selectedBranch == 'TODOS' ? 'selected' : ''); ?>>-- TODAS LAS SUCURSALES --</option>
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($b->name); ?>" <?php echo e($selectedBranch == $b->name ? 'selected' : ''); ?>><?php echo e($b->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </form>
                    <?php else: ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                            <i class="fa-solid fa-hospital me-1"></i> Sucursal: <?php echo e($selectedBranch); ?>

                        </span>
                    <?php endif; ?>

                    
                    <button class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#appointmentModal">
                        <i class="fa-solid fa-plus me-1"></i> Nueva Cita
                    </button>
                </div>
            </div>

            
            <div id="calendar" style="min-height: 650px;"></div>

        </div>
    </div>

    
    <div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-clock me-2"></i>Agendar Nueva Cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createAppointmentForm" data-no-loading="true">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        
                        <?php if($isAdmin): ?>
                            <div class="mb-3">
                                <label class="form-label extra-small fw-bold text-muted">Sucursal de la Cita</label>
                                <select name="branch_name" class="form-select rounded-3" required>
                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($b->name); ?>" <?php echo e($selectedBranch == $b->name ? 'selected' : ''); ?>><?php echo e($b->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="branch_name" value="<?php echo e($selectedBranch); ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Cliente / Paciente</label>
                            <select name="customer_id" id="app_customer" class="form-select rounded-3" required>
                                <option value="">-- Seleccionar Paciente --</option>
                                <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($c->cliente_id); ?>" data-name="<?php echo e($c->nombre); ?>"><?php echo e($c->nombre); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Asunto / Título de la Cita</label>
                            <input type="text" name="title" id="app_title" class="form-control rounded-3" placeholder="Ej. Cita Podológica" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Fecha y Hora de Cita</label>
                            <input type="datetime-local" name="start" id="app_start" class="form-control rounded-3" required>
                        </div>

                        <div class="mb-3 p-3 bg-light rounded-3 border">
                            <label class="form-label extra-small fw-bold text-primary d-block mb-1">
                                <i class="fa-solid fa-arrows-rotate me-1"></i> Repetición / Citas Recurrentes
                            </label>
                            <select name="repeat_count" class="form-select form-select-sm rounded-3">
                                <option value="1">Una sola vez (Sin repetición)</option>
                                <option value="2">Cada semana (2 semanas seguidas)</option>
                                <option value="3">Cada semana (3 semanas seguidas)</option>
                                <option value="4">Cada semana (4 ocasiones / 1 mes)</option>
                                <option value="8">Cada semana (8 ocasiones / 2 meses)</option>
                            </select>
                        </div>

                        
                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Podólogo Asignado</label>
                            <select name="podiatrist_id" id="app_podiatrist" class="form-select rounded-3" required>
                                <option value="">-- Seleccionar Podólogo --</option>
                                <?php $__currentLoopData = $listPodiatrists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($pod): ?>
                                        <option value="<?php echo e($pod->id); ?>" <?php echo e((isset($user) && $pod->id == $user->id) ? 'selected' : ''); ?>>
                                            <?php echo e($pod->name ?? $pod->username ?? $pod->email ?? 'Podólogo #'.$pod->id); ?>

                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                            <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="Ej. Trae expediente previo"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4" id="btnSubmitAppointment">Agendar Cita</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="customerHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0">
                    <h5 class="fw-bold text-primary m-0" id="modalCustomerTitle">Historial del Paciente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr class="extra-small fw-bold text-uppercase">
                                    <th>Fecha y Hora</th>
                                    <th>Asunto</th>
                                    <th>Podólogo</th>
                                    <th>Sucursal</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="customerAppointmentsTable">
                                <!-- Se llena vía JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var isAdmin = <?php echo json_encode($isAdmin, 15, 512) ?>;

            // Auto-completar título con el nombre del cliente
            var appCustomer = document.getElementById('app_customer');
            if (appCustomer) {
                appCustomer.addEventListener('change', function() {
                    var selectedOption = this.options[this.selectedIndex];
                    var customerName = selectedOption.getAttribute('data-name');
                    var titleInput = document.getElementById('app_title');
                    
                    if (customerName && titleInput) {
                        titleInput.value = 'Cita con ' + customerName;
                    }
                });
            }

            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'es',
                initialView: 'timeGridWeek',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                editable: isAdmin,
                selectable: true,
                events: "<?php echo e(route('appointments.events')); ?>?branch=<?php echo e($selectedBranch); ?>",

                select: function(info) {
                    document.getElementById('app_start').value = info.startStr.slice(0, 16);
                    var modal = new bootstrap.Modal(document.getElementById('appointmentModal'));
                    modal.show();
                },

                eventDrop: function(info) { 
                    if(isAdmin) { updateEventDates(info.event); } 
                    else { info.revert(); }
                },
                eventResize: function(info) { 
                    if(isAdmin) { updateEventDates(info.event); } 
                    else { info.revert(); }
                },

                eventClick: function(info) {
                    let modalButtons = {
                        showCancelButton: true,
                        cancelButtonText: 'Cerrar'
                    };

                    if (isAdmin) {
                        modalButtons.showConfirmButton = true;
                        modalButtons.confirmButtonText = 'Eliminar Cita';
                        modalButtons.confirmButtonColor = '#d33';
                    } else {
                        modalButtons.showConfirmButton = false;
                    }

                    Swal.fire({
                        title: info.event.title,
                        html: `
                            <p class="text-start mb-1"><strong>Sucursal:</strong> ${info.event.extendedProps.branch || 'MATRIZ'}</p>
                            <p class="text-start mb-1"><strong>Podólogo:</strong> ${info.event.extendedProps.podiatrist || 'N/A'}</p>
                            <p class="text-start mb-1"><strong>Cliente:</strong> ${info.event.extendedProps.customer || 'N/A'}</p>
                            <p class="text-start mb-1"><strong>Notas:</strong> ${info.event.extendedProps.notes || 'Sin notas'}</p>
                        `,
                        ...modalButtons
                    }).then((result) => {
                        if (result.isConfirmed && isAdmin) {
                            deleteAppointment(info.event.id);
                        }
                    });
                }
            });

            calendar.render();

            // 1. GUARDAR CITA SIN OVERLAY "Procesando solicitud..."
            var createForm = document.getElementById('createAppointmentForm');
            if (createForm) {
                createForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    let formData = new FormData(this);
                    let btnSubmit = document.getElementById('btnSubmitAppointment');
                    
                    if (btnSubmit) {
                        btnSubmit.disabled = true;
                        btnSubmit.innerText = 'Guardando...';
                    }

                    fetch("<?php echo e(route('appointments.store')); ?>", {
                        method: 'POST',
                        headers: { 
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (btnSubmit) {
                            btnSubmit.disabled = false;
                            btnSubmit.innerText = 'Agendar Cita';
                        }

                        if (data.success) {
                            var modalEl = document.getElementById('appointmentModal');
                            var modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();

                            this.reset();
                            calendar.refetchEvents();

                            Swal.fire({
                                title: '¡Éxito!',
                                text: 'Cita agendada correctamente.',
                                icon: 'success',
                                confirmButtonColor: '#8e24aa'
                            });
                        } else {
                            Swal.fire({
                                title: 'Error al Guardar',
                                text: data.error || 'Verifica los campos requeridos.',
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        if (btnSubmit) {
                            btnSubmit.disabled = false;
                            btnSubmit.innerText = 'Agendar Cita';
                        }
                        console.error('Error al guardar cita:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Ocurrió un error al procesar la solicitud.',
                            icon: 'error'
                        });
                    });
                });
            }

            // 2. CONSULTAR HISTORIAL CON RUTA DINÁMICA
            document.getElementById('btnViewCustomerHistory').addEventListener('click', function() {
                const inputVal = document.getElementById('customerSearchInput').value.trim();
                
                if (!inputVal) {
                    Swal.fire({
                        title: 'Atención',
                        text: 'Por favor ingresa o selecciona un cliente para buscar.',
                        icon: 'info',
                        confirmButtonColor: '#8e24aa'
                    });
                    return;
                }

                // Buscar si seleccionó una opción del datalist
                const option = document.querySelector(`#customersList option[value="${inputVal}"]`);
                
                // Si seleccionó del datalist usa el data-id, si solo escribió texto usa el mismo texto (inputVal)
                const customerIdentifier = (option && option.getAttribute('data-id')) 
                    ? option.getAttribute('data-id') 
                    : inputVal;

                document.getElementById('modalCustomerTitle').innerText = `Historial de Citas - ${inputVal}`;

                // Construcción limpia de la URL evitando barras vacías
                const baseUrl = "<?php echo e(url('customer-appointments-history')); ?>";
                const fetchUrl = `${baseUrl}/${encodeURIComponent(customerIdentifier)}`;

                fetch(fetchUrl, {
                    method: 'GET',
                    headers: { 
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return res.json();
                })
                .then(data => {
                    const tbody = document.getElementById('customerAppointmentsTable');
                    tbody.innerHTML = '';

                    if (!data || data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-3"><i class="fa-solid fa-info-circle me-1"></i> No hay citas registradas para este paciente.</td></tr>`;
                    } else {
                        data.forEach(app => {
                            const isPast = new Date(app.start) < new Date();
                            const badge = isPast 
                                ? '<span class="badge bg-secondary-subtle text-secondary border">Realizada</span>' 
                                : '<span class="badge bg-success-subtle text-success border">Próxima</span>';

                            tbody.innerHTML += `
                                <tr>
                                    <td>${app.start}</td>
                                    <td class="fw-bold">${app.title}</td>
                                    <td>${app.podiatrist}</td>
                                    <td>${app.branch}</td>
                                    <td>${badge}</td>
                                </tr>
                            `;
                        });
                    }

                    var historyModal = bootstrap.Modal.getInstance(document.getElementById('customerHistoryModal'));
                    if (!historyModal) {
                        historyModal = new bootstrap.Modal(document.getElementById('customerHistoryModal'));
                    }
                    historyModal.show();
                })
                .catch(err => {
                    console.error('Error al consultar historial:', err);
                    Swal.fire({
                        title: 'Error de Consulta',
                        text: 'Ocurrió un problema al obtener las citas.',
                        icon: 'error'
                    });
                });
            });

            function updateEventDates(event) {
                fetch(`/appointments/${event.id}/dates`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                    },
                    body: JSON.stringify({ start: event.startStr, end: event.endStr })
                });
            }

            function deleteAppointment(id) {
                fetch(`/appointments/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' }
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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\PiesFelices\resources\views/appointments/index.blade.php ENDPATH**/ ?>