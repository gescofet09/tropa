<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TROPA - Gestión de pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container vh-100 d-flex justify-content-center align-items-center">
    <div class="card shadow p-4" style="width: 400px;">
        
        <h2 class="text-center mb-3">TROPA</h2>
        <p class="text-center text-muted">Gestión de pedidos y reparto</p>
        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100">Entrar</button>
        </form>

        <hr>

    </div>
</div>

</body>
</html>
