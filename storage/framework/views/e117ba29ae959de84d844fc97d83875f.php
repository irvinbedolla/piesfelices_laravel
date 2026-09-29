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
    <div class="row g-3 mb-4">
        
        <!-- 1. Top 10 Artículos Más Vendidos del Mes -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold m-0 text-secondary">
                            <i class="fa-solid fa-chart-column me-2 text-primary"></i>Top 10 Artículos Más Vendidos (Mes)
                        </h6>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3">Septiembre</span>
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
                        <span class="badge bg-info-subtle text-info rounded-pill px-3">Año 2026</span>
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
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold m-0 text-secondary">
                            <i class="fa-solid fa-clock-subheading me-2 text-warning"></i>Próximas Citas del Día
                        </h6>
                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3">Hoy</span>
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
                                <tr>
                                    <td><span class="fw-bold">09:00 AM</span></td>
                                    <td>María Elena Gómez</td>
                                    <td>Consulta Podológica</td>
                                    <td class="text-end"><span class="badge bg-success">Confirmada</span></td>
                                </tr>
                                <tr>
                                    <td><span class="fw-bold">10:00 AM</span></td>
                                    <td>Juan Carlos Pérez</td>
                                    <td>Plantillas Personalizadas</td>
                                    <td class="text-end"><span class="badge bg-primary">En Atención</span></td>
                                </tr>
                                <tr>
                                    <td><span class="fw-bold">11:30 AM</span></td>
                                    <td>Ana Sofía Martínez</td>
                                    <td>Tratamiento Láser</td>
                                    <td class="text-end"><span class="badge bg-warning text-dark">Pendiente</span></td>
                                </tr>
                                <tr>
                                    <td><span class="fw-bold">01:00 PM</span></td>
                                    <td>Roberto Hernández</td>
                                    <td>Quiropodia</td>
                                    <td class="text-end"><span class="badge bg-warning text-dark">Pendiente</span></td>
                                </tr>
                                <tr>
                                    <td><span class="fw-bold">04:00 PM</span></td>
                                    <td>Laura Patricia Ríos</td>
                                    <td>Valoración Inicial</td>
                                    <td class="text-end"><span class="badge bg-warning text-dark">Pendiente</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Ingresos por Día (Exclusivo Administradores) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold m-0 text-secondary">
                            <i class="fa-solid fa-sack-dollar me-2 text-success"></i>Ingresos por Día (Semana Actual)
                        </h6>
                        <span class="badge bg-success-subtle text-success rounded-pill px-3">Solo Admin</span>
                    </div>
                    <div style="position: relative; height:260px;">
                        <canvas id="chartIngresosDia"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- SCRIPTS PARA RENDERIZAR LAS GRÁFICAS -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            
            // 1. Gráfica Top 10 Productos
            const ctxProductos = document.getElementById('chartTopProductos').getContext('2d');
            new Chart(ctxProductos, {
                type: 'bar',
                data: {
                    labels: ['Plantilla Gel', 'Jabón Antiséptico', 'Crema Humectante', 'Calzado Clínico', 'Talonera Silicon', 'Spray Antimicótico', 'Cinta Kinesiológica', 'Aceite Esencial', 'Protector Juanete', 'Corte Uñas Esp.'],
                    datasets: [{
                        label: 'Unidades Vendidas',
                        data: [85, 72, 64, 58, 49, 45, 38, 30, 25, 20],
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

            // 2. Gráfica Citas por Mes
            const ctxCitas = document.getElementById('chartCitasMes').getContext('2d');
            new Chart(ctxCitas, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    datasets: [{
                        label: 'Citas',
                        data: [45, 52, 68, 74, 80, 95, 110, 105, 128, 0, 0, 0],
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

            // 3. Gráfica Ingresos por Día (Admin)
            const ctxIngresos = document.getElementById('chartIngresosDia').getContext('2d');
            new Chart(ctxIngresos, {
                type: 'line',
                data: {
                    labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                    datasets: [{
                        label: 'Ingresos ($)',
                        data: [4500, 6200, 5800, 7100, 8900, 9400],
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
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/dashboard.blade.php ENDPATH**/ ?>