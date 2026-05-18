# TROPA - Gestion de pedidos

TROPA es una aplicacion Laravel para gestionar pedidos, productos, usuarios, repartidores, albaranes y facturas. La interfaz esta hecha principalmente con Blade y Tailwind CSS. Para crear pedidos desde la zona de cliente se usa un componente Vue.

## Tecnologias principales

- Laravel: backend, rutas, controladores, modelos, validaciones y sesiones.
- Blade: vistas del administrador, repartidor, cliente, login y documentos PDF.
- Tailwind CSS: estilos responsive y componentes visuales.
- Vue 3: selector dinamico de productos para crear pedidos.
- JavaScript: modales y pequenas interacciones de las vistas Blade.
- Vite: compila CSS y JavaScript.
- DomPDF: genera albaranes y facturas en PDF.

## Estructura importante

- `routes/web.php`: define las rutas web. Las rutas de pedidos y administracion estan protegidas con `auth`.
- `app/Http/Controllers/PedidoController.php`: concentra la logica principal de pedidos, usuarios, productos, estados, documentos y filtros.
- `app/Models`: contiene los modelos de la base de datos y sus relaciones.
- `resources/views/pedidos/admin.blade.php`: panel del administrador.
- `resources/views/pedidos/repartidor.blade.php`: gestion de pedidos por repartidor.
- `resources/views/pedidos/cliente.blade.php`: zona del cliente y punto donde se monta Vue.
- `resources/js/app.js`: registra los modales propios y monta Vue solo si existe el contenedor del creador de pedidos.
- `resources/js/components/PedidoBuilder.vue`: componente Vue para buscar productos, elegir cantidades y enviar el pedido.

## Flujo de pedidos

1. El cliente crea un pedido desde el componente Vue.
2. Laravel valida el usuario y crea el pedido con estado `recibido`.
3. El pedido se asigna a un repartidor de la misma zona.
4. El repartidor marca productos preparados y el estado pasa a `preparacion`.
5. El pedido puede avanzar a `reparto` y luego a `entregado`.
6. El sistema genera albaran cuando el pedido entra en preparacion/reparto/entregado.
7. El sistema genera factura cuando el pedido queda entregado.

## Estados de pedido

- `recibido`: pedido creado y pendiente de preparacion.
- `preparacion`: algun producto ya esta marcado como preparado.
- `reparto`: el pedido esta saliendo hacia el cliente.
- `entregado`: el pedido ha sido entregado y puede tener factura.

## Seguridad

El proyecto ya incluye varias medidas importantes:

- Autenticacion: las rutas de pedidos estan dentro de `Route::middleware('auth')`.
- CSRF: los formularios Blade usan `@csrf`, y Vue envia el token CSRF al crear pedidos.
- Roles: el modelo `User` distingue `admin`, `repartidor` y `cliente`.
- Control de permisos: el controlador bloquea acciones segun el rol. Por ejemplo, un cliente no puede cambiar estados y un repartidor solo gestiona pedidos de su zona.
- Validacion de datos: altas y actualizaciones de usuarios/productos usan `$request->validate()`.
- Password hashing: las contrasenas se guardan con hash mediante Laravel/Hash.
- Proteccion basica de datos: Blade escapa variables con `{{ }}` para reducir riesgo de XSS.
- Restricciones de borrado: no se elimina un usuario o producto si tiene pedidos asociados.

### Tokens usados

El proyecto usa tokens internos de Laravel:

- Token CSRF: protege formularios y peticiones para evitar envios falsos. En Blade se usa con `@csrf` y en Vue se envia como `csrfToken`.
- Token de recuperacion de contraseña: se genera cuando un usuario pide restablecer su contraseña o cuando el administrador envia un enlace de recuperacion.

No se usan tokens API, JWT, Bearer tokens, Sanctum ni Passport.

## Vue

Vue esta instalado y configurado:

- `package.json` incluye `vue` y `@vitejs/plugin-vue`.
- `vite.config.js` carga el plugin de Vue.
- `resources/js/app.js` importa `createApp` y monta `PedidoBuilder.vue`.

Actualmente Vue se usa solo para el creador de pedidos del cliente. El administrador y el repartidor funcionan con Blade, Tailwind y JavaScript propio. Esto esta bien para este proyecto: Vue se usa donde aporta dinamismo real, y Blade queda para pantallas de gestion mas clasicas.

## Tailwind y responsive

Los estilos se hacen con clases Tailwind directamente en Blade/Vue. Se usan prefijos como `sm:`, `md:` y `lg:` para adaptar la interfaz:

- En movil, los formularios y tablas se apilan o permiten scroll horizontal.
- En escritorio, los controles se muestran en fila cuando hay espacio.
- Los componentes reutilizables como `btn-primary`, `btn-danger`, `input-base` y `panel` se definen en `resources/css/app.css`.



## Comandos utiles
Arranca Vite para compilar CSS y JS durante desarrollo.
```bash
npm run dev
```
Genera los assets finales para produccion.

```bash
npm run build
```
Ejecuta los tests del proyecto.

```bash
php artisan test
```
