<x-app-layout>
    <div class="container py-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 mx-auto" style="max-width: 800px;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-primary m-0">Generar Nueva Receta</h4>
                <span class="badge bg-light text-dark border fs-6">{{ date('d/m/Y') }}</span>
            </div>

            <form action="{{ route('prescriptions.store') }}" method="POST" data-loading-text="Cargando.">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-8">
                        <label class="form-label fw-semibold">Nombre del Paciente</label>
                        <input type="text" name="patient_name" class="form-control rounded-3" placeholder="Ej. Juan Pérez Morales" required>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Próxima Cita</label>
                        <input type="date" name="next_appointment" value="{{ date('Y-m-d') }}" class="form-control rounded-3">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Diagnóstico</label>
                        <input type="text" name="diagnosis" class="form-control rounded-3" placeholder="Ej. Onicomicosis / Pie de atleta" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Indicaciones / Tratamiento</label>
                        <textarea name="indications" rows="5" class="form-control rounded-3" placeholder="Escribe aquí las indicaciones médicas o el tratamiento recomendado..." required></textarea>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('prescriptions.index') }}" class="btn btn-secondary rounded-pill px-4">Regresar</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>