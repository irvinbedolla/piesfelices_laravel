<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? 'SISTEMA | Pies Felices'); ?></title>

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

        /* LAYOUT ESTRUCTURA */
        #wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }

        /* SIDEBAR STYLES */
        #sidebar {
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            background: linear-gradient(180deg, #4a148c 0%, #7b1fa2 100%);
            color: #fff;
            transition: all 0.3s ease;
            min-height: 100vh;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
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

        /* CONTENT AREA */
        #content {
            width: 100%;
            min-height: 100vh;
            transition: all 0.3s;
        }

        .navbar-top {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<?php
    $user = Auth::user();
    $perm = $user?->permission;
?>

<div id="wrapper">
    <!-- SIDEBAR -->
    <nav id="sidebar">
        <div class="sidebar-header d-flex align-items-center gap-2">
            <i class="fa-solid fa-shoe-prints fs-4 text-warning"></i>
            <span class="fs-5 fw-bold text-white tracking-wide">PIES FELICES</span>
        </div>

        <ul class="list-unstyled components">
            <li>
                <a href="<?php echo e(route('dashboard')); ?>" class="<?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-chart-pie"></i> Dashboard
                </a>
            </li>

            <div class="sidebar-heading">Operación</div>
            <li><a href="#"><i class="fa-solid fa-hand-holding-dollar"></i> Vender</a></li>
            <?php if($perm?->can_sales): ?> <li><a href="#"><i class="fa-solid fa-receipt"></i> Ventas</a></li> <?php endif; ?>
            <?php if($perm?->can_inventory): ?> <li><a href="#"><i class="fa-solid fa-boxes-stacked"></i> Inventario</a></li> <?php endif; ?>
            <?php if($perm?->can_clients): ?> <li><a href="#"><i class="fa-solid fa-hospital-user"></i> Clientes / Pacientes</a></li> <?php endif; ?>

            <div class="sidebar-heading">Caja & Finanzas</div>
            <?php if($perm?->can_cash_closing): ?> <li><a href="#"><i class="fa-solid fa-vault"></i> Cierre de Caja</a></li> <?php endif; ?>
            <?php if($perm?->can_expenses): ?> <li><a href="#"><i class="fa-solid fa-cart-shopping"></i> Gastos</a></li> <?php endif; ?>
            <?php if($perm?->can_credit): ?> <li><a href="#"><i class="fa-regular fa-credit-card"></i> Créditos</a></li> <?php endif; ?>

            <div class="sidebar-heading">Administración</div>
            <?php if($user?->user_type == 0): ?>
                <li>
                    <a href="<?php echo e(route('branches.index')); ?>" class="<?php echo e(request()->routeIs('branches.*') ? 'active' : ''); ?>">
                        <i class="fa-solid fa-store"></i> Sucursales
                    </a>
                </li>
            <?php endif; ?>
            <?php if($perm?->can_users): ?>
                <li>
                    <a href="<?php echo e(route('users.index')); ?>" class="<?php echo e(request()->routeIs('users.*') ? 'active' : ''); ?>">
                        <i class="fa-solid fa-users-gear"></i> Usuarios
                    </a>
                </li>
            <?php endif; ?>
            <?php if($user?->isAdmin()): ?>
                <li>
                    <a href="<?php echo e(route('roles.index')); ?>" class="<?php echo e(request()->routeIs('roles.*') ? 'active' : ''); ?>">
                        <i class="fa-solid fa-user-shield"></i> Roles y Permisos
                    </a>
                </li>
            <?php endif; ?>
            <?php if($perm?->can_employees && $user?->user_type == 0): ?> <li><a href="#"><i class="fa-solid fa-id-card"></i> Empleados</a></li> <?php endif; ?>
            <?php if($perm?->can_suppliers && $user?->user_type == 0): ?> <li><a href="#"><i class="fa-solid fa-truck-field"></i> Proveedores</a></li> <?php endif; ?>
            <?php if($perm?->can_prescriptions): ?> <li><a href="#"><i class="fa-solid fa-photo-film"></i> Multimedia</a></li> <?php endif; ?>
        </ul>
    </nav>

    <!-- CONTENT -->
    <div id="content">
        <!-- TOP NAVBAR -->
        <nav class="navbar navbar-expand-lg navbar-top px-4 py-2">
            <div class="container-fluid p-0 d-flex justify-content-between align-items-center">
                <span class="text-secondary fw-semibold">
                    <i class="fa-solid fa-hospital me-2 text-primary"></i>Sucursal: <strong class="text-dark"><?php echo e($user->branch_name ?? 'MATRIZ'); ?></strong>
                </span>

                <div class="d-flex align-items-center gap-3">
                    <span class="small fw-semibold text-secondary">
                        <i class="fa-solid fa-circle-user me-1 text-primary"></i> <?php echo e($user->username); ?>

                    </span>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                            <i class="fa-solid fa-power-off me-1"></i> Salir
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <!-- MAIN VIEW -->
        <main class="p-4">
            <?php echo e($slot); ?>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/layouts/app.blade.php ENDPATH**/ ?>