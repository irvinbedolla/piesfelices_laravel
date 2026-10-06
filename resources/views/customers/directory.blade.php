<x-app-layout>
    <div class="container py-5 d-flex flex-column align-items-center justify-content-center min-vh-80">
        
        {{-- ENCABEZADO Y SUBTÍTULO --}}
        <div class="text-center mb-5">
            <h1 class="fw-bold text-dark display-5 mb-2">Gestión de Directorio</h1>
            <p class="text-secondary fs-5 m-0">Selecciona el tipo de registro que deseas administrar:</p>
        </div>

        {{-- TARJETAS DE OPCIONES: CLIENTES VS PACIENTES --}}
        <div class="row g-4 justify-content-center w-100" style="max-width: 900px;">
            
            {{-- OPTION 1: CLIENTES --}}
            <div class="col-12 col-md-6">
                <a href="{{ route('customers.index') }}" class="text-decoration-none">
                    <div class="card border-2 h-100 p-4 text-center rounded-4 shadow-sm directory-card hover-card border-purple">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                            <div class="icon-wrapper mb-3 text-purple">
                                <i class="fa-solid fa-users fa-3x"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-2">Clientes</h3>
                            <p class="text-muted fs-6 mb-0">
                                Administración de compradores y cuentas comerciales.
                            </p>
                        </div>
                    </div>
                </a>
            </div>

            {{-- OPTION 2: PACIENTES (DESARROLLO PRÓXIMO) --}}
            <div class="col-12 col-md-6">
                <a href="{{ route('patients.index') }}" class="text-decoration-none">
                    <div class="card border-2 h-100 p-4 text-center rounded-4 shadow-sm directory-card hover-card border-purple">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                            <div class="icon-wrapper mb-3 text-purple">
                                <i class="fa-solid fa-user-doctor fa-3x"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-2">Pacientes</h3>
                            <p class="text-muted fs-6 mb-0">
                                Expedientes clínicos y seguimiento personalizado.
                            </p>
                        </div>
                    </div>
                </a>
            </div>

        </div>

        {{-- BOTÓN VOLVER AL MENÚ PRINCIPAL --}}
        <div class="mt-5 text-center">
            <a href="{{ route('dashboard') }}" class="text-muted text-decoration-none fw-semibold fs-6 hover-underline">
                <i class="fa-solid fa-arrow-left me-2"></i> Volver al Menú Principal
            </a>
        </div>

    </div>

    {{-- ESTILOS CSS PERSONALIZADOS DE LA PANTALLA HUB --}}
    <style>
        .min-vh-80 {
            min-height: 75vh;
        }
        .text-purple {
            color: #9c27b0 !important;
        }
        .border-purple {
            border-color: #9c27b0 !important;
        }
        .directory-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            background-color: #ffffff;
        }
        .hover-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(156, 39, 176, 0.2) !important;
            background-color: #fcf8ff;
        }
        .hover-underline:hover {
            text-decoration: underline !important;
            color: #9c27b0 !important;
        }
    </style>
</x-app-layout>