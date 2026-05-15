<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva contraseña</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-screen items-center justify-center bg-slate-100 px-4">

<div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-7 shadow-xl">

    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-slate-900">
            Crear nueva contraseña
        </h1>
    </div>

    @if ($errors->any())
        <div class="alert-danger mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">

        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">
                Email
            </label>

            <input
                type="email"
                name="email"
                value="{{ $email }}"
                class="input-base"
                required
            >
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">
                Nueva contraseña
            </label>

            <input
                type="password"
                name="password"
                class="input-base"
                required
            >
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">
                Confirmar contraseña
            </label>

            <input
                type="password"
                name="password_confirmation"
                class="input-base"
                required
            >
        </div>

        <button class="btn-primary w-full" type="submit">
            Guardar contraseña
        </button>

    </form>
</div>

</body>
</html>