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
        
        <div class="row g-3 mb-4">
            
            <!-- 1. Top 10 Artículos Más Vendidos del Mes -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold m-0 text-secondary">
                                <i class="fa-solid fa-chart-column me-2 text-primary"></i>Top 10 Artículos Más Vendidos (Mes)
                            </h6>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3" id="badgeMes">Cargando...</span>
                        </div>
                        <div style="position: relative; height:260px;">
                            <canvas id="chartTopProductos"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Número de Citas por Mes -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold m-0 text-secondary">
                                <i class="fa-solid fa-calendar-check me-2 text-info"></i>Citas Atendidas por Mes
                            </h6>
                            <span class="badge bg-info-subtle text-info rounded-pill px-3" id="badgeAnio">Año <?php echo e(date('Y')); ?></span>
                        </div>
                        <div style="position: relative; height:260px;">
                            <canvas id="chartCitasMes"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row g-3">

            <!-- 3. Próximas 10 Citas del Día -->
            <div class="<?php echo e(auth()->user()->isAdmin() ? 'col-lg-6' : 'col-lg-12'); ?>">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold m-0 text-secondary">
                                <i class="fa-solid fa-clock me-2 text-warning"></i>Próximas Citas del Día
                            </h6>
                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3">Hoy <?php echo e(date('d/m/Y')); ?></span>
                        </div>
                        
                        <div class="table-responsive" style="max-height: 260px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small">Hora</th>
                                        <th class="small">Paciente / Cliente</th>
                                        <th class="small">Servicio / Tipo</th>
                                        <th class="small text-end">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="small">
                                    <?php $__empty_1 = true; $__currentLoopData = $upcomingAppointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $app): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <?php
                                            $hora = $app->cita_hora ?? $app->hora_cita ?? $app->hora ?? '';
                                            $cliente = $app->nombre_cliente ?? $app->cliente ?? $app->paciente ?? 'Cliente General';
                                            $servicio = $app->servicio ?? $app->motivo ?? 'Consulta Podológica';
                                            $estatus = $app->estatus ?? $app->status ?? 'PROGRAMADA';
                                        ?>
                                        <tr>
                                            <td><span class="fw-bold text-primary"><?php echo e($hora); ?></span></td>
                                            <td class="fw-semibold"><?php echo e($cliente); ?></td>
                                            <td><?php echo e($servicio); ?></td>
                                            <td class="text-end">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle"><?php echo e($estatus); ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                <i class="fa-solid fa-calendar-xmark me-1"></i> No hay citas pendientes programadas para hoy.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Ingresos por Día (EXCLUSIVO ADMINISTRADORES) -->
            <?php if(auth()->user()->isAdmin()): ?>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-bold m-0 text-secondary">
                                    <i class="fa-solid fa-sack-dollar me-2 text-success"></i>Ingresos por Día (Semana Actual)
                                </h6>
                                <span class="badge bg-success-subtle text-success rounded-pill px-3"><i class="fa-solid fa-user-shield me-1"></i>Solo Admin</span>
                            </div>
                            <div style="position: relative; height:260px;">
                                <canvas id="chartIngresosDia"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

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
<?php endif; ?>

    <!-- SCRIPT Chart.js DINÁMICO -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            
            fetch("<?php echo e(route('api.dashboard.stats')); ?>")
                .then(response => {
                    if (!response.ok) {
                        throw new Error("HTTP error " + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    
                    const badgeMes = document.getElementById('badgeMes');
                    if (badgeMes) {
                        badgeMes.innerText = (data.mes_nombre || 'MES').toUpperCase();
                    }

                    // 1. GRÁFICA TOP 10 PRODUCTOS
                    const prodLabels = (data.top_products || []).map(item => item.nombre);
                    const prodData = (data.top_products || []).map(item => item.total_vendido);

                    const canvasProd = document.getElementById('chartTopProductos');
                    if (canvasProd) {
                        new Chart(canvasProd.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: prodLabels.length ? prodLabels : ['Sin registros en este mes'],
                                datasets: [{
                                    label: 'Unidades Vendidas',
                                    data: prodData.length ? prodData : [0],
                                    backgroundColor: '#ab47bc',
                                    borderRadius: 6
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: { y: { beginAtZero: true } }
                            }
                        });
                    }

                    // 2. GRÁFICA CITAS POR MES
                    const canvasCitas = document.getElementById('chartCitasMes');
                    if (canvasCitas) {
                        new Chart(canvasCitas.getContext('2d'), {
                            type: 'line',
                            data: {
                                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                                datasets: [{
                                    label: 'Citas',
                                    data: data.citas_mes || Array(12).fill(0),
                                    borderColor: '#29b6f6',
                                    backgroundColor: 'rgba(41, 182, 246, 0.15)',
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: { y: { beginAtZero: true } }
                            }
                        });
                    }

                    // 3. GRÁFICA INGRESOS POR DÍA
                    const canvasIngresos = document.getElementById('chartIngresosDia');
                    if (canvasIngresos) {
                        new Chart(canvasIngresos.getContext('2d'), {
                            type: 'line',
                            data: {
                                labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                                datasets: [{
                                    label: 'Ingresos ($)',
                                    data: data.ingresos_semana || Array(6).fill(0),
                                    borderColor: '#66bb6a',
                                    backgroundColor: 'rgba(102, 187, 106, 0.2)',
                                    fill: true,
                                    tension: 0.3,
                                    pointRadius: 5
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: { 
                                    y: { 
                                        beginAtZero: true,
                                        ticks: { callback: function(value) { return '$' + value; } }
                                    } 
                                }
                            }
                        });
                    }

                })
                .catch(error => {
                    console.error("Error al cargar las estadísticas del dashboard:", error);
                    const badgeMes = document.getElementById('badgeMes');
                    if (badgeMes) badgeMes.innerText = "ERROR DE CONEXIÓN";
                });
        });
    </script><?php /**PATH C:\xampp\htdocs\PiesFelices\resources\views/dashboard.blade.php ENDPATH**/ ?>