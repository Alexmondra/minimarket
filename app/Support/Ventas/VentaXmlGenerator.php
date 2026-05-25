<?php

namespace App\Support\Ventas;

use App\Models\Documento;

class VentaXmlGenerator
{
    public function generar(Documento $documento): string
    {
        $documento->loadMissing([
            'empresa.empresaConfig',
            'cliente',
            'sucursal',
            'detalles.presentacion.unidadMedida',
        ]);

        $items = $documento->detalles->map(function ($detalle): string {
            return sprintf(
                "    <detalle>\n      <producto>%s</producto>\n      <cantidad>%s</cantidad>\n      <unidad>%s</unidad>\n      <precio_unitario>%.2f</precio_unitario>\n      <valor_unitario>%.2f</valor_unitario>\n      <igv>%.2f</igv>\n      <total>%.2f</total>\n    </detalle>",
                htmlspecialchars($detalle->producto_nombre, ENT_XML1),
                number_format((float) $detalle->cantidad, 3, '.', ''),
                htmlspecialchars($detalle->presentacion?->unidadMedida?->abreviatura ?? 'NIU', ENT_XML1),
                (float) $detalle->precio_unitario,
                (float) $detalle->valor_unitario,
                (float) $detalle->total_igv,
                (float) $detalle->total_linea,
            );
        })->implode("\n");

        $empresaRuc = $documento->empresa?->ruc ?? '';
        $empresaRazonSocial = $documento->empresa?->razon_social ?? '';
        $sucursalNombre = $documento->sucursal?->nombre_sucursal ?? '';
        $sucursalDireccion = $documento->sucursal?->direccion ?? '';
        $clienteTipoDocumento = $documento->cliente?->tipo_documento ?? '';
        $clienteNumeroDocumento = $documento->cliente?->documento ?? '';
        $clienteNombre = $documento->cliente?->razon_social
            ?: trim(($documento->cliente?->nombre ?? '').' '.($documento->cliente?->apellido ?? ''));
        $fechaEmision = $documento->fecha_emision?->format('Y-m-d') ?? '';

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<documento>
  <empresa>
    <ruc>{$empresaRuc}</ruc>
    <razon_social><![CDATA[{$empresaRazonSocial}]]></razon_social>
  </empresa>
  <sucursal>
    <nombre><![CDATA[{$sucursalNombre}]]></nombre>
    <direccion><![CDATA[{$sucursalDireccion}]]></direccion>
  </sucursal>
  <comprobante>
    <tipo>{$documento->tipo_comprobante}</tipo>
    <serie>{$documento->serie}</serie>
    <numero>{$documento->numero}</numero>
    <fecha_emision>{$fechaEmision}</fecha_emision>
    <moneda>{$documento->tipo_moneda}</moneda>
    <medio_pago>{$documento->medio_pago}</medio_pago>
  </comprobante>
  <cliente>
    <tipo_documento>{$clienteTipoDocumento}</tipo_documento>
    <numero_documento>{$clienteNumeroDocumento}</numero_documento>
    <nombre><![CDATA[{$clienteNombre}]]></nombre>
  </cliente>
  <totales>
    <subtotal>{$documento->subtotal}</subtotal>
    <descuento>{$documento->total_descuento}</descuento>
    <igv>{$documento->total_igv}</igv>
    <total>{$documento->total_neto}</total>
  </totales>
  <detalles>
{$items}
  </detalles>
</documento>
XML;
    }
}
