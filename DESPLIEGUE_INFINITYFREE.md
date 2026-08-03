# 🚀 Guía de Despliegue en InfinityFree

Guía específica para alojar **Gestión Voxeles** en **InfinityFree** paso a paso.

---

## ⚠️ Particularidades de InfinityFree

InfinityFree es un hosting gratuito con algunas limitaciones importantes:

- ❌ **No permite `CREATE DATABASE` ni `USE database`** en phpMyAdmin
- ❌ **No soporta archivos `.htaccess` con ciertas directivas**
- ⚠️ **La estructura de carpetas debe ser específica**: el contenido de `public/` debe ir en `htdocs/`
- ⚠️ **Los nombres de BD incluyen prefijo**: `epiz_XXXXX_gestion_voxeles`
- ⚠️ **El host de MySQL NO es `localhost`**: suele ser `sqlXXX.infinityfreeapp.com`

---

## 📋 Requisitos Previos

- ✅ Cuenta en InfinityFree creada
- ✅ Dominio asignado (puede ser subdominio gratuito `.infinityfreeapp.com`)
- ✅ FileZilla instalado
- ✅ Los archivos del proyecto descargados

---

## 🗄️ PASO 1: Crear la Base de Datos

### 1.1 Acceder al Panel de Control

1. Inicia sesión en **InfinityFree Control Panel**
2. Ve a **"MySQL Databases"**

### 1.2 Crear Base de Datos

1. Haz clic en **"Create Database"**
2. Ingresa el nombre: `gestion_voxeles` (se agregará un prefijo automático)
3. Haz clic en **"Create"**
4. **⚠️ IMPORTANTE**: Anota el nombre completo que aparece (ejemplo: `epiz_12345678_gestion_voxeles`)

### 1.3 Anotar Credenciales

Guarda esta información que aparece en el panel:

```
Database Name:  epiz_XXXXX_gestion_voxeles  ← NOMBRE COMPLETO
Database User:  epiz_XXXXX                  ← USUARIO (igual que el prefijo)
Database Host:  sqlXXX.infinityfreeapp.com  ← NO es "localhost"
Password:       [tu contraseña]
```

---

## 📥 PASO 2: Importar el Esquema SQL

### 2.1 Abrir phpMyAdmin

1. En el panel de InfinityFree, haz clic en **"phpMyAdmin"** (al lado de tu base de datos)
2. Se abrirá phpMyAdmin en una nueva pestaña
3. Verás tu base de datos en el panel izquierdo

### 2.2 Importar el Archivo SQL CORRECTO

⚠️ **MUY IMPORTANTE**: En InfinityFree debes usar `schema_hosting.sql`, NO `schema.sql`

1. Haz clic en tu base de datos en el panel izquierdo (para seleccionarla)
2. Haz clic en la pestaña **"Import"** (Importar)
3. Haz clic en **"Choose File"** (Elegir archivo)
4. Selecciona: **`database/schema_hosting.sql`** ← **Este archivo es clave**
5. Deja las opciones por defecto
6. Haz scroll hasta abajo y clic en **"Go"** o **"Continuar"**

### 2.3 Verificar que se Crearon las Tablas

Si todo salió bien, verás un mensaje verde: **"Import has been successfully finished"**

En el panel izquierdo, deberías ver estas 6 tablas:

- ✅ `clientes`
- ✅ `estados_orden`
- ✅ `gastos`
- ✅ `ingresos`
- ✅ `ordenes_trabajo`
- ✅ `usuarios`

**Si NO ves las tablas**, revisa los errores en la pantalla roja de phpMyAdmin.

---

## 📁 PASO 3: Preparar los Archivos Localmente

### 3.1 Estructura de Carpetas para InfinityFree

InfinityFree requiere que el archivo `index.php` esté **directamente en `htdocs/`**. Tenemos que "aplanar" la estructura.

**Estructura LOCAL actual:**
```
gestion-voxeles/
├── app/
├── config/
├── database/
├── public/
│   ├── index.php  ← Está DENTRO de public/
│   └── assets/
└── .htaccess
```

**Estructura que necesita InfinityFree en el servidor:**
```
htdocs/
├── index.php      ← DIRECTO en htdocs/
├── assets/
├── app/
├── config/
└── .htaccess
```

### 3.2 Configurar config.php

1. Abre `gestion-voxeles/config/config.php` con un editor de texto
2. **Actualiza TODAS estas líneas** con los datos de InfinityFree:

