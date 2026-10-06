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
    <div class="container-fluid px-2 px-md-4 py-3" style="max-width: 100%; overflow-x: hidden;">

        
        <?php if($birthdays->count() > 0): ?>
            <div class="alert alert-warning border-warning rounded-4 shadow-sm p-3 mb-4">
                <div class="d-flex align-items-center mb-2">
                    <i class="fa-solid fa-cake-candles text-warning fs-4 me-2"></i>
                    <h5 class="m-0 fw-bold text-dark">
                        ¡Clientes que cumplen años hoy! (<?php echo e($birthdays->count()); ?>)
                    </h5>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <?php $__currentLoopData = $birthdays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cumple): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $telLimpio = preg_replace('/[^0-9]/', '', $cumple->telefono);
                            $msgWhatsApp = rawurlencode("¡Hola {$cumple->nombre}! 🎉 Desde Pies Felices queremos desearle un muy feliz cumpleaños. Esperamos que pase un día extraordinario. 🎂✨");
                        ?>
                        <span class="badge bg-white text-dark border p-2 d-flex align-items-center rounded-pill shadow-sm">
                            <span class="me-2 text-primary fw-bold">
                                <i class="fa-solid fa-gift me-1 text-danger"></i> <?php echo e($cumple->nombre); ?>

                            </span>
                            <?php if(!empty($telLimpio)): ?>
                                <a href="https://api.whatsapp.com/send?phone=52<?php echo e($telLimpio); ?>&text=<?php echo e($msgWhatsApp); ?>" 
                                   target="_blank" class="btn btn-sm btn-success py-0 px-2 rounded-pill d-flex align-items-center small">
                                    <i class="fa-brands fa-whatsapp me-1"></i> Felicitaciones
                                </a>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php else: ?>
            <div class="mb-4 p-3 rounded-4 border d-flex align-items-center bg-light shadow-sm">
                <div class="bg-white p-2 rounded-circle me-3 d-flex align-items-center justify-content-center shadow-sm">
                    <i class="fa-regular fa-bell text-muted fs-5"></i>
                </div>
                <div>
                    <h6 class="m-0 fw-bold text-secondary">Búsqueda de Cumpleaños Finalizada</h6>
                    <small class="text-muted">Ningún cliente registrado en esta consulta cumple años el día de hoy.</small>
                </div>
            </div>
        <?php endif; ?>

        
        <form id="formBorradoMasivo" action="<?php echo e(route('customers.bulk-delete')); ?>" method="POST" data-loading-text="Cargando.">
            <?php echo csrf_field(); ?>

            <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
                
                
                <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-between gap-3 mb-4">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <h4 class="fw-bold m-0 text-dark">
                            <i class="fa-solid fa-users text-primary me-2"></i>Directorio de Clientes
                        </h4>

                        <?php if(in_array($user->type, [0, 3])): ?>
                            <select name="sucursal_filtro" class="form-select rounded-pill border-primary shadow-sm" style="width: auto;" onchange="window.location.href='?sucursal_filtro='+this.value;">
                                <option value="TODOS" <?php echo e($selectedBranch == 'TODOS' ? 'selected' : ''); ?>>Todas las Sucursales</option>
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($b->name); ?>" <?php echo e($selectedBranch == $b->name ? 'selected' : ''); ?>>
                                        <?php echo e($b->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex flex-wrap gap-2 justify-content-start justify-content-lg-end">
                        <button type="submit" id="btnBorrarSeleccionados" class="btn btn-danger rounded-pill px-3 fw-semibold shadow-sm d-none" onclick="return confirm('¿Estás seguro de eliminar los clientes seleccionados?');">
                            <i class="fa-solid fa-trash-can me-1"></i> Eliminar Seleccionados (<span id="countCheck">0</span>)
                        </button>

                        <button type="button" class="btn btn-success rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#createCustomerModal">
                            <i class="fa-solid fa-user-plus me-1"></i> Agregar Cliente
                        </button>

                        <a href="<?php echo e(route('customers.pdf', request()->query())); ?>" target="_blank" class="btn btn-outline-danger rounded-pill px-3 fw-semibold shadow-sm">
                            <i class="fa-solid fa-file-pdf me-1"></i> PDF
                        </a>
                    </div>
                </div>

                
                <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i><?php echo e(session('success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                
                <div class="row align-items-center mb-3 g-3">
                    <div class="col-12 col-md-6 d-flex align-items-center gap-2">
                        <span class="text-muted small">Mostrar</span>
                        <select name="per_page" class="form-select form-select-sm rounded-3" style="width: 80px;" onchange="window.location.href='?per_page='+this.value+'&sucursal_filtro=<?php echo e($selectedBranch); ?>'">
                            <option value="10" <?php echo e($perPage == 10 ? 'selected' : ''); ?>>10</option>
                            <option value="25" <?php echo e($perPage == 25 ? 'selected' : ''); ?>>25</option>
                            <option value="50" <?php echo e($perPage == 50 ? 'selected' : ''); ?>>50</option>
                            <option value="100" <?php echo e($perPage == 100 ? 'selected' : ''); ?>>100</option>
                        </select>
                        <span class="text-muted small">registros</span>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                <i class="fa-solid fa-magnifying-glass text-muted"></i>
                            </span>
                            <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por nombre, teléfono, RFC..." onchange="this.form.submit()">
                            <?php if(request('search')): ?>
                                <a href="<?php echo e(route('customers.index', ['sucursal_filtro' => $selectedBranch])); ?>" class="btn btn-outline-secondary rounded-pill ms-2">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Nombre</th>
                                <th>Contacto / RFC</th>
                                <th>Teléfono</th>
                                <th>Localidad / CP</th>
                                <th>Sucursal</th>
                                <th>Cumpleaños</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $esCumple = $c->fecha_nacimiento && $c->fecha_nacimiento->format('m-d') === date('m-d');
                                    $telLimpio = preg_replace('/[^0-9]/', '', $c->telefono);
                                    $msgWp = rawurlencode("Hola {$c->nombre}, le escribimos desde Pies Felices...");
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="clientes_ids[]" value="<?php echo e($c->cliente_id); ?>" class="form-check-input check-cliente">
                                    </td>
                                    <td>
                                        <strong class="text-dark d-block">
                                            <?php echo e($c->nombre); ?>

                                            <?php if($esCumple): ?>
                                                <i class="fa-solid fa-cake-candles text-warning ms-1" title="¡Hoy es su cumpleaños!"></i>
                                            <?php endif; ?>
                                        </strong>
                                        <small class="text-muted"><?php echo e($c->correo ?? 'Sin correo'); ?></small>
                                    </td>
                                    <td>
                                        <div class="small"><b>RFC:</b> <?php echo e($c->rfc ?? 'N/A'); ?></div>
                                        <div class="small text-muted text-truncate" style="max-width: 200px;"><?php echo e($c->direccion ?? 'Sin dirección'); ?></div>
                                    </td>
                                    <td>
                                        <?php if($c->telefono): ?>
                                            <a href="tel:+52<?php echo e($c->telefono); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 small">
                                                <i class="fa-solid fa-phone me-1"></i> <?php echo e($c->telefono); ?>

                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Sin teléfono</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold"><?php echo e($c->localidad ?? 'N/A'); ?></div>
                                        <div class="small text-muted">CP: <?php echo e($c->cp ?? 'S/N'); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?php echo e($c->sucursal); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo e($c->fecha_nacimiento ? $c->fecha_nacimiento->format('d/m/Y') : 'N/A'); ?>

                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <?php if($c->telefono): ?>
                                                <a href="https://api.whatsapp.com/send?phone=52<?php echo e($telLimpio); ?>&text=<?php echo e($msgWp); ?>" 
                                                   target="_blank" class="btn btn-sm btn-outline-success rounded-circle" title="WhatsApp">
                                                    <i class="fa-brands fa-whatsapp"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if(in_array($user->type, [0, 1, 3])): ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-circle open-edit-customer"
                                                        data-id="<?php echo e($c->cliente_id); ?>"
                                                        data-nombre="<?php echo e($c->nombre); ?>"
                                                        data-telefono="<?php echo e($c->telefono); ?>"
                                                        data-correo="<?php echo e($c->correo); ?>"
                                                        data-rfc="<?php echo e($c->rfc); ?>"
                                                        data-direccion="<?php echo e($c->direccion); ?>"
                                                        data-cp="<?php echo e($c->cp); ?>"
                                                        data-localidad="<?php echo e($c->localidad); ?>"
                                                        data-sucursal="<?php echo e($c->sucursal); ?>"
                                                        data-fecha="<?php echo e($c->fecha_nacimiento ? $c->fecha_nacimiento->format('Y-m-d') : ''); ?>"
                                                        title="Editar Cliente">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No se encontraron clientes registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                
                <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 gap-3">
                    <div class="text-muted small">
                        Mostrando del <strong><?php echo e($customers->firstItem() ?? 0); ?></strong> al <strong><?php echo e($customers->lastItem() ?? 0); ?></strong> de <strong><?php echo e($customers->total()); ?></strong> clientes
                    </div>
                    <div>
                        <?php echo e($customers->links()); ?>

                    </div>
                </div>

            </div>
        </form>
    </div>

    <!-- MODAL CREAR CLIENTE -->
    <div class="modal fade" id="createCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-user-plus text-primary me-2"></i>Registrar Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" data-loading-text="Cargando." action="<?php echo e(route('customers.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body py-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombre Completo (*)</label>
                                <input type="text" name="nombre" class="form-control rounded-3" required placeholder="Ej. Juan Pérez">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Teléfono</label>
                                <input type="text" name="telefono" class="form-control rounded-3" placeholder="4431234567">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Correo Electrónico</label>
                                <input type="email" name="correo" class="form-control rounded-3" placeholder="correo@ejemplo.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">RFC</label>
                                <input type="text" name="rfc" class="form-control rounded-3" placeholder="XAXX010101000">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Dirección</label>
                                <input type="text" name="direccion" class="form-control rounded-3" placeholder="Calle, Número, Colonia">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Código Postal</label>
                                <input type="text" name="cp" class="form-control rounded-3" placeholder="58000">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Localidad / Ciudad</label>
                                <input type="text" name="localidad" class="form-control rounded-3" placeholder="Morelia">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Sucursal (*)</label>
                                <select name="sucursal" class="form-select rounded-3" required>
                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($b->name); ?>"><?php echo e($b->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Fecha Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" class="form-control rounded-3">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Guardar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR CLIENTE -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-user-pen text-primary me-2"></i>Editar Datos del Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" data-loading-text="Cargando." id="editCustomerForm">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="modal-body py-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombre Completo (*)</label>
                                <input type="text" name="nombre" id="edit_nombre" class="form-control rounded-3" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Teléfono</label>
                                <input type="text" name="telefono" id="edit_telefono" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Correo Electrónico</label>
                                <input type="email" name="correo" id="edit_correo" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">RFC</label>
                                <input type="text" name="rfc" id="edit_rfc" class="form-control rounded-3">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Dirección</label>
                                <input type="text" name="direccion" id="edit_direccion" class="form-control rounded-3">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Código Postal</label>
                                <input type="text" name="cp" id="edit_cp" class="form-control rounded-3">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Localidad / Ciudad</label>
                                <input type="text" name="localidad" id="edit_localidad" class="form-control rounded-3">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Sucursal (*)</label>
                                <select name="sucursal" id="edit_sucursal" class="form-select rounded-3" required>
                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($b->name); ?>"><?php echo e($b->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Fecha Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="edit_fecha_nacimiento" class="form-control rounded-3">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SCRIPTS JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Select All Checkboxes
            const selectAll = document.getElementById('selectAll');
            const checkClientes = document.querySelectorAll('.check-cliente');
            const btnBorrar = document.getElementById('btnBorrarSeleccionados');
            const countCheck = document.getElementById('countCheck');

            function updateBulkBtn() {
                const selected = document.querySelectorAll('.check-cliente:checked').length;
                if (countCheck) countCheck.textContent = selected;
                if (btnBorrar) {
                    if (selected > 0) {
                        btnBorrar.classList.remove('d-none');
                    } else {
                        btnBorrar.classList.add('d-none');
                    }
                }
            }

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    checkClientes.forEach(chk => chk.checked = this.checked);
                    updateBulkBtn();
                });
            }

            checkClientes.forEach(chk => chk.addEventListener('change', updateBulkBtn));

            // Modal Editar Cliente
            const editBtns = document.querySelectorAll('.open-edit-customer');
            const editModalElem = document.getElementById('editCustomerModal');
            if (editModalElem) {
                const editModal = new bootstrap.Modal(editModalElem);
                const editForm = document.getElementById('editCustomerForm');

                editBtns.forEach(btn => {
                    btn.addEventListener('click', function () {
                        const id = this.getAttribute('data-id');
                        editForm.action = `/customers/${id}`;

                        document.getElementById('edit_nombre').value = this.getAttribute('data-nombre');
                        document.getElementById('edit_telefono').value = this.getAttribute('data-telefono') ?? '';
                        document.getElementById('edit_correo').value = this.getAttribute('data-correo') ?? '';
                        document.getElementById('edit_rfc').value = this.getAttribute('data-rfc') ?? '';
                        document.getElementById('edit_direccion').value = this.getAttribute('data-direccion') ?? '';
                        document.getElementById('edit_cp').value = this.getAttribute('data-cp') ?? '';
                        document.getElementById('edit_localidad').value = this.getAttribute('data-localidad') ?? '';
                        document.getElementById('edit_sucursal').value = this.getAttribute('data-sucursal');
                        document.getElementById('edit_fecha_nacimiento').value = this.getAttribute('data-fecha') ?? '';

                        editModal.show();
                    });
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
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/customers/index.blade.php ENDPATH**/ ?>