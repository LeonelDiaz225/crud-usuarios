# Sistema de Gestión de Entornos y Usuarios CRUD

## 🌟 Características Principales

### Gestión de Usuarios
- Sistema de autenticación seguro con contraseñas hasheadas
- Roles: Administrador y Usuario estándar
- Panel de administración para gestionar usuarios
- Control granular de permisos por usuario

### Permisos Configurables
- Crear entornos
- Eliminar entornos
- Editar entornos
- Editar registros
- Eliminar registros
- Asignación de entornos específicos por usuario

### Gestión de Entornos
- Creación dinámica de entornos personalizados
- Campos configurables por entorno:
  - Texto
  - Número
  - Email
  - Fecha
  - Teléfono
- Validación específica por tipo de campo
- Campos requeridos/opcionales configurables

### Operaciones con Datos
- Carga manual de registros con validación
- Importación masiva desde archivos CSV
- Exportación a Excel
- Buscador en tiempo real
- Paginación dinámica
- CRUD completo de registros

### Interfaz de Usuario
- Diseño responsivo con Bootstrap 5
- Tema oscuro personalizado
- Mensajes flotantes de feedback
- Modales de confirmación
- Sidebar retráctil
- Navegación intuitiva

## 🛠️ Componentes Técnicos

### Backend (PHP)
- Gestión de sesiones segura
- Validación y sanitización de datos
- Queries preparadas para prevenir SQL injection
- Manejo de errores y excepciones
- API REST para operaciones CRUD

### Frontend
- JavaScript modular
- Fetch API para peticiones asíncronas
- Validación de formularios
- Manipulación dinámica del DOM
- Gestión de estado local

### Base de Datos
- MySQL con múltiples tablas relacionadas
- Esquema flexible para entornos dinámicos
- Integridad referencial
- Optimización de consultas

## 📁 Estructura de Archivos

```
crud-usuarios/
├── admin/
│   ├── delete_user.php        # Eliminación de usuarios
│   └── update_user.php        # Actualización de usuarios
├── environments/
│   ├── create_environment.php # Creación de entornos
│   ├── delete_environment.php # Eliminación de entornos
│   ├── import_csv_to_environment.php # Importación CSV
│   ├── read.php              # Lectura de registros
│   └── update_from_environment.php # Actualización de registros
├── includes/
│   └── db.php                # Conexión a base de datos
├── js/
│   ├── entorno-campos.js     # Gestión de campos
│   └── script.js             # Funcionalidad principal
├── css/
│   └── style.css            # Estilos personalizados
├── index.php                # Página principal
├── login.php               # Autenticación
├── logout.php              # Cierre de sesión
└── entorno.php            # Vista de entorno
```

## 🔐 Seguridad
- Protección contra SQL Injection
- Validación de sesiones
- Sanitización de inputs
- Control de accesos por rol
- Tokens CSRF (Cross-Site Request Forgery)
- Hashing seguro de contraseñas
- Validación de tipos de archivo

## 💾 Instalación
1. Configurar servidor Apache y MySQL (XAMPP recomendado)
2. Importar estructura de base de datos (schema.sql)
3. Configurar credenciales en includes/db.php
4. Acceder vía navegador a la ruta del proyecto

## 📊 Base de Datos

### Tabla usuarios
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

### Tabla entornos
```sql
CREATE TABLE entornos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### Tabla entornos_campos
```sql
CREATE TABLE entornos_campos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entorno_nombre VARCHAR(255),
    nombre_campo VARCHAR(255),
    tipo_campo VARCHAR(50),
    es_requerido TINYINT(1),
    orden INT
);
```

## 🔄 Flujo de Trabajo
1. Login con credenciales
2. Acceso al dashboard principal
3. Crear o seleccionar entorno
4. Gestionar registros:
   - Añadir manualmente
   - Importar CSV
   - Editar existentes
   - Eliminar registros
   - Exportar datos
5. Administrar usuarios y permisos

## 🎯 Mejoras Futuras
- Implementar sistema de logs
- Añadir más tipos de campos
- Backup automático de entornos
- Reportes personalizados
- API REST documentada
- Filtros avanzados de búsqueda
- Importación desde Excel
- Sistema de notificaciones