<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;

class Escritorio extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationLabel = 'Escritorio';

    protected static ?string $title = 'Escritorio';

    protected static ?int $navigationSort = -2;

    protected string $view = 'filament.pages.escritorio';

    public function getHeading(): string
    {
        return 'INICIO';
    }

    public function getSubheading(): ?string
    {
        return 'Resumen general del negocio y estadísticas en tiempo real — ' . now()->format('d \d\e F, Y');
    }
}
