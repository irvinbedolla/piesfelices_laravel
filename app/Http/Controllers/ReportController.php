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
        $userRole = $user->tipo_seccion ?? 2;
        $userBranch = $user->branch?->name ?? $user->branch_name ?? 'MATRIZ';

        $branches = Branch::all();
        $podologists = User::all();

        // Obtener la lista única de artículos/productos para el buscador
        $products = SaleDetail::select('nombre', 'sku')
            ->whereNotNull('nombre')
            ->distinct()
            ->orderBy('nombre', 'asc')
            ->get();

        return view('reports.index', compact('branches', 'podologists', 'userRole', 'userBranch', 'products'));
    }

    /**
     * Reporte Especial de Ventas por Artículo y Sucursal Principal
     */
    public function exportSalesReport(Request $request)
    {
        $fecha1 = $request->get('fecha1', date('Y-m-01'));
        $fecha2 = $request->get('fecha2', date('Y-m-d'));
        $sucursal = $request->get('sucursal', 'MATRIZ');
        $usuarioId = $request->get('usuario_id');
        $articulo = trim($request->get('articulo'));
        $formato = $request->get('formato', 'pdf');

        // 1. Detectar dinámicamente la columna de usuario/vendedor en 'ventas'
        $userColumn = null;
        $possibleUserCols = ['user_id', 'id_usuario', 'usuario_id', 'venta_usuario', 'vendedor_id', 'recibido_por', 'atendio'];
        foreach ($possibleUserCols as $col) {
            if (Schema::hasColumn('ventas', $col)) {
                $userColumn = $col;
                break;
            }
        }

        // 2. Construir la consulta dinámica
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

        if ($userColumn) {
            $selects[] = "ventas.{$userColumn} as usuario_venta_id";
        }

        $query = DB::table('venta_producto')
            ->join('ventas', 'venta_producto.venta_id', '=', 'ventas.venta_id')
            ->selectRaw(implode(', ', $selects))
            ->whereBetween('ventas.venta_fecha', [$fecha1, $fecha2])
            ->where('ventas.venta_tipo', '!=', 3); // Excluir cancelados

        // Filtro por sucursal
        if (!empty($sucursal) && $sucursal !== 'TODOS') {
            $query->where('ventas.venta_sucursal', $sucursal);
        }

        // Filtro por usuario sólo si existe la columna en 'ventas'
        if ($userColumn && !empty($usuarioId) && $usuarioId !== 'TODOS') {
            $query->where("ventas.{$userColumn}", $usuarioId);
        }

        // Búsqueda inteligente por nombre o SKU del artículo
        if (!empty($articulo)) {
            $query->where(function($q) use ($articulo) {
                $q->where('venta_producto.nombre', 'LIKE', "%{$articulo}%")
                  ->orWhere('venta_producto.sku', 'LIKE', "%{$articulo}%");
            });
        }

        $salesData = $query->orderBy('ventas.venta_fecha', 'desc')->get();

        // 3. GENERACIÓN EN EXCEL (.xlsx)
        if ($formato === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Ventas por Artículo');

            $headers = [
                'A1' => 'FOLIO VENTA',
                'B1' => 'FECHA',
                'C1' => 'HORA',
                'D1' => 'CLIENTE',
                'E1' => 'ARTÍCULO / PRODUCTO',
                'F1' => 'SKU',
                'G1' => 'CANTIDAD',
                'H1' => 'PRECIO UNIT.',
                'I1' => 'SUBTOTAL',
                'J1' => 'SUCURSAL',
            ];

            foreach ($headers as $cell => $text) {
                $sheet->setCellValue($cell, $text);
            }

            $sheet->getStyle('A1:J1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8E24AA']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(28);

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

                $sheet->getStyle("A{$row}:C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("H{$row}:I{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');

                $row++;
            }

            if (count($salesData) > 0) {
                $sheet->setCellValue('H' . $row, 'TOTAL:');
                $sheet->setCellValue('I' . $row, $totalGeneral);
                $sheet->getStyle("H{$row}:I{$row}")->getFont()->setBold(true);
                $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
            }

            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $fileName = "Reporte_Ventas_Articulo_{$fecha1}_al_{$fecha2}.xlsx";
            $writer = new Xlsx($spreadsheet);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"{$fileName}\"");
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;
        }

        // 4. GENERACIÓN EN PDF
        $pdf = Pdf::loadView('reports.pdf_articulos', compact('salesData', 'fecha1', 'fecha2', 'sucursal', 'articulo'));
        return $pdf->setPaper('letter', 'landscape')->stream("Reporte_Ventas_Articulo_{$fecha1}_al_{$fecha2}.pdf");
    }


    /**
     * Reporte de Citas (Soporta PDF y Excel)
     */
    public function exportAppointments(Request $request)
    {
        $fecha1 = $request->get('fecha1', date('Y-m-01'));
        $fecha2 = $request->get('fecha2', date('Y-m-d'));
        $sucursal = $request->get('sucursal');
        $podologoId = $request->get('podologo_id');
        $formato = $request->get('formato', 'pdf');

        // Detectar columna de fecha en la tabla 'appointments'
        $dateColumn = 'created_at';
        if (\Schema::hasColumn('appointments', 'cita_fecha')) {
            $dateColumn = 'cita_fecha';
        } elseif (\Schema::hasColumn('appointments', 'fecha_cita')) {
            $dateColumn = 'fecha_cita';
        } elseif (\Schema::hasColumn('appointments', 'fecha')) {
            $dateColumn = 'fecha';
        }

        $query = Appointment::whereBetween($dateColumn, [$fecha1 . ' 00:00:00', $fecha2 . ' 23:59:59']);

        if ($sucursal && $sucursal !== 'TODOS') {
            $query->where('sucursal', $sucursal);
        }

        if ($podologoId && $podologoId !== 'TODOS') {
            $query->where(function($q) use ($podologoId) {
                $q->where('user_id', $podologoId)
                  ->orWhere('podologo_id', $podologoId);
            });
        }

        $rawAppointments = $query->orderBy($dateColumn, 'asc')->get();

        // Mapear los datos garantizando que ningún campo quede vacío
        $appointments = $rawAppointments->map(function($app) {
            return [
                'id'       => $app->id ?? $app->cita_id ?? '#',
                'fecha'    => $app->cita_fecha ?? $app->fecha_cita ?? $app->fecha ?? 'N/A',
                'hora'     => $app->cita_hora ?? $app->hora_cita ?? $app->hora ?? 'N/A',
                'cliente'  => $app->nombre_cliente ?? $app->cliente_nombre ?? $app->paciente ?? $app->cliente ?? 'Cliente General',
                'telefono' => $app->telefono ?? $app->celular ?? $app->cliente_telefono ?? 'S/N',
                'podologo' => $app->podologo_nombre ?? $app->podologo ?? $app->user?->name ?? 'Sin asignar',
                'servicio' => $app->servicio ?? $app->motivo ?? $app->observaciones ?? 'Consulta Podológica',
                'sucursal' => $app->sucursal ?? 'MATRIZ',
            ];
        });

        // 1. GENERACIÓN EN EXCEL
        if ($formato === 'excel') {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
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
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"{$fileName}\"");
            $writer->save('php://output');
            exit;
        }

        // 2. GENERACIÓN EN PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf_citas', compact('appointments', 'fecha1', 'fecha2', 'sucursal'));
        return $pdf->setPaper('letter', 'landscape')->stream("Reporte_Citas_{$fecha1}_al_{$fecha2}.pdf");
    }
}