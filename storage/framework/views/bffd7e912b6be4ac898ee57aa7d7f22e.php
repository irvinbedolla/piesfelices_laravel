<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Receta Medica #<?php echo e($prescription->id); ?></title>
    <style>
        @page { margin: 15px; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            margin: 0;
            padding: 10px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .logo-sociedad {
            width: 70px;
            height: auto;
        }
        .logo-pies {
            width: 100px;
            height: auto;
        }
        .title-container {
            text-align: center;
        }
        .title-main {
            color: #0b3c5d;
            font-size: 13px;
            font-weight: bold;
            margin: 0;
        }
        .title-sub {
            color: #d81b60;
            font-size: 16px;
            font-weight: bold;
            margin: 2px 0;
        }
        .info-box {
            border: 1px solid #d81b60;
            border-radius: 5px;
            padding: 8px;
            margin-bottom: 12px;
        }
        .info-table {
            width: 100%;
        }
        .info-table td {
            padding: 2px 0;
            font-size: 11px;
        }
        .section-title {
            color: #d81b60;
            font-weight: bold;
            font-size: 12px;
            border-bottom: 2px solid #d81b60;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }
        .indications-content {
            font-size: 11px;
            line-height: 1.5;
            min-height: 120px;
            white-space: pre-line;
        }
        .footer-table {
            width: 100%;
            margin-top: 15px;
            border-top: 1px solid #ccc;
            padding-top: 8px;
            font-size: 9px;
            color: #555;
        }
        .logo-img { height: 50px; width: auto; max-width: 160px; }
    </style>
</head>
<body>

    <!-- Encabezado con Logotipos -->
    <table class="header-table">
        <tr>
            <td style="width: 20%;">
                <?php if(file_exists(public_path('images/logo_pdf.svg'))): ?>
                    <img src="<?php echo e(public_path('images/logo_pdf.svg')); ?>" class="logo-img" alt="Pies Felices">
                <?php elseif(file_exists(public_path('images/logo.png'))): ?>
                    <img src="<?php echo e(public_path('images/logo.png')); ?>" class="logo-img" alt="Pies Felices">
                <?php endif; ?>
            </td>
            <td style="width: 60%;" class="title-container">
                <div class="title-main">SOCIEDAD MEXICANA DE PODOLOGÍA MÉDICA A.C.</div>
                <div class="title-sub">PIES FELICES</div>
                <div style="font-size: 10px; font-weight: bold;">PODÓLOGO</div>
                <div style="font-size: 9px; color: #444;">CÉDULA PROFESIONAL: 12066211</div>
            </td>
            <td style="width: 20%; text-align: right;">
                <?php if(file_exists(public_path('images/logo_sociedad.svg'))): ?>
                    <img src="<?php echo e(public_path('images/logo_sociedad.svg')); ?>" class="logo-img" alt="Sociedad Mexicana de Podología">
                <?php elseif(file_exists(public_path('images/logo_sociedad.png'))): ?>
                    <img src="<?php echo e(public_path('images/logo_sociedad.png')); ?>" class="logo-img" alt="Sociedad Mexicana de Podología">
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- Bloque de Datos del Paciente y Cita -->
    <div class="info-box">
        <table class="info-table">
            <tr>
                <td><strong>Paciente:</strong> <?php echo e(mb_strtoupper($prescription->patient_name)); ?></td>
                <td style="text-align: right;"><strong>Fecha:</strong> <?php echo e($prescription->created_at->format('d/m/Y')); ?></td>
            </tr>
            <tr>
                <td><strong>Diagnóstico:</strong> <?php echo e(mb_strtoupper($prescription->diagnosis)); ?></td>
                <td style="text-align: right;">
                    <strong>Próxima Cita:</strong> 
                    <?php echo e($prescription->next_appointment ? \Carbon\Carbon::parse($prescription->next_appointment)->format('d/m/Y') : 'N/A'); ?>

                </td>
            </tr>
        </table>
    </div>

    <!-- Indicaciones Médicas -->
    <div class="section-title">INDICACIONES / TRATAMIENTO</div>
    <div class="indications-content">
        <?php echo e($prescription->indications); ?>

    </div>

    <!-- Pie de Página con Datos Dinámicos de la Sucursal -->
    <table class="footer-table">
        <tr>
            <td style="width: 50%;">
                <strong>Contacto:</strong> <?php echo e($branch->phone ?? $branch->telefono ?? '443 138 0409'); ?><br>
                <strong>Dirección:</strong> <?php echo e($branch->address ?? $branch->direccion ?? 'Limón 91, Morelia, 58090, Mich, MX'); ?>

            </td>
            <td style="width: 50%; text-align: right;">
                <strong>Sucursal:</strong> <?php echo e($prescription->branch_name); ?><br>
                <strong>Redes:</strong> <?php echo e($branch->social_media ?? $branch->redes ?? 'PIESFELICES.16'); ?>

            </td>
        </tr>
    </table>

</body>
</html><?php /**PATH C:\Users\Irvin\OneDrive\Documentos\PiesFelices\resources\views/prescriptions/pdf.blade.php ENDPATH**/ ?>