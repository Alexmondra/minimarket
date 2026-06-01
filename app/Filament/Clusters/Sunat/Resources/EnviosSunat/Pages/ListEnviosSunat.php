<?php

namespace App\Filament\Clusters\Sunat\Resources\EnviosSunat\Pages;

use App\Filament\Clusters\Sunat\Resources\EnviosSunat\EnvioSunatResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEnviosSunat extends ListRecords
{
    protected static string $resource = EnvioSunatResource::class;

    public function getTitle(): string
    {
        return 'Envíos SUNAT & Monitor';
    }


    public function getTabs(): array
    {
        return [
            'general_monitor' => Tab::make('Monitor General')
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where(fn ($q) => $q->where('estado_sunat', false)->orWhere('codigo_respuesta_sunat', '!=', '0')->orWhereNull('codigo_respuesta_sunat')))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where(fn ($q) => $q->where('estado_sunat', false)->orWhere('codigo_respuesta_sunat', '!=', '0')->orWhereNull('codigo_respuesta_sunat'))->count())
                ->badgeColor('danger'),
            'boletas_facturas' => Tab::make('Boletas y Facturas')
                ->icon('heroicon-o-document-text')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('documento', fn ($q) => $q->whereIn('tipo_comprobante', ['BOLETA', 'FACTURA'])))
                ->badge(fn () => static::getResource()::getEloquentQuery()->whereHas('documento', fn ($q) => $q->whereIn('tipo_comprobante', ['BOLETA', 'FACTURA']))->count())
                ->badgeColor('success'),
            'notas_credito' => Tab::make('Notas de Crédito')
                ->icon('heroicon-o-document-duplicate')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('documento', fn ($q) => $q->where('tipo_comprobante', 'NOTA_CREDITO')))
                ->badge(fn () => static::getResource()::getEloquentQuery()->whereHas('documento', fn ($q) => $q->where('tipo_comprobante', 'NOTA_CREDITO'))->count())
                ->badgeColor('warning'),
            'todos' => Tab::make('Todos')
                ->icon('heroicon-o-queue-list')
                ->badge(fn () => static::getResource()::getEloquentQuery()->count())
                ->badgeColor('gray'),
        ];
    }
}
