<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TROPA - Gestión de pedidos</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">
    <header class="border-b border-slate-200 bg-slate-900 text-white">
        <div class="app-shell flex flex-col gap-4 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold tracking-wide">TROPA</h1>
                <p class="text-sm text-slate-300">Gestión de pedidos</p>
            </div>

            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center sm:gap-4">
                <span class="text-sm text-slate-300">{{ auth()->user()->name }}</span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn-danger w-full !rounded-full !px-4 !py-2 sm:w-auto" type="submit">Cerrar sesión</button>
                </form>
            </div>
        </div>
    </header>

    <main class="app-shell">
        @yield('content')
    </main>
</body>
</html>
