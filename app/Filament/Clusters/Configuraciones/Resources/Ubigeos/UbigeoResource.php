<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Ubigeos;

use App\Filament\Clusters\Configuraciones\Resources\Ubigeos\Pages\CreateUbigeo;
use App\Filament\Clusters\Configuraciones\Resources\Ubigeos\Pages\EditUbigeo;
use App\Filament\Clusters\Configuraciones\Resources\Ubigeos\Pages\ListUbigeos;
use App\Filament\Clusters\Configuraciones\Resources\Ubigeos\Pages\ViewUbigeo;
use App\Filament\Clusters\Configuraciones\Resources\Ubigeos\Schemas\UbigeoForm;
use App\Filament\Clusters\Configuraciones\Resources\Ubigeos\Schemas\UbigeoInfolist;
use App\Filament\Clusters\Configuraciones\Resources\Ubigeos\Tables\UbigeosTable;
use App\Models\Ubigeo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;
use Illuminate\Database\Eloquent\Model;

class UbigeoResource extends Resource
{
    protected static ?string $model = Ubigeo::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static string|UnitEnum|null $navigationGroup = 'Configuraciones';

    protected static ?string $recordTitleAttribute = 'distrito';

    protected static ?string $navigationLabel = 'Ubigeos';

    protected static ?string $pluralLabel = 'Ubigeos';

    public static function form(Schema $schema): Schema
    {
        return UbigeoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UbigeoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UbigeosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUbigeos::route('/'),
            'create' => CreateUbigeo::route('/create'),
            'view' => ViewUbigeo::route('/{record}'),
            'edit' => EditUbigeo::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('config.ver') ?? false;
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
