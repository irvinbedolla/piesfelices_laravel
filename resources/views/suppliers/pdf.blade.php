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
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f3e5f5; color: #4a148c; font-weight: bold; }
        tr:nth-child(even) { background-color: #fafafa; }
        .footer { margin-top: 30px; text-align: right; font-size: 10px; color: #888; }
    </style>
</head>
<body>

    <div class="header">
        <h2>PIES FELICES - DIRECTORIO DE PROVEEDORES</h2>
        <p>Fecha de impresión: {{ date('d/m/Y H:i') }}</p>
    </div>

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