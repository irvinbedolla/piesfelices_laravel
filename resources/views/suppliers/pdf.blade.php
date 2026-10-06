<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Proveedores - Pies Felices</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #7b1fa2; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #4a148c; }
        .header p { margin: 2px 0; color: #666; font-size: 11px; }

        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; border-bottom: 3px solid #8e24aa; padding-bottom: 8px; }
        .header-table td { vertical-align: middle; }
        .logo-img { height: 50px; width: auto; max-width: 160px; }
        .header-title { text-align: center; }
        .header-title h2 { color: #8e24aa; margin: 0; font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .header-title p { margin: 2px 0 0 0; font-weight: bold; color: #555; font-size: 11px; }

        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f3e5f5; color: #4a148c; font-weight: bold; }
        tr:nth-child(even) { background-color: #fafafa; }
        .footer { margin-top: 30px; text-align: right; font-size: 10px; color: #888; }
    </style>
</head>
<body>

    {{-- TABLA DE ENCABEZADO CON LOGOS EN AMBAS ESQUINAS --}}
    <table class="header-table">
        <tr>
            <td width="20%" style="text-align: left;">
                @if(file_exists(public_path('images/logo_pdf.svg')))
                    <img src="{{ public_path('images/logo_pdf.svg') }}" class="logo-img" alt="Pies Felices">
                @elseif(file_exists(public_path('images/logo.png')))
                    <img src="{{ public_path('images/logo.png') }}" class="logo-img" alt="Pies Felices">
                @endif
            </td>
            <td width="60%" class="header-title">
                <h2>PIES FELICES</h2>
                <p>Fecha de impresión: {{ date('d/m/Y H:i') }}</p>
            </td>
            <td width="20%" style="text-align: right;">
                @if(file_exists(public_path('images/logo_sociedad.svg')))
                    <img src="{{ public_path('images/logo_sociedad.svg') }}" class="logo-img" alt="Sociedad Mexicana de Podología">
                @elseif(file_exists(public_path('images/logo_sociedad.png')))
                    <img src="{{ public_path('images/logo_sociedad.png') }}" class="logo-img" alt="Sociedad Mexicana de Podología">
                @endif
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Empresa</th>
                <th>Contacto</th>
                <th>Teléfono</th>
                <th>Ciudad</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            @foreach($suppliers as $index => $s)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $s->company_name }}</strong></td>
                    <td>{{ $s->contact_name ?? 'N/A' }}</td>
                    <td>{{ $s->phone ?? 'N/A' }}</td>
                    <td>{{ $s->city ?? 'N/A' }}</td>
                    <td>{{ $s->email ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sistema de Gestión Integral Pies Felices
    </div>

</body>
</html>