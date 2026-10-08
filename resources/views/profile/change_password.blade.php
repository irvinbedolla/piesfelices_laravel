<x-app-layout>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    
                    {{-- ENCABEZADO --}}
                    <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                        <div class="bg-primary-subtle text-primary p-3 rounded-circle fs-4">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold m-0 text-dark">Cambiar Mi Contraseña</h5>
                            <small class="text-muted">Actualiza tus credenciales de acceso a la plataforma</small>
                        </div>
                    </div>

                    {{-- MENSAJES DE ÉXITO O ERROR --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 small py-2 px-3 mb-4" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 small py-2 px-3 mb-4" role="alert">
                            <i class="fa-solid fa-circle-exclamation me-2"></i>{{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.password.update') }}" data-loading-text="Actualizando contraseña...">
                        @csrf
                        @method('PUT')

                        {{-- CONTRASEÑA ACTUAL --}}
                        <div class="mb-3">
                            <label for="current_password" class="form-label extra-small fw-bold text-muted text-uppercase mb-1">
                                Contraseña Actual
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 rounded-start-3 text-muted ps-3">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                <input type="password" 
                                       id="current_password" 
                                       name="current_password" 
                                       class="form-control form-control-lg border-start-0 border-end-0 fs-6" 
                                       placeholder="••••••••" 
                                       required>
                                <button class="btn btn-light border border-start-0 rounded-end-3 text-muted pe-3" 
                                        type="button" 
                                        onclick="togglePass('current_password', 'icon1')">
                                    <i class="fa-solid fa-eye" id="icon1"></i>
                                </button>
                            </div>
                        </div>

                        <hr class="my-4 border-light">

                        {{-- NUEVA CONTRASEÑA --}}
                        <div class="mb-3">
                            <label for="password" class="form-label extra-small fw-bold text-muted text-uppercase mb-1">
                                Nueva Contraseña
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 rounded-start-3 text-muted ps-3">
                                    <i class="fa-solid fa-key"></i>
                                </span>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-control form-control-lg border-start-0 border-end-0 fs-6" 
                                       placeholder="Mínimo 8 caracteres" 
                                       required>
                                <button class="btn btn-light border border-start-0 rounded-end-3 text-muted pe-3" 
                                        type="button" 
                                        onclick="togglePass('password', 'icon2')">
                                    <i class="fa-solid fa-eye" id="icon2"></i>
                                </button>
                            </div>
                        </div>

                        {{-- CONFIRMAR NUEVA CONTRASEÑA --}}
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label extra-small fw-bold text-muted text-uppercase mb-1">
                                Confirmar Nueva Contraseña
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 rounded-start-3 text-muted ps-3">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </span>
                                <input type="password" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       class="form-control form-control-lg border-start-0 border-end-0 fs-6" 
                                       placeholder="Repite la nueva contraseña" 
                                       required>
                                <button class="btn btn-light border border-start-0 rounded-end-3 text-muted pe-3" 
                                        type="button" 
                                        onclick="togglePass('password_confirmation', 'icon3')">
                                    <i class="fa-solid fa-eye" id="icon3"></i>
                                </button>
                            </div>
                        </div>

                        {{-- BOTÓN GUARDAR --}}
                        <button type="submit" 
                                class="btn text-white w-100 rounded-pill py-3 fw-bold fs-6 shadow-sm border-0 d-flex align-items-center justify-content-center gap-2" 
                                style="background: linear-gradient(135deg, #8e24aa 0%, #ab47bc 100%);">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePass(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input && icon) {
                const isPass = input.getAttribute("type") === "password";
                input.setAttribute("type", isPass ? "text" : "password");
                icon.classList.toggle("fa-eye");
                icon.classList.toggle("fa-eye-slash");
            }
        }
    </script>
</x-app-layout>