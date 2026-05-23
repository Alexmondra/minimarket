<?php

namespace App\Filament\Clusters\Ventas\Resources\Clientes\Pages;

use App\Filament\Clusters\Ventas\Resources\Clientes\ClienteResource;
use Filament\Resources\Pages\ListRecords;

class ListClientes extends ListRecords
{
    protected static string $resource = ClienteResource::class;
}
