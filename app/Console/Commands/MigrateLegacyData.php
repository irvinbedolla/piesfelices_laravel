<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\CreditPayment;
use App\Models\Product;

class MigrateLegacyData extends Command
{
    protected $signature = 'migrate:legacy-data';
    protected $description = 'Migra datos desde la base de datos legacy a la nueva estructura de Pies Felices';

    public function handle()
    {
        $this->info("Iniciando proceso de migración de datos...");

        // Usamos una transacción para asegurar que si falla algo, todo vuelva a su estado inicial
        DB::beginTransaction();

        try {
            // -------------------------------------------------------------
            // 1. MIGRACIÓN DE PRODUCTOS Y SERVICIOS
            // -------------------------------------------------------------
            $this->info("Migrando Catálogo de Productos...");
            $legacyProducts = DB::connection('legacy')->table('products')->get();

            foreach ($legacyProducts as $lp) {
                // Mapear campos de la tabla vieja a los del modelo de Laravel
                Product::updateOrCreate(
                    ['id' => $lp->id], // Respetar IDs originales para no romper relaciones
                    [
                        'name'        => $lp->nombre,
                        'price'       => $lp->precio,
                        'category_id' => $lp->id_categoria ?? 4, // Categoría por defecto si no existe
                        'type'        => $lp->tipo ?? 'GENERAL',
                        'stock'       => $lp->existencias ?? 0,
                    ]
                );
            }

            // -------------------------------------------------------------
            // 2. MIGRACIÓN DE VENTAS
            // -------------------------------------------------------------
            $this->info("Migrando Historial de Ventas...");
            $legacySales = DB::connection('legacy')->table('ventas_viejas')->get();

            foreach ($legacySales as $ls) {
                DB::table('ventas')->insertOrIgnore([
                    'venta_id'         => $ls->venta_id,
                    'venta_fecha'      => $ls->venta_fecha,
                    'venta_hora'       => $ls->venta_hora ?? '00:00:00',
                    'nombre_cliente'   => $ls->cliente ?? 'Cliente General',
                    'venta_idempleado' => $ls->id_empleado,
                    'venta_sucursal'   => $ls->sucursal,
                    'venta_tipopago'   => $ls->tipo_pago ?? 'Efectivo',
                    'venta_tipo'       => $ls->estatus_tipo, // 1: Contado, 2: Crédito, 3: Cancelado
                    'venta_descuento'  => $ls->descuento ?? 0,
                    'venta_total'      => $ls->total,
                    'venta_abono'      => $ls->abono_acumulado ?? 0,
                    'venta_restante'   => $ls->restante ?? 0,
                    'created_at'       => $ls->fecha_registro ?? now(),
                    'updated_at'       => now(),
                ]);
            }

            // -------------------------------------------------------------
            // 3. MIGRACIÓN DE DETALLES DE VENTAS (venta_producto)
            // -------------------------------------------------------------
            $this->info("Migrando Detalles de Ventas...");
            $legacyDetails = DB::connection('legacy')->table('detalle_ventas_viejas')->get();

            foreach ($legacyDetails as $ld) {
                DB::table('venta_producto')->insertOrIgnore([
                    'venta_id'    => $ld->venta_id,
                    'producto_id' => $ld->producto_id ?? 0, // 0 si es Consulta / Servicio
                    'cantidad'    => $ld->cantidad,
                    'precio'      => $ld->precio_unitario,
                    'nombre'      => $ld->nombre_producto ?? 'Servicio/Producto',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            // -------------------------------------------------------------
            // 4. MIGRACIÓN DE ABONOS A CRÉDITO (abonos)
            // -------------------------------------------------------------
            $this->info("Migrando Historial de Abonos...");
            $legacyPayments = DB::connection('legacy')->table('abonos_viejos')->get();

            foreach ($legacyPayments as $lp) {
                DB::table('abonos')->insertOrIgnore([
                    'abono_id'       => $lp->id,
                    'abono_venta'    => $lp->venta_id, // Foreign key a la tabla ventas
                    'abono_cantidad' => $lp->monto,
                    'abono_fecha'    => $lp->fecha,
                    'abono_sucursal' => $lp->sucursal,
                    'created_at'     => $lp->fecha ?? now(),
                    'updated_at'     => now(),
                ]);
            }

            // Confirmar transacción si todo fue exitoso
            DB::commit();
            $this->info("¡Migración completada con éxito!");

        } catch (\Exception $e) {
            // Deshacer todos los cambios en caso de error
            DB::rollBack();
            $this->error("Error durante la migración: " . $e->getMessage());
        }
    }
}