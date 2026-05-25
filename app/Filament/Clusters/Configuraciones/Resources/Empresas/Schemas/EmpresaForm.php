<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Empresas\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Livewire\Component as Livewire;

class EmpresaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Perfil fiscal de la empresa')
                    ->description('Estos datos identifican a la empresa en comprobantes, ventas y configuracion SUNAT.')
                    ->icon('heroicon-o-building-office')
                    ->columns(3)
                    ->schema([
                        ImageEntry::make('logo')
                            ->label('Logo')
                            ->height(96)
                            ->width(96)
                            ->defaultImageUrl(null)
                            ->placeholder('Sin logo')
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),
                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->directory('empresas/logos')
                            ->imagePreviewHeight('120')
                            ->helperText('Se usa como identificador visual de la empresa.')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),
                        TextEntry::make('entorno_view')
                            ->label('Entorno SUNAT')
                            ->state(fn ($record): string => $record?->entorno
                                ? 'Produccion'
                                : 'Pruebas')
                            ->badge()
                            ->color(fn ($record): string => $record?->entorno ? 'success' : 'warning')
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),
                        Toggle::make('entorno')
                            ->label('Entorno de Producción')
                            ->helperText('Activar para entorno real. Desactivar para pruebas.')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),

                        TextEntry::make('incluido_tributo_view')
                            ->label('Precios incluyen IGV')
                            ->state(fn ($record): string => $record?->incluido_tributo
                                ? 'Si' : 'No')
                            ->badge()
                            ->color(fn ($record): string => $record?->incluido_tributo ? 'success' : 'gray')
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),
                        Toggle::make('incluido_tributo')
                            ->label('Precios incluyen IGV')
                            ->helperText('Activar si los precios ya incluyen IGV')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),

                        TextEntry::make('ruc_view')
                            ->label('RUC')
                            ->state(fn ($record): string => $record?->ruc ?? '—')
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
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
                        TextEntry::make('razon_social_view')
                            ->label('Razón Social')
                            ->state(fn ($record): string => $record?->razon_social ?? '—')
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(2),
                        TextInput::make('razon_social')
                            ->label('Razón Social')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ingrese la razón social')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(2),
                        TextEntry::make('direccion_fiscal_view')
                            ->label('Dirección Fiscal')
                            ->state(fn ($record): string => $record?->direccion_fiscal ?? '—')
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpanFull(),
                        TextInput::make('direccion_fiscal')
                            ->label('Dirección Fiscal')
                            ->maxLength(255)
                            ->placeholder('Av. Ejemplo N° 123')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpanFull(),
                    ]),

                Section::make('Facturacion electronica')
                    ->description('Certificado y credenciales que permiten emitir documentos electronicos.')
                    ->icon('heroicon-o-shield-check')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('tipo_certificado_view')
                            ->label('Tipo de Certificado')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;

                                return $config?->tipo_certificado
                                    ? $config->tipo_certificado
                                    : '— No configurado';
                            })
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
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
                        TextEntry::make('certificado_view')
                            ->label('Certificado Digital')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;

                                return $config?->certificado
                                    ? 'Cargado'
                                    : 'No configurado';
                            })
                            ->badge()
                            ->color(fn ($record): string => $record?->empresaConfig?->certificado ? 'success' : 'danger')
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),
                        FileUpload::make('certificado')
                            ->label('Archivo de Certificado Digital')
                            ->directory('empresas/certificados')
                            ->acceptedFileTypes(['.pem', '.pfx', '.cer', '.crt', '.p12'])
                            ->helperText('Formatos aceptados: .pem, .pfx, .cer, .crt, .p12')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),
                        TextEntry::make('certificado_pass_view')
                            ->label('Contraseña del Certificado')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;

                                return $config?->certificado_pass
                                    ? '••••••••'
                                    : '— No configurado';
                            })
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),
                        TextInput::make('certificado_pass')
                            ->label('Contraseña del Certificado')
                            ->password()
                            ->revealable()
                            ->placeholder('Dejar vacío si no desea cambiarlo')
                            ->helperText('Contraseña del archivo de certificado digital')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),
                        TextEntry::make('user_sol_view')
                            ->label('Usuario SOL')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;

                                return $config?->user_sol
                                    ? 'Configurado'
                                    : '— No configurado';
                            })
                            ->badge()
                            ->color(fn ($record): string => $record?->empresaConfig?->user_sol ? 'success' : 'gray')
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),
                        TextEntry::make('pass_sol_view')
                            ->label('Contraseña SOL')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;

                                return $config?->pass_sol
                                    ? '••••••••'
                                    : '— No configurado';
                            })
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),
                        TextEntry::make('sunat_client_id_view')
                            ->label('Client ID SUNAT')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;

                                return $config?->sunat_client_id
                                    ? 'Configurado'
                                    : '— No configurado';
                            })
                            ->badge()
                            ->color(fn ($record): string => $record?->empresaConfig?->sunat_client_id ? 'success' : 'gray')
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),
                        TextEntry::make('sunat_client_secret_view')
                            ->label('Client Secret')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;

                                return $config?->sunat_client_secret
                                    ? '••••••••'
                                    : '— No configurado';
                            })
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),
                    ]),

                Section::make('Credenciales SUNAT')
                    ->description('Campos sensibles. Si los dejas vacios, se conservan los valores actuales.')
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
