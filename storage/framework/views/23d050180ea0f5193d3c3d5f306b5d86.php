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
    <div class="container-fluid py-4">
        
        
        <div class="d-flex flex-wrap gap-2 mb-4">
            <?php if(in_array($userRole, [0, 1, 3])): ?>
                <button class="btn btn-primary rounded-3 px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#newLoanModal" style="background-color: #ce93d8; border:none; color:#000;">Generar</button>
                <a href="<?php echo e(route('loans.index', ['mode' => 'historial'])); ?>" class="btn btn-primary rounded-3 px-3 fw-bold" style="background-color: #ce93d8; border:none; color:#000;">Historial</a>
                <a href="<?php echo e(route('loans.index', ['mode' => 'activos'])); ?>" class="btn btn-primary rounded-3 px-3 fw-bold" style="background-color: #ce93d8; border:none; color:#000;">Activos</a>
            <?php endif; ?>
            <a href="<?php echo e(route('loans.index', ['mode' => 'mis_prestamos'])); ?>" class="btn btn-primary rounded-3 px-3 fw-bold" style="background-color: #ce93d8; border:none; color:#000;">Mis Préstamos</a>
        </div>

        
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
                        <?php $__empty_1 = true; $__currentLoopData = $loans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><span class="badge bg-light text-primary border">#<?php echo e($l->pres_id); ?></span></td>
                                <td><?php echo e($l->pres_fecha); ?></td>
                                <td class="fw-bold"><?php echo e($l->usuario); ?></td>
                                <td><?php echo e($l->pres_descripcion); ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo e($l->sucursal); ?></span></td>
                                <td class="text-end fw-bold">$<?php echo e(number_format($l->pres_costo, 2)); ?></td>
                                <td class="text-end fw-bold text-danger">$<?php echo e(number_format($l->pres_restante, 2)); ?></td>
                                
                                
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info text-white rounded-3" data-bs-toggle="modal" data-bs-target="#historyModal<?php echo e($l->pres_id); ?>">
                                        <i class="fa-solid fa-receipt"></i>
                                    </button>
                                </td>

                                
                                <td class="text-center">
                                    <?php if($l->pres_restante > 0): ?>
                                        <button class="btn btn-sm btn-primary rounded-3" data-bs-toggle="modal" data-bs-target="#payModal<?php echo e($l->pres_id); ?>">
                                            <i class="fa-solid fa-sack-dollar"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="badge bg-success">Liquidado</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <form action="<?php echo e(route('loans.destroy', $l->pres_id)); ?>" method="POST" data-loading-text="Cargando." onsubmit="return confirm('¿Borrar préstamo?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-sm text-danger border-0"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>

                            
                            <div class="modal fade" id="payModal<?php echo e($l->pres_id); ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 p-3">
                                        <div class="modal-header border-0">
                                            <h5 class="fw-bold text-primary">Abonar a Préstamo #<?php echo e($l->pres_id); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?php echo e(route('loans.payment', $l->pres_id)); ?>" method="POST" data-loading-text="Cargando.">
                                            <?php echo csrf_field(); ?>
                                            <div class="modal-body">
                                                <div class="row g-2 mb-3 fs-6">
                                                    <div class="col-6">Total: <strong>$<?php echo e(number_format($l->pres_costo, 2)); ?></strong></div>
                                                    <div class="col-6 text-danger">Restante: <strong>$<?php echo e(number_format($l->pres_restante, 2)); ?></strong></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label extra-small fw-bold text-muted">Monto a Abonar ($)</label>
                                                    <input type="number" step="0.01" name="monto" max="<?php echo e($l->pres_restante); ?>" class="form-control rounded-3" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label extra-small fw-bold text-muted">Fecha Abono</label>
                                                    <input type="date" name="fecha" value="<?php echo e(date('Y-m-d')); ?>" class="form-control rounded-3" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="submit" class="btn btn-primary rounded-pill px-4">GUARDAR <i class="fa-solid fa-paper-plane ms-1"></i></button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="modal fade" id="historyModal<?php echo e($l->pres_id); ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 p-3">
                                        <div class="modal-header border-0">
                                            <h5 class="fw-bold text-dark">Historial de Abonos - <?php echo e($l->usuario); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <ul class="list-group list-group-flush">
                                                <?php $__empty_2 = true; $__currentLoopData = $l->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <span><i class="fa-solid fa-calendar-day me-2 text-muted"></i><?php echo e($p->fechaPretamo); ?></span>
                                                        <span class="fw-bold text-success">+$<?php echo e(number_format($p->monto, 2)); ?></span>
                                                    </li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                                    <li class="list-group-item text-muted text-center">No hay abonos registrados aún.</li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="10" class="text-center py-4 text-muted">No se encontraron préstamos.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="newLoanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 p-3">
                <div class="modal-header border-0">
                    <h5 class="fw-bold text-primary">Registrar Préstamo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo e(route('loans.store')); ?>" method="POST" data-loading-text="Cargando.">
                    <?php echo csrf_field(); ?>
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
                            <input type="date" name="pres_fecha" value="<?php echo e(date('Y-m-d')); ?>" class="form-control rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Usuario / Empleado</label>
                            <select name="user_id" class="form-select rounded-3" required>
                                <option value="">-- Seleccionar Usuario --</option>
                                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($u->id); ?>"><?php echo e($u->name ?? $u->username); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label extra-small fw-bold text-muted">Sucursal</label>
                            <select name="sucursal" class="form-select rounded-3">
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($b->name); ?>"><?php echo e($b->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/loans/index.blade.php ENDPATH**/ ?>