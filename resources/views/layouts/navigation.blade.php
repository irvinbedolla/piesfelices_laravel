@php 
    $user = Auth::user(); 
@endphp

<nav id="sidebar">
    <!-- LOGO Y TÍTULO -->
    <div class="sidebar-header d-flex align-items-center gap-2">
        <i class="fa-solid fa-shoe-prints fs-4 text-warning"></i>
        <span class="fs-5 fw-bold text-white tracking-wide">PIES FELICES</span>
    </div>

    <ul class="list-unstyled components">
        <!-- DASHBOARD -->
        <li>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Dashboard
            </a>
        </li>

        <!-- SECCIÓN: OPERACIÓN -->
        <div class="sidebar-heading">Operación</div>

        @if($user?->hasAccessTo('can_sales'))
            <li>
                <a href="#"><i class="fa-solid fa-hand-holding-dollar"></i> Vender / POS</a>
            </li>
            <li>
                <a href="#"><i class="fa-solid fa-receipt"></i> Historial Ventas</a>
            </li>
        @endif

            <li>
                <a href="{{ route('inventory.index') }}" class="{{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-boxes-stacked"></i> Inventario
                </a>
            </li>

        @if($user?->hasAccessTo('can_clients') || $user?->hasAccessTo('can_patients'))
            <li>
                <a href="#"><i class="fa-solid fa-hospital-user"></i> Clientes / Pacientes</a>
            </li>
        @endif

        @if($user?->hasAccessTo('can_prescriptions'))
            <li>
                <a href="#"><i class="fa-solid fa-notes-medical"></i> Recetas y Citas</a>
            </li>
        @endif

        <!-- SECCIÓN: CAJA & FINANZAS -->
        <div class="sidebar-heading">Caja & Finanzas</div>

        @if($user?->hasAccessTo('can_cash_closing'))
            <li>
                <a href="#"><i class="fa-solid fa-vault"></i> Cierre de Caja</a>
            </li>
        @endif

        @if($user?->hasAccessTo('can_expenses'))
            <li>
                <a href="#"><i class="fa-solid fa-cart-shopping"></i> Control de Gastos</a>
            </li>
        @endif

        @if($user?->hasAccessTo('can_credit'))
            <li>
                <a href="#"><i class="fa-regular fa-credit-card"></i> Créditos y Cobranza</a>
            </li>
        @endif

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