<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\User;
use App\Models\Loan;
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

        if ($userRole === 1) {
            $branches = Branch::where('status', true)->get();
        } else {
            $branches = Branch::where('name', $userBranch)->get();
        }

        $podologists = User::where('status', 'Activo')->get();

        $products = SaleDetail::select('nombre', 'sku')
            ->whereNotNull('nombre')
            ->distinct()
            ->orderBy('nombre', 'asc')
            ->get();

        return view('reports.index', compact('branches', 'podologists', 'userRole', 'userBranch', 'products'));
    }

    /**
     * Reporte de Ventas Normales / Contado (PDF y Excel)
     */
    public function exportNormalSales(Request $request)
    {
        $user = Auth::user();
        $userRole = (int) ($user->role_id ?? $user->user_type ?? 2);
        $userBranch = $user->branch?->name ?? 'MATRIZ';

        $fecha1    = $request->get('fecha1', date('Y-m-d'));
        $fecha2    = $request->get('fecha2', date('Y-m-d'));
        $sucursal  = $request->get('sucursal', 'TODOS');
        $usuarioId = $request->get('usuario_id', 'TODOS');
        $formato   = $request->get('formato', 'pdf');

        if ($userRole !== 1) {
            $sucursal = $userBranch;
        }

        $query = Sale::with('seller')
            ->whereBetween('venta_fecha', [$fecha1, $fecha2])
            // -------------------------------------------------------------
            // FILTROS ESTRICTOS: SOLO VENTAS PAGADAS Y NO CANCELADAS
            // -------------------------------------------------------------
            ->where('status', 'Pagado')
            ->whereIn('venta_tipo', [1, '1', 'PAGADO', 'CONTADO'])
            ->where('venta_tipo', '!=', 3)
            ->where('status', '!=', 'CANCELADO');

        // Filtro independiente por Sucursal
        if ($sucursal !== 'TODOS' && !empty($sucursal)) {
            $query->where('venta_sucursal', $sucursal);
        }

        // Filtro independiente por Usuario / Vendedor
        if ($usuarioId !== 'TODOS' && !empty($usuarioId)) {
            $query->where('venta_idempleado', $usuarioId);
        }

        $sales = $query->orderBy('venta_fecha', 'desc')
                    ->orderBy('venta_id', 'desc')
                    ->get();

        if ($formato === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Ventas Contado');

            $headers = [
                'A1' => 'FOLIO', 
                'B1' => 'FECHA', 
                'C1' => 'HORA', 
                'D1' => 'CLIENTE', 
                'E1' => 'VENDEDOR', 
                'F1' => 'SUCURSAL', 
                'G1' => 'TIPO PAGO', 
                'H1' => 'TOTAL'
            ];

            foreach ($headers as $cell => $text) {
                $sheet->setCellValue($cell, $text);
            }

            $sheet->getStyle('A1:H1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D6EFD']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            $row = 2;
            foreach ($sales as $s) {
                $vendedor = $s->seller->name ?? $s->seller->username ?? 'S/A';
                $sheet->setCellValue('A' . $row, $s->venta_id);
                $sheet->setCellValue('B' . $row, $s->venta_fecha);
                $sheet->setCellValue('C' . $row, $s->venta_hora);
                $sheet->setCellValue('D' . $row, $s->nombre_cliente ?? 'Cliente General');
                $sheet->setCellValue('E' . $row, $vendedor);
                $sheet->setCellValue('F' . $row, $s->venta_sucursal);
                $sheet->setCellValue('G' . $row, $s->venta_tipopago);
                $sheet->setCellValue('H' . $row, (float)$s->venta_total);
                $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
                $row++;
            }

            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $fileName = "Reporte_Ventas_Contado_{$fecha1}_al_{$fecha2}.xlsx";
            $writer = new Xlsx($spreadsheet);
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"{$fileName}\"");
            $writer->save('php://output');
            exit;
        }

        $pdf = Pdf::loadView('reports.pdf_normal_sales', compact('sales', 'fecha1', 'fecha2', 'sucursal', 'usuarioId'));
        return $pdf->setPaper('letter', 'landscape')->stream("Reporte_Ventas_Contado_{$fecha1}_al_{$fecha2}.pdf");
    }

    /**
     * Reporte de Ventas a Crédito (PDF y Excel)
     */
    public function exportCreditSales(Request $request)
    {
        $user = Auth::user();
        $userRole = (int) ($user->role_id ?? $user->user_type ?? 2);
        $userBranch = $user->branch?->name ?? 'MATRIZ';

        $fecha1    = $request->get('fecha1', date('Y-m-d'));
        $fecha2    = $request->get('fecha2', date('Y-m-d'));
        $sucursal  = $request->get('sucursal', 'TODOS');
        $usuarioId = $request->get('usuario_id', 'TODOS');
        $formato   = $request->get('formato', 'pdf');

        if ($userRole !== 1) {
            $sucursal = $userBranch;
        }

        $query = Sale::with(['seller', 'abonos'])
            ->whereBetween('venta_fecha', [$fecha1, $fecha2])
            // -------------------------------------------------------------
            // FILTROS ESTRICTOS: SOLO VENTAS PAGADAS/AUTORIZADAS Y NO CANCELADAS
            // -------------------------------------------------------------
            ->where('status', 'Pagado')
            ->whereIn('venta_tipo', [2, '2', 'CREDITO'])
            ->where('venta_tipo', '!=', 3)
            ->where('status', '!=', 'CANCELADO');

        // Filtro independiente por Sucursal
        if ($sucursal !== 'TODOS' && !empty($sucursal)) {
            $query->where('venta_sucursal', $sucursal);
        }

        // Filtro independiente por Usuario / Vendedor
        if ($usuarioId !== 'TODOS' && !empty($usuarioId)) {
            $query->where('venta_idempleado', $usuarioId);
        }

        $sales = $query->orderBy('venta_fecha', 'desc')
                    ->orderBy('venta_id', 'desc')
                    ->get();

        if ($formato === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Ventas a Crédito');

            $headers = [
                'A1' => 'FOLIO', 
                'B1' => 'FECHA', 
                'C1' => 'HORA', 
                'D1' => 'CLIENTE', 
                'E1' => 'VENDEDOR', 
                'F1' => 'SUCURSAL', 
                'G1' => 'TOTAL', 
                'H1' => 'ABONO', 
                'I1' => 'RESTANTE'
            ];

            foreach ($headers as $cell => $text) {
                $sheet->setCellValue($cell, $text);
            }

            $sheet->getStyle('A1:I1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF9800']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            $row = 2;
            foreach ($sales as $s) {
                $vendedor = $s->seller->name ?? $s->seller->username ?? 'S/A';
                
                // CÁLCULO SEGURO DEL RESTANTE
                $totalAmount = (float)($s->venta_total ?? 0);
                $abonoAmount = (float)($s->venta_abono ?? 0);
                $restante = isset($s->venta_restante) && $s->venta_restante > 0 
                    ? (float)$s->venta_restante 
                    : max(0, $totalAmount - $abonoAmount);

                $sheet->setCellValue('A' . $row, $s->venta_id);
                $sheet->setCellValue('B' . $row, $s->venta_fecha);
                $sheet->setCellValue('C' . $row, $s->venta_hora);
                $sheet->setCellValue('D' . $row, $s->nombre_cliente ?? 'Cliente General');
                $sheet->setCellValue('E' . $row, $vendedor);
                $sheet->setCellValue('F' . $row, $s->venta_sucursal);
                $sheet->setCellValue('G' . $row, $totalAmount);
                $sheet->setCellValue('H' . $row, $abonoAmount);
                $sheet->setCellValue('I' . $row, $restante);
                $sheet->getStyle("G{$row}:I{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
                $row++;
            }

            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $fileName = "Reporte_Ventas_Credito_{$fecha1}_al_{$fecha2}.xlsx";
            $writer = new Xlsx($spreadsheet);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"{$fileName}\"");
            $writer->save('php://output');
            exit;
        }

        $pdf = Pdf::loadView('reports.pdf_credit_sales', compact('sales', 'fecha1', 'fecha2', 'sucursal', 'usuarioId'));
        return $pdf->setPaper('letter', 'landscape')->stream("Reporte_Ventas_Credito_{$fecha1}_al_{$fecha2}.pdf");
    }

    public function exportLoansReport(Request $request)
    {
        $user = Auth::user();
        $userRole = (int) ($user->role_id ?? $user->user_type ?? 2);
        $userBranch = $user->branch?->name ?? 'MATRIZ';

        $fecha1   = $request->get('fecha1', date('Y-m-d'));
        $fecha2   = $request->get('fecha2', date('Y-m-d'));
        $sucursal = $request->get('sucursal', 'ALTOZANO');
        $formato  = $request->get('formato', 'pdf');

        if ($userRole !== 1) {
            $sucursal = $userBranch;
        }

        // 1. Obtener SÓLO los préstamos creados en el rango de fechas
        $queryLoans = DB::table('prestamos')
            ->whereBetween('pres_fecha', [$fecha1, $fecha2]);

        if ($sucursal !== 'TODOS' && !empty($sucursal)) {
            $queryLoans->where('sucursal', $sucursal);
        }

        $loans = $queryLoans->orderBy('pres_fecha', 'desc')->get();

        // Obtener los IDs de los préstamos filtrados
        $prestamoIds = $loans->pluck('pres_id')->toArray();

        // 2. Traer TODOS los abonos de esos préstamos (SIN filtrar por fechaPretamo)
        // Esto permite incluir abonos posteriores al rango (ej. día 21) tal como en el sistema legacy
        $abonos = [];
        $totalAbonos = 0;

        if (!empty($prestamoIds)) {
            $queryAbonos = DB::table('pago_prestamo')
                ->whereIn('idPrestamo', $prestamoIds);

            if ($sucursal !== 'TODOS' && !empty($sucursal)) {
                $queryAbonos->where('sucursal', $sucursal);
            }

            $abonos = $queryAbonos->get();
            // Suma acumulada idéntica a la variable $total_abono del 'while' legacy
            $totalAbonos = $abonos->sum('monto');
        }

        // 3. Totales exactos del reporte
        $totalPrestamos = $loans->sum('pres_costo');
        $totalRestante  = $totalPrestamos - $totalAbonos;

        // 4. Préstamos Personales (Fórmula legacy: Totalprestamo_pagos - total_abono)
        // $Totalprestamo_pagos en legacy sumaba los abonos cuya fechaPretamo SÍ estaba en el rango
        $queryPagosRango = DB::table('pago_prestamo')
            ->whereBetween('fechaPretamo', [$fecha1, $fecha2]);

        if ($sucursal !== 'TODOS' && !empty($sucursal)) {
            $queryPagosRango->where('sucursal', $sucursal);
        }

        $totalPrestamoPagos = $queryPagosRango->sum('monto');
        $prestamosPersonales = $totalPrestamoPagos - $totalAbonos;

        // Agrupar abonos por préstamo para pasarlos organizados a la vista Blade
        $abonosPorPrestamo = collect($abonos)->groupBy('idPrestamo');

        // EXCEL
        if ($formato === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Préstamos y Abonos');

            $headers = ['A1' => 'DESCRIPCIÓN', 'B1' => 'FECHA', 'C1' => 'SUCURSAL', 'D1' => 'TOTAL', 'E1' => 'RESTANTE'];
            foreach ($headers as $cell => $text) {
                $sheet->setCellValue($cell, $text);
            }

            $sheet->getStyle('A1:E1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E040FB']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            $row = 2;
            foreach ($loans as $l) {
                $sheet->setCellValue('A' . $row, $l->pres_descripcion);
                $sheet->setCellValue('B' . $row, $l->pres_fecha);
                $sheet->setCellValue('C' . $row, $l->sucursal);
                $sheet->setCellValue('D' . $row, $l->pres_costo);
                $sheet->setCellValue('E' . $row, $l->pres_costo - ($abonosPorPrestamo->get($l->pres_id)?->sum('monto') ?? 0));
                $sheet->getStyle("D{$row}:E{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
                $row++;
            }

            $fileName = "Reporte_Prestamos_{$fecha1}_al_{$fecha2}.xlsx";
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"{$fileName}\"");
            $writer->save('php://output');
            exit;
        }

        // PDF
        $pdf = Pdf::loadView('reports.pdf_loans', compact(
            'loans', 'abonosPorPrestamo', 'fecha1', 'fecha2', 'sucursal', 
            'totalPrestamos', 'totalAbonos', 'totalRestante', 'prestamosPersonales'
        ));

        return $pdf->setPaper('letter', 'portrait')->stream("Reporte_Prestamos_{$fecha1}_al_{$fecha2}.pdf");
    }

    public function exportInvoices(Request $request)
    {
        $user = Auth::user();
        $userRole = (int) ($user->role_id ?? $user->user_type ?? 2);
        $userBranch = $user->branch?->name ?? 'MATRIZ';

        $fecha1   = $request->get('fecha1', date('Y-m-d'));
        $fecha2   = $request->get('fecha2', date('Y-m-d'));
        $sucursal = $request->get('sucursal', 'TODOS');
        $formato  = $request->get('formato', 'excel');

        if ($userRole !== 1) {
            $sucursal = $userBranch;
        }

        // Cargamos la relación con 'customer' para asegurar el acceso al correo
        $query = Sale::with('customer')
            ->whereBetween('venta_fecha', [$fecha1, $fecha2])
            // -------------------------------------------------------------
            // FILTROS ESTRICTOS: SOLO VENTAS PAGADAS, FACTURADAS Y NO CANCELADAS
            // -------------------------------------------------------------
            ->where('status', 'Pagado')
            ->where('status', '!=', 'CANCELADO')
            ->where('venta_tipo', '!=', 3)
            ->where(function($q) {
                $q->where('venta_factura', 'SI')
                ->orWhere('venta_factura', 'si')
                ->orWhere('venta_factura', '1')
                ->orWhere('venta_factura', 1);
            });

        if ($sucursal !== 'TODOS' && !empty($sucursal)) {
            $query->where('venta_sucursal', $sucursal);
        }

        $sales = $query->orderBy('numero_factura', 'desc')
                    ->orderBy('venta_id', 'desc')
                    ->get();

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
                'F1' => 'CORREO',
                'G1' => 'RFC / CFDI', 
                'H1' => 'SUCURSAL', 
                'I1' => 'TIPO PAGO', 
                'J1' => 'SUBTOTAL', 
                'K1' => 'DESCUENTO', 
                'L1' => 'TOTAL FACTURADO'
            ];

            foreach ($headers as $cell => $text) {
                $sheet->setCellValue($cell, $text);
            }

            $sheet->getStyle('A1:L1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8E24AA']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            $row = 2;
            foreach ($sales as $sale) {
                $total     = (float)($sale->venta_total ?? 0);
                $descuento = (float)($sale->venta_descuento ?? 0);
                $subtotal  = $total + $descuento;
                
                // Prioridad: 1. correo_cliente de la venta | 2. correo de la tabla clientes | 3. 'N/A'
                $correoCliente = $sale->correo_cliente 
                    ?? $sale->customer?->correo 
                    ?? $sale->customer?->email 
                    ?? 'N/A';

                $sheet->setCellValue('A' . $row, $sale->venta_id);
                $sheet->setCellValue('B' . $row, $sale->numero_factura ?? 'S/N');
                $sheet->setCellValue('C' . $row, $sale->venta_fecha);
                $sheet->setCellValue('D' . $row, $sale->venta_hora);
                $sheet->setCellValue('E' . $row, $sale->nombre_cliente ?? 'Cliente General');
                $sheet->setCellValue('F' . $row, $correoCliente);
                $sheet->setCellValue('G' . $row, $sale->venta_cfdi ?? 'G03');
                $sheet->setCellValue('H' . $row, $sale->venta_sucursal);
                $sheet->setCellValue('I' . $row, $sale->venta_tipopago);
                $sheet->setCellValue('J' . $row, $subtotal);
                $sheet->setCellValue('K' . $row, $descuento);
                $sheet->setCellValue('L' . $row, $total);
                
                $sheet->getStyle("J{$row}:L{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
                $row++;
            }

            // Autoajustar ancho de columnas
            foreach (range('A', 'L') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $fileName = "Reporte_Facturacion_{$fecha1}_al_{$fecha2}.xlsx";
            $writer = new Xlsx($spreadsheet);
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"{$fileName}\"");
            $writer->save('php://output');
            exit;
        }

        $pdf = Pdf::loadView('reports.pdf_invoices', compact('sales', 'fecha1', 'fecha2', 'sucursal'));
        return $pdf->setPaper('letter', 'landscape')->stream("Reporte_Facturacion_{$fecha1}_al_{$fecha2}.pdf");
    }

    public function exportInvoicesExcel(Request $request)
    {
        return $this->exportInvoices($request);
    }

    public function exportAppointments(Request $request)
    {
        $user = Auth::user();
        $userRole = (int) ($user->role_id ?? $user->user_type ?? 2);
        $userBranch = $user->branch?->name ?? 'MATRIZ';

        $fecha1     = $request->get('fecha1', date('Y-m-d'));
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

    public function exportSalesReport(Request $request)
    {
        $user = Auth::user();
        $userRole = (int) ($user->role_id ?? $user->user_type ?? 2);
        $userBranch = $user->branch?->name ?? 'MATRIZ';

        $fecha1    = $request->get('fecha1', date('Y-m-d'));
        $fecha2    = $request->get('fecha2', date('Y-m-d'));
        $sucursal  = $request->get('sucursal', 'MATRIZ');
        $usuarioId = $request->get('usuario_id');
        $articulo  = trim($request->get('articulo'));
        $formato   = $request->get('formato', 'pdf');

        if ($userRole !== 1) {
            $sucursal = $userBranch;
        }

        $selects = [
            'ventas.venta_id', 'ventas.venta_fecha', 'ventas.venta_hora', 'ventas.nombre_cliente',
            'ventas.venta_sucursal', 'ventas.venta_tipopago', 'venta_producto.nombre as producto_nombre',
            'venta_producto.sku', 'venta_producto.cantidad', 'venta_producto.precio'
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

            $headers = ['A1' => 'FOLIO VENTA', 'B1' => 'FECHA', 'C1' => 'HORA', 'D1' => 'CLIENTE', 'E1' => 'ARTÍCULO', 'F1' => 'SKU', 'G1' => 'CANTIDAD', 'H1' => 'PRECIO UNIT.', 'I1' => 'SUBTOTAL', 'J1' => 'SUCURSAL'];
            foreach ($headers as $cell => $text) {
                $sheet->setCellValue($cell, $text);
            }

            $row = 2;
            foreach ($salesData as $item) {
                $subtotal = $item->cantidad * $item->precio;
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