<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Series;

use App\Filament\Clusters\Configuraciones\Resources\Series\Pages\ListSeries;
use App\Filament\Clusters\Configuraciones\Resources\Series\Pages\SeleccionarSucursal;
use App\Models\Serie;
use App\Support\SucursalContext;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;
use UnitEnum;

class SerieResource extends Resource
{
    private const TIPOS_COMPROBANTE = [
        'BOLETA' => 'Boleta de venta',
        'FACTURA' => 'Factura',
        'NOTA_CREDITO' => 'Nota de credito',
        'NOTA_DEBITO' => 'Nota de debito',
        'TICKET' => 'Ticket',
    ];

    protected static ?string $model = Serie::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static string|UnitEnum|null $navigationGroup = 'Configuraciones';

    protected static ?string $navigationLabel = 'Series';

    protected static ?string $modelLabel = 'Serie';

    protected static ?string $pluralModelLabel = 'Series';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Numeracion del comprobante')
                    ->description('Define la serie y el siguiente correlativo que usara cada sucursal al vender.')
                    ->icon('heroicon-o-receipt-percent')
                    ->columns(2)
                    ->schema([
                        Select::make('sucursal_id')
                            ->options(fn (): array => app(SucursalContext::class)
                                ->sucursalesForWrite()
                                ->pluck('nombre_sucursal', 'id')
                                ->all())
                            ->default(fn (): ?int => app(SucursalContext::class)->resolveSucursalForWrite())
                            ->disabled(fn (): bool => app(SucursalContext::class)->activeSucursalId() !== null)
                            ->dehydrated()
                            ->required()
                            ->label('Sucursal')
                            ->helperText('Solo aparecen sucursales a las que el usuario tiene acceso.')
                            ->native(false),
                        Select::make('tipo_comprobante')
                            ->required()
                            ->label('Tipo de comprobante')
                            ->options(self::TIPOS_COMPROBANTE)
                            ->helperText('Ejemplo: B001 para boleta, F001 para factura.')
                            ->native(false),
                        TextInput::make('serie')
                            ->required()
                            ->maxLength(10)
                            ->label('Serie')
                            ->placeholder('B001')
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state)
                                ? mb_strtoupper(trim($state))
                                : $state)
                            ->unique(
                                table: 'series',
                                column: 'serie',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule
                                    ->where('sucursal_id', $get('sucursal_id'))
                                    ->where('tipo_comprobante', $get('tipo_comprobante'))
                                    ->whereNull('deleted_at'),
                            )
                            ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                            ->helperText('Usa el formato fiscal que corresponda a tu comprobante.'),
                        TextInput::make('correlativo')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->label('Siguiente numero')
                            ->helperText('Se incrementa cuando se emite un comprobante.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sucursal.nombre_sucursal')
                    ->label('Sucursal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo_comprobante')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'BOLETA' => 'success',
                        'FACTURA' => 'info',
                        'NOTA_CREDITO' => 'warning',
                        'NOTA_DEBITO' => 'danger',
                        'TICKET' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => self::TIPOS_COMPROBANTE[$state] ?? $state)
                    ->label('Comprobante'),
                TextColumn::make('serie')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->weight('bold')
                    ->label('Serie'),
                TextColumn::make('correlativo')
                    ->numeric()
                    ->sortable()
                    ->label('Siguiente numero')
                    ->description(fn (Serie $record): string => sprintf(
                        '%s-%08d',
                        $record->serie,
                        max((int) $record->correlativo, 1)
                    )),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Creado'),
            ])
            ->filters([
                SelectFilter::make('sucursal_id')
                    ->label('Sucursal')
                    ->options(fn (): array => app(SucursalContext::class)
                        ->sucursalesForWrite()
                        ->pluck('nombre_sucursal', 'id')
                        ->all()),
                SelectFilter::make('tipo_comprobante')
                    ->label('Tipo de comprobante')
                    ->options(self::TIPOS_COMPROBANTE),
                TrashedFilter::make(),
            ])
            ->defaultSort('sucursal_id')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeries::route('/'),
            'seleccionar' => SeleccionarSucursal::route('/seleccionar'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->whereHas('sucursal', function (Builder $query) {
                $query->where('empresa_id', Auth::user()->empresa_id);
            });

        $activeSucursalId = app(SucursalContext::class)->activeSucursalId();
        if ($activeSucursalId) {
            $query->where('sucursal_id', $activeSucursalId);
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('config.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('config.editar') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('config.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('config.editar') ?? false;
    }

    public static function canForceDelete(Model $record): bool
    {
        return static::canDelete($record);
    }

    public static function canRestore(Model $record): bool
    {
        return static::canDelete($record);
    }
}
