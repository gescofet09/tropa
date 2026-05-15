<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TROPA - Gestión de pedidos</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 px-4">
    <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-7 shadow-xl shadow-slate-200/60">
        <div class="mb-5 text-center">
            <div class="mx-auto flex w-64 items-center justify-center">
                <img src="{{ asset('images/logo.png') }}" alt="TROPA" class="-mb-10 h-56 w-56 object-contain">
            </div>
            <p class="mt-1 text-sm text-slate-500">Gestión de pedidos y reparto</p>
        </div>

        <form method="POST" action="{{ route('login.submit') }}" class="space-y-5">
            @csrf

            @if ($errors->any())
                <div class="alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="space-y-2">
                <label for="email" class="text-sm font-medium text-slate-700">Email</label>
                <input id="email" type="email" name="email" class="input-base" required>
            </div>

            <div class="space-y-2">
                <label for="password" class="text-sm font-medium text-slate-700">Contraseña</label>
                <input id="password" type="password" name="password" class="input-base" required>
            </div>

            <button class="btn-primary w-full" type="submit">Entrar</button>
            <div class="text-center">
                <a href="{{ route('password.request') }}" class="text-sm text-sky-600 hover:underline">¿Has olvidado tu contraseña?</a>
            </div>
        </form>
    </div>
</body>
</html>
