<?php

namespace App\Filament\Clusters\Reportes\Resources;

use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class Reportes extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|UnitEnum|null $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $modelLabel = 'Dashboard';

    protected static ?string $pluralModelLabel = 'Reportes';

    protected static ?string $slug = 'dashboard';

    public static function getModel(): string
    {
        return User::class;
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
