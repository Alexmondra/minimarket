<?php

namespace App\Filament\Clusters\Sunat\Resources\EnviosSunat\Pages;

use App\Filament\Clusters\Sunat\Resources\EnviosSunat\EnvioSunatResource;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListEnviosSunat extends ListRecords
{
    protected static string $resource = EnvioSunatResource::class;

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos')
                ->badge(fn () => $this->getEloquentQuery()->count()),
            'aceptados' => Tab::make('Aceptados')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado_sunat', true))
                ->badge(fn () => $this->getEloquentQuery()->where('estado_sunat', true)->count()),
            'con_error' => Tab::make('Con Error')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado_sunat', false))
                ->badge(fn () => $this->getEloquentQuery()->where('estado_sunat', false)->count()),
        ];
    }
}
