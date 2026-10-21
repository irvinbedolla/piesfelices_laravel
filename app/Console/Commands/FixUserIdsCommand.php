<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixUserIdsCommand extends Command
{
    /**
     * El nombre y firma del comando para la terminal.
     */
    protected $signature = 'users:fix-ids';

    /**
     * Descripción del comando.
     */
    protected $description = 'Actualiza los IDs de los usuarios y sincroniza sus referencias en las tablas hijas';

    /**
     * Ejecuta la lógica del comando.
     */
    public function handle()
    {
        $this->info('Iniciando actualización de IDs de usuarios...');

        // Mapeo de IDs: ['ID_ACTUAL_EN_USERS' => 'ID_DESEADO_PRODUCCION']
        // Agrega o modifica las parejas de IDs según lo requieras
        $mapaIds = [
            1 => 43,
            // Ejemplo de más usuarios:
            // 2 => 50,
            // 3 => 52,
        ];

        DB::beginTransaction();

        try {
            // 1. Desactivar la revisión de claves foráneas para esta sesión
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

            foreach ($mapaIds as $oldId => $newId) {
                $this->line("Procesando: ID {$oldId} ---> ID {$newId}");

                // Actualizar tabla principal de usuarios
                $updatedUsers = DB::table('users')->where('id', $oldId)->update(['id' => $newId]);

                // Actualizar tabla employees (si existe la columna user_id)
                if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'user_id')) {
                    DB::table('employees')->where('user_id', $oldId)->update(['user_id' => $newId]);
                }

                // Actualizar tabla supply_orders
                if (Schema::hasTable('supply_orders') && Schema::hasColumn('supply_orders', 'user_id')) {
                    DB::table('supply_orders')->where('user_id', $oldId)->update(['user_id' => $newId]);
                }

                // Actualizar tabla ventas
                if (Schema::hasTable('ventas') && Schema::hasColumn('ventas', 'venta_idempleado')) {
                    DB::table('ventas')->where('venta_idempleado', $oldId)->update(['venta_idempleado' => $newId]);
                }

                // Actualizar tabla abonos (si aplica)
                if (Schema::hasTable('abonos') && Schema::hasColumn('abonos', 'abono_usuario')) {
                    DB::table('abonos')->where('abono_usuario', $oldId)->update(['abono_usuario' => $newId]);
                }
            }

            // 2. Reactivar la revisión de claves foráneas
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

            DB::commit();

            $this->info('¡Los IDs de usuarios y sus tablas relacionadas se actualizaron con éxito!');

        } catch (\Exception $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            $this->error('Ocurrió un error al actualizar los IDs: ' . $e->getMessage());
        }

        return 0;
    }
}