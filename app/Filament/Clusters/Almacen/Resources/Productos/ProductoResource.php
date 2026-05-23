<?php

namespace App\Filament\Clusters\Almacen\Resources\Productos;

use App\Filament\Clusters\Almacen\Resources\Productos\Pages\CreateProducto;
use App\Filament\Clusters\Almacen\Resources\Productos\Pages\EditProducto;
use App\Filament\Clusters\Almacen\Resources\Productos\Pages\ListProductos;
use App\Models\Producto;
use BackedEnum;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|UnitEnum|null $navigationGroup = 'Almacén';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?string $navigationLabel = 'Productos';

    protected static ?string $modelLabel = 'Producto';

    protected static ?string $pluralModelLabel = 'Productos';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del Producto')
                    ->description('Información general del producto')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set, $context) {
                                if ($context === 'create') {
                                    $baseSlug = Str::slug($state);
                                    $slug = $baseSlug;
                                    $counter = 1;
                                    while (\App\Models\Producto::where('slug', $slug)->exists()) {
                                        $slug = $baseSlug . '-' . $counter;
                                        $counter++;
                                    }
                                    $set('slug', $slug);
                                }
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->visible(fn ($context) => $context === 'create'),
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
                                $categoria = \App\Models\Categoria::create([
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
                                $marca = \App\Models\Marca::create([
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
                        Toggle::make('afecto_igv')
                            ->label('¿Afecto a IGV?')
                            ->default(true),
                        Toggle::make('activo')
                            ->default(true),
                    ]),
                Section::make('Presentaciones del Producto')
                    ->description('Agregue una o más presentaciones para este producto')
                    ->schema([
                        Repeater::make('presentaciones')
                            ->relationship()
                            ->schema([
                                TextInput::make('tipo_presentacion')
                                    ->label('Tipo de Presentación')
                                    ->placeholder('Escriba para buscar o cree una nueva...')
                                    ->required()
                                    ->datalist(function () {
                                        $empresaId = auth()->user()->empresa_id;
                                        return \App\Models\ProductoPresentacion::query()
                                            ->join('productos', 'producto_presentacion.producto_id', '=', 'productos.id')
                                            ->where('productos.empresa_id', $empresaId)
                                            ->whereNull('producto_presentacion.deleted_at')
                                            ->whereNull('productos.deleted_at')
                                            ->distinct()
                                            ->pluck('producto_presentacion.tipo_presentacion')
                                            ->toArray();
                                    }),
                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                                Select::make('unidad_medida_id')
                                    ->label('Unidad de Medida')
                                    ->relationship('unidadMedida', 'nombre')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                FileUpload::make('imagen')
                                    ->label('Imagen de la Presentación')
                                    ->image()
                                    ->imageEditor()
                                    ->disk('public')
                                    ->directory('productos/presentaciones')
                                    ->visibility('public'),
                                TextInput::make('codigo_barra')
                                    ->label('Código de Barras')
                                    ->maxLength(255)
                                    ->default(null),
                                Toggle::make('es_pesable')
                                    ->label('¿Es pesable?')
                                    ->default(false),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->minItems(0)
                            ->addActionLabel('+ Agregar Presentación')
                            ->reorderable(false)
                            ->collapsible(),
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
                    ->action(function ($record, \Livewire\Component $livewire): void {
                        $presentacionPrioritaria = $record->presentacionPrioritaria();
                        if (!$presentacionPrioritaria) return;

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
}
