<?php

namespace App\Filament\Clusters\Ventas\Resources\Documentos;

use App\Filament\Clusters\Ventas\Resources\Documentos\Pages\ListDocumentos;
use App\Models\Documento;
use App\Support\SucursalContext;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class DocumentoResource extends Resource
{

    protected static ?string $model = Documento::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $navigationLabel = 'Documentos';

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentos::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return app(SucursalContext::class)->applyToQuery(parent::getEloquentQuery());
    }
}
