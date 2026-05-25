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

                /* --- SECTION 1: SYSTEM ENVIRONMENT & TAX DASHBOARD --- */
                Section::make('Ambiente y Configuración Fiscal')
                    ->description('Monitoreo del entorno de emisión tributaria y vigencia de la firma electrónica.')
                    ->icon('heroicon-o-cpu-chip')
                    ->extraAttributes(['class' => 'empresa-dashboard-card'])
                    ->columns(3)
                    ->schema([
                        // SUNAT Environment Indicator
                        TextEntry::make('entorno_view')
                            ->label('Entorno Tributario SUNAT')
                            ->state(fn ($record): string => $record?->entorno
                                ? 'PRODUCCIÓN (Comprobantes Reales)'
                                : 'DESARROLLO (Modo Pruebas / Demo)')
                            ->badge()
                            ->color(fn ($record): string => $record?->entorno ? 'success' : 'warning')
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),
                        Toggle::make('entorno')
                            ->label('Habilitar Entorno de Producción')
                            ->helperText('ATENCIÓN: Al activarlo, las facturas y boletas tendrán validez legal tributaria ante SUNAT.')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),

                        // Digital Certificate Status
                        TextEntry::make('certificado_view')
                            ->label('Certificado Digital (.pfx)')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;
                                return $config?->certificado
                                    ? 'CERTIFICADO CARGADO'
                                    : 'SIN CERTIFICADO CONFIGURADO';
                            })
                            ->badge()
                            ->color(fn ($record): string => $record?->empresaConfig?->certificado ? 'success' : 'danger')
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),

                        // Prices Include Tax Status
                        TextEntry::make('incluido_tributo_view')
                            ->label('Precios y Tributos')
                            ->state(fn ($record): string => $record?->incluido_tributo
                                ? 'Precios incluyen IGV'
                                : 'Precios + IGV adicional')
                            ->badge()
                            ->color(fn ($record): string => $record?->incluido_tributo ? 'success' : 'gray')
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),
                        Toggle::make('incluido_tributo')
                            ->label('Precios de venta incluyen IGV')
                            ->helperText('Active si los precios cargados en su catálogo de productos ya contienen el 18% del impuesto.')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),
                    ]),

                /* --- SECTION 2: IDENTITY & FISCAL PROFILE --- */
                Section::make('Identidad y Perfil Fiscal')
                    ->description('Datos legales de la empresa y dirección fiscal declarados ante las entidades públicas.')
                    ->icon('heroicon-o-building-office')
                    ->extraAttributes(['class' => 'empresa-settings-card'])
                    ->columns(3)
                    ->schema([
                        ImageEntry::make('logo')
                            ->label('Logotipo Comercial')
                            ->height(80)
                            ->width(80)
                            ->circular()
                            ->defaultImageUrl(null)
                            ->placeholder('Sin logo corporativo')
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),
                        FileUpload::make('logo')
                            ->label('Cargar Logotipo')
                            ->image()
                            ->directory('empresas/logos')
                            ->imagePreviewHeight('100')
                            ->helperText('Resolución ideal: 512x512px. Formato recomendado: PNG.')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),

                        TextEntry::make('ruc_view')
                            ->label('Número de RUC')
                            ->state(fn ($record): string => $record?->ruc ?? '—')
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),
                        TextInput::make('ruc')
                            ->label('Número de RUC')
                            ->required()
                            ->maxLength(11)
                            ->minLength(11)
                            ->rules(['digits:11'])
                            ->unique(ignoreRecord: true)
                            ->placeholder('10XXXXXXXXX')
                            ->hintIcon('heroicon-o-document-text')
                            ->hint('11 dígitos')
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
                            ->placeholder('Razón social completa o nombres comerciales')
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
                            ->placeholder('Av. Principal N° 123, Distrito, Provincia')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpanFull(),
                    ]),

                /* --- SECTION 3: DIGITAL CERTIFICATE DETAILS --- */
                Section::make('Firma Digital y Certificado')
                    ->description('Carga y vigencia del certificado digital necesario para la firma y envío del XML de comprobantes.')
                    ->icon('heroicon-o-shield-check')
                    ->extraAttributes(['class' => 'empresa-settings-card'])
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
                            ->helperText('Tipo de certificado digital para firmar documentos XML.')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),

                        FileUpload::make('certificado')
                            ->label('Cargar Certificado Digital')
                            ->directory('empresas/certificados')
                            ->acceptedFileTypes(['.pem', '.pfx', '.cer', '.crt', '.p12'])
                            ->helperText('Formatos válidos: .pem, .pfx, .cer, .crt, .p12')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),

                        TextEntry::make('certificado_pass_view')
                            ->label('Contraseña del Certificado')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;
                                return $config?->certificado_pass ? '••••••••' : '— No configurado';
                            })
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),
                        TextInput::make('certificado_pass')
                            ->label('Contraseña del Certificado')
                            ->password()
                            ->revealable()
                            ->placeholder('Dejar vacío si no desea cambiarla')
                            ->helperText('Contraseña del archivo de certificado digital cargado.')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),
                    ]),

                /* --- SECTION 4: SUNAT API & SOL CREDENTIALS --- */
                Section::make('Credenciales y Conexión SUNAT')
                    ->description('Campos de autenticación del usuario SOL secundario y tokens API para interactuar con los servidores de SUNAT.')
                    ->icon('heroicon-o-key')
                    ->extraAttributes(['class' => 'empresa-settings-card'])
                    ->columns(2)
                    ->schema([
                        // View mode
                        TextEntry::make('user_sol_view')
                            ->label('Usuario SOL')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;
                                return $config?->user_sol ? 'Usuario secundario configurado' : '— No configurado';
                            })
                            ->badge()
                            ->color(fn ($record): string => $record?->empresaConfig?->user_sol ? 'success' : 'gray')
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),
                        TextEntry::make('pass_sol_view')
                            ->label('Contraseña SOL')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;
                                return $config?->pass_sol ? '••••••••' : '— No configurado';
                            })
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),

                        TextEntry::make('sunat_client_id_view')
                            ->label('Client ID SUNAT')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;
                                return $config?->sunat_client_id ? 'API Client ID configurado' : '— No configurado';
                            })
                            ->badge()
                            ->color(fn ($record): string => $record?->empresaConfig?->sunat_client_id ? 'success' : 'gray')
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),
                        TextEntry::make('sunat_client_secret_view')
                            ->label('Client Secret SUNAT')
                            ->state(function ($record): string {
                                $config = $record?->empresaConfig;
                                return $config?->sunat_client_secret ? '••••••••' : '— No configurado';
                            })
                            ->visible(fn (Livewire $livewire): bool => ! $livewire->isEditing)
                            ->columnSpan(1),

                        // Edit mode
                        TextInput::make('user_sol')
                            ->label('Usuario SOL')
                            ->maxLength(255)
                            ->placeholder('Ej: MODDATOS')
                            ->helperText('Usuario secundario con permisos de emisión de comprobantes.')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),
                        TextInput::make('pass_sol')
                            ->label('Contraseña SOL')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->placeholder('Dejar vacío para mantener actual')
                            ->helperText('Contraseña del usuario secundario SOL.')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),

                        TextInput::make('sunat_client_id')
                            ->label('Client ID SUNAT')
                            ->maxLength(255)
                            ->placeholder('Client ID')
                            ->helperText('ID obtenido desde el portal de SUNAT para APIs.')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),
                        TextInput::make('sunat_client_secret')
                            ->label('Client Secret SUNAT')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->placeholder('Client Secret')
                            ->helperText('Secret obtenido desde el portal de SUNAT para APIs.')
                            ->visible(fn (Livewire $livewire): bool => $livewire->isEditing)
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
