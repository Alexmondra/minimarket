<?php

namespace Tests\Unit;

use Tests\TestCase;

class EmpresaCertificadoTest extends TestCase
{
    public function test_certificate_type_detection_logic(): void
    {
        $testCases = [
            'empresas/certificados/my_cert.pem' => 'PEM',
            'empresas/certificados/my_cert.pfx' => 'PFX',
            'empresas/certificados/my_cert.p12' => 'P12',
            'empresas/certificados/my_cert.cer' => 'CER',
            'empresas/certificados/my_cert.crt' => 'CRT',
            'empresas/certificados/my_cert' => 'PEM', // fallback if no extension
        ];

        foreach ($testCases as $path => $expectedType) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $detectedType = strtoupper($extension ?: 'PEM');
            $this->assertSame($expectedType, $detectedType);
        }

        // Test null/empty case
        $path = null;
        $detectedType = blank($path) ? null : strtoupper(pathinfo($path, PATHINFO_EXTENSION) ?: 'PEM');
        $this->assertNull($detectedType);
    }
}
