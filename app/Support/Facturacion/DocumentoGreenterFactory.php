<?php

namespace App\Support\Facturacion;

use App\Models\Cliente;
use App\Models\DetalleDocumento;
use App\Models\Documento;
use App\Models\Empresa;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use NumberFormatter;
use RuntimeException;

class DocumentoGreenterFactory
{
    public function make(Documento $documento): Invoice
    {
        $documento->loadMissing([
            'empresa.empresaConfig',
            'sucursal.ubigeoRel',
            'cliente',
            'detalles.presentacion.unidadMedida',
        ]);

        if (! in_array($documento->tipo_comprobante, ['FACTURA', 'BOLETA'], true)) {
            throw new RuntimeException('El comprobante no aplica para envio SUNAT.');
        }

        $legends = [
            (new Legend)
                ->setCode('1000')
                ->setValue($this->montoEnLetras($this->money($documento->total_neto))),
        ];

        if ($documento->esExentoAmazonia()) {
            $legends[] = (new Legend)
                ->setCode('2001')
                ->setValue('BIENES TRANSFERIDOS EN LA AMAZONÍA REGIÓN SELVA PARA SER CONSUMIDOS EN LA MISMA');
        }

        return (new Invoice)
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101')
            ->setTipoDoc($documento->tipo_comprobante === 'FACTURA' ? '01' : '03')
            ->setSerie((string) $documento->serie)
            ->setCorrelativo(str_pad((string) $documento->numero, 8, '0', STR_PAD_LEFT))
            ->setFechaEmision($documento->fecha_emision?->toDateTime() ?? now()->toDateTime())
            ->setFormaPago(new FormaPagoContado)
            ->setTipoMoneda((string) ($documento->tipo_moneda ?: 'PEN'))
            ->setCompany($this->company($documento->empresa, $documento))
            ->setClient($this->client($documento->cliente))
            ->setMtoOperGravadas($this->money($documento->op_gravada))
            ->setMtoOperExoneradas($this->money($documento->op_exonerada))
            ->setMtoOperInafectas($this->money($documento->op_inafecta))
            ->setMtoIGV($this->money($documento->total_igv))
            ->setTotalImpuestos($this->money($documento->total_igv))
            ->setValorVenta($this->money($documento->subtotal))
            ->setSubTotal($this->money($documento->total_neto))
            ->setMtoImpVenta($this->money($documento->total_neto))
            ->setDetails($this->details($documento))
            ->setLegends($legends);
    }

    protected function company(Empresa $empresa, Documento $documento): Company
    {
        $ubigeo = $documento->sucursal?->ubigeoRel;

        return (new Company)
            ->setRuc((string) $empresa->ruc)
            ->setRazonSocial((string) $empresa->razon_social)
            ->setNombreComercial((string) $empresa->razon_social)
            ->setAddress(
                (new Address)
                    ->setUbigueo($ubigeo?->ubigeo ?: $ubigeo?->codigo ?: null)
                    ->setDepartamento($ubigeo?->departamento ?: '-')
                    ->setProvincia($ubigeo?->provincia ?: '-')
                    ->setDistrito($ubigeo?->distrito ?: '-')
                    ->setDireccion($documento->sucursal?->direccion ?: $empresa->direccion_fiscal ?: '-')
                    ->setCodLocal($this->codigoLocal($documento->sucursal?->codigo))
            );
    }

    protected function client(?Cliente $cliente): Client
    {
        return (new Client)
            ->setTipoDoc($this->tipoDocumentoCliente($cliente?->tipo_documento))
            ->setNumDoc((string) ($cliente?->documento ?: '00000000'))
            ->setRznSocial((string) ($cliente?->razon_social ?: trim(($cliente?->nombre ?? 'Cliente').' '.($cliente?->apellido ?? 'Varios'))))
            ->setAddress((new Address)->setDireccion($cliente?->direccion ?: '-'));
    }

    /**
     * @return array<int, SaleDetail>
     */
    protected function details(Documento $documento): array
    {
        return $documento->detalles
            ->values()
            ->map(fn (DetalleDocumento $detalle, int $index): SaleDetail => $this->detail($detalle, $index + 1, $documento))
            ->all();
    }

    protected function detail(DetalleDocumento $detalle, int $index, Documento $documento): SaleDetail
    {
        $tipoAfectacion = strtoupper(trim((string) $detalle->tipo_afectacion));
        $tipAfeIgv = match ($tipoAfectacion) {
            '10', 'GRAVADO' => '10',
            '30', 'INAFECTO' => '30',
            default => '20',
        };
        $porcentajeIgv = $tipAfeIgv === '10' ? $this->money($documento->porcentaje_igv) : 0.0;
        $igv = $tipAfeIgv === '10' ? $this->money($detalle->total_igv) : 0.00;

        return (new SaleDetail)
            ->setCodProducto((string) ($detalle->producto_presentacion_id ?: $index))
            ->setUnidad($this->unidadSunat($detalle->presentacion?->unidadMedida?->abreviatura))
            ->setCantidad((float) $detalle->cantidad)
            ->setDescripcion((string) $detalle->producto_nombre)
            ->setMtoBaseIgv($this->money($detalle->subtotal_neto))
            ->setPorcentajeIgv($porcentajeIgv)
            ->setIgv($igv)
            ->setTipAfeIgv($tipAfeIgv)
            ->setTotalImpuestos($igv)
            ->setMtoValorVenta($this->money($detalle->subtotal_neto))
            ->setMtoValorUnitario($this->money($detalle->valor_unitario))
            ->setMtoPrecioUnitario($this->money($detalle->precio_unitario));
    }

