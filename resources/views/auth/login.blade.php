<x-guest-layout>
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="width: 100%; max-width: 400px; margin: 0 auto;">
        
        {{-- CABECERA MORADA CON LOGO --}}
        <div class="text-center p-4 text-white" style="background: linear-gradient(135deg, #8e24aa 0%, #ab47bc 100%);">
            <div class="mb-2 d-flex justify-content-center">
                <div class="bg-white p-2 rounded-4 shadow-sm" style="display: inline-block;">
                    <img src="{{ asset('images/logo.jpg') }}" 
                         alt="Pies Felices Logo" 
                         style="height: 55px; width: auto; object-fit: contain;">
                </div>
            </div>
            <h5 class="fw-bold m-0 tracking-wide fs-6">BIENVENIDO A PIES FELICES</h5>
            <small class="opacity-75" style="font-size: 12px;">Ingresa tus credenciales para acceder</small>
        </div>

        {{-- CUERPO DEL FORMULARIO --}}
        <div class="card-body p-4 bg-white">
            
            {{-- ALERTA DE ERRORES DE AUTENTICACIÓN --}}
            @if ($errors->any())
                <div class="alert alert-danger border-0 rounded-3 small py-2 px-3 mb-3 text-center">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" data-loading-text="Iniciando sesión...">
                @csrf

                {{-- CAMPO CORREO / USUARIO (NAME ATRIBUTO EN 'email') --}}
                <div class="mb-3 text-center">
                    <label for="email" class="form-label extra-small fw-bold text-uppercase text-muted mb-1 d-block">
                        Usuario o Correo
                    </label>
                    <input type="text" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           class="form-control form-control-lg rounded-3 text-center fs-6 border" 
                           placeholder="admin@piesfelices.com" 
                           required 
                           autofocus
                           style="background-color: #f8f9fa;">
                </div>

                {{-- CAMPO CONTRASEÑA CENTRADO --}}
                <div class="mb-3 text-center">
                    <label for="password" class="form-label extra-small fw-bold text-uppercase text-muted mb-1 d-block">
                        Contraseña
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control form-control-lg rounded-3 text-center fs-6 border" 
                           placeholder="••••••••" 
                           required
                           style="background-color: #f8f9fa;">
                </div>

                {{-- RECORDAR SESIÓN CENTRADO --}}
                <div class="d-flex justify-content-center align-items-center mb-4">
                    <div class="form-check m-0">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                        <label class="form-check-label small text-muted fw-semibold ms-1" for="remember_me">
                            Recordar sesión
                        </label>
                    </div>
                </div>

                {{-- BOTÓN MEJORADO A ANCHO COMPLETO --}}
                <button type="submit" 
                        class="btn btn-primary text-white w-100 rounded-pill py-2.5 fw-bold fs-6 shadow-sm border-0 d-flex align-items-center justify-content-center gap-2" 
                        style="background: linear-gradient(135deg, #8e24aa 0%, #ab47bc 100%); transition: all 0.3s ease; height: 45px;">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>INICIAR SESIÓN</span>
                </button>
            </form>

        </div>

        {{-- PIE DE PÁGINA --}}
        <div class="card-footer bg-light border-0 py-3 text-center">
            <small class="text-muted extra-small">
                &copy; {{ date('Y') }} <strong>Pies Felices</strong>. Todos los derechos reservados.
            </small>
        </div>

    </div>
</x-guest-layout>