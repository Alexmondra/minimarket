<?php

namespace App\Filament\Clusters\Compras\Resources\Compras\Pages;

use App\Filament\Clusters\Compras\Resources\Compras\CompraResource;
use App\Models\Compra;
use App\Support\SucursalContext;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ViewCompra extends ViewRecord
{
    protected static string $resource = CompraResource::class;

    public string $view = 'filament.pages.view-compra';

    public function getTitle(): string
    {
        $compra = $this->getRecord();
        return ($compra->proveedor?->nombre ?? 'Sin proveedor');
    }

    public static function getNavigationLabel(): string
    {
        return 'Detalle de Compra';
    }

    /**
     * Get estado label for visual display only.
     */
    public function getEstadoLabel(): string
    {
        $estado = $this->getRecord()->estado;
        return match (true) {
            $estado === 'anulada' || $estado === false || $estado === 0 => 'Anulada',
            $estado === true || $estado === 1 || $estado === 'completada' || $estado === 'recibida' => 'Recibida',
            $estado === 'pendiente' => 'Pendiente',
            default => ucfirst((string) ($estado ?? 'borrador')),
        };
    }

    /**
     * Get estado color for badge.
     */
    public function getEstadoColor(): string
    {
        $estado = $this->getRecord()->estado;
        return match (true) {
            $estado === 'anulada' || $estado === false || $estado === 0 => 'danger',
            $estado === true || $estado === 1 || $estado === 'completada' || $estado === 'recibida' => 'success',
            $estado === 'pendiente' => 'warning',
            default => 'gray',
        };
    }

    /**
     * Get estado icon.
     */
    public function getEstadoIcon(): string
    {
        $estado = $this->getRecord()->estado;
        return match (true) {
            $estado === 'anulada' || $estado === false || $estado === 0 => 'o-x-circle',
            $estado === true || $estado === 1 || $estado === 'completada' || $estado === 'recibida' => 'o-check-circle',
            $estado === 'pendiente' => 'o-clock',
            default => 'o-question-mark-circle',
        };
    }

    /**
     * Get comprobante file extension.
     */
    public function getComprobanteExtension(): ?string
    {
        $compra = $this->getRecord();
        if (!$compra->archivo_comprobante) return null;
        return strtolower(pathinfo($compra->archivo_comprobante, PATHINFO_EXTENSION));
    }

    /**
     * Get the computed data for the view.
     */
    public function getCompra(): Compra
    {
        return $this->getRecord()->load([
            'proveedor',
            'sucursal',
            'user',
            'detalle.lote',
            'detalle.lote.lotePresentaciones.productoPresentacion.producto',
            'detalle.lote.lotePresentaciones.productoPresentacion.unidadMedida',
        ]);
    }

    /**
     * Get the comprobante URL.
     */
    public function getComprobanteUrl(): ?string
    {
        $compra = $this->getRecord();
        if (!$compra->archivo_comprobante) {
            return null;
        }
        return route('filament.compras.comprobante', $compra);
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        if (Auth::user()->can('compras.editar')) {
            // ─── Acción: Editar datos generales de la compra ───
            $actions[] = Action::make('editar')
                ->label('Editar Compra')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->form([
                    Select::make('proveedor_id')
                        ->label('Proveedor')
                        ->relationship('proveedor', 'nombre')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('sucursal_id')
                        ->label('Sucursal')
                        ->options(fn (): array => app(SucursalContext::class)
                            ->sucursalesForWrite()
                            ->pluck('nombre_sucursal', 'id')
                            ->all())
                        ->disabled(fn (): bool => app(SucursalContext::class)->activeSucursalId() !== null)
                        ->dehydrated()
                        ->required(),
                    Select::make('tipo_comprobante')
                        ->label('Tipo Comprobante')
                        ->options([
                            'factura' => 'Factura',
                            'boleta' => 'Boleta',
                            'nota_credito' => 'Nota de Crédito',
                            'nota_debito' => 'Nota de Débito',
                        ])
                        ->required(),
                    TextInput::make('numero_factura_proveedor')
                        ->label('N° Factura'),
                    DatePicker::make('fecha_recepcion')
                        ->label('Fecha Recepción')
                        ->required(),
                    FileUpload::make('archivo_comprobante')
                        ->label('Comprobante')
                        ->directory('comprobantes')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/gif'])
                        ->maxSize(10240),
                    Textarea::make('observaciones')
                        ->label('Observaciones')
                        ->maxLength(1000),
                ])
                ->fillForm(fn (Compra $record): array => [
                    'proveedor_id' => $record->proveedor_id,
                    'sucursal_id' => $record->sucursal_id,
                    'tipo_comprobante' => $record->tipo_comprobante,
                    'numero_factura_proveedor' => $record->numero_factura_proveedor,
                    'fecha_recepcion' => $record->fecha_recepcion?->format('Y-m-d'),
                    'archivo_comprobante' => $record->archivo_comprobante,
                    'observaciones' => $record->observaciones,
                ])
                ->action(function (array $data, Compra $record): void {
                    $sucursalId = app(SucursalContext::class)->resolveSucursalForWrite($data['sucursal_id'] ?? null);

                    abort_unless($sucursalId, 403, 'Selecciona una sucursal válida.');
                    $data['sucursal_id'] = $sucursalId;

                    if (isset($data['archivo_comprobante']) && $data['archivo_comprobante'] !== $record->archivo_comprobante) {
                        if ($record->archivo_comprobante) {
                            Storage::disk('public')->delete($record->archivo_comprobante);
                        }
                    } else {
                        unset($data['archivo_comprobante']);
                    }

                    $record->update($data);

                    Notification::make()
                        ->title('Compra actualizada correctamente')
                        ->success()
                        ->send();
                })
                ->slideOver()
                ->modalWidth('lg');

        }

        if (Auth::user()->can('compras.anular')) {
            $actions[] = Action::make('anular')
                ->label('Anular Compra')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('¿Anular compra?')
                ->modalDescription('Esta acción marcará la compra como anulada. Los movimientos de inventario relacionados podrían verse afectados.')
                ->action(function () {
                    $compra = $this->getRecord();
                    $compra->update(['estado' => 'anulada']);

                    Notification::make()
                        ->title('Compra anulada correctamente')
                        ->success()
                        ->send();
                });
        }

        return $actions;
    }

    public function getRelationManagers(): array
    {
        return [];
    }

    /**
     * Mount the record and authorize access.
     */
    public function mount(int|string $record): void
    {
        abort_unless(Auth::user()->can('compras.ver'), 403, 'No tienes permiso para ver compras.');

        parent::mount($record);

        abort_unless(
            app(SucursalContext::class)->canAccessSucursal((int) $this->getRecord()->sucursal_id),
            403,
            'No tienes acceso a esta compra.'
        );
    }

}
