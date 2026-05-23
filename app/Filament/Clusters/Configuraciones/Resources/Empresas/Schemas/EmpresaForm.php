<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Empresas\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Livewire\Component as Livewire;

class EmpresaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ╔══════════════════════════════════════════════════════════╗
                // ║  1. INFORMACIÓN GENERAL                                 ║
                // ╚══════════════════════════════════════════════════════════╝
                Section::make('Información General')
                    ->description('Datos principales de la empresa')
                    ->icon('heroicon-o-building-office')
                    ->columns(3)
                    ->schema([

                        // ─── ENTORNO SUNAT (indicador/toggle) ───
                        TextEntry::make('entorno_view')
                            ->label('Entorno SUNAT')
                            ->state(fn ($record): string => $record?->entorno
                                ? '🌐 Producción'
                                : '🔬 Pruebas')
                            ->visible(fn (Livewire $livewire): bool => !$livewire->isEditing)
                            ->columnSpan(1),
                        Toggle::make('entorno')
                            ->label('Entorno de Producción')
                            ->helperText('Activar para entorno real. Desactivar para pruebas.')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),

                        // ─── PRECIOS INCLUYEN IGV (indicador/toggle) ───
                        TextEntry::make('incluido_tributo_view')
                            ->label('Precios incluyen IGV')
                            ->state(fn ($record): string => $record?->incluido_tributo
                                ? '✅ Sí' : '❌ No')
                            ->visible(fn (Livewire $livewire): bool => !$livewire->isEditing)
                            ->columnSpan(1),
                        Toggle::make('incluido_tributo')
                            ->label('Precios incluyen IGV')
                            ->helperText('Activar si los precios ya incluyen IGV')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),

                        // ─── Espaciador (modo vista: fila 1, columna 3 vacía) ───
                        // Para que los indicadores queden a la izquierda en modo vista
                        // (ya que son los primeros elementos de la sección)

                        // ─── LOGO ───
                        ImageEntry::make('logo')
                            ->label('Logo')
                            ->height(60)
                            ->width(60)
                            ->defaultImageUrl(null)
                            ->placeholder('— Sin logo')
                            ->visible(fn (Livewire $livewire): bool => !$livewire->isEditing)
                            ->columnSpan(1),
                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->directory('empresas/logos')
                            ->imagePreviewHeight('80')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),

                        // ─── RUC ───
                        TextEntry::make('ruc_view')
                            ->label('RUC')
                            ->state(fn ($record): string => $record?->ruc ?? '—')
                            ->visible(fn (Livewire $livewire): bool => !$livewire->isEditing)
                            ->columnSpan(1),
                        TextInput::make('ruc')
                            ->label('RUC')
                            ->required()
                            ->maxLength(11)
                            ->minLength(11)
                            ->rules(['digits:11'])
                            ->unique(ignoreRecord: true)
                            ->placeholder('12345678901')
                            ->hintIcon('heroicon-o-document-text')
                            ->hint('11 dígitos numéricos')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),

                        // ─── RAZÓN SOCIAL ───
                        TextEntry::make('razon_social_view')
                            ->label('Razón Social')
                            ->state(fn ($record): string => $record?->razon_social ?? '—')
                            ->visible(fn (Livewire $livewire): bool => !$livewire->isEditing)
                            ->columnSpan(1),
                        TextInput::make('razon_social')
                            ->label('Razón Social')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ingrese la razón social')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),

                        // ─── DIRECCIÓN FISCAL ───
                        TextEntry::make('direccion_fiscal_view')
                            ->label('Dirección Fiscal')
                            ->state(fn ($record): string => $record?->direccion_fiscal ?? '—')
                            ->visible(fn (Livewire $livewire): bool => !$livewire->isEditing)
                            ->columnSpanFull(),
                        TextInput::make('direccion_fiscal')
                            ->label('Dirección Fiscal')
                            ->maxLength(255)
                            ->placeholder('Av. Ejemplo N° 123')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpanFull(),
                    ]),

                // ╔══════════════════════════════════════════════════════════╗
                // ║  2. CONFIGURACIÓN SUNAT                                 ║
                // ╚══════════════════════════════════════════════════════════╝
                Section::make('Configuración SUNAT')
                    ->description('Certificado digital para facturación electrónica')
                    ->icon('heroicon-o-shield-check')
                    ->columns(3)
                    ->schema([

                        // ─── TIPO DE CERTIFICADO ───
                        TextEntry::make('tipo_certificado_view')
                            ->label('Tipo de Certificado')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;
                                return $config?->tipo_certificado
                                    ? $config->tipo_certificado
                                    : '— No configurado';
                            })
                            ->visible(fn (Livewire $livewire): bool => !$livewire->isEditing)
                            ->columnSpan(1),
                        Select::make('tipo_certificado')
                            ->label('Tipo de Certificado')
                            ->options([
                                'PRODUCCIÓN' => 'Producción',
                                'CERTIFICADO' => 'Certificado',
                            ])
                            ->placeholder('Seleccione el tipo')
                            ->nullable()
                            ->helperText('Tipo de certificado digital para firmar facturas electrónicas')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),

                        // ─── ARCHIVO DE CERTIFICADO DIGITAL ───
                        TextEntry::make('certificado_view')
                            ->label('Certificado Digital')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;
                                return $config?->certificado
                                    ? '📄 Certificado cargado'
                                    : '⛔ No configurado';
                            })
                            ->visible(fn (Livewire $livewire): bool => !$livewire->isEditing)
                            ->columnSpan(1),
                        FileUpload::make('certificado')
                            ->label('Archivo de Certificado Digital')
                            ->directory('empresas/certificados')
                            ->acceptedFileTypes(['.pem', '.pfx', '.cer', '.crt', '.p12'])
                            ->helperText('Formatos aceptados: .pem, .pfx, .cer, .crt, .p12')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),

                        // ─── CONTRASEÑA DEL CERTIFICADO ───
                        TextEntry::make('certificado_pass_view')
                            ->label('Contraseña del Certificado')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;
                                return $config?->certificado_pass
                                    ? '••••••••'
                                    : '— No configurado';
                            })
                            ->visible(fn (Livewire $livewire): bool => !$livewire->isEditing)
                            ->columnSpan(1),
                        TextInput::make('certificado_pass')
                            ->label('Contraseña del Certificado')
                            ->password()
                            ->revealable()
                            ->placeholder('Dejar vacío si no desea cambiarlo')
                            ->helperText('Contraseña del archivo de certificado digital')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),

                        // ─── CREDENCIALES SUNAT (Modo Vista) ───
                        TextEntry::make('user_sol_view')
                            ->label('Usuario SOL')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;
                                return $config?->user_sol
                                    ? '👤 ………'
                                    : '— No configurado';
                            })
                            ->visible(fn (Livewire $livewire): bool => !$livewire->isEditing)
                            ->columnSpan(1),
                        TextEntry::make('pass_sol_view')
                            ->label('Contraseña SOL')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;
                                return $config?->pass_sol
                                    ? '••••••••'
                                    : '— No configurado';
                            })
                            ->visible(fn (Livewire $livewire): bool => !$livewire->isEditing)
                            ->columnSpan(1),
                        TextEntry::make('sunat_client_id_view')
                            ->label('Client ID SUNAT')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;
                                return $config?->sunat_client_id
                                    ? '🔑 ………'
                                    : '— No configurado';
                            })
                            ->visible(fn (Livewire $livewire): bool => !$livewire->isEditing)
                            ->columnSpan(1),
                        TextEntry::make('sunat_client_secret_view')
                            ->label('Client Secret')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;
                                return $config?->sunat_client_secret
                                    ? '••••••••'
                                    : '— No configurado';
                            })
                            ->visible(fn (Livewire $livewire): bool => !$livewire->isEditing)
                            ->columnSpan(1),
                    ]),

                // ╔══════════════════════════════════════════════════════════╗
                // ║  3. CREDENCIALES SUNAT  (solo modo edición)            ║
                // ╚══════════════════════════════════════════════════════════╝
                Section::make('Credenciales SUNAT')
                    ->description('Credenciales de acceso a los servicios de SUNAT (solo modo edición)')
                    ->icon('heroicon-o-key')
                    ->columns(2)
                    ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                    ->schema([

                        TextInput::make('user_sol')
                            ->label('Usuario SOL')
                            ->maxLength(255)
                            ->placeholder('Dejar vacío si no desea cambiarlo')
                            ->helperText('Usuario del sistema SOL de SUNAT'),

                        TextInput::make('pass_sol')
                            ->label('Contraseña SOL')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->placeholder('Dejar vacío si no desea cambiarlo')
                            ->helperText('Contraseña del sistema SOL de SUNAT'),

                        TextInput::make('sunat_client_id')
                            ->label('Client ID SUNAT')
                            ->maxLength(255)
                            ->placeholder('Dejar vacío si no desea cambiarlo')
                            ->helperText('Client ID para autenticación API SUNAT'),

                        TextInput::make('sunat_client_secret')
                            ->label('Client Secret')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->placeholder('Dejar vacío si no desea cambiarlo')
                            ->helperText('Client Secret para autenticación API SUNAT'),
                    ]),
            ]);
    }
}