    protected function tipoDocumentoCliente(?string $tipoDocumento): string
    {
        return match (strtoupper((string) $tipoDocumento)) {
            'RUC' => '6',
            'CE', 'CARNET_EXTRANJERIA' => '4',
            'PASAPORTE' => '7',
            default => '1',
        };
    }

    protected function montoEnLetras(float $total): string
    {
        $entero = (int) floor($total);
        $centimos = (int) round(($total - $entero) * 100);
        $letras = number_format($entero, 0, '.', '');

        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter('es_PE', NumberFormatter::SPELLOUT);
            $letras = mb_strtoupper((string) $formatter->format($entero));
        }

        return sprintf('SON %s CON %02d/100 SOLES', $letras, $centimos);
    }

    protected function codigoLocal(?string $codigo): string
    {
        return preg_match('/^\d{4}$/', (string) $codigo) ? (string) $codigo : '0000';
    }

    protected function unidadSunat(?string $unidad): string
    {
        $unidad = strtoupper(trim((string) $unidad));

        return match ($unidad) {
            'NIU', 'ZZ', 'KGM', 'LTR', 'MTR', 'BX' => $unidad,
            default => 'NIU',
        };
    }

    /**
     * Crea un objeto Greenter Note (Nota de Crédito) para anular un documento.
     */
    public function makeNotaCredito(Documento $nota, Documento $documentoAfectado): Note
    {
        $nota->loadMissing([
            'empresa.empresaConfig',
            'sucursal.ubigeoRel',
            'cliente',
            'documentoReferencia',
            'detalles.presentacion.unidadMedida',
        ]);

        $documentoAfectado->loadMissing([
            'empresa.empresaConfig',
            'sucursal.ubigeoRel',
            'cliente',
            'detalles.presentacion.unidadMedida',
        ]);

        $tipoDocAfectado = $documentoAfectado->tipo_comprobante === 'FACTURA' ? '01' : '03';

        $ref = $nota->documentoReferencia;

        $serieOriginal = (string) $nota->serie;
        if (str_starts_with($serieOriginal, 'NC') || str_starts_with($serieOriginal, 'N')) {
            $prefix = $tipoDocAfectado === '01' ? 'FC' : 'BC';
            $suffixDigits = preg_replace('/[^0-9]/', '', $serieOriginal);
            $fiscalSerie = $prefix . sprintf('%02d', (int) $suffixDigits ?: 1);
        } else {
            $fiscalSerie = $serieOriginal;
        }

        $note = new Note;
        $note
            ->setUblVersion('2.1')
            ->setTipDocAfectado($tipoDocAfectado)
            ->setNumDocfectado($documentoAfectado->serie.'-'.$documentoAfectado->numero)
            ->setCodMotivo($ref?->motivo_codigo ?? '01')   // 01 = Anulación
            ->setDesMotivo($ref?->motivo_descripcion ?? 'Anulación de comprobante')
            ->setTipoDoc('07')                               // 07 = Nota de Crédito
            ->setSerie($fiscalSerie)
            ->setCorrelativo(str_pad((string) $nota->numero, 8, '0', STR_PAD_LEFT))
            ->setFechaEmision($nota->fecha_emision?->toDateTime() ?? now()->toDateTime())
            ->setTipoMoneda((string) ($nota->tipo_moneda ?: 'PEN'))
            ->setCompany($this->company($nota->empresa, $nota))
            ->setClient($this->client($nota->cliente ?: $documentoAfectado->cliente));

        // Totales (mismos montos del documento afectado)
        $note
            ->setMtoOperGravadas($this->money($documentoAfectado->op_gravada))
            ->setMtoOperExoneradas($this->money($documentoAfectado->op_exonerada))
            ->setMtoOperInafectas($this->money($documentoAfectado->op_inafecta))
            ->setMtoIGV($this->money($documentoAfectado->total_igv))
            ->setTotalImpuestos($this->money($documentoAfectado->total_igv))
            ->setValorVenta($this->money($documentoAfectado->subtotal))
            ->setSubTotal($this->money($documentoAfectado->total_neto))
            ->setMtoImpVenta($this->money($documentoAfectado->total_neto));

        // Detalles (mismos items del documento afectado)
        $details = $documentoAfectado->detalles
            ->values()
            ->map(fn (DetalleDocumento $detalle, int $index): SaleDetail => $this->detail($detalle, $index + 1, $documentoAfectado))
            ->all();

        $note->setDetails($details);

        // Leyendas
        $legends = [
            (new Legend)
                ->setCode('1000')
                ->setValue($this->montoEnLetras($this->money($documentoAfectado->total_neto))),
        ];

        if ($nota->esExentoAmazonia()) {
            $legends[] = (new Legend)
                ->setCode('2001')
                ->setValue('BIENES TRANSFERIDOS EN LA AMAZONÍA REGIÓN SELVA PARA SER CONSUMIDOS EN LA MISMA');
        }

        $note->setLegends($legends);

        return $note;
    }

    protected function money(mixed $value): float
    {
        return round((float) $value, 2);
    }
}
