<?php

namespace Tests\Unit;

use App\Models\Cliente;
use App\Models\DetalleDocumento;
use App\Models\Documento;
use App\Models\Empresa;
use App\Models\ProductoPresentacion;
use App\Models\Sucursal;
use App\Models\UniMedida;
use App\Support\Facturacion\DocumentoGreenterFactory;
use Carbon\Carbon;
use Tests\TestCase;

class DocumentoGreenterFactoryTest extends TestCase
{
    public function test_mapea_boleta_a_documento_greenter_con_impuestos(): void
    {
        $empresa = new Empresa([
            'ruc' => '20123456789',
            'razon_social' => 'MINIMARKET SAC',
            'direccion_fiscal' => 'AV. PERU 123',
        ]);
        $empresa->setRelation('empresaConfig', null);

        $sucursal = new Sucursal([
            'codigo' => '0001',
            'ubigeo' => '150101',
            'direccion' => 'AV. TIENDA 456',
        ]);

        $cliente = new Cliente([
            'tipo_documento' => 'DNI',
            'documento' => '12345678',
            'nombre' => 'Juan',
            'apellido' => 'Perez',
        ]);

        $presentacion = new ProductoPresentacion(['id' => 5]);
        $presentacion->setRelation('unidadMedida', new UniMedida(['abreviatura' => 'UND']));

        $detalle = new DetalleDocumento([
            'producto_presentacion_id' => 5,
            'producto_nombre' => 'Arroz',
            'cantidad' => 2,
            'precio_unitario' => 11.80,
            'valor_unitario' => 10.00,
            'subtotal_neto' => 20.00,
            'total_igv' => 3.60,
            'tipo_afectacion' => 'GRAVADO',
        ]);
        $detalle->setRelation('presentacion', $presentacion);

        $documento = new Documento([
            'tipo_comprobante' => 'BOLETA',
            'serie' => 'B001',
            'numero' => '00000001',
            'fecha_emision' => Carbon::parse('2026-05-25'),
            'tipo_moneda' => 'PEN',
            'op_gravada' => 20.00,
            'op_exonerada' => 0,
            'op_inafecta' => 0,
            'total_igv' => 3.60,
            'subtotal' => 20.00,
            'total_neto' => 23.60,
            'porcentaje_igv' => 18,
        ]);
        $documento->setRelation('empresa', $empresa);
        $documento->setRelation('sucursal', $sucursal);
        $documento->setRelation('cliente', $cliente);
        $documento->setRelation('detalles', collect([$detalle]));

        $invoice = (new DocumentoGreenterFactory())->make($documento);

        $this->assertSame('03', $invoice->getTipoDoc());
        $this->assertSame('B001', $invoice->getSerie());
        $this->assertSame('1', $invoice->getCorrelativo());
        $this->assertSame(23.60, $invoice->getMtoImpVenta());
        $this->assertSame('20123456789', $invoice->getCompany()->getRuc());
        $this->assertSame('1', $invoice->getClient()->getTipoDoc());
        $this->assertSame('NIU', $invoice->getDetails()[0]->getUnidad());
        $this->assertSame('10', $invoice->getDetails()[0]->getTipAfeIgv());
    }
}
