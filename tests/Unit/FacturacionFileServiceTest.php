<?php

namespace Tests\Unit;

use App\Support\Facturacion\FacturacionFileService;
use Tests\TestCase;
use ZipArchive;

class FacturacionFileServiceTest extends TestCase
{
    public function test_extrae_xml_del_zip_cdr(): void
    {
        if (! class_exists(ZipArchive::class)) {
            $this->markTestSkipped('ZipArchive no esta disponible.');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'cdr_test_');
        $zip = new ZipArchive;
        $zip->open($tmp, ZipArchive::OVERWRITE);
        $zip->addFromString('R-20123456789-01-F001-1.xml', '<cdr>aceptado</cdr>');
        $zip->close();

        $content = file_get_contents($tmp);
        @unlink($tmp);

        $this->assertSame('<cdr>aceptado</cdr>', (new FacturacionFileService)->extraerCdrXml($content));
    }
}
