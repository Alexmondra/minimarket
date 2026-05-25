<?php

namespace App\Filament\Clusters\Sunat\Resources\Archivos;

use App\Filament\Clusters\Sunat\Resources\Archivos\Pages\ListArchivos;
use App\Models\Archivo;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ArchivoResource extends Resource
{
    protected static ?string $model = Archivo::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static string|UnitEnum|null $navigationGroup = 'Sunat';

    protected static ?string $navigationLabel = 'Archivos';

    public static function getPages(): array
    {
        return [
            'index' => ListArchivos::route('/'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('documento.serie')
                    ->label('Serie')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('documento.numero')
                    ->label('Numero')
                    ->searchable(),
                TextColumn::make('tipo_archivo')
                    ->label('Tipo')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('nombre_archivo')
                    ->label('Archivo')
                    ->searchable(),
                TextColumn::make('bucket')
                    ->label('Bucket'),
                TextColumn::make('created_at')
                    ->label('Generado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Archivo $record) => route('filament.archivos.view', $record), shouldOpenInNewTab: true),
                Action::make('descargar')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Archivo $record) => route('filament.archivos.download', $record), shouldOpenInNewTab: true),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('documento', fn (Builder $query) => $query->where('empresa_id', Auth::user()->empresa_id))
            ->with('documento');
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('sunat.archivos') ?? false;
    }
}
