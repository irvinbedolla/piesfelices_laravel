<x-app-layout>
    <div class="container py-4">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-calculator me-2 text-warning"></i>Reporte de Comisiones</h5>
            <p class="text-muted small"><strong>Empleado:</strong> {{ $employeeName }} | <strong>Periodo:</strong> {{ $fecha1 }} al {{ $fecha2 }} | <strong>Sucursal:</strong> {{ $sucursal }}</p>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Concepto</th>
                            <th class="text-end">Venta Total</th>
                            <th class="text-center">% Comisión</th>
                            <th class="text-end">Comisión Ganada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Venta General</td>
                            <td class="text-end">${{ number_format($totalGeneral, 2) }}</td>
                            <td class="text-center">10%</td>
                            <td class="text-end fw-bold text-success">${{ number_format($comisionGeneral, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Venta de Calzado</td>
                            <td class="text-end">${{ number_format($totalCalzado, 2) }}</td>
                            <td class="text-center">10%</td>
                            <td class="text-end fw-bold text-success">${{ number_format($comisionCalzado, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Venta de Medicamento</td>
                            <td class="text-end">${{ number_format($totalMedicamento, 2) }}</td>
                            <td class="text-center">10%</td>
                            <td class="text-end fw-bold text-success">${{ number_format($comisionMedicamento, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Consultas y Servicios</td>
                            <td class="text-end">${{ number_format($totalConsulta, 2) }}</td>
                            <td class="text-center">Personalizado</td>
                            <td class="text-end fw-bold text-success">${{ number_format($comisionConsulta, 2) }}</td>
                        </tr>
                        <tr class="table-light fw-bold fs-6">
                            <td colspan="3" class="text-end">TOTAL A PAGAR:</td>
                            <td class="text-end text-primary">${{ number_format($totalComisiones, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>