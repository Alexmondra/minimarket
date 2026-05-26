<?php

namespace Tests\Unit;

use App\Models\Empresa;
use App\Models\EmpresaConfig;
use App\Support\Facturacion\GreenterSeeFactory;
use RuntimeException;
use Tests\TestCase;

class GreenterSeeFactoryTest extends TestCase
{
    public function test_make_falla_con_mensaje_claro_si_no_existe_extension_soap(): void
    {
        if (class_exists(\SoapClient::class)) {
            $this->markTestSkipped('La extension SOAP esta instalada en este entorno.');
        }

        $empresa = new Empresa([
            'ruc' => '20123456789',
            'entorno' => false,
        ]);
        $empresa->setRelation('empresaConfig', new EmpresaConfig([
            'user_sol' => 'MODDATOS',
            'pass_sol' => 'MODDATOS',
            'certificado' => 'certificado.pem',
            'certificado_pass' => '123456',
        ]));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('La extension SOAP de PHP no esta instalada o habilitada.');

        (new GreenterSeeFactory)->make($empresa);
    }
}
