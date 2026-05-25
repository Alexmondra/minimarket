<?php

namespace App\Support\Ventas;

class VentaCalculator
{
    public function calcular(array $items, bool $preciosIncluyenImpuesto, float $tasaIgv, float $descuentoDocumento = 0): array
    {
        $factorIgv = 1 + ($tasaIgv / 100);
        $items = array_values($items);

        $totalesPrevios = array_sum(array_map(
            fn (array $item): float => round((float) $item['cantidad'] * (float) $item['precio_unitario'], 2),
            $items
        ));

        $descuentoDocumento = min(round($descuentoDocumento, 2), max($totalesPrevios, 0));

        $lineas = [];
        $descuentoDistribuido = 0.0;
        $ultimaLinea = count($items) - 1;

        foreach ($items as $index => $item) {
            $cantidad = round((float) $item['cantidad'], 3);
            $precioReferencia = round((float) $item['precio_unitario'], 2);
            $afectoIgv = (bool) ($item['afecto_igv'] ?? true);
            $subtotalBruto = round($cantidad * $precioReferencia, 2);

            $descuentoLinea = 0.0;
            if ($descuentoDocumento > 0 && $subtotalBruto > 0) {
                $descuentoLinea = $index === $ultimaLinea
                    ? round($descuentoDocumento - $descuentoDistribuido, 2)
                    : round($descuentoDocumento * ($subtotalBruto / $totalesPrevios), 2);
            }

            $descuentoDistribuido += $descuentoLinea;
            $subtotalConDescuento = round($subtotalBruto - $descuentoLinea, 2);

            if (! $afectoIgv) {
                $valorUnitario = $cantidad > 0 ? round($subtotalConDescuento / $cantidad, 6) : 0;
                $precioUnitarioFinal = $valorUnitario;
                $baseImponible = 0;
                $montoIgv = 0;
                $opExonerada = $subtotalConDescuento;
                $opInafecta = 0;
            } elseif ($preciosIncluyenImpuesto) {
                $baseImponible = round($subtotalConDescuento / $factorIgv, 2);
                $montoIgv = round($subtotalConDescuento - $baseImponible, 2);
                $valorUnitario = $cantidad > 0 ? round($baseImponible / $cantidad, 6) : 0;
                $precioUnitarioFinal = $cantidad > 0 ? round($subtotalConDescuento / $cantidad, 6) : 0;
                $opExonerada = 0;
                $opInafecta = 0;
            } else {
                $baseImponible = $subtotalConDescuento;
                $montoIgv = round($baseImponible * ($tasaIgv / 100), 2);
                $valorUnitario = $cantidad > 0 ? round($baseImponible / $cantidad, 6) : 0;
                $precioUnitarioFinal = $cantidad > 0 ? round(($baseImponible + $montoIgv) / $cantidad, 6) : 0;
                $subtotalConDescuento = round($baseImponible + $montoIgv, 2);
                $opExonerada = 0;
                $opInafecta = 0;
            }

            $lineas[] = [
                'cantidad' => $cantidad,
                'precio_unitario' => round($precioUnitarioFinal, 2),
                'valor_unitario' => round($valorUnitario, 2),
                'igv_unitario' => $cantidad > 0 ? round($montoIgv / $cantidad, 2) : 0,
                'total_igv' => round($montoIgv, 2),
                'descuento_total' => round($descuentoLinea, 2),
                'descuento_unitario' => $cantidad > 0 ? round($descuentoLinea / $cantidad, 2) : 0,
                'subtotal_bruto' => round($subtotalBruto, 2),
                'subtotal_descuento' => round($descuentoLinea, 2),
                'subtotal_neto' => round($subtotalConDescuento, 2),
                'total_linea' => round($subtotalConDescuento, 2),
                'op_gravada' => round($baseImponible, 2),
                'op_exonerada' => round($opExonerada, 2),
                'op_inafecta' => round($opInafecta, 2),
                'tipo_afectacion' => $afectoIgv ? '10' : '20',
            ];
        }

        return [
            'lineas' => $lineas,
            'totales' => [
                'total_bruto' => round(array_sum(array_column($lineas, 'subtotal_bruto')), 2),
                'total_descuento' => round(array_sum(array_column($lineas, 'subtotal_descuento')), 2),
                'subtotal' => round(array_sum(array_column($lineas, 'op_gravada')) + array_sum(array_column($lineas, 'op_exonerada')) + array_sum(array_column($lineas, 'op_inafecta')), 2),
                'total_neto' => round(array_sum(array_column($lineas, 'total_linea')), 2),
                'op_gravada' => round(array_sum(array_column($lineas, 'op_gravada')), 2),
                'op_exonerada' => round(array_sum(array_column($lineas, 'op_exonerada')), 2),
                'op_inafecta' => round(array_sum(array_column($lineas, 'op_inafecta')), 2),
                'total_igv' => round(array_sum(array_column($lineas, 'total_igv')), 2),
            ],
        ];
    }
}
