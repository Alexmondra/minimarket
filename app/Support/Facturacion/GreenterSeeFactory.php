<?php

namespace App\Support\Facturacion;

use App\Models\Empresa;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\XMLSecLibs\Certificate\X509Certificate;
use Greenter\XMLSecLibs\Certificate\X509ContentType;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GreenterSeeFactory
{
    public function make(Empresa $empresa): See
    {
        $this->ensureSoapExtension();

        $config = $empresa->empresaConfig;

        if (! $config) {
            throw new RuntimeException('La empresa no tiene configuracion SUNAT.');
        }

        if (blank($empresa->ruc) || blank($config->user_sol) || blank($config->pass_sol)) {
            throw new RuntimeException('Faltan credenciales SOL de la empresa.');
        }

        $certificate = $this->certificatePem(
            path: (string) $config->certificado,
            password: (string) $config->certificado_pass
        );

        $see = new See;
        $see->setCertificate($certificate);
        $see->setClaveSOL((string) $empresa->ruc, (string) $config->user_sol, (string) $config->pass_sol);
        $see->setService($empresa->entorno ? SunatEndpoints::FE_PRODUCCION : SunatEndpoints::FE_BETA);

        return $see;
    }

    protected function ensureSoapExtension(): void
    {
        if (! class_exists(\SoapClient::class)) {
            throw new RuntimeException('La extension SOAP de PHP no esta instalada o habilitada. Instala php-soap/php8.x-soap y reinicia PHP para enviar comprobantes a SUNAT con Greenter.');
        }
    }

    protected function certificatePem(string $path, string $password): string
    {
        if (blank($path)) {
            throw new RuntimeException('No hay certificado digital configurado.');
        }

        $content = $this->readCertificate($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, ['pfx', 'p12'], true)) {
            if (blank($password)) {
                throw new RuntimeException('El certificado PFX/P12 requiere clave.');
            }

            return (new X509Certificate($content, $password))->export(X509ContentType::PEM);
        }

        return $content;
    }

    protected function readCertificate(string $path): string
    {
        foreach (['public', config('filesystems.default'), 'local'] as $diskName) {
            if (! $diskName) {
                continue;
            }

            $disk = Storage::disk($diskName);
            if ($disk->exists($path)) {
                return $disk->get($path);
            }
        }

        throw new RuntimeException('No se encontro el archivo del certificado digital.');
    }
}
