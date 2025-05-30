# 📋 CRUD con Entornos Dinámicos y CSV

Este proyecto es un sistema de gestión de datos multi-entorno que permite:
- Gestionar usuarios y permisos mediante un sistema **CRUD** (Crear, Leer, Actualizar y Eliminar).
- Crear **entornos de trabajo** independientes (por ejemplo, "Capacitaciones Mayo 2025").
- Cada entorno funciona como una **tabla separada** en MySQL.
- Cargar datos manualmente o importar desde archivos `.csv`.
- Realizar operaciones CRUD dentro de cada entorno.
- Control de acceso por usuario y roles (admin y usuarios estándar).

---

## 🛠️ Tecnologías utilizadas
- PHP (Back-end)
- MySQL (XAMPP/phpMyAdmin)
- HTML5 / CSS3
- JavaScript Vanilla (sin frameworks)
- Fetch API

---

## 🗂️ Estructura del proyecto

```
crud-usuarios/
├── index.php                       # Panel principal, listado de entornos y gestión de usuarios
├── entorno.php                     # Visualización y gestión de un entorno seleccionado
├── environments/
│   ├── create_environment.php      # Crea un nuevo entorno y su tabla
│   ├── delete_environment.php      # Elimina un entorno y su tabla
│   ├── delete_from_environment.php # Elimina un registro de un entorno
│   ├── import_csv_to_environment.php # Importa registros desde CSV
│   ├── read.php                    # Lee los datos de un entorno (JSON)
│   ├── update_from_environment.php # Actualiza un registro de un entorno
├── includes/
│   └── db.php                      # Conexión a la base de datos
├── js/
│   ├── script.js                   # Lógica JS para CRUD y CSV
│   └── debug.js                    # Herramientas de depuración
├── css/
│   └── style.css                   # Estilos personalizados
├── login.php                       # Login de usuarios
├── logout.php                      # Cierre de sesión
├── hash.php                        # Utilidad para generar hashes de contraseñas
```

---

## 🧱 Base de datos MySQL

### 1. Tabla `entornos`
Registra los entornos creados (uno por cada tabla de trabajo).

```sql
CREATE TABLE entornos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### 2. Tabla de usuarios
Ejemplo de estructura para control de acceso:

```sql
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  rol ENUM('admin','user') DEFAULT 'user',
  puede_crear_entorno TINYINT(1) DEFAULT 0,
  puede_eliminar_entorno TINYINT(1) DEFAULT 0,
  puede_editar_entorno TINYINT(1) DEFAULT 0,
  puede_editar_registros TINYINT(1) DEFAULT 0,
  puede_eliminar_registros TINYINT(1) DEFAULT 0,
  entornos_asignados TEXT
);
```

### 3. Tablas por entorno
Cada entorno genera su propia tabla con esta estructura:

```sql
CREATE TABLE nombre_entorno (
  id INT AUTO_INCREMENT PRIMARY KEY,
  apellido_nombre VARCHAR(255),
  cuit_dni VARCHAR(50),
  razon_social VARCHAR(255),
  telefono VARCHAR(50),
  correo VARCHAR(100),
  rubro VARCHAR(100)
);
```

> ⚠️ `nombre_entorno` se genera automáticamente (espacios reemplazados por guiones bajos).

---

## 🚀 ¿Cómo usarlo?

1. Copia los archivos en `htdocs/` dentro de XAMPP.
2. Asegúrate de tener Apache y MySQL activos desde el panel de XAMPP.
3. Crea las tablas `entornos` y `usuarios` desde phpMyAdmin.
4. Ingresa a `http://localhost/crud-usuarios` para comenzar.
5. Crea un entorno (ejemplo: "Capacitaciones Mayo 2025").
6. Ingresa al entorno para:
   - Cargar datos manualmente.
   - Importar desde `.csv`.
   - Editar y eliminar registros.

---

## 📄 Formato del archivo CSV
El archivo `.csv` debe tener 6 columnas, en este orden:

```
Apellido y Nombre,CUIT o DNI,Razón Social,Teléfono,Correo Electrónico,Rubro
Ejemplo Uno,20300123456,Empresa Uno,1122334455,uno@email.com,Comercio
```

---

## ✅ Funcionalidades
- [x] Autenticación de usuarios y control de permisos
- [x] Crear entornos con su propia tabla
- [x] Cargar registros manualmente
- [x] Importar múltiples registros desde CSV
- [x] Editar registros (modal o inline)
- [x] Eliminar registros con confirmación
- [x] Separación total entre entornos
- [x] Gestión de roles y permisos

---

## 💡 Mejoras y recomendaciones
- Validación y sanitización de datos en frontend y backend
- Exportación de registros a Excel o CSV
- Edición en línea sin `prompt()`
- Buscador por CUIT/DNI o nombre
- Documentación de endpoints y ejemplos de uso
- Refactorización para separar lógica y presentación

---
