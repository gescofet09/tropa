<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TROPA - Gestión de pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f9;
        }

        /* Header */
        header {
            background: #1e293b;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 20px;
        }

        .user-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        button.logout {
            background: #ef4444;
            border: none;
            color: white;
            padding: 6px 12px;
            cursor: pointer;
            border-radius: 4px;
        }

        /* Container */
        .container {
            padding: 30px;
        }

        /* Cards */
        .card {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #e2e8f0;
            padding: 10px;
            text-align: left;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        /* Status timeline */
        .timeline {
            display: flex;
            gap: 10px;
        }

        .step {
            padding: 5px 10px;
            border-radius: 20px;
            background: #e5e7eb;
            font-size: 12px;
        }

        .active {
            background: #22c55e;
            color: white;
        }

        .btn {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }
    </style>
</head>
<body>
 
<header>
    <h1>TROPA</h1>

    <div class="user-info">
        <span>{{ auth()->user()->name }}</span>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout">Cerrar sesión</button>
        </form>
    </div>
</header>

<div class="container">
    @yield('content')
</div>

</body>
</html>
