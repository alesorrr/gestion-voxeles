# 🚀 Guía de Despliegue en Hosting con FileZilla

Esta guía te ayudará a alojar **Gestión Voxeles** en tu servidor web usando FileZilla.

---

## 📋 Requisitos Previos

Antes de comenzar, asegúrate de tener:

- ✅ **Hosting con soporte PHP 8.0+** (cPanel, Plesk, o hosting compartido)
- ✅ **Base de datos MySQL** (versión 5.7+ o MariaDB 10.3+)
- ✅ **FileZilla Client** instalado ([descargar aquí](https://filezilla-project.org/))
- ✅ **Credenciales FTP/SFTP** de tu proveedor de hosting
- ✅ **Acceso a phpMyAdmin** o panel de base de datos

---

## 🔧 Paso 1: Preparar la Base de Datos

### 1.1 Crear la Base de Datos

1. **Accede al panel de control de tu hosting** (cPanel, Plesk, etc.)
2. Ve a **"MySQL Databases"** o **"Bases de Datos MySQL"**
3. Crea una nueva base de datos:
   - **Nombre sugerido:** `gestion_voxeles` o `tu_usuario_voxeles`
   - Anota el nombre completo (puede incluir un prefijo del hosting)

### 1.2 Crear Usuario de Base de Datos

1. En la misma sección, crea un **nuevo usuario MySQL**:
   - **Usuario:** `voxeles_admin` (o el que prefieras)
   - **Contraseña:** Genera una contraseña segura y guárdala
2. **Asigna el usuario a la base de datos** con **todos los privilegios**

### 1.3 Importar el Esquema SQL

1. Abre **phpMyAdmin** desde tu panel de hosting
2. Selecciona la base de datos que acabas de crear (clic en el nombre en el panel izquierdo)
3. Haz clic en la pestaña **"Importar"** o **"Import"**
4. Selecciona el archivo correcto según tu tipo de hosting:
   - **Hosting compartido (cPanel, InfinityFree, etc.)**: `database/schema_hosting.sql` ⭐ **Recomendado**
   - **VPS/Servidor dedicado**: `database/schema.sql`
5. Haz clic en **"Continuar"** o **"Go"**
6. ✅ **Verifica que se crearon las tablas:** `usuarios`, `clientes`, `estados_orden`, `ordenes_trabajo`, `gastos`, `ingresos`

> ⚠️ **Importante**: Si ves errores sobre "CREATE DATABASE" o "USE", es porque estás usando `schema.sql` en un hosting compartido. Usa `schema_hosting.sql` en su lugar.

---

## 📁 Paso 2: Preparar los Archivos Localmente

### 2.1 Configurar config.php

1. En tu computadora, navega a: `gestion-voxeles/config/`
2. Abre el archivo `config.php` con un editor de texto
3. **Actualiza las credenciales de la base de datos:**

```php
// Configuración de Base de Datos
define('DB_HOST', 'localhost');  // O la IP/host que te proporcionó tu hosting
define('DB_NAME', 'gestion_voxeles');  // El nombre exacto de tu BD
define('DB_USER', 'voxeles_admin');     // Tu usuario MySQL
define('DB_PASS', 'TU_CONTRASEÑA_SEGURA');  // La contraseña del usuario
define('DB_CHARSET', 'utf8mb4');

// URL Base de la Aplicación
define('BASE_URL', '/');  // Si está en la raíz del dominio
// O define('BASE_URL', '/gestion-voxeles/'); si está en un subdirectorio
```

4. **Guarda los cambios**

> ⚠️ **IMPORTANTE:** Si tu hosting usa un host diferente a `localhost` (como `127.0.0.1` o un host remoto), actualiza `DB_HOST` según las instrucciones de tu proveedor.

### 2.2 Verificar BASE_URL

- **Si tu sitio será:** `https://tudominio.com/` → `define('BASE_URL', '/');`
- **Si será:** `https://tudominio.com/gestion-voxeles/` → `define('BASE_URL', '/gestion-voxeles/');`
- **Si será un subdominio:** `https://voxeles.tudominio.com/` → `define('BASE_URL', '/');`

---

## 🌐 Paso 3: Subir Archivos con FileZilla

### 3.1 Conectarse al Servidor

1. **Abre FileZilla**
2. En la barra superior, completa los datos de conexión:
   - **Servidor/Host:** ftp.tudominio.com (o la IP proporcionada)
   - **Usuario:** tu_usuario_ftp
   - **Contraseña:** tu_contraseña_ftp
   - **Puerto:** 21 (FTP) o 22 (SFTP/SSH)
3. Haz clic en **"Conexión rápida"** o **"Quickconnect"**

### 3.2 Ubicar la Carpeta de Destino

En el **panel derecho** (servidor remoto), navega a la carpeta raíz de tu sitio web:
- **Común:** `public_html/` o `www/` o `htdocs/`
- Si instalas en subdirectorio, entra a `public_html/gestion-voxeles/`

### 3.3 Subir los Archivos

**OPCIÓN A - Subir TODO el proyecto (Recomendado para principiantes):**

1. En el **panel izquierdo** (local), navega a la carpeta `gestion-voxeles/`
2. Selecciona **TODOS los archivos y carpetas** del proyecto
3. Haz clic derecho → **"Subir"** o arrastra al panel derecho
4. Espera a que todos los archivos se transfieran (puede tomar 5-10 minutos)

**OPCIÓN B - Estructura correcta en hosting:**

Si tu hosting requiere que el contenido de `public/` esté en `public_html/`:

1. Sube las carpetas `app/`, `config/`, `database/` a `public_html/gestion-voxeles/` (o un nivel arriba)
2. Sube **solo el contenido** de la carpeta `public/` directamente a `public_html/`
3. Ajusta las rutas en `public/index.php` si es necesario

> 💡 **Consejo:** La mayoría de hostings compartidos funcionan bien con la OPCIÓN A.

---

## ⚙️ Paso 4: Configurar el Servidor Web

### 4.1 Verificar .htaccess

Asegúrate de que los archivos `.htaccess` se hayan subido correctamente:
- ✅ `gestion-voxeles/.htaccess`
- ✅ `gestion-voxeles/public/.htaccess`

> **Nota:** FileZilla a veces oculta archivos que empiezan con punto. Ve a **Servidor → Forzar mostrar archivos ocultos**.

### 4.2 Configurar Permisos (Opcional)

En algunos hostings, necesitas ajustar permisos:

1. Haz clic derecho en la carpeta `public/uploads/` (créala si no existe)
2. **Permisos de archivo:** `755` o `775`
3. Esto permite que PHP escriba archivos subidos

---

## 🧪 Paso 5: Probar la Instalación

### 5.1 Acceder a la Aplicación

Abre tu navegador y visita:
- **Dominio raíz:** `https://tudominio.com/`
- **Subdirectorio:** `https://tudominio.com/gestion-voxeles/`
- **Subdominio:** `https://voxeles.tudominio.com/`

### 5.2 Inicio de Sesión

Si todo está correcto, verás la pantalla de login:
- **Usuario:** `admin`
- **Contraseña:** `admin123`

### 5.3 Checklist de Verificación

- [ ] ✅ La página de login carga sin errores
- [ ] ✅ Puedes iniciar sesión con admin/admin123
- [ ] ✅ El dashboard muestra tarjetas de estadísticas
- [ ] ✅ Puedes crear una nueva orden de trabajo
- [ ] ✅ El tablero Kanban muestra columnas
- [ ] ✅ Puedes arrastrar tarjetas entre columnas
- [ ] ✅ El módulo de contabilidad carga correctamente
- [ ] ✅ Puedes generar e imprimir un MOP

---

## 🔒 Paso 6: Seguridad Post-Instalación

### 6.1 Cambiar Contraseña del Administrador

1. Accede a phpMyAdmin
2. Ve a la tabla `usuarios`
3. Edita el registro del usuario `admin`
4. Genera un nuevo hash de contraseña ejecutando en PHP:
   ```php
   echo password_hash('TuNuevaContraseña', PASSWORD_DEFAULT);
   ```
5. Reemplaza el valor de `password_hash` con el nuevo hash

### 6.2 Proteger config.php

Verifica que `config.php` NO sea accesible públicamente:
- Intenta visitar: `https://tudominio.com/config/config.php`
- **Debe dar error 403/404**. Si muestra contenido, contacta a tu hosting.

### 6.3 HTTPS (Recomendado)

Si tu hosting ofrece SSL/HTTPS gratuito (Let's Encrypt):
1. Actívalo desde el panel de control
2. Fuerza HTTPS agregando al `.htaccess` principal:
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

---

## 🆘 Solución de Problemas Comunes

### Error: "Error de conexión a la base de datos"

**Causa:** Credenciales incorrectas en `config.php`

**Solución:**
1. Verifica `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` en `config/config.php`
2. Confirma que el usuario tenga privilegios en la BD
3. Algunos hostings usan `127.0.0.1` en vez de `localhost`

### Error: "500 Internal Server Error"

**Causa:** Problema con `.htaccess` o permisos

**Solución:**
1. Renombra `.htaccess` temporalmente a `.htaccess.bak`
2. Si funciona, el problema es mod_rewrite. Contacta al hosting.
3. Verifica que PHP 8.0+ esté activo en el panel de hosting

### Las imágenes/CSS no cargan

**Causa:** `BASE_URL` mal configurado

**Solución:**
1. Abre `config/config.php`
2. Ajusta `BASE_URL` según tu estructura:
   - Raíz: `'/'`
   - Subdirectorio: `'/nombre-carpeta/'`

### No puedo iniciar sesión

**Causa:** Tabla `usuarios` vacía o error al importar SQL

**Solución:**
1. Ve a phpMyAdmin → tabla `usuarios`
2. Verifica que exista el registro del admin
3. Si está vacío, re-importa `database/schema.sql`

### "No such file or directory" en logs

**Causa:** Rutas absolutas mal configuradas

**Solución:**
1. Verifica que todas las carpetas (`app/`, `config/`, `public/`) estén en la ubicación correcta
2. Revisa el autoloader en `public/index.php`

---

## 📞 Soporte Adicional

Si encuentras problemas no listados aquí:

1. **Revisa los logs de error de PHP:**
   - En cPanel: **Métricas → Errores**
   - O consulta `error_log` en tu carpeta raíz

2. **Verifica la versión de PHP:**
   - Debe ser **PHP 8.0** o superior
   - Puedes cambiarla desde el panel de hosting

3. **Contacta a tu proveedor de hosting:**
   - Pregunta por: mod_rewrite, PDO, y permisos de escritura

---

## ✅ Checklist de Despliegue Completo

- [ ] Base de datos creada e importada
- [ ] Usuario MySQL configurado con privilegios
- [ ] `config.php` editado con credenciales correctas
- [ ] `BASE_URL` configurado según tu estructura
- [ ] Todos los archivos subidos via FileZilla
- [ ] `.htaccess` presente y funcional
- [ ] Login exitoso con admin/admin123
- [ ] Dashboard cargando correctamente
- [ ] Kanban drag & drop funcionando
- [ ] MOP imprimible generándose
- [ ] Contraseña de admin cambiada
- [ ] HTTPS activado (opcional pero recomendado)

---

## 🎉 ¡Felicitaciones!

**Gestión Voxeles** está ahora alojado y listo para gestionar tu emprendimiento de impresión 3D.

**Próximos pasos recomendados:**
1. Crear nuevos usuarios (si planeas tener múltiples operadores)
2. Cargar tus clientes reales
3. Importar órdenes de trabajo existentes
4. Configurar backups automáticos de la base de datos

---

**Desarrollado por:** Alejandro Soria  
**Proyecto:** Gestión Voxeles  
**Stack:** PHP 8.2 + MySQL + Bootstrap 5
