<x-app-layout>
    <div class="container py-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 mx-auto" style="max-width: 800px;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-primary m-0">Editar Receta Medica #{{ $prescription->id }}</h4>
                <span class="badge bg-light text-dark border fs-6">Sucursal: {{ $prescription->branch_name }}</span>
            </div>

            <form action="{{ route('prescriptions.update', $prescription->id) }}" method="POST" data-loading-text="Cargando.">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12 col-md-8">
                        <label class="form-label fw-semibold">Nombre del Paciente</label>
                        <input type="text" name="patient_name" value="{{ old('patient_name', $prescription->patient_name) }}" class="form-control rounded-3" required>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Próxima Cita</label>
                        <input type="date" name="next_appointment" value="{{ old('next_appointment', $prescription->next_appointment) }}" class="form-control rounded-3">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Diagnóstico</label>
                        <input type="text" name="diagnosis" value="{{ old('diagnosis', $prescription->diagnosis) }}" class="form-control rounded-3" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Indicaciones / Tratamiento</label>
                        <textarea name="indications" rows="5" class="form-control rounded-3" required>{{ old('indications', $prescription->indications) }}</textarea>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('prescriptions.index') }}" class="btn btn-secondary rounded-pill px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Actualizar Receta</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>