<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userRole = (int) ($user->role_id ?? $user->user_type ?? 2);
        $userBranch = $user->branch?->name ?? 'MATRIZ';

        // Cargar sucursales según el nivel de acceso
        if ($userRole === 1) {
            $branches = Branch::where('status', true)->get();
        } else {
            $branches = Branch::where('name', $userBranch)->get();
        }

        $podologists = User::where('status', 'Activo')->get();

        // Lista de artículos para el desplegable/datalist
        $products = SaleDetail::select('nombre', 'sku')
            ->whereNotNull('nombre')
            ->distinct()
            ->orderBy('nombre', 'asc')
            ->get();

        return view('reports.index', compact('branches', 'podologists', 'userRole', 'userBranch', 'products'));
    }

    /**
     * Reporte de Facturación (Soporta PDF y Excel, incluye numero_factura)
     */
    public function exportInvoices(Request $request)
    {
        $user = Auth::user();
        $userRole = (int) ($user->role_id ?? $user->user_type ?? 2);
        $userBranch = $user->branch?->name ?? 'MATRIZ';

        $fecha1   = $request->get('fecha1', date('Y-m-d'));
        $fecha2   = $request->get('fecha2', date('Y-m-d'));
        $sucursal = $request->get('sucursal', 'TODOS');
        $formato  = $request->get('formato', 'excel');

        // Control de permisos de sucursal para Rol 2
        if ($userRole !== 1) {
            $sucursal = $userBranch;
        }

        $query = Sale::whereBetween('venta_fecha', [$fecha1, $fecha2])
            ->where('venta_factura', 'SI')
            ->where('venta_tipo', '!=', 3);

        if ($sucursal !== 'TODOS' && !empty($sucursal)) {
            $query->where('venta_sucursal', $sucursal);
        }

        $sales = $query->orderBy('venta_fecha', 'desc')->get();

        // GENERACIÓN EN EXCEL
        if ($formato === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Facturación');

            $headers = [
                'A1' => 'FOLIO / N° VENTA',
                'B1' => 'N° FACTURA',
                'C1' => 'FECHA',
                'D1' => 'HORA',
                'E1' => 'CLIENTE',
                'F1' => 'RFC / CFDI',
                'G1' => 'SUCURSAL',
                'H1' => 'TIPO PAGO',
                'I1' => 'SUBTOTAL',
                'J1' => 'DESCUENTO',
                'K1' => 'TOTAL FACTURADO',
            ];

            foreach ($headers as $cell => $text) {
                $sheet->setCellValue($cell, $text);
            }

            $sheet->getStyle('A1:K1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8E24AA']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            $row = 2;
            foreach ($sales as $sale) {
                $subtotal = $sale->venta_total + $sale->venta_descuento;

                $sheet->setCellValue('A' . $row, $sale->venta_id);
                $sheet->setCellValue('B' . $row, $sale->numero_factura ?? 'S/N');
                $sheet->setCellValue('C' . $row, $sale->venta_fecha);
                $sheet->setCellValue('D' . $row, $sale->venta_hora);
                $sheet->setCellValue('E' . $row, $sale->nombre_cliente ?? 'Cliente General');
                $sheet->setCellValue('F' . $row, $sale->venta_cfdi ?? 'G03');
                $sheet->setCellValue('G' . $row, $sale->venta_sucursal);
                $sheet->setCellValue('H' . $row, $sale->venta_tipopago);
                $sheet->setCellValue('I' . $row, $subtotal);
                $sheet->setCellValue('J' . $row, $sale->venta_descuento);
                $sheet->setCellValue('K' . $row, $sale->venta_total);

                $sheet->getStyle("I{$row}:K{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
                $row++;
            }

            foreach (range('A', 'K') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $fileName = "Reporte_Facturacion_{$fecha1}_al_{$fecha2}.xlsx";
            $writer = new Xlsx($spreadsheet);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"{$fileName}\"");
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;
        }

        // GENERACIÓN EN PDF
        $pdf = Pdf::loadView('reports.pdf_invoices', compact('sales', 'fecha1', 'fecha2', 'sucursal'));
        return $pdf->setPaper('letter', 'landscape')->stream("Reporte_Facturacion_{$fecha1}_al_{$fecha2}.pdf");
    }

    /**
     * Alias por compatibilidad
     */
    public function exportInvoicesExcel(Request $request)
    {
        return $this->exportInvoices($request);
    }

    /**
     * Reporte de Citas (Soporta PDF y Excel)
     */
    public function exportAppointments(Request $request)
    {
        $user = Auth::user();
        $userRole = (int) ($user->role_id ?? $user->user_type ?? 2);
        $userBranch = $user->branch?->name ?? 'MATRIZ';

        $fecha1     = $request->get('fecha1', date('Y-m-01'));
        $fecha2     = $request->get('fecha2', date('Y-m-d'));
        $sucursal   = $request->get('sucursal', 'TODOS');
        $podologoId = $request->get('podologo_id');
        $formato    = $request->get('formato', 'pdf');

        if ($userRole !== 1) {
            $sucursal = $userBranch;
        }

        $dateColumn = 'created_at';
        if (Schema::hasColumn('appointments', 'cita_fecha')) {
            $dateColumn = 'cita_fecha';
        } elseif (Schema::hasColumn('appointments', 'fecha_cita')) {
            $dateColumn = 'fecha_cita';
        } elseif (Schema::hasColumn('appointments', 'fecha')) {
            $dateColumn = 'fecha';
        }

        $query = Appointment::whereBetween($dateColumn, [$fecha1 . ' 00:00:00', $fecha2 . ' 23:59:59']);

        if ($sucursal !== 'TODOS' && !empty($sucursal)) {
            $query->where('sucursal', $sucursal);
        }

        if ($podologoId && $podologoId !== 'TODOS') {
            $query->where(function($q) use ($podologoId) {
                $q->where('user_id', $podologoId)
                  ->orWhere('podologo_id', $podologoId);
            });
        }

        $rawAppointments = $query->orderBy($dateColumn, 'asc')->get();

        $appointments = $rawAppointments->map(function($app) {
            return [
                'id'       => $app->id ?? $app->cita_id ?? '#',
                'fecha'    => $app->cita_fecha ?? $app->fecha_cita ?? $app->fecha ?? 'N/A',
                'hora'     => $app->cita_hora ?? $app->hora_cita ?? $app->hora ?? 'N/A',
                'cliente'  => $app->nombre_cliente ?? $app->paciente ?? $app->cliente ?? 'Cliente General',
                'telefono' => $app->telefono ?? $app->celular ?? 'S/N',
                'podologo' => $app->podologo_nombre ?? $app->podologo ?? $app->user?->name ?? 'Sin asignar',
                'servicio' => $app->servicio ?? $app->motivo ?? 'Consulta Podológica',
                'sucursal' => $app->sucursal ?? 'MATRIZ',
            ];
        });

        if ($formato === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Citas');

            $headers = ['A1' => '# ID', 'B1' => 'FECHA', 'C1' => 'HORA', 'D1' => 'PACIENTE', 'E1' => 'TELÉFONO', 'F1' => 'PODÓLOGO', 'G1' => 'SERVICIO', 'H1' => 'SUCURSAL'];
            foreach ($headers as $cell => $text) {
                $sheet->setCellValue($cell, $text);
            }

            $row = 2;
            foreach ($appointments as $app) {
                $sheet->setCellValue('A' . $row, $app['id']);
                $sheet->setCellValue('B' . $row, $app['fecha']);
                $sheet->setCellValue('C' . $row, $app['hora']);
                $sheet->setCellValue('D' . $row, $app['cliente']);
                $sheet->setCellValue('E' . $row, $app['telefono']);
                $sheet->setCellValue('F' . $row, $app['podologo']);
                $sheet->setCellValue('G' . $row, $app['servicio']);
                $sheet->setCellValue('H' . $row, $app['sucursal']);
                $row++;
            }

            $fileName = "Reporte_Citas_{$fecha1}_al_{$fecha2}.xlsx";
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"{$fileName}\"");
            $writer->save('php://output');
            exit;
        }

        $pdf = Pdf::loadView('reports.pdf_citas', compact('appointments', 'fecha1', 'fecha2', 'sucursal'));
        return $pdf->setPaper('letter', 'landscape')->stream("Reporte_Citas_{$fecha1}_al_{$fecha2}.pdf");
    }

    /**
     * Reporte Especial de Ventas por Artículo
     */
    public function exportSalesReport(Request $request)
    {
        $user = Auth::user();
        $userRole = (int) ($user->role_id ?? $user->user_type ?? 2);
        $userBranch = $user->branch?->name ?? 'MATRIZ';

        $fecha1    = $request->get('fecha1', date('Y-m-01'));
        $fecha2    = $request->get('fecha2', date('Y-m-d'));
        $sucursal  = $request->get('sucursal', 'MATRIZ');
        $usuarioId = $request->get('usuario_id');
        $articulo  = trim($request->get('articulo'));
        $formato   = $request->get('formato', 'pdf');

        if ($userRole !== 1) {
            $sucursal = $userBranch;
        }

        $selects = [
            'ventas.venta_id',
            'ventas.venta_fecha',
            'ventas.venta_hora',
            'ventas.nombre_cliente',
            'ventas.venta_sucursal',
            'ventas.venta_tipopago',
            'venta_producto.nombre as producto_nombre',
            'venta_producto.sku',
            'venta_producto.cantidad',
            'venta_producto.precio'
        ];

        $query = DB::table('venta_producto')
            ->join('ventas', 'venta_producto.venta_id', '=', 'ventas.venta_id')
            ->selectRaw(implode(', ', $selects))
            ->whereBetween('ventas.venta_fecha', [$fecha1, $fecha2])
            ->where('ventas.venta_tipo', '!=', 3);

        if (!empty($sucursal) && $sucursal !== 'TODOS') {
            $query->where('ventas.venta_sucursal', $sucursal);
        }

        if (!empty($usuarioId) && $usuarioId !== 'TODOS') {
            $query->where("ventas.venta_idempleado", $usuarioId);
        }

        if (!empty($articulo)) {
            $query->where(function($q) use ($articulo) {
                $q->where('venta_producto.nombre', 'LIKE', "%{$articulo}%")
                  ->orWhere('venta_producto.sku', 'LIKE', "%{$articulo}%");
            });
        }

        $salesData = $query->orderBy('ventas.venta_fecha', 'desc')->get();

        if ($formato === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Ventas por Artículo');

            $headers = [
                'A1' => 'FOLIO VENTA', 'B1' => 'FECHA', 'C1' => 'HORA', 'D1' => 'CLIENTE',
                'E1' => 'ARTÍCULO / PRODUCTO', 'F1' => 'SKU', 'G1' => 'CANTIDAD', 'H1' => 'PRECIO UNIT.',
                'I1' => 'SUBTOTAL', 'J1' => 'SUCURSAL',
            ];

            foreach ($headers as $cell => $text) {
                $sheet->setCellValue($cell, $text);
            }

            $row = 2;
            $totalGeneral = 0;

            foreach ($salesData as $item) {
                $subtotal = $item->cantidad * $item->precio;
                $totalGeneral += $subtotal;

                $sheet->setCellValue('A' . $row, $item->venta_id);
                $sheet->setCellValue('B' . $row, $item->venta_fecha);
                $sheet->setCellValue('C' . $row, $item->venta_hora);
                $sheet->setCellValue('D' . $row, $item->nombre_cliente ?? 'Cliente General');
                $sheet->setCellValue('E' . $row, $item->producto_nombre);
                $sheet->setCellValue('F' . $row, $item->sku ?? 'S/N');
                $sheet->setCellValue('G' . $row, $item->cantidad);
                $sheet->setCellValue('H' . $row, $item->precio);
                $sheet->setCellValue('I' . $row, $subtotal);
                $sheet->setCellValue('J' . $row, $item->venta_sucursal);

                $sheet->getStyle("H{$row}:I{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
                $row++;
            }

            $fileName = "Reporte_Ventas_Articulo_{$fecha1}_al_{$fecha2}.xlsx";
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"{$fileName}\"");
            $writer->save('php://output');
            exit;
        }

        $pdf = Pdf::loadView('reports.pdf_articulos', compact('salesData', 'fecha1', 'fecha2', 'sucursal', 'articulo'));
        return $pdf->setPaper('letter', 'landscape')->stream("Reporte_Ventas_Articulo_{$fecha1}_al_{$fecha2}.pdf");
    }
}