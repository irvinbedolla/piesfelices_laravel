<x-app-layout>
    <div class="container-fluid py-4">
        
        {{-- ALERTA DE CUMPLEAÑEROS DEL DÍA (VISIBLE PARA TODOS LOS USUARIOS) --}}
        @if(isset($birthdays) && $birthdays->count() > 0)
            <div class="alert alert-warning border-warning rounded-4 shadow-sm p-3 mb-4">
                <div class="d-flex align-items-center mb-2">
                    <i class="fa-solid fa-cake-candles text-warning fs-4 me-2"></i>
                    <h5 class="m-0 fw-bold text-dark">
                        ¡Clientes que cumplen años hoy! ({{ $birthdays->count() }})
                    </h5>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    @foreach($birthdays as $cumple)
                        @php
                            $telLimpio = preg_replace('/[^0-9]/', '', $cumple->telefono);
                            $textoMensaje = "🎉🎂 ¡MUCHAS FELICIDADES EN TU CUMPLEAÑOS, {$cumple->nombre}! 🎂🎉\n\n"
                                          . "Desde PIES FELICES queremos desearte un cumpleaños lleno de alegría, salud, amor y momentos inolvidables.\n\n"
                                          . "Que este nuevo año de vida esté acompañado de grandes bendiciones, éxitos y muchas razones para sonreír.\n\n"
                                          . "¡Disfruta mucho tu día! 🥳🎁\n\n"
                                          . "Con cariño y nuestros mejores deseos,\n"
                                          . "Familia PIES FELICES 💜\n"
                                          . "Salud y felicidad a tus pies.";
                            $msgWhatsApp = rawurlencode($textoMensaje);
                        @endphp
                        <span class="badge bg-white text-dark border p-2 d-flex align-items-center rounded-pill shadow-sm">
                            <span class="me-2 text-primary fw-bold">
                                <i class="fa-solid fa-gift me-1 text-danger"></i> {{ $cumple->nombre }}
                            </span>
                            @if(!empty($telLimpio))
                                <a href="https://api.whatsapp.com/send?phone=52{{ $telLimpio }}&text={{ $msgWhatsApp }}" 
                                   target="_blank" class="btn btn-sm btn-success py-0 px-2 rounded-pill d-flex align-items-center small">
                                    <i class="fa-brands fa-whatsapp me-1"></i> Felicitaciones
                                </a>
                            @endif
                        </span>
                    @endforeach
                </div>
            </div>
        @else
            <div class="mb-4 p-3 rounded-4 border d-flex align-items-center bg-white shadow-sm">
                <div class="bg-light p-2 rounded-circle me-3 d-flex align-items-center justify-content-center">
                    <i class="fa-regular fa-bell text-muted fs-5"></i>
                </div>
                <div>
                    <h6 class="m-0 fw-bold text-secondary">Búsqueda de Cumpleaños Finalizada</h6>
                    <small class="text-muted">Ningún cliente registrado cumple años el día de hoy.</small>
                </div>
            </div>
        @endif

        <div class="row g-3 mb-4">
            
            <!-- 1. Top 10 Artículos Más Vendidos del Mes -->
            @if(auth()->user()->isAdmin())
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
            @endif

            <!-- 2. Número de Citas por Mes -->
             @if(auth()->user()->isAdmin())
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-bold m-0 text-secondary">
                                    <i class="fa-solid fa-calendar-check me-2 text-info"></i>Citas Atendidas por Mes
                                </h6>
                                <span class="badge bg-info-subtle text-info rounded-pill px-3" id="badgeAnio">Año {{ date('Y') }}</span>
                            </div>
                            <div style="position: relative; height:260px;">
                                <canvas id="chartCitasMes"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        <div class="row g-3">

            <!-- 3. Próximas 10 Citas del Día -->
            <div class="{{ auth()->user()->isAdmin() ? 'col-lg-6' : 'col-lg-12' }}">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold m-0 text-secondary">
                                <i class="fa-solid fa-clock me-2 text-warning"></i>Próximas Citas del Día
                            </h6>
                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3">Hoy {{ date('d/m/Y') }}</span>
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
                                    @forelse($upcomingAppointments as $app)
                                        @php
                                            $hora = $app->start ?? $app->start ?? $app->start ?? '';
                                            $cliente = $app->title ?? $app->title ?? $app->title ?? 'Cliente General';
                                            $servicio = $app->notes ?? $app->notes ?? 'Consulta Podológica';
                                            $estatus = $app->estatus ?? $app->status ?? 'PROGRAMADA';
                                        @endphp
                                        <tr>
                                            <td><span class="fw-bold text-primary">{{ $hora }}</span></td>
                                            <td class="fw-semibold">{{ $cliente }}</td>
                                            <td>{{ $servicio }}</td>
                                            <td class="text-end">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $estatus }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                <i class="fa-solid fa-calendar-xmark me-1"></i> No hay citas pendientes programadas para hoy.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Ingresos por Día (EXCLUSIVO ADMINISTRADORES) -->
            @if(auth()->user()->isAdmin())
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
            @endif

        </div>

    </div>
</x-app-layout>

    <!-- SCRIPT Chart.js DINÁMICO -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            
            fetch("{{ route('api.dashboard.stats') }}")
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
    </script>