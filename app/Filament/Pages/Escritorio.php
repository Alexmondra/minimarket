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
        return '';
    }

    public function getSubheading(): ?string
    {
        return null;
    }
}
