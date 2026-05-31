<?php

namespace App\Filament\Clusters\Ventas\Resources\Caja;

use App\Filament\Clusters\Ventas\Resources\Caja\Pages\ListCaja;
use App\Models\SessioneCaja;
use App\Support\SucursalContext;
use App\Support\Ventas\CajaService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class CajaResource extends Resource
{
    protected static ?string $model = SessioneCaja::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $navigationLabel = 'Caja';

    public static function getPages(): array
    {
        return [
            'index' => ListCaja::route('/'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sucursal.nombre_sucursal')
                    ->label('Sucursal')
                    ->searchable()
                    ->icon('heroicon-o-building-storefront')
                    ->iconColor('gray'),
                TextColumn::make('user.name')
                    ->label('Cajero')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->iconColor('gray')
                    ->visible(fn () => Auth::user()?->hasRole('Administrador')),
                TextColumn::make('fecha_apertura')
                    ->label('Apertura')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-play')
                    ->iconColor('success'),
                TextColumn::make('fecha_cierre')
                    ->label('Cierre')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->icon('heroicon-o-stop')
                    ->iconColor('danger')
                    ->sortable(),
                
               
                TextColumn::make('diferencia')
                    ->label('Diferencia')
                    ->money('PEN')
                    ->color(fn ($state) => $state === null ? 'gray' : ((float) $state === 0.0 ? 'success' : 'danger'))
                    ->placeholder('-')
                    ->icon(fn ($state) => $state === null ? 'heroicon-o-minus' : ((float) $state === 0.0 ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-circle')),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Abierta' : 'Cerrada')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed'),
            ])
            ->actions([
                Action::make('verDetalles')
                    ->label('Ver detalles')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Detalles de la Sesión de Caja')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalWidth('2xl')
                    ->modalContent(function (SessioneCaja $record) {
                        $ventasQuery = $record->documentos()->where('estado', true);
                        $totalVentas = (float) $ventasQuery->sum('total_neto');

                        $efectivo = (float) $record->documentos()->where('estado', true)->where('medio_pago', 'EFECTIVO')->sum('total_neto');
                        $yape = (float) $record->documentos()->where('estado', true)->where('medio_pago', 'YAPE')->sum('total_neto');
                        $plin = (float) $record->documentos()->where('estado', true)->where('medio_pago', 'PLIN')->sum('total_neto');
                        $transferencia = (float) $record->documentos()->where('estado', true)->where('medio_pago', 'TRANSFERENCIA')->sum('total_neto');
                        $tarjeta = (float) $record->documentos()->where('estado', true)->where('medio_pago', 'TARJETA')->sum('total_neto');
                        $otro = (float) $record->documentos()->where('estado', true)->where('medio_pago', 'OTRO')->sum('total_neto');

                        return view('filament.clusters.ventas.resources.caja.pages.caja-detail-modal', [
                            'caja' => $record,
                            'totalVentas' => $totalVentas,
                            'efectivo' => $efectivo,
                            'yape' => $yape,
                            'plin' => $plin,
                            'transferencia' => $transferencia,
                            'tarjeta' => $tarjeta,
                            'otro' => $otro,
                        ]);
                    }),
                Action::make('cerrarCaja')
                    ->label('Cerrar caja')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->visible(fn (SessioneCaja $record) => $record->estado && $record->user_id === Auth::id() && (Auth::user()?->can('cajas.cerrar') ?? false))
                    ->modalHeading('Cerrar Sesión de Caja')
                    ->modalDescription('Por favor, cuente el efectivo físico en caja y verifique la diferencia antes de cerrar la sesión.')
                    ->form([
                        \Filament\Forms\Components\Placeholder::make('saldo_esperado_card')
                            ->label('')
                            ->content(function (SessioneCaja $record) {
                                $inicial = $record->saldo_inicial;
                                $teorico = app(CajaService::class)->saldoTeorico($record);
                                $ventasEfectivo = $teorico - $inicial;

                                return new \Illuminate\Support\HtmlString("
                                    <div class='grid grid-cols-1 md:grid-cols-2 gap-4 pb-4'>
                                        <!-- Card 1: Inicial -->
                                        <div class='flex items-center gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-850 shadow-sm'>
                                            <div class='flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400'>
                                                <svg class='h-6 w-6' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor'>
                                                    <path stroke-linecap='round' stroke-linejoin='round' d='M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class='text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider'>Saldo Inicial</p>
                                                <p class='text-lg font-black text-slate-800 dark:text-slate-200'>S/ " . number_format($inicial, 2) . "</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Card 2: Teórico Total -->
                                        <div class='flex items-center gap-4 p-4 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/5 border border-emerald-500/20 dark:border-emerald-500/10 shadow-sm'>
                                            <div class='flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-md shadow-emerald-500/10'>
                                                <svg class='h-6 w-6' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor'>
                                                    <path stroke-linecap='round' stroke-linejoin='round' d='M9 7h6m0 10v-3m-3 3v-3m-3 3v-3M9 17h6M4 9h16a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2v-8a2 2 0 012-2zm2-5V4a2 2 0 012-2h8a2 2 0 012 2v2M8 12v1m4-1v1m4-1v1' />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class='text-xs font-semibold text-emerald-600 dark:text-emerald-450 uppercase tracking-wider'>Saldo Esperado</p>
                                                <p class='text-xl font-black text-emerald-700 dark:text-emerald-350'>S/ " . number_format($teorico, 2) . "</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='text-[11px] text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 pb-3 mb-2 flex justify-between font-medium'>
                                        <span>Ventas en efectivo registradas: S/ " . number_format($ventasEfectivo, 2) . "</span>
                                        <span>Fórmula: Inicial + Ventas en Efectivo</span>
                                    </div>
                                ");
                            }),
                        TextInput::make('saldo_real')
                            ->label('Dinero físico en caja')
                            ->numeric()
                            ->prefix('S/')
                            ->required()
                            ->live()
                            ->extraInputAttributes(['class' => 'text-xl font-black tracking-tight text-slate-900 dark:text-white'])
                            ->helperText('Ingrese la cantidad total de dinero en efectivo físico encontrado en la caja.'),
                        \Filament\Forms\Components\Placeholder::make('diferencia_calculada')
                            ->label('')
                            ->content(function ($get, SessioneCaja $record) {
                                $real = $get('saldo_real');
                                if ($real === null || $real === '') {
                                    return new \Illuminate\Support\HtmlString("
                                        <div class='flex items-center gap-3 p-4 rounded-xl border border-dashed border-slate-300 dark:border-slate-800 bg-slate-50/55 dark:bg-slate-950/20 text-slate-400 dark:text-slate-500 justify-center text-xs font-semibold py-6 transition-all duration-300'>
                                            <svg class='h-5 w-5 animate-pulse text-slate-400 dark:text-slate-600' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor'>
                                                <path stroke-linecap='round' stroke-linejoin='round' d='M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z' />
                                            </svg>
                                            <span>Ingrese el dinero físico para calcular la diferencia</span>
                                        </div>
                                    ");
                                }
                                $teorico = app(CajaService::class)->saldoTeorico($record);
                                $diff = round((float) $real - $teorico, 2);
                                if ($diff > 0) {
                                    return new \Illuminate\Support\HtmlString("
                                        <div class='p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 dark:bg-amber-950/20 dark:border-amber-900/30 flex items-start gap-4 transition-all duration-300 shadow-sm'>
                                            <div class='flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-md shadow-amber-500/20'>
                                                <svg class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='currentColor'>
                                                    <path stroke-linecap='round' stroke-linejoin='round' d='M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' />
                                                </svg>
                                            </div>
                                            <div class='space-y-0.5 text-left'>
                                                <h4 class='text-xs font-bold text-amber-800 dark:text-amber-450 uppercase tracking-wider'>Sobrante de Caja Detectado</h4>
                                                <div class='flex items-baseline gap-1.5'>
                                                    <span class='text-2xl font-black text-amber-700 dark:text-amber-450'>+ S/ " . number_format($diff, 2) . "</span>
                                                    <span class='text-[10px] text-amber-600 dark:text-amber-500 font-bold bg-amber-500/15 dark:bg-amber-500/10 px-2 py-0.5 rounded'>Sobrante</span>
                                                </div>
                                                <p class='text-[10px] text-amber-655 dark:text-amber-500 pt-1 leading-normal'>El dinero real supera el saldo esperado. Por favor, especifique el motivo en las observaciones.</p>
                                            </div>
                                        </div>
                                    ");
                                } elseif ($diff < 0) {
                                    return new \Illuminate\Support\HtmlString("
                                        <div class='p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 dark:bg-rose-950/20 dark:border-rose-900/30 flex items-start gap-4 transition-all duration-300 shadow-sm'>
                                            <div class='flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-500 text-white shadow-md shadow-rose-500/20'>
                                                <svg class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='currentColor'>
                                                    <path stroke-linecap='round' stroke-linejoin='round' d='M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' />
                                                </svg>
                                            </div>
                                            <div class='space-y-0.5 text-left'>
                                                <h4 class='text-xs font-bold text-rose-800 dark:text-rose-400 uppercase tracking-wider'>Faltante de Caja Detectado</h4>
                                                <div class='flex items-baseline gap-1.5'>
                                                    <span class='text-2xl font-black text-rose-700 dark:text-rose-455'>S/ " . number_format($diff, 2) . "</span>
                                                    <span class='text-[10px] text-rose-600 dark:text-rose-500 font-bold bg-rose-500/15 dark:bg-rose-500/10 px-2 py-0.5 rounded'>Faltante</span>
                                                </div>
                                                <p class='text-[10px] text-rose-655 dark:text-rose-500 pt-1 leading-normal'>El dinero real es menor que el saldo esperado. Esta diferencia quedará registrada como faltante.</p>
                                            </div>
                                        </div>
                                    ");
                                } else {
                                    return new \Illuminate\Support\HtmlString("
                                        <div class='p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 dark:bg-emerald-950/20 dark:border-emerald-900/30 flex items-start gap-4 transition-all duration-300 shadow-sm'>
                                            <div class='flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-md shadow-emerald-500/20'>
                                                <svg class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='currentColor'>
                                                    <path stroke-linecap='round' stroke-linejoin='round' d='M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' />
                                                </svg>
                                            </div>
                                            <div class='space-y-0.5 text-left'>
                                                <h4 class='text-xs font-bold text-emerald-800 dark:text-emerald-450 uppercase tracking-wider'>¡Caja Cuadrada Perfecta!</h4>
                                                <div class='flex items-baseline gap-1.5'>
                                                    <span class='text-2xl font-black text-emerald-700 dark:text-emerald-450'>S/ 0.00</span>
                                                    <span class='text-[10px] text-emerald-600 dark:text-emerald-500 font-bold bg-emerald-500/15 dark:bg-emerald-500/10 px-2 py-0.5 rounded'>Cuadrada</span>
                                                </div>
                                                <p class='text-[10px] text-emerald-655 dark:text-emerald-550 pt-1 leading-normal'>El saldo físico coincide exactamente con el saldo esperado. ¡Excelente trabajo!</p>
                                            </div>
                                        </div>
                                    ");
                                }
                            }),
                        Textarea::make('observaciones')
                            ->label('Observaciones de Cierre')
                            ->placeholder('Ingrese comentarios o justificaciones de diferencias si las hay...')
                            ->rows(3),
                    ])
                    ->action(function (array $data, SessioneCaja $record): void {
                        $teorico = app(CajaService::class)->saldoTeorico($record);
                        $real = round((float) $data['saldo_real'], 2);

                        $apertura = $record->getObservacionApertura();
                        $cierre = trim($data['observaciones'] ?? '');
                        $formattedObservations = "APERTURA: {$apertura}\nCIERRE: {$cierre}";

                        $record->update([
                            'fecha_cierre' => now(),
                            'saldo_teorico' => $teorico,
                            'saldo_real' => $real,
                            'diferencia' => round($real - $teorico, 2),
                            'estado' => false,
                            'observaciones' => $formattedObservations,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Caja cerrada con éxito')
                            ->success()
                            ->send();
                    }),
            ])
            ->filters([
                Filter::make('fecha_apertura')
                    ->label('Fecha')
                    ->form([
                        DatePicker::make('fecha')
                            ->label('Seleccionar Fecha')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->placeholder('dd/mm/aaaa'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['fecha'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_apertura', $date),
                            );
                    }),
                SelectFilter::make('sucursal_id')
                    ->label('Sucursal')
                    ->relationship('sucursal', 'nombre_sucursal')
                    ->preload(),
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        '1' => 'Abierta',
                        '0' => 'Cerrada',
                    ]),
                SelectFilter::make('user_id')
                    ->label('Cajero')
                    ->relationship('user', 'name')
                    ->preload()
                    ->visible(fn () => Auth::user()?->hasRole('Administrador')),
            ])
            ->recordAction('verDetalles')
            ->defaultSort('fecha_apertura', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['sucursal', 'user']);
        
        if (Auth::user()?->hasRole('Administrador')) {
            return $query;
        }

        // Cada usuario solo ve sus propias cajas
        return $query->where('user_id', Auth::id());
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('cajas.ver') ?? false;
    }
}
