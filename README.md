# 📋 CRUD con Entornos Dinámicos y CSV

Este proyecto es un sistema de gestión de datos que permite:
-Ggestionar usuarios mediante un sistema **CRUD** (Crear, Leer, Actualizar y Eliminar), 
- Crear **entornos de trabajo** 
- Cada entorno funciona como una **tabla independiente** en MySQL.
- Cargar datos de usuarios manualmente o desde un archivo `.csv`.
- Realizar operaciones CRUD dentro de cada entorno (crear, leer, editar, eliminar).

---

## 🛠️ Tecnologías utilizadas

- PHP (Back-end)
- MySQL (con XAMPP/phpMyAdmin)
- HTML5 / CSS3
- JavaScript Vanilla
- Fetch API

---

## 🗂️ Estructura del proyecto

crud-usuarios/
├── index.php # Página principal para listar y crear entornos
├── entorno.php # Visualiza y gestiona un entorno seleccionado
├── create_environment.php # Crea un nuevo entorno y su tabla correspondiente
├── import_csv_to_environment.php # Importa un CSV a la tabla de un entorno
├── read.php # Lee los datos de un entorno (con ?tabla=...)
├── update_from_environment.php # Actualiza un registro específico en un entorno
├── delete_from_environment.php # Elimina un registro específico en un entorno
├── db.php # Conexión a la base de datos
├── script.js # Funcionalidad JS para CSV, edición y borrado
├── style.css # Estilos personalizados

---

## 🧱 Base de datos MySQL

### 1. Tabla `entornos`
Guarda los entornos creados (uno por cada tabla de trabajo).

sql
CREATE TABLE entornos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
);

2. Tablas por entorno
Cada entorno genera su propia tabla con esta estructura:

CREATE TABLE nombre_entorno (
  id INT AUTO_INCREMENT PRIMARY KEY,
  apellido_nombre VARCHAR(255),
  cuit_dni VARCHAR(50),
  razon_social VARCHAR(255),
  telefono VARCHAR(50),
  correo VARCHAR(100),
  rubro VARCHAR(100)
);

🚀 ¿Cómo usarlo?
Copiá los archivos en htdocs/ dentro de XAMPP.

Asegurate de tener Apache y MySQL activos desde el panel de XAMPP.

Creá la tabla entornos desde phpMyAdmin.

Ingresá a http://localhost/crud-usuarios para comenzar.

Creá un entorno como "Capacitaciones Mayo 2025".

Ingresá al entorno para:

Cargar datos manualmente.

Importar desde .csv.

Editar y eliminar registros.

📄 Formato del archivo CSV
El archivo .csv debe tener 6 columnas:
Apellido y Nombre,CUIT o DNI,Razón Social,Teléfono,Correo Electrónico,Rubro
Ejemplo Uno,20300123456,Empresa Uno,1122334455,uno@email.com,Comercio

✅ Funcionalidades
 Crear entornos con su propia tabla

 Cargar registros manualmente

 Importar múltiples registros desde CSV

 Editar registros con prompt()

 Eliminar registros con confirmación

 Separación total entre entornos

 💡 Actualizaciones futuras
Autenticación de usuarios

Roles por entorno

Exportación de registros a Excel

Edición en línea sin prompt()

Buscador por CUIT/DNI o nombre
