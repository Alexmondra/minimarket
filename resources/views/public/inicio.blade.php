<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $empresa?->razon_social ?? config('app.name', 'Mini Market') }}</title>
    <meta name="description" content="Catálogo de productos de {{ $empresa?->razon_social ?? config('app.name', 'Mini Market') }}">

    @fonts

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @livewireStyles

    {{-- 3D Card Tilt Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('[data-3d-tilt]');
            cards.forEach(card => {
                card.addEventListener('mousemove', (e) => {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    const rotateX = ((y - centerY) / centerY) * -8;
                    const rotateY = ((x - centerX) / centerX) * 8;
                    card.style.transform = `perspective(1200px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02,1.02,1.02)`;
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'perspective(1200px) rotateX(0deg) rotateY(0deg) scale3d(1,1,1)';
                });
            });
        });
    </script>

    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px) translateX(0px); }
            33% { transform: translateY(-20px) translateX(10px); }
            66% { transform: translateY(10px) translateX(-10px); }
        }
        @keyframes float-slow {
            0%, 100% { transform: translateY(0px) translateX(0px); }
            50% { transform: translateY(-30px) translateX(-15px); }
        }
        @keyframes pulse-glow {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 0.8; }
        }
        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-slow { animation: float-slow 8s ease-in-out infinite; }
        .animate-pulse-glow { animation: pulse-glow 3s ease-in-out infinite; }
        .animate-gradient { background-size: 200% 200%; animation: gradient-shift 4s ease infinite; }

        [data-3d-tilt] {
            transition: transform 0.15s ease-out, box-shadow 0.3s ease;
            transform-style: preserve-3d;
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 antialiased overflow-x-hidden">

    {{-- ==================== NAVIGATION ==================== --}}
    <header class="fixed inset-x-0 top-0 z-50 border-b border-white/20 bg-white/70 dark:bg-slate-950/70 backdrop-blur-xl supports-[backdrop-filter]:bg-white/60">
        <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="#inicio" class="flex items-center gap-3 group">
                @if ($empresa?->logo)
                    <div class="relative">
                        <div class="absolute -inset-1 rounded-lg bg-gradient-to-r from-emerald-400 to-teal-400 opacity-50 blur group-hover:opacity-75 transition-opacity"></div>
                        <img src="{{ asset('storage/'.$empresa->logo) }}" alt="{{ $empresa->razon_social }}" class="relative size-10 rounded-lg object-cover ring-2 ring-white/50">
                    </div>
                @else
                    <div class="relative">
                        <div class="absolute -inset-1 rounded-xl bg-gradient-to-br from-emerald-400 via-teal-500 to-cyan-500 opacity-60 blur group-hover:opacity-100 transition-opacity animate-gradient"></div>
                        <span class="relative flex size-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-600 to-teal-600 text-lg font-black text-white shadow-lg shadow-emerald-500/25">
                            {{ mb_substr($empresa?->razon_social ?? 'M', 0, 1) }}
                        </span>
                    </div>
                @endif
                <span class="text-base font-black text-slate-900 dark:text-white tracking-tight">
                    {{ $empresa?->razon_social ?? 'Mini Market' }}
                </span>
            </a>

            <div class="hidden items-center gap-1 rounded-2xl bg-slate-100 dark:bg-slate-800/50 p-1 md:flex">
                <a href="#productos" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 transition-all hover:bg-white dark:hover:bg-slate-700 hover:text-emerald-700 dark:hover:text-emerald-400 hover:shadow-sm">Productos</a>
                <a href="#nosotros" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 transition-all hover:bg-white dark:hover:bg-slate-700 hover:text-emerald-700 dark:hover:text-emerald-400 hover:shadow-sm">Nosotros</a>
                <a href="#contacto" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 transition-all hover:bg-white dark:hover:bg-slate-700 hover:text-emerald-700 dark:hover:text-emerald-400 hover:shadow-sm">Contacto</a>
            </div>

            <a href="{{ url('/admin') }}" class="group relative inline-flex items-center gap-2 overflow-hidden rounded-xl bg-gradient-to-r from-slate-900 to-slate-800 dark:from-white dark:to-slate-200 px-5 py-2.5 text-sm font-bold text-white dark:text-slate-900 shadow-lg shadow-slate-900/10 transition-all hover:shadow-xl hover:shadow-slate-900/20 hover:-translate-y-0.5 active:translate-y-0">
                <span class="relative z-10">Ingresar</span>
                <svg class="relative z-10 h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </a>
        </nav>
    </header>

    {{-- ==================== HERO SECTION ==================== --}}
    <section id="inicio" class="relative min-h-screen flex items-center overflow-hidden bg-slate-950">
        {{-- Grid background --}}
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:40px_40px] [mask-image:radial-gradient(ellipse_70%_80%_at_50%_40%,black_40%,transparent_75%)]"></div>

        {{-- Floating orbs --}}
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-emerald-500/20 blur-[120px] animate-float"></div>
        <div class="absolute top-1/2 -right-32 w-80 h-80 rounded-full bg-teal-500/15 blur-[100px] animate-float-slow"></div>
        <div class="absolute -bottom-40 left-1/3 w-72 h-72 rounded-full bg-cyan-500/10 blur-[90px] animate-pulse-glow"></div>

        {{-- Floating minimarket elements --}}
        {{-- Shopping cart --}}
        <div class="absolute top-[18%] right-[12%] w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-400/15 to-teal-500/15 border border-white/10 backdrop-blur-sm rotate-6 animate-float hidden lg:flex items-center justify-center" style="animation-delay: 0.5s;">
            <svg class="h-10 w-10 text-white/30" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
            </svg>
        </div>

        {{-- Shopping bag with items --}}
        <div class="absolute top-[55%] right-[22%] w-16 h-16 rounded-2xl bg-gradient-to-tr from-cyan-400/15 to-blue-500/15 border border-white/10 backdrop-blur-sm -rotate-12 animate-float-slow hidden lg:flex items-center justify-center" style="animation-delay: 1s;">
            <svg class="h-8 w-8 text-white/25" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
            </svg>
        </div>

        {{-- Fresh products box --}}
        <div class="absolute top-[28%] left-[8%] w-16 h-16 rounded-2xl bg-gradient-to-bl from-amber-400/15 to-orange-500/15 border border-white/10 backdrop-blur-sm -rotate-6 animate-float hidden lg:flex items-center justify-center" style="animation-delay: 1.5s;">
            <svg class="h-8 w-8 text-white/25" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
            </svg>
        </div>

        {{-- Price tag --}}
        <div class="absolute top-[70%] left-[18%] w-14 h-14 rounded-full bg-gradient-to-br from-rose-400/10 to-pink-500/10 border border-white/10 backdrop-blur-sm rotate-12 animate-float-slow hidden lg:flex items-center justify-center" style="animation-delay: 2s;">
            <svg class="h-7 w-7 text-white/20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>
            </svg>
        </div>

        {{-- Fresh vegetable/fruit leaf --}}
        <div class="absolute top-[40%] right-[35%] w-12 h-12 rounded-xl bg-gradient-to-tr from-lime-400/10 to-emerald-500/10 border border-white/10 backdrop-blur-sm rotate-45 animate-float hidden lg:flex items-center justify-center" style="animation-delay: 0.8s;">
            <svg class="h-6 w-6 text-white/20 -rotate-45" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>
            </svg>
        </div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 py-32 sm:px-6 lg:px-8 lg:py-40">
            <div class="max-w-3xl">
                {{-- Badge --}}
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-4 py-1.5 backdrop-blur-sm mb-6">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-300">Catálogo por sucursal</span>
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-7xl font-black tracking-tight text-white leading-[1.05]">
                    {{ $empresa?->razon_social ?? 'Mini Market' }}
                    <span class="block mt-3 text-transparent bg-clip-text bg-gradient-to-r from-emerald-300 via-teal-300 to-cyan-300 animate-gradient">
                        Productos frescos, siempre disponibles
                    </span>
                </h1>

                <p class="mt-6 max-w-xl text-base sm:text-lg leading-relaxed text-slate-300/90">
                    Explora nuestro catálogo de productos organizados por categorías.
                    Calidad, variedad y atención cercana en tu minimarket de confianza.
                </p>

                <div class="mt-10 flex flex-col sm:flex-row gap-4">
                    <a href="#productos" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-8 py-4 text-base font-black text-white shadow-2xl shadow-emerald-500/25 transition-all duration-300 hover:shadow-emerald-500/50 hover:-translate-y-1 active:translate-y-0">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        Explorar Productos
                    </a>
                    <a href="https://wa.me/{{ $sucursalPrincipal?->telefono ?? '51999999999' }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-white/20 bg-white/5 backdrop-blur-sm px-8 py-4 text-base font-black text-white transition-all duration-300 hover:bg-white/15 hover:border-white/40 hover:-translate-y-1 active:translate-y-0">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                        WhatsApp
                    </a>
                </div>

                {{-- Stats row --}}
                <div class="mt-14 grid grid-cols-3 gap-6 max-w-lg">
                    <div>
                        <p class="text-2xl font-black text-white font-mono">{{ $empresa?->sucursales()->where('activo', true)->count() ?? 0 }}</p>
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mt-1">Sucursales</p>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-white font-mono">24/7</p>
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mt-1">Disponible</p>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-white font-mono">100%</p>
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mt-1">Calidad</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Scroll indicator --}}
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 animate-bounce">
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Scroll</span>
            <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
        </div>
    </section>

    {{-- ==================== FEATURES STRIP ==================== --}}
    <section class="relative -mt-1 bg-slate-900">
        <div class="mx-auto grid max-w-7xl gap-4 px-4 py-8 sm:grid-cols-3 sm:px-6 lg:px-8">
            <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-400">Sucursales</p>
                    <p class="mt-1 text-sm text-slate-400 leading-relaxed">Precios y disponibilidad según la tienda que elijas.</p>
                </div>
            </div>
            <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-500/20 text-teal-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-teal-400">Catálogo</p>
                    <p class="mt-1 text-sm text-slate-400 leading-relaxed">Productos activos organizados por categorías y marcas.</p>
                </div>
            </div>
            <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-500/20 text-cyan-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-cyan-400">Atención</p>
                    <p class="mt-1 text-sm text-slate-400 leading-relaxed">Consulta directa con el negocio vía WhatsApp o teléfono.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== PRODUCT CATALOG (Livewire) ==================== --}}
    @if ($empresa)
        <livewire:publico.catalogo-productos :empresaId="$empresa->id" />
    @else
        <section class="bg-white dark:bg-slate-900 py-20 text-center">
            <p class="text-slate-500">No hay productos disponibles.</p>
        </section>
    @endif

    {{-- ==================== NOSOTROS ==================== --}}
    <section id="nosotros" class="relative overflow-hidden bg-slate-50 dark:bg-slate-900">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808006_1px,transparent_1px),linear-gradient(to_bottom,#80808006_1px,transparent_1px)] bg-[size:20px_20px] pointer-events-none"></div>
        <div class="relative z-10 mx-auto grid max-w-7xl gap-10 px-4 py-20 sm:px-6 lg:grid-cols-[1fr_1fr] lg:px-8 lg:py-28">
            <div class="flex flex-col justify-center">
                <span class="text-sm font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Nosotros</span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                    Tu minimarket de confianza, pensado para compras rápidas y claras.
                </h2>
                <p class="mt-4 max-w-lg text-base leading-relaxed text-slate-600 dark:text-slate-400">
                    Somos un negocio local enfocado en ofrecerte productos frescos, buena atención
                    y disponibilidad por sucursal. Cada cliente puede revisar nuestro catálogo
                    desde cualquier dispositivo.
                </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div data-3d-tilt class="group relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-white dark:bg-slate-800/60 p-6 shadow-sm transition-shadow hover:shadow-xl">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-500 rounded-t-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 mb-4 group-hover:scale-110 transition-transform">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">Surtido diario</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">Trabajamos con productos frescos y un surtido pensado para el día a día de tu hogar.</p>
                </div>
                <div data-3d-tilt class="group relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-white dark:bg-slate-800/60 p-6 shadow-sm transition-shadow hover:shadow-xl">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-violet-400 to-purple-500 rounded-t-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10 text-violet-600 dark:text-violet-400 mb-4 group-hover:scale-110 transition-transform">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">Precios por sucursal</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">Elige tu sucursal más cercana y consulta el catálogo actualizado para esa ubicación.</p>
                </div>
                <div data-3d-tilt class="group relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-white dark:bg-slate-800/60 p-6 shadow-sm transition-shadow hover:shadow-xl sm:col-span-2">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-orange-500 rounded-t-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 group-hover:scale-110 transition-transform">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Atención personalizada</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">Contáctanos directamente por WhatsApp o visítanos. Estamos listos para ayudarte.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== CONTACTO ==================== --}}
    <section id="contacto" class="relative overflow-hidden bg-white dark:bg-slate-950">
        <div class="absolute top-0 right-0 w-96 h-96 rounded-full bg-emerald-500/5 blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 rounded-full bg-teal-500/5 blur-[100px] pointer-events-none"></div>
        <div class="relative z-10 mx-auto grid max-w-7xl gap-10 px-4 py-20 sm:px-6 lg:grid-cols-[1fr_1fr] lg:px-8 lg:py-28">
            <div class="flex flex-col justify-center">
                <span class="text-sm font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Contáctanos</span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                    Estamos listos para atenderte.
                </h2>
                <p class="mt-4 max-w-lg text-base leading-relaxed text-slate-600 dark:text-slate-400">
                    Visítanos en nuestra tienda, escríbenos por WhatsApp o llámanos.
                    Siempre hay alguien disponible para ayudarte.
                </p>
            </div>
            <div class="grid gap-4">
                <div data-3d-tilt class="group relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-900/60 p-5 shadow-sm hover:shadow-lg transition-shadow">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Teléfono</p>
                            <p class="text-base font-black text-slate-900 dark:text-white mt-0.5">{{ $sucursalPrincipal?->telefono ?? 'Por definir' }}</p>
                        </div>
                    </div>
                </div>
                <div data-3d-tilt class="group relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-900/60 p-5 shadow-sm hover:shadow-lg transition-shadow">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 group-hover:scale-110 transition-transform">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Dirección</p>
                            <p class="text-base font-black text-slate-900 dark:text-white mt-0.5">{{ $empresa?->direccion_fiscal ?: 'Por definir' }}</p>
                        </div>
                    </div>
                </div>
                <div data-3d-tilt class="group relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-900/60 p-5 shadow-sm hover:shadow-lg transition-shadow">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 group-hover:scale-110 transition-transform">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Horario</p>
                            <p class="text-base font-black text-slate-900 dark:text-white mt-0.5">Lunes a domingo, 8:00 a.m. - 9:00 p.m.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== FOOTER ==================== --}}
    <footer class="relative overflow-hidden bg-slate-950">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff04_1px,transparent_1px),linear-gradient(to_bottom,#ffffff04_1px,transparent_1px)] bg-[size:32px_32px] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_50%,black,transparent)]"></div>
        <div class="relative z-10 mx-auto flex max-w-7xl flex-col gap-6 px-4 py-12 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <span class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-500 text-sm font-black text-white shadow-lg shadow-emerald-500/20">
                    {{ mb_substr($empresa?->razon_social ?? 'M', 0, 1) }}
                </span>
                <p class="font-black text-white">{{ $empresa?->razon_social ?? 'Mini Market' }}</p>
            </div>
            <div class="flex flex-wrap gap-4 text-sm text-slate-400">
                <span>{{ $sucursalPrincipal?->email ?? 'contacto@minimarket.local' }}</span>
                <span class="text-slate-600">/</span>
                <span>{{ $sucursalPrincipal?->telefono ?? '' }}</span>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
