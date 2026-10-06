<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $fecha1;
    protected $fecha2;
    protected $sucursal;

    public function __construct($fecha1, $fecha2, $sucursal = null)
    {
        $this->fecha1 = $fecha1;
        $this->fecha2 = $fecha2;
        $this->sucursal = $sucursal;
    }

    public function collection()
    {
        $query = Sale::whereBetween('venta_fecha', [$this->fecha1, $this->fecha2])
            ->where('venta_factura', 'SI')
            ->where('venta_tipo', '!=', 3);

        if ($this->sucursal && $this->sucursal !== 'TODOS') {
            $query->where('venta_sucursal', $this->sucursal);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'FOLIO / N° VENTA',
            'FECHA',
            'HORA',
            'CLIENTE',
            'RFC / CFDI',
            'SUCURSAL',
            'TIPO PAGO',
            'SUBTOTAL',
            'DESCUENTO',
            'TOTAL FACTURADO',
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->venta_id,
            $sale->venta_fecha,
            $sale->venta_hora,
            $sale->nombre_cliente,
            $sale->venta_cfdi ?? 'G03 - Gastos en general',
            $sale->venta_sucursal,
            $sale->venta_tipopago,
            number_format($sale->venta_total + $sale->venta_descuento, 2),
            number_format($sale->venta_descuento, 2),
            number_format($sale->venta_total, 2),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'color' => ['argb' => '8E24AA']],
            ],
        ];
    }
}