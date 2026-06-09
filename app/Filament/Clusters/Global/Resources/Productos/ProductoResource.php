<?php

namespace App\Filament\Clusters\Global\Resources\Productos;

use App\Filament\Clusters\Global\Resources\Productos\Pages\CreateProducto;
use App\Filament\Clusters\Global\Resources\Productos\Pages\EditProducto;
use App\Filament\Clusters\Global\Resources\Productos\Pages\ListProductos;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Illuminate\Database\Eloquent\Model;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Component;
use UnitEnum;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|UnitEnum|null $navigationGroup = 'Catálogo Global';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?string $navigationLabel = 'Productos';

    protected static ?string $modelLabel = 'Producto';

    protected static ?string $pluralModelLabel = 'Productos';

    public static function form(Schema $schema): Schema
    {
        $isCreate = $schema->getLivewire() instanceof CreateProducto;

        $generalFields = [
            TextInput::make('nombre')
                ->required()
                ->maxLength(255),
            Select::make('categoria_id')
                ->label('Categoría')
                ->relationship('categoria', 'nombre', fn (Builder $query) => $query->where('empresa_id', auth()->user()->empresa_id))
                ->searchable()
                ->preload()
                ->required()
                ->createOptionForm([
                    TextInput::make('nombre')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('descripcion')
                        ->maxLength(65535),
                    Toggle::make('estado')
                        ->default(true),
                ])
                ->createOptionUsing(function (array $data): int {
                    $categoria = Categoria::create([
                        'empresa_id' => auth()->user()->empresa_id,
                        'nombre' => $data['nombre'],
                        'descripcion' => $data['descripcion'] ?? null,
                        'estado' => $data['estado'] ?? true,
                    ]);

                    return $categoria->id;
                }),
            Select::make('marca_id')
                ->label('Marca')
                ->relationship('marca', 'nombre', fn (Builder $query) => $query->where('empresa_id', auth()->user()->empresa_id))
                ->searchable()
                ->preload()
                ->required()
                ->createOptionForm([
                    TextInput::make('nombre')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('descripcion')
                        ->maxLength(65535),
                ])
                ->createOptionUsing(function (array $data): int {
                    $marca = Marca::create([
                        'empresa_id' => auth()->user()->empresa_id,
                        'nombre' => $data['nombre'],
                        'descripcion' => $data['descripcion'] ?? null,
                    ]);

                    return $marca->id;
                }),
            TextInput::make('codigo_interno')
                ->maxLength(255)
                ->default(null),
            Textarea::make('descripcion')
                ->maxLength(65535)
                ->default(null)
                ->columnSpanFull(),

        ];

        if ($isCreate) {
            return $schema
                ->components([
                    Section::make('Datos del Producto')
                        ->description('Información general para registrar un nuevo producto')
                        ->columns(2)
                        ->columnSpanFull()
                        ->extraAttributes([
                            'onkeydown' => 'if(event.key === "Enter" && event.target.tagName !== "TEXTAREA") { event.preventDefault(); }',
                        ])
                        ->schema([
                            View::make('filament.components.prevent-nested-submit'),
                            ...$generalFields,
                        ]),
                ]);
        }

        return $schema
            ->components([
                Tabs::make('Detalles del Producto')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Datos Básicos')
                            ->icon('heroicon-m-document-text')
                            ->columns(2)
                            ->schema($generalFields),
                        Tab::make('Presentaciones')
                            ->icon('heroicon-m-squares-2x2')
                            ->schema([
                                View::make('filament.components.presentaciones-manager')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nombre'),
                TextEntry::make('categoria.nombre')
                    ->label('Categoría'),
                TextEntry::make('marca.nombre')
                    ->label('Marca'),
                TextEntry::make('codigo_interno')
                    ->placeholder('-'),
                TextEntry::make('descripcion')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('afecto_igv')
                    ->label('Afecto a IGV')
                    ->boolean(),
                IconEntry::make('activo')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Producto $record): bool => $record->trashed()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail_url')
                    ->label('Imagen')
                    ->width(50)
                    ->height(50)
                    ->defaultImageUrl(fn () => url('/images/no-image.svg'))
                    ->action(function ($record, Component $livewire): void {
                        $presentacionPrioritaria = $record->presentacionPrioritaria();
                        if (! $presentacionPrioritaria) {
                            return;
                        }

                        $imagenUrl = $presentacionPrioritaria->imagen_url;

                        $presentaciones = $record->presentaciones_con_imagen->map(function ($p) {
                            return [
                                'imagen' => $p->imagen_url,
                                'tipo' => $p->tipo_presentacion,
                            ];
                        })->values()->toJson();

                        $livewire->openProductImageModal($imagenUrl, $presentaciones);
                    })
                    ->extraAttributes(['class' => 'cursor-pointer']),
                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('marca.nombre')
                    ->label('Marca')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('codigo_interno')
                    ->label('Cód. Interno')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('presentaciones_count')
                    ->label('Presentaciones')
                    ->counts('presentaciones')
                    ->sortable(),
                IconColumn::make('activo')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
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
            'index' => ListProductos::route('/'),
            'create' => CreateProducto::route('/create'),
            'edit' => EditProducto::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('empresa_id', auth()->user()->empresa_id);
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
        return (auth()->user()?->can('productos.global') && auth()->user()?->can('productos.ver')) ?? false;
    }

    public static function canCreate(): bool
    {
        return (auth()->user()?->can('productos.global') && auth()->user()?->can('productos.crear')) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return (auth()->user()?->can('productos.global') && auth()->user()?->can('productos.editar')) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return (auth()->user()?->can('productos.global') && auth()->user()?->can('productos.eliminar')) ?? false;
    }
}
