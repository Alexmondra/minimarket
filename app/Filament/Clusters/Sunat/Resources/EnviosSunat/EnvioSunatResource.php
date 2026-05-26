<?php

namespace App\Filament\Clusters\Sunat\Resources\EnviosSunat;

use App\Filament\Clusters\Sunat\Resources\EnviosSunat\Pages\ListEnviosSunat;
use App\Models\Sunat;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EnvioSunatResource extends Resource
{

    protected static ?string $model = Sunat::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static string|UnitEnum|null $navigationGroup = 'Sunat';

    protected static ?string $navigationLabel = 'Envíos SUNAT';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('documento.serie')
                    ->label('Comprobante')
                    ->state(fn (Sunat $record) => $record->documento ? "{$record->documento->serie}-{$record->documento->numero}" : '')
                    ->weight('bold')
                    ->description(fn (Sunat $record) => $record->documento?->tipo_comprobante)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('documento', function (Builder $q) use ($search) {
                            $q->where('serie', 'like', "%{$search}%")
                              ->orWhere('numero', 'like', "%{$search}%")
                              ->orWhere('tipo_comprobante', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('estado_sunat')
                    ->label('Estado SUNAT')
                    ->badge()
                    ->state(fn (Sunat $record): string => $record->estado_sunat ? 'ACEPTADO' : 'CON ERROR')
                    ->color(fn (Sunat $record): string => $record->estado_sunat ? 'success' : 'danger')
                    ->icon(fn (Sunat $record): string => $record->estado_sunat ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->description(fn (Sunat $record): string => $record->codigo_respuesta_sunat !== null ? "Código: {$record->codigo_respuesta_sunat}" : null),
                TextColumn::make('mensaje_sunat')
                    ->label('Respuesta de SUNAT')
                    ->limit(100)
                    ->fontFamily('mono')
                    ->wrap()
                    ->tooltip(fn (Sunat $record): string => $record->mensaje_sunat ?? ''),
                TextColumn::make('fecha_envio')
                    ->label('Fecha Envío')
                    ->dateTime('d/m/Y H:i:s')
                    ->description(fn (Sunat $record) => $record->fecha_envio?->diffForHumans())
                    ->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnviosSunat::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('empresa_id', Auth::user()?->empresa_id)
            ->with('documento');
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('sunat.monitor') ?? false;
    }
}
