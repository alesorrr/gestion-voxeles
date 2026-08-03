# ✅ Checklist de Despliegue - Gestión Voxeles

**Imprime esta lista y marca cada paso a medida que lo completes.**

---

## 🗄️ BASE DE DATOS

- [ ] Base de datos MySQL creada
- [ ] Usuario MySQL creado con contraseña segura
- [ ] Usuario asignado a la base de datos (todos los privilegios)
- [ ] Archivo `schema.sql` importado en phpMyAdmin
- [ ] Tablas verificadas: `usuarios`, `clientes`, `estados_orden`, `ordenes_trabajo`, `gastos`, `ingresos`
- [ ] Usuario admin existe en tabla `usuarios`

**Anotaciones:**
```
Nombre BD: _______________________________
Usuario:   _______________________________
Host:      _______________________________
```

---

## 📝 CONFIGURACIÓN LOCAL

- [ ] Archivo `config.php` abierto en editor
- [ ] `DB_HOST` actualizado
- [ ] `DB_NAME` actualizado
- [ ] `DB_USER` actualizado
- [ ] `DB_PASS` actualizado
- [ ] `BASE_URL` configurado correctamente
- [ ] Cambios guardados en `config.php`

**Mi BASE_URL es:**
```
[ ] / (raíz del dominio)
[ ] /gestion-voxeles/ (subdirectorio)
[ ] Otro: _______________________________
```

---

## 🌐 FILEZILLA - CONEXIÓN

- [ ] FileZilla instalado
- [ ] Credenciales FTP/SFTP obtenidas del hosting
- [ ] Conexión exitosa al servidor
- [ ] Carpeta raíz localizada (`public_html/` o `www/`)

**Datos de conexión:**
```
Host:   _______________________________
Usuario: _______________________________
Puerto:  [ ] 21 (FTP)  [ ] 22 (SFTP)
```

---

## 📤 SUBIDA DE ARCHIVOS

- [ ] Todas las carpetas seleccionadas en panel local de FileZilla
- [ ] Archivos subidos a la ubicación correcta
- [ ] Carpeta `app/` presente en servidor
- [ ] Carpeta `config/` presente en servidor
- [ ] Carpeta `public/` presente en servidor
- [ ] Carpeta `database/` presente en servidor
- [ ] Archivo `.htaccess` principal visible
- [ ] Archivo `public/.htaccess` visible
- [ ] Transferencia completada sin errores

---

## ⚙️ CONFIGURACIÓN SERVIDOR

- [ ] Versión de PHP verificada (debe ser 8.0+)
- [ ] Archivos `.htaccess` confirmados
- [ ] Permisos de carpetas ajustados si es necesario
- [ ] Carpeta `uploads/` creada con permisos 755

**Versión PHP del hosting:** _______

---

## 🧪 PRUEBAS FUNCIONALES

- [ ] Sitio accesible desde navegador
- [ ] Página de login carga correctamente
- [ ] Inicio de sesión exitoso (admin / admin123)
- [ ] Dashboard muestra estadísticas
- [ ] Puedo crear una nueva orden de trabajo
- [ ] Tablero Kanban carga con 6 columnas
- [ ] Puedo arrastrar tarjetas entre columnas
- [ ] El Kanban actualiza el estado (verificar en BD o recargar página)
- [ ] Módulo de contabilidad funciona
- [ ] Puedo registrar un gasto
- [ ] Vista MOP imprimible se genera
- [ ] Botón "Imprimir MOP" funciona

---

## 🔒 SEGURIDAD

- [ ] Contraseña del admin cambiada (NO usar admin123 en producción)
- [ ] `config.php` NO es accesible públicamente
- [ ] SSL/HTTPS activado (recomendado)
- [ ] Redirección HTTPS configurada si aplica
- [ ] Backup de base de datos configurado

**Nueva contraseña admin guardada en:** _______________________________

---

## 🐛 RESOLUCIÓN DE PROBLEMAS

Si algo falla, revisa:

| Error | Revisar |
|-------|---------|
| Error conexión BD | Credenciales en `config.php` |
| 500 Internal Server | `.htaccess`, versión PHP, logs de error |
| CSS/JS no cargan | `BASE_URL` en `config.php` |
| No puedo login | Tabla `usuarios`, reimportar SQL |
| Kanban no actualiza | Revisar consola del navegador (F12) |

---

## 📊 ESTADO FINAL

**Fecha de despliegue:** ___/___/______

**URL de la aplicación:** _______________________________

**Estado del proyecto:**
- [ ] ✅ Totalmente funcional
- [ ] ⚠️ Funcional con advertencias menores
- [ ] ❌ Requiere ajustes adicionales

**Notas adicionales:**
```
_________________________________________________________________

_________________________________________________________________

_________________________________________________________________
```

---

## 🎯 PRÓXIMOS PASOS

Una vez que todo esté funcionando:

- [ ] Crear usuario de respaldo
- [ ] Cargar clientes reales
- [ ] Importar órdenes existentes (si aplica)
- [ ] Configurar backup automático semanal
- [ ] Capacitar al equipo en el uso del sistema
- [ ] Documentar proceso de backup y restauración

---

**¡Despliegue completado exitosamente! 🎉**

_Gestión Voxeles v1.0_