```php
// ⚠️ USAR LAS CREDENCIALES EXACTAS DE INFINITYFREE
define('DB_HOST', 'sql123.infinityfreeapp.com');  // ← Cambia sqlXXX por tu servidor
define('DB_NAME', 'epiz_12345678_gestion_voxeles'); // ← Nombre COMPLETO con prefijo
define('DB_USER', 'epiz_12345678');                 // ← Usuario (mismo que prefijo BD)
define('DB_PASS', 'TU_CONTRASEÑA_MYSQL');           // ← La que creaste en InfinityFree
define('DB_CHARSET', 'utf8mb4');

// URL Base: dejar vacío si está en la raíz
define('BASE_URL', '');  // ← Dejar así si el dominio apunta a htdocs/
```

3. **Guarda el archivo**

### 3.3 Modificar public/index.php para Estructura Plana

Como el `index.php` va a estar en la raíz (no dentro de `public/`), necesitamos ajustar las rutas.

1. Abre `gestion-voxeles/public/index.php`
2. **Reemplaza las líneas 15-19** (donde dice `$rutaConfig = dirname(__DIR__)...`) con esto:

```php
// Configuración y constantes
// NOTA: En InfinityFree, index.php está en htdocs/ (raíz), no en public/
$rutaConfig = __DIR__ . '/config/config.php';
if (!is_file($rutaConfig)) {
    http_response_code(500);
    exit('Falta el archivo config/config.php. Copiá config.example.php y completá los datos.');
}
require $rutaConfig;
```

3. **Reemplaza la línea 31** (dentro del autoloader, `dirname(__DIR__) . '/app/'`) con:

```php
$archivo = __DIR__ . '/app/' . str_replace('\\', '/', $relativa) . '.php';
```

4. **Guarda el archivo**

---

## 🌐 PASO 4: Subir Archivos con FileZilla

### 4.1 Conectarse a InfinityFree

1. Abre **FileZilla**
2. Ve al **panel de InfinityFree** → **"FTP Details"**
3. En FileZilla, ingresa:
   - **Host:** `ftpupload.net` o el que aparezca en tu panel
   - **Usuario:** `epiz_XXXXX` (tu ID de cuenta)
   - **Contraseña:** La que usas para entrar a InfinityFree
   - **Puerto:** `21` (FTP normal, NO SFTP)
4. Haz clic en **"Conexión rápida"**

### 4.2 Ubicar la Carpeta Correcta

En el panel **derecho** (servidor remoto), navega a:

```
/htdocs/
```

⚠️ **TODO debe ir DENTRO de `htdocs/`**, no en la raíz.

### 4.3 Subir los Archivos en el Orden Correcto

**IMPORTANTE**: Sube en este orden específico para evitar el 403:

#### Paso A: Subir las carpetas de backend

En el panel **izquierdo** (local), selecciona **SOLO** estas carpetas del proyecto:

- `app/`
- `config/`
- `database/`

Arrástralas al panel derecho (`htdocs/`).

#### Paso B: Subir el contenido de public/

1. En el panel izquierdo, **entra** a la carpeta `public/`
2. Selecciona **TODO** lo que está dentro de `public/`:
   - `index.php` ← MUY IMPORTANTE
   - `assets/` (carpeta)
   - `.htaccess` (puede estar oculto)
3. Arrástralos **directamente a `htdocs/`** (no crear subcarpeta `public/`)

#### Paso C: Subir .htaccess raíz (si existe)

Si hay un `.htaccess` en la raíz del proyecto (fuera de `public/`), **NO LO SUBAS** - puede causar conflictos.

### 4.4 Verificar Archivos Ocultos

FileZilla puede ocultar archivos que empiezan con punto:

1. En FileZilla, ve a **Servidor → Forzar mostrar archivos ocultos**
2. Verifica que `.htaccess` esté en `htdocs/`

### 4.5 Estructura Final en el Servidor

Tu `htdocs/` debería verse así:

```
htdocs/
├── index.php          ← DEBE ESTAR AQUÍ
├── .htaccess          ← DEBE ESTAR AQUÍ
├── assets/
│   ├── css/
│   └── js/
├── app/
│   ├── Controllers/
│   ├── Models/
│   └── Views/
├── config/
│   ├── config.php
│   └── config.example.php
└── database/
    ├── schema.sql
    └── schema_hosting.sql
```

---

## 🧪 PASO 5: Probar la Instalación

### 5.1 Acceder al Sitio

Abre tu navegador y visita:

- **Dominio gratuito**: `https://tu-cuenta.infinityfreeapp.com/`
- **Dominio propio**: `https://tudominio.com/`

### 5.2 Si Aparece Error 403

**Causa común**: `index.php` no está en `htdocs/` o tiene el nombre incorrecto.

**Soluciones**:

1. Verifica que `index.php` esté en `htdocs/` (no en `htdocs/public/`)
2. Verifica que el nombre sea exactamente `index.php` (minúsculas)
3. Borra el `.htaccess` temporalmente y vuelve a intentar

### 5.3 Si Aparece "Error de conexión a la base de datos"

