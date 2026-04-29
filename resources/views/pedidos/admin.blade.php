@extends('layouts.app')

@section('content')
    @php
        $statsCards = [
            [
                'label' => 'Usuarios',
                'value' => $stats['usuarios'],
                'tone' => 'blue',
                'href' => '#admin-usuarios',
            ],
            [
                'label' => 'Productos',
                'value' => $stats['productos'],
                'tone' => 'blue',
                'href' => '#admin-productos',
            ],
            [
                'label' => 'Pedidos',
                'value' => $stats['pedidos'],
                'tone' => 'violet',
                'href' => '#admin-pedidos',
            ],
            [
                'label' => 'Stock bajo',
                'value' => $stats['stock_bajo'],
                'tone' => 'amber',
                'href' => '#admin-stock-bajo',
            ],
            [
                'label' => 'Ventas',
                'value' => number_format($stats['ventas_totales'], 2, ',', '.') . ' €',
                'tone' => 'emerald',
                'href' => '#admin-ventas',
            ],
        ];
        @endphp

    <div class="space-y-6">
        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert-danger">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-danger">
                <p class="font-semibold">Revisa los datos enviados.</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $usuariosPorRol = [
                'admin' => [
                    'titulo' => 'Administradores',
                    'descripcion' => 'Control total del sistema.',
                    'badge' => 'bg-slate-100 text-slate-700',
                ],
                'repartidor' => [
                    'titulo' => 'Repartidores',
                    'descripcion' => 'Usuarios asignados a entregas y zonas.',
                    'badge' => 'bg-sky-100 text-sky-700',
                ],
                'cliente' => [
                    'titulo' => 'Clientes',
                    'descripcion' => 'Usuarios que realizan pedidos.',
                    'badge' => 'bg-emerald-100 text-emerald-700',
                ],
            ];

            $pedidosAgrupados = $pedidos
                ->sortByDesc('id')
                ->groupBy(fn ($pedido) => $pedido->repartidor?->id ? 'repartidor-' . $pedido->repartidor->id : 'sin-asignar');
        @endphp

        <section class="panel !rounded-[28px] !p-4 sm:!p-6 lg:!p-8">
            <div class="space-y-6 sm:space-y-8">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-sky-600 sm:text-sm">Administración</p>
                    <h2 class="mt-4 text-3xl font-semibold tracking-tight text-slate-900 sm:text-[3.5rem] sm:leading-none">Panel de control TROPA</h2>
                    <p class="mt-4 max-w-3xl text-base text-slate-500 sm:mt-6 sm:text-[1.05rem]">
                        Gestiona usuarios, productos, pedidos y stock desde un único sitio.
                    </p>
                </div>

                <div class="admin-stats-grid">
                    @foreach ($statsCards as $card)
                        @php
                            $toneClasses = match ($card['tone']) {
                                'amber' => 'bg-amber-50 text-amber-500',
                                'emerald' => 'bg-emerald-50 text-emerald-600',
                                'violet' => 'bg-violet-50 text-violet-600',
                                default => 'bg-sky-50 text-sky-600',
                            };

                            $valueClasses = match ($card['tone']) {
                                'amber' => 'text-orange-600',
                                'emerald' => 'text-emerald-700',
                                default => 'text-slate-900',
                            };
                        @endphp

                        <a href="{{ $card['href'] }}" class="block rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm shadow-slate-200/60 transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                            <div class="flex min-h-[72px] items-center gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $toneClasses }}">
                                    @if ($card['label'] === 'Usuarios')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 19a4 4 0 0 0-8 0m11-7a3 3 0 1 0-2.999-3A3 3 0 0 0 19 12ZM8 12A3 3 0 1 0 5.001 9 3 3 0 0 0 8 12Zm8 7H4a1 1 0 0 1-1-1 5 5 0 0 1 5-5h4a5 5 0 0 1 5 5 1 1 0 0 1-1 1Z" />
                                        </svg>
                                    @elseif ($card['label'] === 'Productos')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m12 3 8 4.5-8 4.5L4 7.5 12 3Zm8 4.5V16.5L12 21l-8-4.5V7.5M12 12v9" />
                                        </svg>
                                    @elseif ($card['label'] === 'Pedidos')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6m-8 3h10a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm2 5h6m-6 4h4" />
                                        </svg>
                                    @elseif ($card['label'] === 'Stock bajo')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 3.75c.43 0 .83.23 1.05.6l8 13.75a1.2 1.2 0 0 1-1.05 1.8H4a1.2 1.2 0 0 1-1.04-1.8l8-13.75A1.2 1.2 0 0 1 12 3.75Zm-.75 5.5v4.5h1.5v-4.5h-1.5Zm0 6v1.5h1.5v-1.5h-1.5Z" />
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5m0 14h16M7 15l3-3 3 2 4-6" />
                                        </svg>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">{{ $card['label'] }}</p>
                                    <p class="mt-1 text-lg font-semibold leading-none sm:text-xl {{ $valueClasses }}">
                                        <span class="block truncate">{{ $card['value'] }}</span>
                                    </p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="admin-create-grid">
            <div class="panel !rounded-[24px] !p-4 sm:!p-6 lg:!p-7">
                <div class="flex items-start gap-3 sm:gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-50 text-sky-600 sm:h-12 sm:w-12">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2m17-8v3m1.5-1.5h-3M15 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Zm6 4a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">Crear usuario</h3>
                        <p class="mt-1 text-sm text-slate-500">Altas rápidas para clientes, repartidores o administradores.</p>
                    </div>
                </div>

                <form class="mt-6 grid gap-4 md:grid-cols-2 sm:mt-8 sm:gap-5" method="POST" action="{{ route('admin.usuarios.store') }}">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="user_name">Nombre</label>
                        <input class="input-base" id="user_name" name="name" type="text" value="{{ old('name') }}" placeholder="Nombre completo" required>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="user_email">Email</label>
                        <input class="input-base" id="user_email" name="email" type="email" value="{{ old('email') }}" placeholder="correo@ejemplo.com" required>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="user_password">Contraseña</label>
                        <input class="input-base" id="user_password" name="password" type="password" placeholder=".........." required>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="user_rol">Rol</label>
                        <select class="input-base" id="user_rol" name="rol" required>
                            <option value="cliente">Cliente</option>
                            <option value="repartidor">Repartidor</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="user_zona">Zona</label>
                        <select class="input-base" id="user_zona" name="zona_id">
                            <option value="">Sin zona asignada</option>
                            @foreach ($zonas as $zona)
                                <option value="{{ $zona->id }}">{{ $zona->nombre }} ({{ $zona->codigo_postal }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <button class="btn-primary gap-2 !rounded-xl !px-5 !py-3" type="submit">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                            </svg>
                            Crear usuario
                        </button>
                    </div>
                </form>
            </div>

            <div class="panel !rounded-[24px] !p-4 sm:!p-6 lg:!p-7">
                <div class="flex items-start gap-3 sm:gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-50 text-sky-600 sm:h-12 sm:w-12">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m12 3 8 4.5-8 4.5L4 7.5 12 3Zm8 4.5V16.5L12 21l-8-4.5V7.5M12 12v9" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">Crear producto</h3>
                        <p class="mt-1 text-sm text-slate-500">Añade nuevos productos y déjalos listos para pedidos.</p>
                    </div>
                </div>

                <form class="mt-6 grid gap-4 md:grid-cols-2 sm:mt-8 sm:gap-5" method="POST" action="{{ route('admin.productos.store') }}">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="producto_nombre">Nombre</label>
                        <input class="input-base" id="producto_nombre" name="nombre" type="text" value="{{ old('nombre') }}" placeholder="Nombre del producto" required>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="producto_categoria">Categoría</label>
                        <select class="input-base" id="producto_categoria" name="categoria_id" required>
                            <option value="">Selecciona una categoría</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="producto_precio">Precio</label>
                        <input class="input-base" id="producto_precio" name="precio" type="number" min="0" step="0.01" value="{{ old('precio', '0.00') }}" required>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="producto_stock">Stock</label>
                        <input class="input-base" id="producto_stock" name="stock" type="number" min="0" step="1" value="{{ old('stock', 0) }}" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="producto_unidad">Unidad</label>
                        <input class="input-base" id="producto_unidad" name="unidad" type="text" value="{{ old('unidad', 'kg') }}" required>
                    </div>

                    <div class="md:col-span-2">
                        <button class="btn-primary gap-2 !rounded-xl !px-5 !py-3" type="submit">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                            </svg>
                            Crear producto
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section id="admin-usuarios" class="panel scroll-mt-24 !rounded-[24px] !p-4 sm:!p-6 lg:!p-7">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-50 text-sky-600 sm:h-12 sm:w-12">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 19a4 4 0 0 0-8 0m11-7a3 3 0 1 0-2.999-3A3 3 0 0 0 19 12ZM8 12A3 3 0 1 0 5.001 9 3 3 0 0 0 8 12Zm8 7H4a1 1 0 0 1-1-1 5 5 0 0 1 5-5h4a5 5 0 0 1 5 5 1 1 0 0 1-1 1Z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">Gestionar usuarios</h3>
                    <p class="mt-1 text-sm text-slate-500">Edita datos, cambia roles y elimina usuarios cuando sea necesario.</p>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                @foreach ($usuariosPorRol as $rolKey => $config)
                    @php
                        $usuariosRol = $usuarios->where('rol', $rolKey);
                    @endphp

                    @if ($usuariosRol->isNotEmpty())
                        <details class="overflow-hidden rounded-2xl border border-slate-100 bg-white">
                            <summary class="flex cursor-pointer list-none flex-col gap-3 bg-slate-50/80 px-4 py-4 marker:hidden sm:flex-row sm:items-center sm:justify-between sm:px-5">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h4 class="text-base font-semibold text-slate-900 sm:text-lg">{{ $config['titulo'] }}</h4>
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $config['badge'] }}">
                                            {{ $usuariosRol->count() }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm text-slate-500">{{ $config['descripcion'] }}</p>
                                </div>
                                <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">
                                    Ver usuarios
                                </span>
                            </summary>

                            <div class="overflow-x-auto border-t border-slate-100">
                                <table class="min-w-[820px] text-sm">
                                    <thead class="bg-white">
                                        <tr class="text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                                            <th class="px-5 py-4">Usuario</th>
                                            <th class="px-5 py-4">Contacto</th>
                                            <th class="px-5 py-4">Rol</th>
                                            <th class="px-5 py-4">Zona</th>
                                            <th class="px-5 py-4">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @foreach ($usuariosRol as $usuario)
                                            <tr class="align-top">
                                                <td class="px-5 py-5">
                                                    <input class="input-base" form="user-update-{{ $usuario->id }}" name="name" type="text" value="{{ $usuario->name }}" required>
                                                </td>
                                                <td class="space-y-3 px-5 py-5">
                                                    <div class="text-sm text-slate-600">{{ $usuario->email }}</div>
                                                    <input class="input-base" form="user-update-{{ $usuario->id }}" name="email" type="email" value="{{ $usuario->email }}" required>
                                                    <input class="input-base" form="user-update-{{ $usuario->id }}" name="password" type="password" placeholder="Nueva contraseña (opcional)">
                                                </td>
                                                <td class="px-5 py-5">
                                                    <select class="input-base" form="user-update-{{ $usuario->id }}" name="rol" required>
                                                        <option value="cliente" @selected($usuario->rol === 'cliente')>Cliente</option>
                                                        <option value="repartidor" @selected($usuario->rol === 'repartidor')>Repartidor</option>
                                                        <option value="admin" @selected($usuario->rol === 'admin')>Administrador</option>
                                                    </select>
                                                </td>
                                                <td class="px-5 py-5">
                                                    <select class="input-base" form="user-update-{{ $usuario->id }}" name="zona_id">
                                                        <option value="">Sin zona</option>
                                                        @foreach ($zonas as $zona)
                                                            <option value="{{ $zona->id }}" @selected((string) $usuario->zona_id === (string) $zona->id)>{{ $zona->nombre }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-5 py-5">
                                                    <div class="flex flex-wrap gap-2">
                                                        <form id="user-update-{{ $usuario->id }}" method="POST" action="{{ route('admin.usuarios.update', $usuario) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button class="btn-primary gap-2 !rounded-xl !px-4 !py-2.5 !text-xs" type="submit">
                                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12l5 5L20 7" />
                                                                </svg>
                                                                Guardar
                                                            </button>
                                                        </form>

                                                        <form method="POST" action="{{ route('admin.usuarios.destroy', $usuario) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn-danger gap-2 !rounded-xl !px-4 !py-2.5 !text-xs" type="submit" @disabled(auth()->id() === $usuario->id)>
                                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-9 0V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m-8 0 1 12a1 1 0 0 0 1 .92h6a1 1 0 0 0 1-.92L17 7" />
                                                                </svg>
                                                                Eliminar
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    @endif
                @endforeach
            </div>
        </section>

        <section id="admin-productos" class="panel scroll-mt-24 !rounded-[24px] !p-4 sm:!p-6 lg:!p-7">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-50 text-sky-600 sm:h-12 sm:w-12">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m12 3 8 4.5-8 4.5L4 7.5 12 3Zm8 4.5V16.5L12 21l-8-4.5V7.5M12 12v9" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">Gestionar productos y stock</h3>
                    <p class="mt-1 text-sm text-slate-500">Actualiza precio, categoría, unidad y reposición de stock por categorías.</p>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                @foreach ($categorias as $categoria)
                    @php
                        $productosCategoria = $productos->where('categoria_id', $categoria->id)->sortBy('nombre');
                    @endphp

                    @if ($productosCategoria->isNotEmpty())
                        <details @if($loop->first) id="admin-stock-bajo" @endif class="overflow-hidden rounded-2xl border border-slate-200 bg-white scroll-mt-24">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 bg-slate-50 px-4 py-4 marker:hidden sm:px-5">
                                <div>
                                    <h4 class="text-base font-semibold text-slate-900 sm:text-lg">{{ $categoria->nombre }}</h4>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $productosCategoria->count() }} {{ \Illuminate\Support\Str::plural('producto', $productosCategoria->count()) }}
                                    </p>
                                </div>
                                <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">
                                    Ver productos
                                </span>
                            </summary>

                            <div class="overflow-x-auto border-t border-slate-100">
                                <table class="min-w-[900px] text-sm">
                                    <thead class="bg-white">
                                        <tr class="text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                                            <th class="px-5 py-4">Producto</th>
                                            <th class="px-5 py-4">Precio</th>
                                            <th class="px-5 py-4">Stock</th>
                                            <th class="px-5 py-4">Unidad</th>
                                            <th class="px-5 py-4">Mover a</th>
                                            <th class="px-5 py-4">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @foreach ($productosCategoria as $producto)
                                            <tr class="align-top {{ $producto->stock <= 5 ? 'bg-amber-50/50' : '' }}">
                                                <td class="px-5 py-5">
                                                    <input class="input-base" form="product-update-{{ $producto->id }}" name="nombre" type="text" value="{{ $producto->nombre }}" required>
                                                </td>
                                                <td class="px-5 py-5">
                                                    <input class="input-base" form="product-update-{{ $producto->id }}" name="precio" type="number" min="0" step="0.01" value="{{ number_format((float) $producto->precio, 2, '.', '') }}" required>
                                                </td>
                                                <td class="px-5 py-5">
                                                    <input class="input-base" form="product-update-{{ $producto->id }}" name="stock" type="number" min="0" step="1" value="{{ $producto->stock }}" required>
                                                    @if ($producto->stock <= 5)
                                                        <span class="mt-2 inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Stock bajo</span>
                                                    @endif
                                                </td>
                                                <td class="px-5 py-5">
                                                    <input class="input-base" form="product-update-{{ $producto->id }}" name="unidad" type="text" value="{{ $producto->unidad }}" required>
                                                </td>
                                                <td class="px-5 py-5">
                                                    <select class="input-base" form="product-update-{{ $producto->id }}" name="categoria_id" required>
                                                        @foreach ($categorias as $categoriaOption)
                                                            <option value="{{ $categoriaOption->id }}" @selected((string) $producto->categoria_id === (string) $categoriaOption->id)>{{ $categoriaOption->nombre }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-5 py-5">
                                                    <div class="flex flex-wrap gap-2">
                                                        <form id="product-update-{{ $producto->id }}" method="POST" action="{{ route('admin.productos.update', $producto) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button class="btn-primary gap-2 !rounded-xl !px-4 !py-2.5 !text-xs" type="submit">
                                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12l5 5L20 7" />
                                                                </svg>
                                                                Guardar
                                                            </button>
                                                        </form>

                                                        <form method="POST" action="{{ route('admin.productos.destroy', $producto) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn-danger gap-2 !rounded-xl !px-4 !py-2.5 !text-xs" type="submit">
                                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-9 0V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m-8 0 1 12a1 1 0 0 0 1 .92h6a1 1 0 0 0 1-.92L17 7" />
                                                                </svg>
                                                                Eliminar
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    @endif
                @endforeach
            </div>
        </section>

        <section id="admin-pedidos" class="panel scroll-mt-24 !rounded-[24px] !p-4 sm:!p-6 lg:!p-7">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-violet-50 text-violet-600 sm:h-12 sm:w-12">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6m-8 3h10a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm2 5h6m-6 4h4" />
                    </svg>
                </div>
                <div>
                    <h3 id="admin-ventas" class="text-xl font-semibold tracking-tight text-slate-900 scroll-mt-24 sm:text-2xl">Gestionar pedidos</h3>
                    <p class="mt-1 text-sm text-slate-500">Supervisión completa de clientes, repartidores, estados y documentos.</p>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                @foreach ($pedidosAgrupados as $grupoKey => $pedidosGrupo)
                    @php
                        $repartidorGrupo = $pedidosGrupo->first()?->repartidor;
                        $tituloGrupo = $repartidorGrupo?->name ?? 'Sin asignar';
                        $descripcionGrupo = $repartidorGrupo
                            ? 'Pedidos a cargo de este repartidor.'
                            : 'Pedidos pendientes de asignar a un repartidor.';
                    @endphp

                    <details class="overflow-hidden rounded-2xl border border-slate-100 bg-white">
                        <summary class="flex cursor-pointer list-none flex-col gap-3 bg-slate-50/80 px-4 py-4 marker:hidden sm:flex-row sm:items-center sm:justify-between sm:px-5">
                            <div>
                                <div class="flex items-center gap-3">
                                    <h4 class="text-base font-semibold text-slate-900 sm:text-lg">{{ $tituloGrupo }}</h4>
                                    <span class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-700">
                                        {{ $pedidosGrupo->count() }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">{{ $descripcionGrupo }}</p>
                            </div>
                            <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Ver pedidos
                            </span>
                        </summary>

                        <div class="overflow-x-auto border-t border-slate-100">
                            <table class="min-w-[760px] text-sm">
                                <thead class="bg-white">
                                    <tr class="text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                                        <th class="px-5 py-4">ID</th>
                                        <th class="px-5 py-4">Cliente</th>
                                        <th class="px-5 py-4">Repartidor</th>
                                        <th class="px-5 py-4">Total</th>
                                        <th class="px-5 py-4">Seguimiento</th>
                                        <th class="px-5 py-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach ($pedidosGrupo as $pedido)
                                        <tr data-pedido-id="{{ $pedido->id }}" class="align-top">
                                            <td class="px-5 py-5 font-semibold text-slate-700">#{{ $pedido->id }}</td>
                                            <td class="px-5 py-5 text-slate-600">{{ $pedido->cliente->name ?? 'Sin cliente' }}</td>
                                            <td class="px-5 py-5 text-slate-600">{{ $pedido->repartidor->name ?? 'Sin asignar' }}</td>
                                            <td class="px-5 py-5 text-slate-600">{{ number_format((float) $pedido->total, 2, ',', '.') }} €</td>
                                            <td class="estado-pedido px-5 py-5">
                                                <x-estado-pedido :estado="$pedido->estado" />
                                            </td>
                                            <td class="px-5 py-5" x-data="{ open: false }" @keydown.escape.window="open = false">
                                                <div class="flex flex-wrap gap-2">
                                                    <button class="btn-primary gap-2 !rounded-xl !px-4 !py-2.5 !text-xs" type="button" @click="open = true">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
                                                        </svg>
                                                        Ver pedido
                                                    </button>

                                                    <form action="{{ route('pedidos.destroy', $pedido->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn-danger gap-2 !rounded-xl !px-4 !py-2.5 !text-xs" type="submit">
                                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-9 0V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m-8 0 1 12a1 1 0 0 0 1 .92h6a1 1 0 0 0 1-.92L17 7" />
                                                            </svg>
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                </div>

                                                <template x-teleport="body">
                                                    <div x-show="open" x-cloak>
                                                        <div class="modal-overlay" @click="open = false"></div>
                                                        <div class="modal-panel max-h-[85vh] max-w-3xl overflow-y-auto" @click.stop>
                                                            <div class="modal-header">
                                                                <div>
                                                                    <h3 class="text-lg font-semibold text-slate-900">Pedido #{{ $pedido->id }}</h3>
                                                                    <p class="text-sm text-slate-500">
                                                                        Cliente: {{ $pedido->cliente->name ?? 'Sin cliente' }} · Repartidor: {{ $pedido->repartidor->name ?? 'Sin asignar' }}
                                                                    </p>
                                                                </div>
                                                                <button class="btn-secondary !rounded-xl" type="button" @click="open = false">Cerrar</button>
                                                            </div>

                                                            <div class="space-y-4">
                                                                @foreach ($categorias as $categoria)
                                                                    @php
                                                                        $productosCategoria = $pedido->productos->where('categoria_id', $categoria->id);
                                                                    @endphp

                                                                    @if ($productosCategoria->isNotEmpty())
                                                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                                            <h4 class="font-semibold text-slate-800">{{ $categoria->nombre }}</h4>
                                                                            <ul class="mt-2 space-y-1 text-sm text-slate-600">
                                                                                @foreach ($productosCategoria as $producto)
                                                                                    <li>{{ $producto->nombre }} · Cantidad: {{ $producto->pivot->cantidad }} · Preparado: {{ $producto->pivot->preparado ? 'Sí' : 'No' }}</li>
                                                                                @endforeach
                                                                            </ul>
                                                                        </div>
                                                                    @endif
                                                                @endforeach

                                                                <div class="grid gap-3 md:grid-cols-2">
                                                                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Albarán</p>
                                                                        <p class="mt-2 text-sm text-slate-700">{{ $pedido->albaran?->archivoPDF ? 'Generado' : 'Pendiente' }}</p>
                                                                    </div>
                                                                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Factura</p>
                                                                        <p class="mt-2 text-sm text-slate-700">{{ $pedido->factura?->archivoPDF ? 'Generada' : 'Pendiente' }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                @endforeach
            </div>
        </section>
    </div>

    <script>
        setInterval(() => {
            document.querySelectorAll('tr[data-pedido-id]').forEach((row) => {
                const pedidoId = row.dataset.pedidoId;

                fetch(`/pedidos/${pedidoId}/documentos`)
                    .then((res) => res.json())
                    .then((data) => {
                        if (data.estado_html) {
                            row.querySelector('.estado-pedido').innerHTML = data.estado_html;
                        }
                    });
            });
        }, 5000);
    </script>
@endsection
