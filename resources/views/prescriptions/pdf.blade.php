<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Receta Medica #{{ $prescription->id }}</title>
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


        /* Tarjeta Destacada de Sucursal estilo Pies Felices */
        .branch-card-container {
            margin-top: 30px;
            width: 100%;
            background-color: #fff5f8; /* Fondo rosado muy suave */
            border: 1px solid #f8bbd0; /* Borde rosa claro */
            border-left: 5px solid #e91e63; /* Borde lateral rosa fucsia corporativo */
            border-radius: 6px;
            padding: 8px 12px;
            box-sizing: border-box;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        .branch-card-header {
            font-size: 10px;
            font-weight: bold;
            color: #880e4f; /* Color guinda/rosa oscuro */
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 4px;
            margin-bottom: 6px;
            border-bottom: 1px dashed #f48fb1;
        }

        .branch-card-header span {
            color: #e91e63; /* Fucsia para resaltar el nombre de la sucursal */
            font-size: 11px;
        }

        .branch-card-body {
            width: 100%;
            border-collapse: collapse !important;
            border: none !important;
        }

        .branch-card-body td {
            border: none !important;
            padding: 2px 0 !important;
            vertical-align: middle;
        }

        .col-address {
            text-align: left;
        }

        .col-phone {
            text-align: right;
            width: 180px;
        }

        .label-title {
            font-size: 9px;
            font-weight: bold;
            color: #ad1457;
        }

        .label-value {
            font-size: 9px;
            color: #333333;
        }

        .label-value-phone {
            font-size: 10px;
            font-weight: bold;
            color: #e91e63;
        }
    </style>
</head>
<body>

    <!-- Encabezado con Logotipos -->
    <table class="header-table">
        <tr>
            <td style="width: 20%;">
                @if(file_exists(public_path('images/logo_pdf.svg')))
                    <img src="{{ public_path('images/logo_pdf.svg') }}" class="logo-img" alt="Pies Felices">
                @elseif(file_exists(public_path('images/logo.png')))
                    <img src="{{ public_path('images/logo.png') }}" class="logo-img" alt="Pies Felices">
                @endif
            </td>
            <td style="width: 60%;" class="title-container">
                <div class="title-main">SOCIEDAD MEXICANA DE PODOLOGÍA MÉDICA A.C.</div>
                <div class="title-sub">PIES FELICES</div>
                <div style="font-size: 10px; font-weight: bold;">PODÓLOGO</div>
                <div style="font-size: 9px; color: #444;">CÉDULA PROFESIONAL: 12066211</div>
            </td>
            <td style="width: 20%; text-align: right;">
                @if(file_exists(public_path('images/logo_sociedad.svg')))
                    <img src="{{ public_path('images/logo_sociedad.svg') }}" class="logo-img" alt="Sociedad Mexicana de Podología">
                @elseif(file_exists(public_path('images/logo_sociedad.png')))
                    <img src="{{ public_path('images/logo_sociedad.png') }}" class="logo-img" alt="Sociedad Mexicana de Podología">
                @endif
            </td>
        </tr>
    </table>

    <!-- Bloque de Datos del Paciente y Cita -->
    <div class="info-box">
        <table class="info-table">
            <tr>
                <td><strong>Paciente:</strong> {{ mb_strtoupper($prescription->patient_name) }}</td>
                <td style="text-align: right;"><strong>Fecha:</strong> {{ $prescription->created_at->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Diagnóstico:</strong> {{ mb_strtoupper($prescription->diagnosis) }}</td>
                <td style="text-align: right;">
                    <strong>Próxima Cita:</strong> 
                    {{ $prescription->next_appointment ? \Carbon\Carbon::parse($prescription->next_appointment)->format('d/m/Y') : 'N/A' }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Indicaciones Médicas -->
    <div class="section-title">INDICACIONES / TRATAMIENTO</div>
    <div class="indications-content">
        {{ $prescription->indications }}
    </div>

    {{-- PIE DE PÁGINA: SUCURSAL DE ATENCIÓN --}}
    <div class="branch-card-container">
        <div class="branch-card-header">
            SUCURSAL DE ATENCIÓN: <span>{{ strtoupper($branch->name) }}</span>
        </div>
        
        <table class="branch-card-body">
            <tr>
                <td class="col-address">
                    <span class="label-title">Dirección:</span> 
                    <span class="label-value">{{ $branch->address ?? 'Pendiente' }}</span>
                </td>
                <td class="col-phone">
                    <span class="label-title">Teléfono:</span> 
                    <span class="label-value-phone">{{ $branch->phone ?? 'S/D' }}</span>
                </td>
            </tr>
        </table>
</div>

</body>
</html>