<x-app-layout>
    <div class="container py-4">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            
            {{-- ENCABEZADO Y FILTROS DEL REPORTE --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 pb-3 border-bottom gap-2">
                <div>
                    <h5 class="fw-bold text-dark m-0">
                        <i class="fa-solid fa-calculator me-2 text-warning"></i>Reporte de Comisiones por Empleado
                    </h5>
                    <p class="text-muted small m-0 mt-1">
                        <strong>Empleado:</strong> <span class="text-primary fw-bold">{{ $employeeName }}</span> | 
                        <strong>Periodo:</strong> {{ $fecha1 }} al {{ $fecha2 }} | 
                        <strong>Sucursal:</strong> {{ $sucursal ?: 'TODAS' }}
                    </p>
                </div>
                <div>
                    <button type="button" onclick="window.print();" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fa-solid fa-print me-1"></i> Imprimir
                    </button>
                </div>
            </div>

            {{-- TABLA DE COMISIONES DESGLOSADA --}}
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr class="extra-small fw-bold text-uppercase">
                            <th>Concepto / Categoría</th>
                            <th class="text-end">Venta Total</th>
                            <th class="text-center">% Comisión</th>
                            <th class="text-end">Comisión Ganada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-semibold text-dark">
                                <i class="fa-solid fa-box-open me-2 text-info"></i>Venta General
                            </td>
                            <td class="text-end">${{ number_format($totalGeneral, 2) }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ $pct }}%</span></td>
                            <td class="text-end fw-bold text-success">${{ number_format($comisionGeneral, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-dark">
                                <i class="fa-solid fa-shoe-prints me-2 text-primary"></i>Venta de Calzado
                            </td>
                            <td class="text-end">${{ number_format($totalCalzado, 2) }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ $pct }}%</span></td>
                            <td class="text-end fw-bold text-success">${{ number_format($comisionCalzado, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-dark">
                                <i class="fa-solid fa-pills me-2 text-danger"></i>Venta de Medicamento
                            </td>
                            <td class="text-end">${{ number_format($totalMedicamento, 2) }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ $pct }}%</span></td>
                            <td class="text-end fw-bold text-success">${{ number_format($comisionMedicamento, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-dark">
                                <i class="fa-solid fa-user-doctor me-2 text-warning"></i>Consultas y Servicios
                            </td>
                            <td class="text-end">${{ number_format($totalConsulta, 2) }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ $pct }}%</span></td>
                            <td class="text-end fw-bold text-success">${{ number_format($comisionConsulta, 2) }}</td>
                        </tr>
                        
                        {{-- RESUMEN FINAL --}}
                        <tr class="table-light fw-bold">
                            <td class="text-end">TOTALES:</td>
                            <td class="text-end text-dark">${{ number_format($totalVentas, 2) }}</td>
                            <td class="text-center">-</td>
                            <td class="text-end text-primary fs-5">${{ number_format($totalComisiones, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>