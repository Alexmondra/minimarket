<!DOCTYPE html>
<html lang="es" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso denegado</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 1.5rem;
        }
        .dark body { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); }
        .card {
            max-width: 440px;
            width: 100%;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 2rem;
            padding: 2.5rem 2rem;
            box-shadow: 0 35px 90px -42px rgba(14,165,233,0.55);
            text-align: center;
        }
        .icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #fef3c7, #fed7aa);
            border-radius: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 18px 32px -18px rgba(245,158,11,0.4);
        }
        .icon svg { width: 36px; height: 36px; color: #d97706; }
        h1 {
            font-size: 1.75rem;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }
        p {
            font-size: 0.875rem;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .actions { display: flex; flex-direction: column; gap: 0.75rem; }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(to right, #f59e0b, #ea580c);
            color: white;
            font-weight: 700;
            font-size: 0.875rem;
            border: none;
            border-radius: 0.75rem;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
            box-shadow: 0 10px 15px -10px rgba(234,88,12,0.2);
        }
        .btn-primary:hover {
            background: linear-gradient(to right, #d97706, #c2410c);
            transform: translateY(-1px);
            box-shadow: 0 15px 20px -12px rgba(234,88,12,0.3);
        }
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: transparent;
            color: #64748b;
            font-weight: 600;
            font-size: 0.875rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
        }
        .btn-secondary:hover {
            border-color: #f59e0b;
            color: #d97706;
            transform: translateY(-1px);
        }
        .code {
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>
        <div class="code">Error 403</div>
        <h1>Acceso denegado</h1>
        <p>No tienes permisos para acceder a esta sección.<br>Si crees que esto es un error, contacta al administrador.</p>
        <div class="actions">
            <a href="{{ url('/admin') }}" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
                Volver al inicio
            </a>
            <a href="{{ route('filament.admin.auth.logout') }}" class="btn-secondary" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
                Cerrar sesión
            </a>
            <form id="logout-form" action="{{ route('filament.admin.auth.logout') }}" method="POST" style="display: none;">@csrf</form>
        </div>
    </div>
</body>
</html>
