# Gestión Voxeles

Sistema web de gestión para un emprendimiento de **impresión 3D**. Permite administrar
órdenes de trabajo (MOP), imprimir las órdenes optimizadas para el taller, mover trabajos
en un **tablero Kanban** con arrastrar y soltar, y llevar la **contabilidad** básica
(ingresos automáticos de órdenes pagadas + gastos manuales, con balance neto).

Construido en **PHP 8.x nativo** con un patrón **MVC** simple, **Bootstrap 5** y **MySQL/MariaDB**.
Sin dependencias externas (no requiere Composer).

---

## ✨ Módulos

- **Órdenes de trabajo (MOP):** cliente, proyecto, archivo 3D, material (PLA/PETG/ASA/TPU/Resina/Nylon/Otro), color, peso y tiempo estimados, relleno, costo de material y precio final.
- **MOP imprimible:** vista limpia con `@media print` lista para imprimir en el taller (encabezado, cliente, detalles técnicos, tabla de costos y espacio para firmas).
- **Tablero Kanban:** 6 columnas (Pendiente/Presupuestado → En Cola → Imprimiendo → Post-procesamiento → Listo/Enviado → Completado/Pagado) con drag & drop y guardado por AJAX.
- **Contabilidad:** ingresos automáticos al marcar una orden como pagada (o moverla a "Completado/Pagado"), registro manual de gastos por categoría, tarjetas de resumen y filtros por rango de fechas.
- **Presupuestos (calculadora de costos):** calculadora de costos de impresión 3D (material, electricidad, hora de máquina, mano de obra, hardware y embalaje) con cálculo en vivo, márgenes por niveles (Competitivo / Estándar / Premium / Lujo) e IVA. Un presupuesto puede convertirse en orden de trabajo con un clic.
- **Usuarios:** alta/edición/baja de usuarios y roles (solo administradores). Roles disponibles: **Administrador**, **Operario** y **Usuario Ventas**, con menú y permisos según el rol.
- **Login básico** usuario/contraseña con contraseñas hasheadas (`password_hash`).

---

## 📋 Requisitos

- PHP **8.0** o superior con las extensiones `pdo_mysql` y `mbstring`.
- **MySQL 8.x** o **MariaDB 10.x**.
- Un servidor web (Apache con `mod_rewrite`, o el servidor embebido de PHP para pruebas).

---

## 🚀 Instalación

### 1. Clonar / copiar el proyecto

```bash
git clone <URL-del-repo> gestion-voxeles
cd gestion-voxeles
```

### 2. Importar la base de datos

El script crea la base `gestion_voxeles`, todas las tablas y datos iniciales
(estados del Kanban, usuario admin y un cliente de ejemplo).

```bash
mysql -u root -p < database/schema.sql
```

### 3. Configurar las credenciales

Copiá el archivo de ejemplo y editá los valores según tu entorno:

```bash
cp config/config.example.php config/config.php
```

Editá `config/config.php`:

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'gestion_voxeles');
define('DB_USER', 'root');
define('DB_PASS', 'tu_password');

// Si la app corre en un subdirectorio, ajustá BASE_URL. Ej: '/gestion-voxeles/public'
// Si corre en la raíz del dominio, dejalo como ''.
define('BASE_URL', '');
```

> `config/config.php` está incluido en `.gitignore` para no versionar credenciales reales.

### 4. Ejecutar

#### Opción A — Servidor embebido de PHP (ideal para probar)

```bash
php -S localhost:8000 -t public
```

Abrí <http://localhost:8000> en el navegador.

#### Opción B — Apache con Virtual Host

Apuntá el `DocumentRoot` a la carpeta `public/` del proyecto y asegurate de tener
`mod_rewrite` habilitado. El `.htaccess` incluido reenvía todas las rutas al
front controller.

```apache
<VirtualHost *:80>
    ServerName voxeles.local
    DocumentRoot /ruta/a/gestion-voxeles/public

    <Directory /ruta/a/gestion-voxeles/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Si en cambio servís desde la raíz del proyecto (no desde `public/`), el `.htaccess`
de la raíz redirige automáticamente hacia `public/`.

---

## 🔑 Acceso por defecto

| Usuario | Contraseña |
|---------|------------|
| `admin` | `admin123` |

> Cambiá la contraseña luego del primer ingreso generando un nuevo hash:
> ```bash
> php -r "echo password_hash('tu_nueva_clave', PASSWORD_DEFAULT), PHP_EOL;"
> ```
> y actualizá el campo `password_hash` del usuario en la tabla `usuarios`.
>
> A partir de la versión 2 también podés crear y administrar usuarios desde la
> propia app en **`/usuarios`** (solo el rol Administrador), sin tocar la base de datos.

### Roles

| Rol | Etiqueta en la app | Acceso |
|-----|--------------------|--------|
| `admin` | Administrador | Todo (incluye Usuarios y Contabilidad) |
| `ventas` | Usuario Ventas | Panel, Órdenes, Kanban y Presupuestos |
| `operador` | Operario | Panel y Tablero Kanban |

---

## 🗂️ Estructura del proyecto

```
gestion-voxeles/
├── public/              # Raíz web (front controller + assets)
│   ├── index.php        # Punto de entrada único
│   └── assets/          # CSS y JS
├── app/
│   ├── Controllers/     # Lógica de cada módulo
│   ├── Models/          # Acceso a datos (PDO)
│   └── Views/           # Plantillas PHP + layouts
├── config/              # config.php (credenciales, constantes)
├── database/            # schema.sql
├── .htaccess            # Reescritura de URLs
└── README.md
```

---

## 🧭 Rutas principales

| Ruta | Descripción |
|------|-------------|
| `/login` | Inicio de sesión |
| `/` | Panel principal (dashboard) |
| `/ordenes` | Listado de órdenes |
| `/ordenes/nueva` | Crear orden |
| `/ordenes/{id}/editar` | Editar orden |
| `/ordenes/{id}/mop` | MOP imprimible |
| `/kanban` | Tablero Kanban |
| `/contabilidad` | Dashboard financiero |
| `/presupuestos` | Listado de presupuestos |
| `/presupuestos/nuevo` | Calculadora / crear presupuesto |
| `/presupuestos/{id}/convertir` | Convertir presupuesto en orden |
| `/usuarios` | Gestión de usuarios (solo admin) |

---

## 📝 Notas

- Todo el código (variables, comentarios y UI) está en **español**.
- Los ingresos se registran automáticamente al marcar una orden como *pagada* o al
  moverla a la columna *Completado / Pagado* del Kanban (sin duplicar el ingreso).
- La moneda mostrada se controla con la constante `MONEDA` en `config/config.php`.
