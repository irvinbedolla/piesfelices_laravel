<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SISTEMA | Pies Felices' }}</title>

    <!-- Bootstrap 5.3 & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --sidebar-width: 260px;
            --pf-primary: #8e24aa;
            --pf-primary-dark: #6a1b9a;
            --pf-accent: #e040fb;
            --pf-bg: #f4f6f9;
        }

        body {
            background-color: var(--pf-bg);
            font-family: 'Segoe UI', system-ui, sans-serif;
            overflow-x: hidden;
        }

        /* ESTRUCTURA GENERAL RESPONSIVA */
        #wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
            position: relative;
        }

        /* SIDEBAR STYLES CON SOPORTE PARA COLAPSO */
        #sidebar {
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            background: linear-gradient(180deg, #4a148c 0%, #7b1fa2 100%);
            color: #fff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
            z-index: 1040;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }

        /* ESTADO COLAPSADO O EN PANTALLAS PEQUEÑAS */
        #sidebar.collapsed {
            margin-left: calc(-1 * var(--sidebar-width));
        }

        #sidebar .sidebar-header {
            padding: 20px 15px;
            background: rgba(0, 0, 0, 0.15);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        #sidebar ul.components {
            padding: 15px 10px;
        }

        .sidebar-heading {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.6);
            padding: 10px 15px 5px 15px;
            font-weight: 700;
        }

        #sidebar ul li a {
            padding: 10px 15px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 4px;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        #sidebar ul li a:hover, #sidebar ul li.active > a {
            color: #fff;
            background: rgba(255, 255, 255, 0.18);
            transform: translateX(4px);
        }

        #sidebar ul li a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 1.05rem;
        }

        /* ÁREA DE CONTENIDO */
        #content {
            width: 100%;
            min-height: 100vh;
            transition: all 0.3s ease;
            overflow-x: hidden;
        }

        .navbar-top {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        /* OVERLAY PARA CELULARES CUANDO EL MENÚ ESTÁ ABIERTO */
        #sidebarOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1030;
            display: none;
        }

        /* MEDIA QUERIES PARA PANTALLAS PEQUEÑAS (< 992px) */
        @media (max-width: 991.98px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                margin-left: calc(-1 * var(--sidebar-width));
            }

            #sidebar.show-mobile {
                margin-left: 0;
            }

            #sidebarOverlay.active {
                display: block;
            }
        }

        /* OVERLAY GLOBAL DE CARGA */
        #globalLoadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.88);
            z-index: 99999;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(4px);
            transition: opacity 0.2s ease-in-out;
        }
        .spinner-pf {
            width: 3.5rem;
            height: 3.5rem;
            color: #8e24aa;
        }
    </style>
</head>
<body>

@php
    $user = Auth::user();
@endphp

<!-- OVERLAY DE CARGA GLOBAL -->
<div id="globalLoadingOverlay" class="d-none">
    <div class="spinner-border spinner-pf mb-3" role="status"></div>
    <h5 class="fw-bold text-dark m-0" id="loadingText">Procesando solicitud...</h5>
    <p class="text-muted small mt-1 mb-0">Por favor, espera un momento.</p>
</div>

<!-- OVERLAY OSCURO PARA MENÚ MÓVIL -->
<div id="sidebarOverlay"></div>

<div id="wrapper">
    <!-- NAVEGACIÓN MODULARIZADA -->
    @include('layouts.navigation')

    <!-- CONTENT -->
    <div id="content">
        <!-- TOP NAVBAR CON BOTÓN DE HAMBURGUESA -->
        <nav class="navbar navbar-expand-lg navbar-top px-3 px-md-4 py-2">
            <div class="container-fluid p-0 d-flex justify-content-between align-items-center">
                
                <div class="d-flex align-items-center gap-2">
                    <!-- Botón para Colapsar/Mostrar Menú -->
                    <button id="sidebarToggle" class="btn btn-light border-0 rounded-circle p-2 shadow-sm" type="button">
                        <i class="fa-solid fa-bars fs-5 text-primary"></i>
                    </button>

                    <span class="text-secondary fw-semibold d-none d-sm-inline">
                        <i class="fa-solid fa-hospital me-1 text-primary"></i>Sucursal: <strong class="text-dark">{{ $user?->branch?->name ?? $user?->branch_name ?? 'MATRIZ' }}</strong>
                    </span>
                </div>

                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <span class="small fw-semibold text-secondary">
                        <i class="fa-solid fa-circle-user me-1 text-primary"></i> {{ $user?->name ?? $user?->username ?? 'Usuario' }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}" data-loading-text="Cerrando sesión...">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                            <i class="fa-solid fa-power-off me-1"></i> <span class="d-none d-md-inline">Salir</span>
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <!-- MAIN VIEW -->
        <main class="p-2 p-md-4">
            {{ $slot }}
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // CONTROL DEL MENÚ LATERAL RESPONSIVO
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        if (window.innerWidth < 992) {
            sidebar.classList.toggle('show-mobile');
            sidebarOverlay.classList.toggle('active');
        } else {
            sidebar.classList.toggle('collapsed');
        }
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show-mobile');
            sidebarOverlay.classList.remove('active');
        });
    }

    // CARGADOR GLOBAL DE FORMULAROS
    window.showLoading = function(text = 'Procesando solicitud...') {
        const textElem = document.getElementById('loadingText');
        const overlayElem = document.getElementById('globalLoadingOverlay');
        let cleanText = (text || 'Procesando solicitud...').replace(/<[^>]*>?/gm, '').trim();
        if (!cleanText || cleanText.includes('input') || cleanText.length > 50) cleanText = 'Procesando solicitud...';
        if (textElem) textElem.innerText = cleanText;
        if (overlayElem) overlayElem.classList.remove('d-none');
    };

    window.hideLoading = function() {
        const overlayElem = document.getElementById('globalLoadingOverlay');
        if (overlayElem) overlayElem.classList.add('d-none');
    };

    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.target === '_blank' || form.classList.contains('no-loading') || form.hasAttribute('data-no-loading')) return;
        showLoading(form.getAttribute('data-loading-text') || 'Procesando solicitud...');
        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;
    });
</script>
</body>
</html>