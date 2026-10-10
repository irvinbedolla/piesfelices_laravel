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
                    {{-- Nombre del Paciente --}}
                    <div class="col-12 col-md-5">
                        <label class="form-label fw-semibold">Nombre del Paciente</label>
                        <input type="text" name="patient_name" class="form-control rounded-3" placeholder="Ej. Juan Pérez Morales" required>
                    </div>

                    {{-- Sucursal --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Sucursal</label>
                        @if(count($branches) > 1)
                            {{-- Si es Admin o tiene acceso a múltiples sucursales --}}
                            <select name="branch_id" class="form-select rounded-3" required>
                                <option value="" disabled selected>-- Selecciona Sucursal --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id', Auth::user()->branch_id) == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            {{-- Si es un usuario normal asignado a una sola sucursal --}}
                            <input type="text" class="form-control rounded-3" value="{{ $branches->first()->name ?? 'Sin Sucursal' }}" readonly disabled>
                            <input type="hidden" name="branch_id" value="{{ $branches->first()->id ?? Auth::user()->branch_id }}">
                        @endif
                    </div>

                    {{-- Próxima Cita --}}
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-semibold">Próxima Cita</label>
                        <input type="date" name="next_appointment" value="{{ date('Y-m-d') }}" class="form-control rounded-3">
                    </div>

                    {{-- Diagnóstico --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">Diagnóstico</label>
                        <input type="text" name="diagnosis" class="form-control rounded-3" placeholder="Ej. Onicomicosis / Pie de atleta" required>
                    </div>

                    {{-- Indicaciones --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">Indicaciones / Tratamiento</label>
                        <textarea name="indications" rows="5" class="form-control rounded-3" placeholder="Escribe aquí las indicaciones médicas o el tratamiento recomendado..." required></textarea>
                    </div>

                    {{-- Botones --}}
                    <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('prescriptions.index') }}" class="btn btn-secondary rounded-pill px-4">Regresar</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar</button>
                    </div>
                </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>