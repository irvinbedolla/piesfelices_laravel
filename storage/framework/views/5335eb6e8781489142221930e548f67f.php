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

    <div class="container-fluid py-3">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            
            
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h4 class="fw-bold text-primary m-0">
                        <i class="fa-solid fa-calendar-check me-2"></i>Agenda de Citas Podológicas
                    </h4>
                    <small class="text-muted">
                        <?php if(in_array($userRole, [1, 3])): ?>
                            Vista Administrador General (Todas las sucursales)
                        <?php elseif($userRole == 3): ?>
                            Vista Administrador de Centro (Sucursal: <?php echo e($selectedBranch); ?>)
                        <?php else: ?>
                            Mis Citas Asignadas (Podólogo: <?php echo e($user->name ?? $user->username); ?>)
                        <?php endif; ?>
                    </small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    
                    <?php if(in_array($userRole, [1, 3])): ?>
                        <form method="GET" data-loading-text="Cargando." action="<?php echo e(route('appointments.index')); ?>" id="branchForm">
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

                    <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#appointmentModal">
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
                <form id="createAppointmentForm">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        
                        
                        <?php if(in_array($userRole, [0, 3])): ?>
                            <div class="mb-3">
                                <label class="form-label extra-small fw-bold text-muted">Sucursal de la Cita</label>
                                <select name="branch_name" class="form-select rounded-3" required>
                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($b->name); ?>"><?php echo e($b->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        <?php endif; ?>

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
                                <?php $__currentLoopData = $podiatrists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($pod->id); ?>" <?php echo e($pod->id ==$user->id ? 'selected' : ''); ?>>
                                        <?php echo e($pod->name ?? $pod->username); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Cliente / Paciente</label>
                            <select name="customer_id" id="app_customer" class="form-select rounded-3">
                                <option value="">-- Cliente General / Opcional --</option>
                                <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($c->id); ?>"><?php echo e($c->nombre); ?></option>
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
                events: "<?php echo e(route('appointments.events', ['branch' => $selectedBranch])); ?>",

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

                fetch("<?php echo e(route('appointments.store')); ?>", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
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
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/appointments/index.blade.php ENDPATH**/ ?>