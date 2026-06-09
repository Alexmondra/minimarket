<?php

namespace App\Filament\Clusters\Global\Resources\Presentaciones;

use App\Filament\Clusters\Global\Resources\Presentaciones\Pages\ListPresentaciones;
use App\Filament\Clusters\Global\Resources\Productos\ProductoResource;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use Illuminate\Database\Eloquent\Model;

class PresentacionResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = ProductoPresentacion::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube-transparent';

    protected static string|UnitEnum|null $navigationGroup = 'Catálogo Global';

    protected static ?string $navigationLabel = 'Presentaciones';

    protected static ?string $modelLabel = 'Presentación';

    protected static ?string $pluralModelLabel = 'Presentaciones';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('tipo_presentacion')
                    ->label('Tipo de Presentación')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_productos')
                    ->label('Cant. Productos')
                    ->sortable(),
            ])
            ->recordUrl(function (ProductoPresentacion $record): ?string {
                $tipoPresentacion = $record->tipo_presentacion;
                $productos = Producto::query()
                    ->where('empresa_id', auth()->user()->empresa_id)
                    ->whereHas('presentaciones', fn ($q) => $q->where('tipo_presentacion', $tipoPresentacion))
                    ->get();

                if ($productos->count() === 1) {
                    return ProductoResource::getUrl('edit', ['record' => $productos->first()->id]);
                }

                return null; // No redirige, se queda en la página
            })
            ->actions([
                Action::make('ver_productos')
                    ->label('Ver Productos')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (ProductoPresentacion $record): string => 'Productos con presentación: '.$record->tipo_presentacion)
                    ->modalContent(function (ProductoPresentacion $record) {
                        $tipoPresentacion = $record->tipo_presentacion;
                        $productos = Producto::query()
                            ->where('empresa_id', auth()->user()->empresa_id)
                            ->whereHas('presentaciones', fn ($q) => $q->where('tipo_presentacion', $tipoPresentacion))
                            ->get();

                        return view('filament.tables.modals.productos-presentacion', [
                            'productos' => $productos,
                            'tipo_presentacion' => $tipoPresentacion,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
            ])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPresentaciones::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $empresaId = auth()->user()->empresa_id;

        return ProductoPresentacion::query()
            ->selectRaw('MIN(id) as id, tipo_presentacion, COUNT(DISTINCT producto_id) as total_productos')
            ->selectRaw('(SELECT pp2.imagen FROM producto_presentacion pp2 WHERE pp2.tipo_presentacion = producto_presentacion.tipo_presentacion AND pp2.imagen IS NOT NULL AND pp2.imagen != \'\' LIMIT 1) as imagen_ejemplo')
            ->whereHas('producto', fn ($q) => $q->where('empresa_id', $empresaId))
            ->groupBy('tipo_presentacion')
            ->orderBy('tipo_presentacion');
    }

    public static function canViewAny(): bool
    {
        return (auth()->user()?->can('productos.global') && auth()->user()?->can('productos.ver')) ?? false;
    }

    public static function canCreate(): bool
    {
        return (auth()->user()?->can('productos.global') && auth()->user()?->can('productos.crear')) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return (auth()->user()?->can('productos.global') && auth()->user()?->can('productos.editar')) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
