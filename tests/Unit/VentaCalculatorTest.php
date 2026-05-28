<?php

namespace Tests\Unit;

use App\Support\Ventas\VentaCalculator;
use PHPUnit\Framework\TestCase;

class VentaCalculatorTest extends TestCase
{
    public function test_calcula_igv_cuando_el_precio_ya_lo_incluye(): void
    {
        $calculator = new VentaCalculator;

        $resultado = $calculator->calcular([
            [
                'cantidad' => 2,
                'precio_unitario' => 11.80,
                'afecto_igv' => true,
            ],
        ], true, 18);

        $this->assertSame(23.60, $resultado['totales']['total_bruto']);
        $this->assertSame(20.00, $resultado['totales']['subtotal']);
        $this->assertSame(3.60, $resultado['totales']['total_igv']);
        $this->assertSame(23.60, $resultado['totales']['total_neto']);
    }

    public function test_calcula_igv_cuando_el_precio_no_lo_incluye_y_aplica_descuento(): void
    {
        $calculator = new VentaCalculator;

        $resultado = $calculator->calcular([
            [
                'cantidad' => 1,
                'precio_unitario' => 10.00,
                'afecto_igv' => true,
            ],
            [
                'cantidad' => 1,
                'precio_unitario' => 5.00,
                'afecto_igv' => false,
            ],
        ], false, 18, 2.00);

        $this->assertSame(15.00, $resultado['totales']['total_bruto']);
        $this->assertSame(2.00, $resultado['totales']['total_descuento']);
        $this->assertSame(8.67, $resultado['totales']['op_gravada']);
        $this->assertSame(1.56, $resultado['totales']['total_igv']);
        $this->assertSame(4.33, $resultado['totales']['op_exonerada']);
        $this->assertSame(14.56, $resultado['totales']['total_neto']);
    }
}
