<?php

namespace Tests\Feature;

use App\Models\Articulo;
use App\Models\Cliente;
use App\Models\InventarioStock;
use App\Models\Roles;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class FlujoCompletoMuebleriaTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $vendedor;
    protected User $cobrador;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear roles base
        $rolAdmin    = Roles::create(['id' => Str::uuid()->toString(), 'name' => 'administrador']);
        $rolVendedor = Roles::create(['id' => Str::uuid()->toString(), 'name' => 'vendedor']);
        $rolCobrador = Roles::create(['id' => Str::uuid()->toString(), 'name' => 'cobrador']);

        $this->admin = User::factory()->create(['username' => 'admin', 'is_active' => true]);
        $this->admin->roles()->attach($rolAdmin->id);

        $this->vendedor = User::factory()->create(['username' => 'vendedor1', 'is_active' => true]);
        $this->vendedor->roles()->attach($rolVendedor->id);

        $this->cobrador = User::factory()->create(['username' => 'cobrador1', 'is_active' => true]);
        $this->cobrador->roles()->attach($rolCobrador->id);
    }

    public function test_ciclo_completo_inventario_traspaso_venta_y_abonos(): void
    {
        // 1. ALTA DE ARTÍCULO EN ALMACÉN GENERAL (10 piezas)
        $resArt = $this->actingAs($this->admin)->post(route('articulos.store'), [
            'nombre'        => 'REFRIGERADOR PRUEBA 14P',
            'marca'         => 'MABE',
            'modelo'        => 'RMA14',
            'barras'        => '7501234567890',
            'pcompra'       => 5000,
            'pcontado'      => 7500,
            'pcredito'      => 9000,
            'stock_inicial' => 10,
            'stock_min'     => 2,
        ]);
        $resArt->assertRedirect(route('articulos.index'));

        $articulo = Articulo::where('barras', '7501234567890')->firstOrFail();
        $this->assertEquals(10, InventarioStock::where('articulo_id', $articulo->id)->whereNull('user_id')->value('cantidad'));

        // 2. BLOQUEO DE ARTÍCULO DUPLICADO
        $resDup = $this->actingAs($this->admin)->post(route('articulos.store'), [
            'nombre'        => 'REFRIGERADOR PRUEBA 14P',
            'marca'         => 'MABE',
            'modelo'        => 'RMA14',
            'pcompra'       => 5000,
            'pcontado'      => 7500,
            'pcredito'      => 9000,
            'stock_inicial' => 5,
            'stock_min'     => 2,
        ]);
        $resDup->assertSessionHas('error');

        // 3. TRASPASO DE 4 PIEZAS DE ALMACÉN A VENDEDOR
        $this->actingAs($this->admin)->post(route('articulos.traspaso.store'), [
            'articulo_id' => $articulo->id,
            'origen'      => 'almacen',
            'destino'     => $this->vendedor->id,
            'cantidad'    => 4,
        ])->assertRedirect(route('articulos.index'));

        $this->assertEquals(6, InventarioStock::where('articulo_id', $articulo->id)->whereNull('user_id')->value('cantidad'));
        $this->assertEquals(4, InventarioStock::where('articulo_id', $articulo->id)->where('user_id', $this->vendedor->id)->value('cantidad'));

        // 4. VENTA A CRÉDITO EN POS (1 pieza con $1,000 de anticipo)
        $clienteId = Str::uuid()->toString();
        DB::table('clientes')->insert([
            'id' => $clienteId, 'nombre' => 'JUAN PEREZ PRUEBA', 'status' => 'Activo',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $payloadVenta = [
            'cliente_id'        => $clienteId,
            'vendedor_id'       => $this->vendedor->id,
            'cobrador_id'       => $this->cobrador->id,
            'tipo_venta'        => 'Credito',
            'fecha_venta'       => date('Y-m-d'),
            'forma_pago'        => 'Efectivo',
            'subtotal'          => 9000,
            'total'             => 9000,
            'anticipo'          => 1000,
            'frecuencia_nombre' => 'Semanal',
            'num_pagos'         => 16,
            'cuota_pago'        => 500,
            'articulos'         => [[
                'id'              => $articulo->id,
                'cantidad'        => 1,
                'precio_unitario' => 9000,
                'subtotal'        => 9000,
            ]],
        ];

        $resVenta = $this->actingAs($this->vendedor)->postJson(route('ventas.store'), $payloadVenta);
        $resVenta->assertOk()->assertJson(['status' => 'success']);

        $ventaId = $resVenta->json('venta_id');
        $venta   = Venta::findOrFail($ventaId);

        $this->assertEquals(8000, (float) $venta->saldo_restante);
        $this->assertEquals(3, InventarioStock::where('articulo_id', $articulo->id)->where('user_id', $this->vendedor->id)->value('cantidad'));

        // 5. CANDADO ANTI-VENTA DUPLICADA MISMO DÍA / CLIENTE / ARTÍCULOS
        $this->actingAs($this->vendedor)
            ->postJson(route('ventas.store'), $payloadVenta)
            ->assertStatus(422);

        // 6. REGISTRO DE ABONO DE $500 Y LIQUIDACIÓN
        $this->actingAs($this->cobrador)->postJson(route('abonos.store'), [
            'id'    => $venta->id,
            'abono' => 500,
            'fecha' => date('Y-m-d'),
            'fpago' => 'Efectivo',
            'tipo'  => 1,
        ])->assertOk();

        $venta->refresh();
        $this->assertEquals(7500, (float) $venta->saldo_restante);

        // Liquidar cuenta completa
        $this->actingAs($this->cobrador)->postJson(route('abonos.store'), [
            'id'    => $venta->id,
            'abono' => 7500,
            'fecha' => date('Y-m-d'),
            'fpago' => 'Efectivo',
            'tipo'  => 2,
        ])->assertOk();

        $venta->refresh();
        $this->assertEquals(0, (float) $venta->saldo_restante);
        $this->assertEquals('Pagado', $venta->status);
    }
}