<?php

namespace App\Filament\Clusters\Reportes\Resources;

use App\Filament\Clusters\Reportes\Resources\Reportes\Pages\ReporteDashboard;
use App\Filament\Clusters\Reportes\Resources\Reportes\Pages\ReporteVentas;
use App\Filament\Clusters\Reportes\Resources\Reportes\Pages\ReporteGanancias;
use App\Filament\Clusters\Reportes\Resources\Reportes\Pages\ReportePerdidas;
use App\Filament\Clusters\Reportes\Resources\Reportes\Pages\ReporteProductos;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class Reportes extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|UnitEnum|null $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Dashboard Reportes';

    protected static ?string $modelLabel = 'Dashboard';

    protected static ?string $pluralModelLabel = 'Reportes';

    protected static ?string $slug = 'reportes';

    protected static ?int $navigationSort = 0;

    public static function getModel(): string
    {
        return User::class;
    }

    public static function getPages(): array
    {
        return [
            'index' => ReporteDashboard::route('/'),
            'ventas' => ReporteVentas::route('/ventas'),
            'ganancias' => ReporteGanancias::route('/ganancias'),
            'perdidas' => ReportePerdidas::route('/perdidas'),
            'productos' => ReporteProductos::route('/productos'),
        ];
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canView(Model $record): bool
    {
        return true;
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
