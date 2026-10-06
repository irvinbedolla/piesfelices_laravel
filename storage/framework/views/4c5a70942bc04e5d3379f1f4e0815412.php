<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="container py-5 d-flex flex-column align-items-center justify-content-center min-vh-80">
        
        
        <div class="text-center mb-5">
            <h1 class="fw-bold text-dark display-5 mb-2">Gestión de Directorio</h1>
            <p class="text-secondary fs-5 m-0">Selecciona el tipo de registro que deseas administrar:</p>
        </div>

        
        <div class="row g-4 justify-content-center w-100" style="max-width: 900px;">
            
            
            <div class="col-12 col-md-6">
                <a href="<?php echo e(route('customers.index')); ?>" class="text-decoration-none">
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

            
            <div class="col-12 col-md-6">
                <a href="<?php echo e(route('patients.index')); ?>" class="text-decoration-none">
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

        
        <div class="mt-5 text-center">
            <a href="<?php echo e(route('dashboard')); ?>" class="text-muted text-decoration-none fw-semibold fs-6 hover-underline">
                <i class="fa-solid fa-arrow-left me-2"></i> Volver al Menú Principal
            </a>
        </div>

    </div>

    
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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/customers/directory.blade.php ENDPATH**/ ?>