**Verifica en `config/config.php`**:

```php
// ⚠️ VERIFICA QUE ESTOS VALORES COINCIDAN EXACTAMENTE con tu panel de InfinityFree
define('DB_HOST', 'sql123.infinityfreeapp.com');  // ← Verifica el número
define('DB_NAME', 'epiz_12345678_gestion_voxeles'); // ← Prefijo correcto
define('DB_USER', 'epiz_12345678');                 // ← Mismo prefijo
define('DB_PASS', 'TU_CONTRASEÑA');                 // ← Contraseña correcta
```

### 5.4 Login Exitoso

Si todo está bien, verás la pantalla de **login**:

- **Usuario**: `admin`
- **Contraseña**: `admin123`

---

## 🔒 PASO 6: Seguridad Post-Instalación

### 6.1 Cambiar Contraseña del Admin

1. Accede a **phpMyAdmin**
2. Abre la tabla `usuarios`
3. Edita el registro del `admin`
4. Genera un nuevo hash en PHP:

```php
<?php
echo password_hash('TuNuevaContraseñaSegura', PASSWORD_DEFAULT);
```

5. Reemplaza el valor de `password_hash`

### 6.2 HTTPS

InfinityFree ofrece **HTTPS gratuito** activado por defecto. Verifica que tu sitio cargue con `https://`.

---

## 🆘 Solución de Problemas Comunes en InfinityFree

### ❌ Error 403 Forbidden

**Causa 1**: `index.php` no está en `htdocs/` directamente

**Solución**: Mueve `public/index.php` a `htdocs/index.php`

---

**Causa 2**: `.htaccess` con directivas no permitidas

**Solución**: Renombra `.htaccess` a `.htaccess.bak` temporalmente

---

**Causa 3**: Archivos subidos en carpeta incorrecta

**Solución**: Todo debe estar en `/htdocs/`, no en `/` raíz

---

### ❌ No se crean las tablas en phpMyAdmin

**Causa**: Usaste `schema.sql` en vez de `schema_hosting.sql`

**Solución**:

1. Elimina todas las tablas (si se crearon parcialmente)
2. Importa **`schema_hosting.sql`** nuevamente

---

### ❌ CSS/JS no cargan

**Causa**: Rutas incorrectas en las vistas

**Solución**: Verifica que `BASE_URL` en `config.php` esté vacío:

```php
define('BASE_URL', '');  // ← Debe estar vacío para raíz
```

---

### ❌ "Too many connections" o sitio lento

**Causa**: InfinityFree tiene límites de conexiones concurrentes

**Solución**: Esto es normal en hosting gratuito. Considera migrar a un hosting pago cuando el negocio crezca.

---

## ✅ Checklist de Despliegue en InfinityFree

- [ ] Base de datos creada desde el panel de InfinityFree
- [ ] Credenciales anotadas (con prefijo `epiz_` y host `sqlXXX.infinityfreeapp.com`)
- [ ] Archivo `schema_hosting.sql` importado en phpMyAdmin
- [ ] 6 tablas verificadas en phpMyAdmin
- [ ] `config.php` editado con credenciales exactas de InfinityFree
- [ ] `public/index.php` modificado para estructura plana (rutas ajustadas)
- [ ] `BASE_URL` configurado como cadena vacía `''`
- [ ] `app/`, `config/`, `database/` subidas a `htdocs/`
- [ ] Contenido de `public/` (index.php, assets/) subido **directamente** a `htdocs/`
- [ ] `.htaccess` presente en `htdocs/`
- [ ] Sitio accesible desde el navegador (https)
- [ ] Login exitoso con admin/admin123
- [ ] Contraseña de admin cambiada

---

## 🎯 Próximos Pasos

Una vez funcionando:

1. **Cambia la contraseña del admin** (es crítico)
2. **Crea tu primer cliente y orden real**
3. **Prueba el Kanban arrastrando tarjetas**
4. **Genera un MOP e imprímelo** para verificar formato
5. **Configura backups semanales** (exporta la BD desde phpMyAdmin)

---

## 📌 Notas Importantes sobre InfinityFree

- ⏱️ **Inactividad**: Si no hay tráfico por 30 días, la cuenta se suspende
- 💾 **Límites**: 5GB de espacio, ancho de banda ilimitado (con límites de uso razonable)
- 🔄 **Uptime**: Puede haber tiempos de inactividad ocasionales (es gratuito)
- 📧 **Email**: No incluye correo electrónico (solo hosting web)
- 🚀 **Upgrade**: Si el negocio crece, considera HostGator, Bluehost o SiteGround

---

**¡Felicitaciones! Gestión Voxeles está ahora en InfinityFree y listo para usar. 🎉**

_Desarrollado por Alejandro Soria · Gestión Voxeles v1.0_
