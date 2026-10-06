<?php 
    $user = Auth::user(); 
?>

<nav id="sidebar">
    <!-- LOGO Y TÍTULO -->
    <div class="sidebar-header d-flex align-items-center justify-content-center px-3 py-3">
        <a href="<?php echo e(route('dashboard')); ?>">
            <img src="<?php echo e(asset('images/logo.jpg')); ?>" 
                alt="Pies Felices" 
                style="height: 48px; width: auto; max-width: 180px; object-fit: contain;">
        </a>
    </div>

    <ul class="list-unstyled components">
        <!-- DASHBOARD -->
        <li>
            <a href="<?php echo e(route('dashboard')); ?>" class="<?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                <i class="fa-solid fa-chart-pie"></i> Inicio
            </a>
        </li>

        <!-- SECCIÓN: OPERACIÓN -->
        <div class="sidebar-heading">Operación</div>

        <?php if($user?->hasAccessTo('can_sales')): ?>
            
            <li class="nav-item">
                <a href="<?php echo e(route('pos.index')); ?>" class="nav-link <?php echo e(request()->routeIs('pos.*') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-cart-shopping me-2"></i>
                    <span>Venta</span>
                </a>
            </li>
        <?php endif; ?>

            <li>
                <a href="<?php echo e(route('inventory.index')); ?>" class="<?php echo e(request()->routeIs('inventory.*') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-boxes-stacked"></i> Inventario
                </a>
            </li>

        
        <li class="nav-item">
            <a href="<?php echo e(route('customers.directory')); ?>" class="nav-link <?php echo e(request()->routeIs('customers.*') ? 'active' : ''); ?>">
                <i class="fa-solid fa-users me-2"></i>
                <span>Clientes</span>
            </a>
        </li>

        
        <?php if($user?->hasAccessTo('can_prescriptions')): ?>
            <li>
                <a href="<?php echo e(route('prescriptions.index')); ?>" class="nav-link <?php echo e(request()->routeIs('prescriptions.*') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-file-medical me-2"></i>
                    <span>Recetas</span>
                </a>
            </li>
        <?php endif; ?>

        
        <li class="<?php echo e(request()->routeIs('appointments.*') ? 'active' : ''); ?>">
            <a href="<?php echo e(route('appointments.index')); ?>">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Citas</span>
            </a>
        </li>

        <li class="<?php echo e(request()->routeIs('sales.consultation.*') ? 'active' : ''); ?>">
            <a href="<?php echo e(route('sales.consultation.index')); ?>">
                <i class="fa-solid fa-magnifying-glass-dollar"></i>
                <span>Consulta de Ventas</span>
            </a>
        </li>

        <!-- SECCIÓN: CAJA & FINANZAS -->
        <div class="sidebar-heading">Caja & Finanzas</div>
        
        
        <li class="nav-item">
            <a href="<?php echo e(route('cash-closing.index')); ?>" class="nav-link <?php echo e(request()->routeIs('cash-closing.*') ? 'active' : ''); ?>">
                <i class="fa-solid fa-vault me-2"></i>
                <span>Cierres de Caja</span>
            </a>
        </li>

        <?php if($user?->hasAccessTo('can_expenses')): ?>
            <li class="<?php echo e(request()->routeIs('expenses.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('expenses.index')); ?>">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>Control de Gastos</span>
                </a>
            </li>
            <li class="<?php echo e(request()->routeIs('loans.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('loans.index')); ?>">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <span>Préstamos</span>
                </a>
            </li>
        <?php endif; ?>

        
        <?php if($user?->hasAccessTo('can_credit') ?? true): ?>
            <li class="<?php echo e(request()->routeIs('credits.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('credits.index')); ?>">
                    <i class="fa-regular fa-credit-card"></i>
                    <span>Créditos y Cobranza</span>
                </a>
            </li>
        <?php endif; ?>
        
            <li class="<?php echo e(request()->routeIs('reports.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('reports.index')); ?>">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Reportes</span>
                </a>
            </li>

        <!-- SECCIÓN: ADMINISTRACIÓN -->
        <?php if($user?->isAdmin() || $user?->hasAccessTo('can_users') || $user?->hasAccessTo('can_employees') || $user?->hasAccessTo('can_suppliers')): ?>
            <div class="sidebar-heading">Administración</div>

            <?php if($user?->isAdmin()): ?>
                <li>
                    <a href="<?php echo e(route('branches.index')); ?>" class="<?php echo e(request()->routeIs('branches.*') ? 'active' : ''); ?>">
                        <i class="fa-solid fa-store"></i> Sucursales
                    </a>
                </li>
                <li>
                    <a href="<?php echo e(route('roles.index')); ?>" class="<?php echo e(request()->routeIs('roles.*') ? 'active' : ''); ?>">
                        <i class="fa-solid fa-user-shield"></i> Roles y Permisos
                    </a>
                </li>
            <?php endif; ?>

            <?php if($user?->hasAccessTo('can_employees')): ?>
                <li>
                    <a href="<?php echo e(route('employees.index')); ?>" class="<?php echo e(request()->routeIs('employees.*') ? 'active' : ''); ?>">
                        <i class="fa-solid fa-id-card"></i> Empleados
                    </a>
                </li>
            <?php endif; ?>

            <?php if($user?->hasAccessTo('can_users')): ?>
                <li>
                    <a href="<?php echo e(route('users.index')); ?>" class="<?php echo e(request()->routeIs('users.*') ? 'active' : ''); ?>">
                        <i class="fa-solid fa-users-gear"></i> Usuarios
                    </a>
                </li>
            <?php endif; ?>

            <?php if($user?->hasAccessTo('can_suppliers')): ?>
                <li>
                    <a href="<?php echo e(route('suppliers.index')); ?>" class="<?php echo e(request()->routeIs('suppliers.*') ? 'active' : ''); ?>">
                        <i class="fa-solid fa-truck-field"></i> Proveedores
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>
    </ul>
</nav><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>