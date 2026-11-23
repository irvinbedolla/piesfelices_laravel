<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use ICal\ICal;
use Carbon\Carbon;

class ImportarCitasIcs extends Command
{
    protected $signature = 'citas:importar {archivo} {sucursal} {--podologo=1} {--desde=2026-11-23}';
    protected $description = 'Importa citas futuras desde un archivo .ics de Google Calendar';

    public function handle()
    {
        $ruta = storage_path('app/calendars/' . $this->argument('archivo'));
        $sucursal = strtoupper(trim($this->argument('sucursal')));
        $podologoId = (int) $this->option('podologo');
        $fechaDesde = Carbon::parse($this->option('desde'))->startOfDay();

        if (!file_exists($ruta)) {
            $this->error("No se encontró el archivo: {$ruta}");
            return 1;
        }

        $ical = new ICal($ruta, [
            'defaultTimeZone' => 'America/Mexico_City',
            'filterDaysBefore' => 1,
        ]);

        // Expande citas recurrentes hasta 6 meses adelante
        $eventos = $ical->eventsFromRange(
            $fechaDesde->format('Y-m-d H:i:s'),
            $fechaDesde->copy()->addMonths(6)->format('Y-m-d H:i:s')
        );

        $insertadas = 0;
        $omitidas = 0;

        foreach ($eventos as $evento) {
            $inicio = Carbon::parse($evento->dtstart)->timezone('America/Mexico_City');
            $fin = $evento->dtend 
                ? Carbon::parse($evento->dtend)->timezone('America/Mexico_City') 
                : $inicio->copy()->addMinutes(30);

            if ($inicio->lt($fechaDesde)) {
                continue;
            }

            $titulo = mb_substr(trim($evento->summary ?? 'Cita importada'), 0, 250);
            $descripcionOriginal = trim($evento->description ?? '');

            // Evitar duplicados si vuelves a ejecutar el comando para la misma sucursal
            $yaExiste = DB::table('appointments')
                ->where('branch_name', $sucursal)
                ->where('start', $inicio->format('Y-m-d H:i:s'))
                ->where('title', $titulo)
                ->exists();

            if ($yaExiste) {
                $omitidas++;
                continue;
            }

            // Extraer teléfono de 10 dígitos del título o descripción
            preg_match('/\b\d{10}\b/', $titulo . ' ' . $descripcionOriginal, $matches);
            $telefono = $matches[0] ?? null;

            // Buscar cliente existente por teléfono (ajusta 'customers' o 'clientes' según tu tabla)
            $clienteId = null;
            if ($telefono) {
                $cliente = DB::table('customers')
                    ->where('phone', 'LIKE', "%{$telefono}%")
                    ->first();
                $clienteId = $cliente?->id;
            }

            // Armar notas conservando teléfono y descripción original
            $notasPartes = [];
            if ($telefono) {
                $notasPartes[] = "Tel: {$telefono}";
            }
            if ($descripcionOriginal !== '') {
                $notasPartes[] = $descripcionOriginal;
            }
            $notasFinal = !empty($notasPartes) ? implode("\n", $notasPartes) : null;

            DB::table('appointments')->insert([
                'title'         => $titulo,
                'start'         => $inicio->format('Y-m-d H:i:s'),
                'end'           => $fin->format('Y-m-d H:i:s'),
                'branch_name'   => $sucursal,
                'customer_id'   => $clienteId,
                'podiatrist_id' => $podologoId,
                'color'         => '#8e24aa',
                'status'        => 'Agendada',
                'notes'         => $notasFinal,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $insertadas++;
        }

        $this->info("Listo ({$sucursal}): {$insertadas} citas importadas, {$omitidas} duplicadas omitidas.");
        return 0;
    }
}