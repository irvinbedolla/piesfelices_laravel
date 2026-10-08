@php 
    $user = Auth::user(); 
@endphp

<nav id="sidebar">
    <!-- LOGO Y TÍTULO -->
    <div class="sidebar-header d-flex align-items-center justify-content-center px-3 py-3">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('images/logo.jpg') }}" 
                alt="Pies Felices" 
                style="height: 48px; width: auto; max-width: 180px; object-fit: contain;">
        </a>
    </div>


    <ul class="list-unstyled components">
        <!-- DASHBOARD -->
        <li>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Inicio
            </a>
        </li>

        <!-- SECCIÓN: OPERACIÓN -->
        <div class="sidebar-heading">Operación</div>

        @if($user?->hasAccessTo('can_sales'))
            {{-- ÍTEM VENTAS / PUNTO DE VENTA EN EL SIDEBAR --}}
            <li class="nav-item">
                <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-cart-shopping me-2"></i>
                    <span>Venta</span>
                </a>
            </li>
        @endif

            <li>
                <a href="{{ route('inventory.index') }}" class="{{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-boxes-stacked"></i> Inventario
                </a>
            </li>

        {{-- ÍTEM CLIENTES EN EL SIDEBAR --}}
        <li class="nav-item">
            <a href="{{ route('customers.directory') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users me-2"></i>
                <span>Clientes</span>
            </a>
        </li>

        {{-- ÍTEM RECETAS Y CITAS EN EL SIDEBAR --}}
        @if($user?->hasAccessTo('can_prescriptions'))
            <li>
                <a href="{{ route('prescriptions.index') }}" class="nav-link {{ request()->routeIs('prescriptions.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-medical me-2"></i>
                    <span>Recetas</span>
                </a>
            </li>
        @endif

        {{-- ÍTEM CITAS EN EL SIDEBAR --}}
        <li class="{{ request()->routeIs('appointments.*') ? 'active' : '' }}">
            <a href="{{ route('appointments.index') }}">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Citas</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('sales.consultation.*') ? 'active' : '' }}">
            <a href="{{ route('sales.consultation.index') }}">
                <i class="fa-solid fa-magnifying-glass-dollar"></i>
                <span>Consulta de Ventas</span>
            </a>
        </li>

        <!-- SECCIÓN: CAJA & FINANZAS -->
        <div class="sidebar-heading">Caja & Finanzas</div>
        
        {{-- ÍTEM CIERRE DE CAJA EN EL SIDEBAR --}}
        <li class="nav-item">
            <a href="{{ route('cash-closing.index') }}" class="nav-link {{ request()->routeIs('cash-closing.*') ? 'active' : '' }}">
                <i class="fa-solid fa-vault me-2"></i>
                <span>Cierres de Caja</span>
            </a>
        </li>

        @if($user?->hasAccessTo('can_expenses'))
            <li class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <a href="{{ route('expenses.index') }}">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>Control de Gastos</span>
                </a>
            </li>
            <li class="{{ request()->routeIs('loans.*') ? 'active' : '' }}">
                <a href="{{ route('loans.index') }}">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <span>Préstamos</span>
                </a>
            </li>
        @endif

        {{-- ÍTEM CRÉDITOS Y COBRANZA EN EL SIDEBAR --}}
        @if($user?->hasAccessTo('can_credit') ?? true)
            <li class="{{ request()->routeIs('credits.*') ? 'active' : '' }}">
                <a href="{{ route('credits.index') }}">
                    <i class="fa-regular fa-credit-card"></i>
                    <span>Créditos y Cobranza</span>
                </a>
            </li>
        @endif
        
            <li class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <a href="{{ route('reports.index') }}">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Reportes</span>
                </a>
            </li>

        <!-- SECCIÓN: ADMINISTRACIÓN -->
        @if($user?->isAdmin() || $user?->hasAccessTo('can_users') || $user?->hasAccessTo('can_employees') || $user?->hasAccessTo('can_suppliers'))
            <div class="sidebar-heading">Administración</div>

            @if($user?->isAdmin())
                <li>
                    <a href="{{ route('branches.index') }}" class="{{ request()->routeIs('branches.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-store"></i> Sucursales
                    </a>
                </li>
                <li>
                    <a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-user-shield"></i> Roles y Permisos
                    </a>
                </li>
            @endif

            @if($user?->hasAccessTo('can_employees'))
                <li>
                    <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-id-card"></i> Empleados
                    </a>
                </li>
            @endif

            @if($user?->hasAccessTo('can_users'))
                <li>
                    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users-gear"></i> Usuarios
                    </a>
                </li>
            @endif

            @if($user?->hasAccessTo('can_suppliers'))
                <li>
                    <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-truck-field"></i> Proveedores
                    </a>
                </li>
            @endif
        @endif
    </ul>
</nav>