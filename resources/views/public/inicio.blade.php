@php
    $nombre = $empresa?->razon_social ?? config('app.name', 'Mini Market');
    $direccion = $empresa?->direccion_fiscal ?? '';
    $logo = $empresa?->logo;
    $sucursalPrincipal = $empresa?->sucursales()->where('activo', true)->orderBy('id')->first();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $nombre }}</title>
        <meta name="description" content="Catalogo de productos de {{ $nombre }}">

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        @livewireStyles
    </head>
    <body class="bg-white font-sans text-gray-950 antialiased">
        <header class="fixed inset-x-0 top-0 z-40 border-b border-white/20 bg-white/90 backdrop-blur">
            <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <a href="#inicio" class="flex items-center gap-3">
                    @if ($logo)
                        <img src="{{ asset('storage/'.$logo) }}" alt="{{ $nombre }}" class="size-10 rounded-md object-cover">
                    @else
                        <span class="flex size-10 items-center justify-center rounded-md bg-emerald-700 text-lg font-black text-white">
                            {{ mb_substr($nombre, 0, 1) }}
                        </span>
                    @endif
                    <span class="text-base font-black text-gray-950">{{ $nombre }}</span>
                </a>

                <div class="hidden items-center gap-7 text-sm font-semibold text-gray-700 md:flex">
                    <a href="#productos" class="transition hover:text-emerald-700">Productos</a>
                    <a href="#nosotros" class="transition hover:text-emerald-700">Nosotros</a>
                    <a href="#contacto" class="transition hover:text-emerald-700">Contactanos</a>
                </div>

                <a href="{{ url('/admin') }}" class="rounded-md bg-gray-950 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-800">
                    Ingresar
                </a>
            </nav>
        </header>

        <main id="inicio">
            <section
                class="relative overflow-hidden bg-cover bg-center pt-28"
                style="background-image: linear-gradient(90deg, rgba(10, 20, 16, 0.82), rgba(10, 20, 16, 0.48), rgba(10, 20, 16, 0.18)), url('{{ asset('images/landing/minimarket-hero.webp') }}');"
            >
                <div class="mx-auto max-w-7xl px-4 pb-20 pt-20 sm:px-6 lg:px-8 lg:pb-24 lg:pt-24">
                    <div class="max-w-2xl text-white">
                        <p class="text-sm font-bold uppercase tracking-wide text-amber-300">Catalogo por sucursal</p>
                        <h1 class="mt-4 text-4xl font-black leading-tight sm:text-5xl lg:text-6xl">
                            {{ $nombre }}
                        </h1>
                        <p class="mt-5 max-w-xl text-base leading-7 text-white/85 sm:text-lg">
                            Productos frescos, precios claros y atencion cercana.
                        </p>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="#productos" class="rounded-md bg-amber-400 px-5 py-3 text-center text-sm font-black text-gray-950 transition hover:bg-amber-300">
                                Ver productos
                            </a>
                            <a href="https://wa.me/{{ $sucursalPrincipal?->telefono ?? '51999999999' }}" target="_blank" class="rounded-md border border-white/60 px-5 py-3 text-center text-sm font-black text-white transition hover:bg-white hover:text-gray-950">
                                Consultar por WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-gray-950 text-white">
                <div class="mx-auto grid max-w-7xl gap-4 px-4 py-5 sm:grid-cols-3 sm:px-6 lg:px-8">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-300">Sucursales</p>
                        <p class="mt-1 text-sm text-white/80">Precios segun tienda disponible.</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-300">Inventario</p>
                        <p class="mt-1 text-sm text-white/80">Productos activos y presentaciones.</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-300">Atencion</p>
                        <p class="mt-1 text-sm text-white/80">Consulta directa con el negocio.</p>
                    </div>
                </div>
            </section>

            @if ($empresa)
                <livewire:publico.catalogo-productos :empresaId="$empresa->id" />
            @else
                <section class="bg-white py-20 text-center">
                    <p class="text-gray-500">No hay productos disponibles.</p>
                </section>
            @endif

            <section id="nosotros" class="bg-gray-50">
                <div class="mx-auto grid max-w-7xl gap-8 px-4 py-16 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-emerald-700">Nosotros</p>
                        <h2 class="mt-3 text-3xl font-black text-gray-950">Una tienda pensada para compras rapidas y claras.</h2>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <p class="text-lg font-black text-gray-950">Surtido diario</p>
                            <p class="mt-2 text-sm leading-6 text-gray-600">Somos un minimarket local enfocado en surtido diario, buena atencion y disponibilidad por sucursal.</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <p class="text-lg font-black text-gray-950">Precios por sucursal</p>
                            <p class="mt-2 text-sm leading-6 text-gray-600">Cada cliente puede revisar el precio de venta segun la tienda donde desea comprar.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="contacto" class="bg-white">
                <div class="mx-auto grid max-w-7xl gap-8 px-4 py-16 sm:px-6 lg:grid-cols-[1fr_1fr] lg:px-8">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-emerald-700">Contactanos</p>
                        <h2 class="mt-3 text-3xl font-black text-gray-950">Estamos listos para atenderte.</h2>
                        <p class="mt-4 max-w-xl text-sm leading-6 text-gray-600">Visitanos en nuestra tienda o contactanos por telefono.</p>
                    </div>

                    <div class="grid gap-3">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Telefono</p>
                            <p class="mt-1 font-bold text-gray-950">{{ $sucursalPrincipal?->telefono ?? 'Por definir' }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Direccion</p>
                            <p class="mt-1 font-bold text-gray-950">{{ $direccion ?: 'Por definir' }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Horario</p>
                            <p class="mt-1 font-bold text-gray-950">Lunes a domingo, 8:00 a.m. - 9:00 p.m.</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-gray-200 bg-gray-950">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-8 text-sm text-white/70 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <p class="font-semibold text-white">{{ $nombre }}</p>
                <p>{{ $sucursalPrincipal?->email ?? 'contacto@minimarket.local' }} / {{ $sucursalPrincipal?->telefono ?? '' }}</p>
            </div>
        </footer>

        @livewireScripts
    </body>
</html>
