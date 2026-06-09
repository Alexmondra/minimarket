<div class="w-full lg:h-screen flex flex-col lg:flex-row overflow-y-auto lg:overflow-hidden relative font-sans bg-cover bg-center bg-no-repeat bg-fixed"
     style="background-image: url('{{ asset('images/landing/minimarket-hero.webp') }}');">
    
    <!-- No background gradient mask overlay (removed completely as requested to keep background clear) -->

    <div class="w-full flex flex-col lg:flex-row relative z-10 lg:h-screen">
        <!-- LEFT PANEL: Brand Info & Features (Responsive: Order 2, goes below login on mobile) -->
        <div class="order-2 lg:order-1 w-full lg:w-1/2 flex flex-col justify-center items-center p-6 lg:py-12 lg:pl-16 lg:pr-8 xl:pl-24 xl:pr-12 lg:h-screen relative z-10 shrink-0">
            <!-- Glassmorphic Features Container ("Cajoncito" that embraces the left panel text) -->
            <div class="w-full max-w-[450px] bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border border-white/20 dark:border-slate-800 rounded-[28px] p-6 md:p-8 shadow-2xl shadow-slate-900/10 dark:shadow-slate-950/50 animate-fade-in space-y-5">
                
                <!-- Brand Logo Header -->
                <div class="flex items-center gap-3">
                    <!-- Orange Shopping Cart Icon -->
                    <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white p-2.5 rounded-2xl shadow-lg shadow-orange-500/20 hover:scale-105 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5.5 h-5.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.116 60.116 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center flex-wrap gap-x-1 leading-none">
                            <span class="text-slate-900 dark:text-white font-black text-xl tracking-tight font-sans">Market</span>
                            <span class="text-orange-500 font-black text-xl tracking-tight font-sans">G0</span>
                            <span class="text-slate-700 dark:text-slate-300 font-bold text-xs tracking-wide uppercase font-sans">Food Market</span>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-[9px] font-bold uppercase tracking-wider mt-1">Tu supermercado inteligente</p>
                    </div>
                </div>

                <!-- Divider -->
                <div class="h-px bg-slate-200/50 dark:bg-slate-800/50"></div>

                <!-- Headline -->
                <div class="space-y-2">
                    <h1 class="text-2xl lg:text-3xl font-black text-slate-900 dark:text-white leading-tight">
                        Gestiona tu negocio <br>
                        <span class="text-orange-500">de forma inteligente</span>
                    </h1>
                    <p class="text-slate-600 dark:text-slate-400 text-xs lg:text-sm leading-relaxed">
                        Todo lo que necesitas para administrar tu supermercado, en un solo lugar.
                    </p>
                </div>

                <!-- Feature items -->
                <div class="space-y-4">
                    <!-- Feature 1 -->
                    <div class="flex items-start gap-3.5 group">
                        <div class="bg-orange-100/60 dark:bg-orange-500/10 p-2 rounded-xl text-orange-600 dark:text-orange-400 shrink-0 shadow-sm group-hover:scale-110 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v5.75c0 .621-.504 1.125-1.125 1.125h-2.25A1.125 1.125 0 0 1 3 18.875v-5.75ZM18.625 12c.621 0 1.125.504 1.125 1.125v5.75c0 .621-.504 1.125-1.125 1.125h-2.25A1.125 1.125 0 0 1 15 18.875v-5.75c0-.621.504-1.125 1.125-1.125h-2.25ZM7.5 12c.621 0 1.125.504 1.125 1.125v5.75c0 .621-.504 1.125-1.125 1.125h-2.25A1.125 1.125 0 0 1 7.5 18.875v-5.75c0-.621.504-1.125 1.125-1.125h-2.25Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75h6M9 6h6m-6 2.25h6M9 10.5h6" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.75 9.75 0 1 0 0-19.5 9.75 9.75 0 0 0 0 19.5Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-slate-800 dark:text-slate-200 font-extrabold text-sm">Control total</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">Monitorea ventas, inventario y más en tiempo real.</p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="flex items-start gap-3.5 group">
                        <div class="bg-green-100/60 dark:bg-green-500/10 p-2 rounded-xl text-green-600 dark:text-green-400 shrink-0 shadow-sm group-hover:scale-110 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-slate-800 dark:text-slate-200 font-extrabold text-sm">Inventario eficiente</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">Administra tus productos y stock de manera fácil y rápida.</p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="flex items-start gap-3.5 group">
                        <div class="bg-blue-100/60 dark:bg-blue-500/10 p-2 rounded-xl text-blue-600 dark:text-blue-400 shrink-0 shadow-sm group-hover:scale-110 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A11.386 11.386 0 0 1 10.089 20.4c-2.114 0-4.084-.567-5.786-1.557m0 0V19.1c0-2.31 1.87-4.18 4.18-4.18h1.02c2.31 0 4.18 1.87 4.18 4.18v.143m-8.989-.109C3.045 18.1 3 17.061 3 16c0-2.308 1.87-4.18 4.18-4.18h1.02a4.18 4.18 0 0 1 3.513 1.905m-.008-5.321a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM18 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-slate-800 dark:text-slate-200 font-extrabold text-sm">Equipo conectado</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">Colabora con tu equipo desde cualquier lugar.</p>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="h-px bg-slate-200/50 dark:bg-slate-800/50"></div>

                <!-- Trust Badge (Now simplified inside the container) -->
                <div class="flex items-center justify-center gap-2 text-xs text-slate-500 dark:text-slate-400 font-bold text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-amber-500 shrink-0">
                        <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                    </svg>
                    <span>Más de 2,000 supermercados confían en nosotros</span>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: Login Form Card (Responsive: Order 1, appears first on mobile) -->
        <div class="order-1 lg:order-2 w-full lg:w-1/2 flex flex-col justify-center items-center p-6 md:p-8 lg:py-12 lg:pr-16 lg:pl-8 xl:pr-24 xl:pl-12 lg:h-screen relative z-10 shrink-0">
            
            <!-- Orange decorative swoosh in background -->
            <div class="absolute -bottom-48 -left-48 w-96 h-96 bg-gradient-to-tr from-amber-500/20 to-orange-600/20 rounded-full blur-3xl -z-10"></div>
            
            <div class="w-full max-w-[430px] space-y-4">
                <!-- Floating Glassmorphic Login Card -->
                <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border border-white/20 dark:border-slate-800 rounded-[28px] p-6 md:p-8 shadow-2xl shadow-slate-900/10 dark:shadow-slate-950/50 animate-fade-in">
                    
                    <!-- Grocery Basket Image Illustration with Success Checkmark Badge -->
                    <div class="relative flex justify-center mb-4">
                        <div class="relative w-24 h-24 lg:w-28 lg:h-28">
                            <img src="{{ asset('images/landing/shopping-basket.png') }}" 
                                 alt="Canasta de bienvenida" 
                                 class="w-full h-full object-contain hover:scale-105 transition-transform duration-300">
                            
                            <!-- Green Checkmark Badge -->
                            <div class="absolute bottom-0 right-1 bg-green-500 text-white p-0.5 rounded-full border-4 border-white dark:border-slate-900 shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Welcome Headers -->
                    <div class="text-center space-y-1 mb-5">
                        <h2 class="text-xl lg:text-2xl font-black text-slate-800 dark:text-white">¡Bienvenido de nuevo!</h2>
                        <div class="w-10 h-0.5 bg-amber-500 rounded-full mx-auto my-1"></div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Ingresa a tu cuenta para continuar</p>
                    </div>

                    <!-- Form Content -->
                    <form wire:submit="authenticate" class="space-y-4">
                        <!-- Email Input -->
                        <div class="space-y-1.5">
                            <label for="email" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Correo electrónico
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                    </svg>
                                </div>
                                <input type="email" 
                                       id="email" 
                                       wire:model="data.email" 
                                       placeholder="Ingresa tu correo electrónico" 
                                       required
                                       autofocus
                                       class="block w-full pl-11 pr-4 py-2.5 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 dark:focus:ring-amber-500/5 dark:focus:border-amber-400 transition-all duration-300 text-sm">
                            </div>
                            @error('data.email')
                                <p class="text-red-500 dark:text-red-400 text-xs font-semibold mt-1 animate-fade-in">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Password Input -->
                        <div class="space-y-1.5">
                            <label for="password" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Contraseña
                            </label>
                            <div class="relative" x-data="{ show: false }">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                    </svg>
                                </div>
                                <input :type="show ? 'text' : 'password'" 
                                       id="password" 
                                       wire:model="data.password" 
                                       placeholder="Ingresa tu contraseña" 
                                       required
                                       class="block w-full pl-11 pr-11 py-2.5 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 dark:focus:ring-amber-500/5 dark:focus:border-amber-400 transition-all duration-300 text-sm">
                                
                                <!-- Visibility Toggle Icon -->
                                <button type="button" 
                                        @click="show = !show" 
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none transition-colors duration-200">
                                    <!-- Eye Icon (hidden) -->
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5" x-show="!show">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <!-- Eye Off Icon (visible) -->
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5" x-show="show" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            @error('data.password')
                                <p class="text-red-500 dark:text-red-400 text-xs font-semibold mt-1 animate-fade-in">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Remember Me & Forgot Password Row -->
                        <div class="flex items-center justify-between text-xs sm:text-sm pt-1">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" 
                                       wire:model="data.remember" 
                                       class="h-4 w-4 rounded border-slate-300 dark:border-slate-700 text-orange-500 focus:ring-orange-500/20 dark:bg-slate-900 transition-colors duration-200">
                                <span class="text-slate-600 dark:text-slate-400 font-bold group-hover:text-slate-800 dark:group-hover:text-slate-200 transition-colors duration-200">
                                    Recordarme
                                </span>
                            </label>
                            @if (filament()->hasPasswordReset())
                                <a href="{{ filament()->getRequestPasswordResetUrl() }}" 
                                   class="text-orange-500 hover:text-orange-600 dark:text-orange-400 dark:hover:text-orange-300 font-bold transition-colors duration-200">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            @endif
                        </div>

                        <!-- Login Button -->
                        <button type="submit" 
                                class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-bold rounded-xl shadow-lg shadow-orange-500/10 hover:shadow-orange-500/25 active:scale-[0.98] transition-all duration-300 flex items-center justify-center gap-2 group text-sm">
                            <span>Iniciar sesión</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </form>

                    <!-- Secured connection badge -->
                    <div class="mt-4 flex justify-center items-center gap-2 text-[11px] text-slate-400 dark:text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-green-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                        <span class="font-semibold">Tu información está protegida</span>
                    </div>
                </div>

                <!-- Back to Home link under the card -->
  <div class="flex justify-center">
    <a href="/"
       class="inline-flex items-center gap-2 px-6 py-3
              rounded-xl
              text-sm font-semibold
              text-orange-700
              bg-orange-50/95
              border border-orange-200
              shadow-lg shadow-orange-500/10
              hover:bg-orange-500
              hover:text-white
              hover:border-orange-500
              hover:-translate-y-0.5
              transition-all duration-300
              group">
        <svg xmlns="http://www.w3.org/2000/svg"
             fill="none"
             viewBox="0 0 24 24"
             stroke-width="2"
             stroke="currentColor"
             class="w-4 h-4 transition-transform duration-300 group-hover:-translate-x-1">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
        </svg>

        <span>Volver al inicio</span>
    </a>
</div>
            </div>
        </div>
    </div>
</div>
