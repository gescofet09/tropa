<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-screen items-center justify-center bg-slate-100 px-4">

<div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-7 shadow-xl">

    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-slate-900">
            Recuperar contraseña
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Introduce tu email y te enviaremos un enlace.
        </p>
    </div>

    @if (session('status'))
        <div class="alert-success mb-4">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-danger mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">
                Email
            </label>

            <input
                type="email"
                name="email"
                class="input-base"
                required
            >
        </div>

        <button class="btn-primary w-full" type="submit">
            Enviar enlace
        </button>
    </form>

    <div class="mt-6 text-center">
        <a href="{{ route('login') }}" class="text-sm text-sky-600 hover:underline"> Volver al login</a>
    </div>
</div>

</body>
</html>