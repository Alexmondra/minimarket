<?php

namespace App\Filament\Clusters\Inventario\Resources\Lotes;

use App\Filament\Clusters\Inventario\Resources\Lotes\Pages\ListLotes;
use App\Models\Lote;
use App\Support\SucursalContext;
use BackedEnum;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use Illuminate\Database\Eloquent\Model;

class LoteResource extends Resource
{
    protected static ?string $model = Lote::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Lotes';

    protected static ?string $recordTitleAttribute = 'codigo_lote';

    public static function getNavigationBadge(): ?string
    {
        $today = now()->startOfDay();
        $query = static::getModel()::query()
            ->whereIn('estado_lote', ['activo', 'vencido', 'por_confirmar'])
            ->whereDate('fecha_vencimiento', '<=', $today)
            ->whereHas('lotePresentaciones', function ($q) {
                $q->where('stock', '>', 0);
            });

        $query = app(SucursalContext::class)->applyToQuery($query);

        return $query->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLotes::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return app(SucursalContext::class)->applyToQuery(parent::getEloquentQuery());
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('ventas.crear') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
