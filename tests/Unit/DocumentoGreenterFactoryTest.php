<?php

namespace Tests\Unit;

use App\Models\Cliente;
use App\Models\DetalleDocumento;
use App\Models\Documento;
use App\Models\Empresa;
use App\Models\ProductoPresentacion;
use App\Models\Sucursal;
use App\Models\Ubigeo;
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
        $sucursal->setRelation('ubigeoRel', new Ubigeo([
            'ubigeo' => '150101',
            'departamento' => 'LIMA',
            'provincia' => 'LIMA',
            'distrito' => 'LIMA',
        ]));

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

        $invoice = (new DocumentoGreenterFactory)->make($documento);

        $this->assertSame('03', $invoice->getTipoDoc());
        $this->assertSame('B001', $invoice->getSerie());
        $this->assertSame('00000001', $invoice->getCorrelativo());
        $this->assertSame(23.60, $invoice->getMtoImpVenta());
        $this->assertSame('20123456789', $invoice->getCompany()->getRuc());
        $this->assertSame('LIMA', $invoice->getCompany()->getAddress()->getDepartamento());
        $this->assertSame('LIMA', $invoice->getCompany()->getAddress()->getProvincia());
        $this->assertSame('LIMA', $invoice->getCompany()->getAddress()->getDistrito());
        $this->assertSame('1', $invoice->getClient()->getTipoDoc());
        $this->assertSame('NIU', $invoice->getDetails()[0]->getUnidad());
        $this->assertSame('10', $invoice->getDetails()[0]->getTipAfeIgv());
    }

    public function test_mapea_afectaciones_correctamente_y_igv_cero_para_no_gravados(): void
    {
        $empresa = new Empresa([
            'ruc' => '20123456789',
            'razon_social' => 'MINIMARKET SAC',
        ]);
        $empresa->setRelation('empresaConfig', null);

        $sucursal = new Sucursal([
            'codigo' => '0001',
            'ubigeo' => '150101',
        ]);
        $sucursal->setRelation('ubigeoRel', new Ubigeo(['ubigeo' => '150101']));

        // Detalle 1: GRAVADO (tipo_afectacion = '10') -> debe ser '10', IGV debe mantenerse
        $detalle1 = new DetalleDocumento([
            'producto_presentacion_id' => 1,
            'producto_nombre' => 'Producto 1',
            'cantidad' => 1,
            'precio_unitario' => 11.80,
            'valor_unitario' => 10.00,
            'subtotal_neto' => 10.00,
            'total_igv' => 1.80,
            'tipo_afectacion' => '10',
        ]);

        // Detalle 2: INAFECTO (tipo_afectacion = 'INAFECTO') -> debe ser '30', IGV debe ser 0.00
        $detalle2 = new DetalleDocumento([
            'producto_presentacion_id' => 2,
            'producto_nombre' => 'Producto 2',
            'cantidad' => 1,
            'precio_unitario' => 10.00,
            'valor_unitario' => 10.00,
            'subtotal_neto' => 10.00,
            'total_igv' => 1.80,
            'tipo_afectacion' => 'INAFECTO',
        ]);

        // Detalle 3: EXONERADO (tipo_afectacion = '20') -> debe ser '20', IGV debe ser 0.00
        $detalle3 = new DetalleDocumento([
            'producto_presentacion_id' => 3,
            'producto_nombre' => 'Producto 3',
            'cantidad' => 1,
            'precio_unitario' => 10.00,
            'valor_unitario' => 10.00,
            'subtotal_neto' => 10.00,
            'total_igv' => 1.80,
            'tipo_afectacion' => '20',
        ]);

        $documento = new Documento([
            'tipo_comprobante' => 'FACTURA',
            'serie' => 'F001',
            'numero' => '00000002',
            'fecha_emision' => Carbon::parse('2026-05-25'),
            'tipo_moneda' => 'PEN',
            'op_gravada' => 10.00,
            'op_exonerada' => 10.00,
            'op_inafecta' => 10.00,
            'total_igv' => 1.80,
            'subtotal' => 30.00,
            'total_neto' => 31.80,
            'porcentaje_igv' => 18,
        ]);
        $documento->setRelation('empresa', $empresa);
        $documento->setRelation('sucursal', $sucursal);
        $documento->setRelation('cliente', null);
        $documento->setRelation('detalles', collect([$detalle1, $detalle2, $detalle3]));

        $invoice = (new DocumentoGreenterFactory)->make($documento);

        $details = $invoice->getDetails();
        $this->assertCount(3, $details);

        // Detalle 1 assertions
        $this->assertSame('10', $details[0]->getTipAfeIgv());
        $this->assertSame(1.80, $details[0]->getIgv());
        $this->assertSame(1.80, $details[0]->getTotalImpuestos());

        // Detalle 2 assertions
        $this->assertSame('30', $details[1]->getTipAfeIgv());
        $this->assertSame(0.0, $details[1]->getIgv());
        $this->assertSame(0.0, $details[1]->getTotalImpuestos());

        // Detalle 3 assertions
        $this->assertSame('20', $details[2]->getTipAfeIgv());
        $this->assertSame(0.0, $details[2]->getIgv());
        $this->assertSame(0.0, $details[2]->getTotalImpuestos());
    }
}
