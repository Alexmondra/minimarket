<x-filament-panels::page>
    <div class="mp-root space-y-6">
        {{-- Cabecera --}}
        <div class="flex items-start justify-between gap-6">
            <div class="flex-1 min-w-0">
                <a href="{{ url('admin/permisos/roles') }}" class="mp-back inline-flex items-center gap-2 text-sm font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Volver a Roles
                </a>
            </div>
            <div class="mp-shield-wrap">
                <div class="mp-shield">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l8 4v6c0 5-3.5 9.3-8 10-4.5-.7-8-5-8-10V6l8-4z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M12 9v6"/>
                    </svg>
                </div>
                <span class="mp-spark mp-spark-1"></span>
                <span class="mp-spark mp-spark-2"></span>
                <span class="mp-spark mp-spark-3"></span>
            </div>
        </div>

        {{-- Selector de Rol --}}
        <div class="flex items-center gap-3 flex-wrap">
            <label class="mp-label">Selecciona un Rol:</label>
            <div class="mp-select-wrap">
                <svg class="mp-select-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 8a7 7 0 1114 0H3z"/>
                </svg>
                <select wire:model.live="selectedRoleId" class="mp-select">
                    <option value="">-- Elige un rol --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
                <svg class="mp-select-caret" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.39a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>

        {{-- Banner de información --}}
        @if($selectedRoleId)
            <div class="mp-info">
                <div class="mp-info-icon">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="mp-info-title">Asigna los permisos que tendrá este rol en el sistema.</p>
                    <p class="mp-info-text">Los cambios se guardarán automáticamente.</p>
                </div>
            </div>
        @endif

        {{-- Tabla de Permisos --}}
        @if($selectedRoleId && count($groupedPermissions) > 0)
            <div class="mp-table-card">
                <div class="mp-table-scroll">
                    <table class="mp-table">
                        <thead>
                            <tr>
                                <th class="mp-th mp-th-num">#</th>
                                <th class="mp-th mp-th-module">Módulo</th>
                                <th class="mp-th mp-th-action mp-c-blue">
                                    <span class="mp-th-inner">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12C3.7 7.9 7.5 5 12 5s8.3 2.9 9.5 7c-1.2 4.1-5 7-9.5 7s-8.3-2.9-9.5-7z"/>
                                        </svg>
                                        Ver
                                    </span>
                                </th>
                                <th class="mp-th mp-th-action mp-c-green">
                                    <span class="mp-th-inner">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Crear
                                    </span>
                                </th>
                                <th class="mp-th mp-th-action mp-c-yellow">
                                    <span class="mp-th-inner">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9.4a2 2 0 112.8 2.8L11.8 15H9v-2.8l8.6-8.6z"/>
                                        </svg>
                                        Editar
                                    </span>
                                </th>
                                <th class="mp-th mp-th-action mp-c-red">
                                    <span class="mp-th-inner">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.9 12.1A2 2 0 0116.1 21H7.9a2 2 0 01-2-1.9L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Eliminar
                                    </span>
                                </th>
                                <th class="mp-th mp-th-special mp-c-purple">
                                    <span class="mp-th-inner mp-th-inner-left">
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2l2.4 6.9 7.3.3-5.8 4.5 2.1 7-6-4.3-6 4.3 2.1-7L2.3 9.2l7.3-.3L12 2z"/>
                                        </svg>
                                        Especiales
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $index = 1; @endphp
                            @foreach($groupedPermissions as $module => $perms)
                                <tr>
                                    <td class="mp-td mp-td-num">{{ $index }}</td>
                                    <td class="mp-td mp-td-module">
                                        <div class="mp-module">
                                            @php
                                                $iconMap = [
                                                    'ventas' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
                                                    'cajas' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                                                    'guias' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.6L19 9.4V19a2 2 0 01-2 2z',
                                                    'sunat' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.6L19 9.4V19a2 2 0 01-2 2z',
                                                    'reportes' => 'M9 19v-6m4 6V5m4 14v-10',
                                                    'lotes' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10',
                                                    'stock' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
                                                    'clientes' => 'M17 20h5v-2a4 4 0 00-3-3.9M9 20H4v-2a4 4 0 013-3.9m6-2a4 4 0 100-8 4 4 0 000 8zm6 0a4 4 0 100-8 4 4 0 000 8z',
                                                    'productos' => 'M20 7l-8-4-8 4m16 0v10l-8 4m0 0L4 17V7m8 4l8-4M12 11v10M12 11L4 7',
                                                    'categorias' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                                                    'marcas' => 'M5 5a2 2 0 012-2h6.6L20 9.4V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5z',
                                                    'compras' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17',
                                                    'proveedores' => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zm10 0a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8h4.5a1 1 0 011 .7l1.8 5.4a1 1 0 01-1 1.4H17',
                                                    'usuarios' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                                                    'roles' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                                    'permisos' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                                                    'sucursales' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2m-2 0V11m-9 10v-4m4 4v-4m4 4v-4M9 7h6m-6 4h6',
                                                    'config' => 'M10.3 3.6a1.7 1.7 0 013.4 0c0 1 1.1 1.6 2 1.1 1.6-.9 3.3.9 2.4 2.4-.5.9 0 2 1.1 2 1.5.2 1.5 2.4 0 2.5-1 .1-1.6 1.2-1.1 2 .9 1.6-.9 3.3-2.4 2.4-.9-.5-2 .1-2 1.1-.1 1.5-2.4 1.5-2.5 0 0-1-1.2-1.6-2-1.1-1.6.9-3.3-.8-2.4-2.4.5-.8-.1-2-1.1-2-1.5-.1-1.5-2.3 0-2.5 1 0 1.6-1.1 1.1-2-.9-1.5.8-3.3 2.4-2.4.8.5 2 0 2-1.1zM12 15a3 3 0 100-6 3 3 0 000 6z',
                                                ];
                                                $iconPath = $iconMap[$module] ?? 'M5 3a2 2 0 012-2h6a2 2 0 012 2v2h2a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V7a2 2 0 012-2h2V3z';
                                            @endphp
                                            <div class="mp-module-icon mp-module-icon-{{ $index % 6 }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="{{ $iconPath }}"/>
                                                </svg>
                                            </div>
                                            <span class="mp-module-name">{{ $this->getModuleName($module) }}</span>
                                        </div>
                                    </td>
                                    @foreach($mainActions as $action)
                                        <td class="mp-td mp-td-check">
                                            @if(isset($perms['main'][$action]))
                                                <label class="mp-check mp-check-{{ $action }}">
                                                    <input
                                                        type="checkbox"
                                                        wire:click="togglePermission('{{ $module.'.'.$action }}')"
                                                        @checked($perms['main'][$action])
                                                    >
                                                    <span class="mp-check-box"></span>
                                                </label>
                                            @else
                                                <span class="mp-empty">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="mp-td mp-td-special">
                                        @if(count($perms['special']) > 0)
                                            <div class="mp-pills">
                                                @foreach($perms['special'] as $action => $checked)
                                                    @php
                                                        $colorMap = [
                                                            'anular' => 'red', 'abrir' => 'green', 'cerrar' => 'red',
                                                            'monitor' => 'blue', 'archivos' => 'yellow',
                                                            'ventas' => 'blue', 'inventario' => 'orange',
                                                            'global' => 'blue', 'asignar' => 'blue',
                                                            'revocar' => 'red', 'digemid' => 'purple',
                                                            'ajustar' => 'yellow',
                                                        ];
                                                        $color = $colorMap[$action] ?? 'gray';
                                                    @endphp
                                                    <label class="mp-pill mp-pill-{{ $color }} {{ $checked ? 'is-on' : '' }}">
                                                        <input
                                                            type="checkbox"
                                                            wire:click="togglePermission('{{ $module.'.'.$action }}')"
                                                            @checked($checked)
                                                        >
                                                        <span class="mp-pill-dot"></span>
                                                        <span class="mp-pill-text">{{ ucfirst($action) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @php $index++; @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="mp-actions">
                <button type="button" wire:click="$refresh" class="mp-btn mp-btn-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.5m15.4 2A8 8 0 004.6 9m0 0H9m11 11v-5h-.6m0 0a8 8 0 01-15.4-2m15.4 2H15"/>
                    </svg>
                    Restablecer cambios
                </button>
                <button type="button" wire:click="save" class="mp-btn mp-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Guardar Cambios
                </button>
            </div>
        @elseif($selectedRoleId)
            <div class="mp-info">
                <div class="mp-info-icon">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="mp-info-title">No hay permisos disponibles para este rol.</p>
            </div>
        @endif

        <style>
            .mp-root {
                --mp-bg: rgba(255, 255, 255, 0.02);
                --mp-border: rgba(148, 163, 184, 0.18);
                --mp-text: #e2e8f0;
                --mp-muted: #94a3b8;
                --mp-blue: #3b82f6;
                --mp-green: #22c55e;
                --mp-yellow: #eab308;
                --mp-red: #ef4444;
                --mp-purple: #a855f7;
                --mp-orange: #f97316;
                color: var(--mp-text);
            }
            .mp-back {
                color: #60a5fa;
                text-decoration: none;
                margin-bottom: 0.5rem;
                transition: color 0.2s;
            }
            .mp-back:hover { color: #93c5fd; }
            .mp-title {
                font-size: 2rem;
                font-weight: 800;
                line-height: 1.2;
                margin: 0.5rem 0 0;
                background: linear-gradient(120deg, #fff 30%, #c4b5fd 80%);
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
            }
            .mp-shield-wrap {
                position: relative;
                width: 110px;
                height: 110px;
                flex-shrink: 0;
            }
            .mp-shield {
                width: 100%;
                height: 100%;
                border-radius: 22px;
                background: linear-gradient(135deg, #7c3aed 0%, #6366f1 50%, #8b5cf6 100%);
                box-shadow: 0 18px 40px -10px rgba(124, 58, 237, 0.55), inset 0 1px 0 rgba(255,255,255,0.25);
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                position: relative;
                overflow: hidden;
            }
            .mp-shield::before {
                content: '';
                position: absolute;
                inset: 0;
                background: radial-gradient(circle at 30% 20%, rgba(255,255,255,0.35), transparent 50%);
            }
            .mp-shield svg { width: 56px; height: 56px; position: relative; z-index: 1; }
            .mp-spark {
                position: absolute;
                background: #fff;
                border-radius: 50%;
                box-shadow: 0 0 8px 2px rgba(255,255,255,0.6);
            }
            .mp-spark-1 { width: 6px; height: 6px; top: -4px; right: -8px; opacity: 0.9; }
            .mp-spark-2 { width: 4px; height: 4px; top: 18px; right: -14px; opacity: 0.7; }
            .mp-spark-3 { width: 3px; height: 3px; bottom: 10px; right: -6px; opacity: 0.6; }

            .mp-label { color: var(--mp-text); font-weight: 600; font-size: 0.95rem; }
            .mp-select-wrap {
                position: relative;
                min-width: 260px;
            }
            .mp-select {
                width: 100%;
                appearance: none;
                background: rgba(30, 41, 59, 0.7);
                color: var(--mp-text);
                border: 1px solid var(--mp-border);
                border-radius: 12px;
                padding: 0.65rem 2.5rem 0.65rem 2.5rem;
                font-weight: 600;
                font-size: 0.95rem;
                cursor: pointer;
                transition: border-color .2s, box-shadow .2s;
            }
            .mp-select:focus {
                outline: none;
                border-color: var(--mp-purple);
                box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.25);
            }
            .mp-select-icon, .mp-select-caret {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                width: 18px;
                height: 18px;
                color: var(--mp-muted);
                pointer-events: none;
            }
            .mp-select-icon { left: 0.75rem; }
            .mp-select-caret { right: 0.75rem; }

            .mp-info {
                display: flex;
                align-items: flex-start;
                gap: 0.85rem;
                padding: 1rem 1.25rem;
                border-radius: 14px;
                background: linear-gradient(180deg, rgba(30,58,138,0.18), rgba(30,58,138,0.06));
                border: 1px solid rgba(59, 130, 246, 0.25);
            }
            .mp-info-icon {
                width: 36px; height: 36px;
                flex-shrink: 0;
                border-radius: 999px;
                background: rgba(59, 130, 246, 0.18);
                color: #60a5fa;
                display: flex; align-items: center; justify-content: center;
            }
            .mp-info-icon svg { width: 22px; height: 22px; }
            .mp-info-title { color: #fff; font-weight: 700; margin: 0; }
            .mp-info-text { color: #cbd5e1; font-size: 0.85rem; margin: 0.15rem 0 0; }

            .mp-table-card {
                border: 1px solid var(--mp-border);
                border-radius: 16px;
                background: linear-gradient(180deg, rgba(15,23,42,0.45), rgba(15,23,42,0.2));
                overflow: hidden;
                backdrop-filter: blur(6px);
            }
            .mp-table-scroll { overflow-x: auto; }
            .mp-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.9rem; }
            .mp-th {
                padding: 0.95rem 1rem;
                text-align: center;
                font-size: 0.78rem;
                font-weight: 700;
                text-transform: none;
                letter-spacing: 0.02em;
                background: rgba(30, 41, 59, 0.55);
                border-bottom: 1px solid var(--mp-border);
                white-space: nowrap;
            }
            .mp-th-num { text-align: center; width: 56px; color: var(--mp-muted); }
            .mp-th-module { text-align: left; color: var(--mp-text); min-width: 200px; }
            .mp-th-action { width: 96px; }
            .mp-th-special { text-align: left; min-width: 280px; }
            .mp-th-inner {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.4rem;
            }
            .mp-th-inner-left { justify-content: flex-start; }
            .mp-th-inner svg { width: 16px; height: 16px; }
            .mp-c-blue   { color: #60a5fa; }
            .mp-c-green  { color: #4ade80; }
            .mp-c-yellow { color: #facc15; }
            .mp-c-red    { color: #f87171; }
            .mp-c-purple { color: #c084fc; }

            .mp-td {
                padding: 0.85rem 1rem;
                border-bottom: 1px solid rgba(148, 163, 184, 0.10);
                vertical-align: middle;
            }
            .mp-table tbody tr:last-child .mp-td { border-bottom: none; }
            .mp-table tbody tr:hover .mp-td { background: rgba(99, 102, 241, 0.05); }
            .mp-td-num {
                text-align: center;
                color: #60a5fa;
                font-weight: 700;
            }
            .mp-td-check { text-align: center; }
            .mp-empty { color: rgba(148, 163, 184, 0.4); }

            .mp-module { display: flex; align-items: center; gap: 0.75rem; }
            .mp-module-icon {
                width: 32px; height: 32px;
                border-radius: 9px;
                display: flex; align-items: center; justify-content: center;
                color: #fff;
                flex-shrink: 0;
            }
            .mp-module-icon svg { width: 16px; height: 16px; }
            .mp-module-icon-0 { background: linear-gradient(135deg, #06b6d4, #3b82f6); }
            .mp-module-icon-1 { background: linear-gradient(135deg, #22c55e, #10b981); }
            .mp-module-icon-2 { background: linear-gradient(135deg, #f59e0b, #f97316); }
            .mp-module-icon-3 { background: linear-gradient(135deg, #ec4899, #d946ef); }
            .mp-module-icon-4 { background: linear-gradient(135deg, #8b5cf6, #6366f1); }
            .mp-module-icon-5 { background: linear-gradient(135deg, #ef4444, #f43f5e); }
            .mp-module-name { color: #fff; font-weight: 600; }

            .mp-check {
                position: relative;
                display: inline-flex;
                cursor: pointer;
            }
            .mp-check input {
                position: absolute;
                opacity: 0;
                width: 100%; height: 100%;
                cursor: pointer;
                margin: 0;
            }
            .mp-check-box {
                width: 22px; height: 22px;
                border-radius: 6px;
                border: 2px solid rgba(148, 163, 184, 0.5);
                background: rgba(30, 41, 59, 0.6);
                transition: all .15s;
                display: inline-block;
                position: relative;
            }
            .mp-check-box::after {
                content: '';
                position: absolute;
                inset: 0;
                background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'><polyline points='20 6 9 17 4 12'/></svg>");
                background-size: 14px 14px;
                background-position: center;
                background-repeat: no-repeat;
                opacity: 0;
                transition: opacity .15s;
            }
            .mp-check input:checked + .mp-check-box { border-color: transparent; }
            .mp-check input:checked + .mp-check-box::after { opacity: 1; }
            .mp-check-ver input:checked + .mp-check-box      { background: var(--mp-blue);   box-shadow: 0 0 0 3px rgba(59,130,246,0.2); }
            .mp-check-crear input:checked + .mp-check-box    { background: var(--mp-green);  box-shadow: 0 0 0 3px rgba(34,197,94,0.2); }
            .mp-check-editar input:checked + .mp-check-box   { background: var(--mp-yellow); box-shadow: 0 0 0 3px rgba(234,179,8,0.2); }
            .mp-check-eliminar input:checked + .mp-check-box { background: var(--mp-red);    box-shadow: 0 0 0 3px rgba(239,68,68,0.2); }
            .mp-check:hover .mp-check-box { border-color: rgba(255,255,255,0.7); }

            .mp-pills { display: flex; flex-wrap: wrap; gap: 0.4rem; }
            .mp-pill {
                position: relative;
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                padding: 0.35rem 0.75rem;
                border-radius: 999px;
                font-size: 0.75rem;
                font-weight: 600;
                cursor: pointer;
                transition: all .15s;
                border: 1px solid transparent;
                background: rgba(30, 41, 59, 0.6);
                color: var(--mp-muted);
            }
            .mp-pill input { position: absolute; opacity: 0; inset: 0; cursor: pointer; margin: 0; }
            .mp-pill-dot {
                width: 8px; height: 8px;
                border-radius: 999px;
                background: currentColor;
                opacity: 0.5;
            }
            .mp-pill.is-on .mp-pill-dot { opacity: 1; box-shadow: 0 0 6px currentColor; }
            .mp-pill-blue   { color: #93c5fd; border-color: rgba(59,130,246,0.35); background: rgba(30, 58, 138, 0.25); }
            .mp-pill-green  { color: #86efac; border-color: rgba(34,197,94,0.35);  background: rgba(20, 83, 45, 0.25); }
            .mp-pill-yellow { color: #fde047; border-color: rgba(234,179,8,0.35);  background: rgba(113, 63, 18, 0.25); }
            .mp-pill-red    { color: #fca5a5; border-color: rgba(239,68,68,0.35);  background: rgba(127, 29, 29, 0.25); }
            .mp-pill-purple { color: #d8b4fe; border-color: rgba(168,85,247,0.35); background: rgba(88, 28, 135, 0.25); }
            .mp-pill-orange { color: #fdba74; border-color: rgba(249,115,22,0.35); background: rgba(124, 45, 18, 0.25); }
            .mp-pill-gray   { color: #cbd5e1; border-color: rgba(148,163,184,0.35); background: rgba(51, 65, 85, 0.25); }
            .mp-pill:hover { transform: translateY(-1px); filter: brightness(1.15); }
            .mp-pill.is-on { filter: brightness(1.2) saturate(1.2); }

            .mp-actions {
                display: flex;
                justify-content: center;
                gap: 1rem;
                padding-top: 0.5rem;
            }
            .mp-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.7rem 1.5rem;
                border-radius: 12px;
                font-weight: 700;
                font-size: 0.9rem;
                cursor: pointer;
                border: 1px solid transparent;
                transition: all .15s;
            }
            .mp-btn svg { width: 18px; height: 18px; }
            .mp-btn-ghost {
                background: rgba(30, 41, 59, 0.6);
                color: var(--mp-text);
                border-color: var(--mp-border);
            }
            .mp-btn-ghost:hover { background: rgba(51, 65, 85, 0.6); }
            .mp-btn-primary {
                background: linear-gradient(135deg, #7c3aed, #6366f1);
                color: #fff;
                box-shadow: 0 10px 25px -8px rgba(124, 58, 237, 0.6);
            }
            .mp-btn-primary:hover { filter: brightness(1.1); transform: translateY(-1px); }

            /* Modo claro: ajustes legibles */
            :root:not(.dark) .mp-root {
                color: #0f172a;
            }
            :root:not(.dark) .mp-title {
                background: linear-gradient(120deg, #0f172a 30%, #6d28d9 80%);
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
            }
            :root:not(.dark) .mp-select {
                background: #fff;
                color: #0f172a;
                border-color: #cbd5e1;
            }
            :root:not(.dark) .mp-info-title { color: #0f172a; }
            :root:not(.dark) .mp-info-text  { color: #475569; }
            :root:not(.dark) .mp-table-card {
                background: #fff;
                border-color: #e2e8f0;
            }
            :root:not(.dark) .mp-th { background: #f8fafc; border-bottom-color: #e2e8f0; }
            :root:not(.dark) .mp-th-module { color: #1e293b; }
            :root:not(.dark) .mp-td { border-bottom-color: #f1f5f9; }
            :root:not(.dark) .mp-td-num { color: #2563eb; }
            :root:not(.dark) .mp-module-name { color: #0f172a; }
            :root:not(.dark) .mp-check-box { background: #fff; border-color: #cbd5e1; }
            :root:not(.dark) .mp-btn-ghost { background: #f1f5f9; color: #0f172a; border-color: #e2e8f0; }
        </style>
    </div>
</x-filament-panels::page>
