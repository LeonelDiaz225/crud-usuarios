# 📋 CRUD de Usuarios con Importación CSV

Este proyecto es una aplicación web que permite gestionar usuarios mediante un sistema **CRUD** (Crear, Leer, Actualizar y Eliminar), con la funcionalidad adicional de **importar usuarios desde un archivo `.csv`**.

## 🛠️ Tecnologías utilizadas

- **PHP** (Backend)
- **MySQL** (Base de datos)
- **JavaScript** (Frontend y comunicación con PHP)
- **HTML5 & CSS3** (Diseño y estructura)
- **Fetch API** (para las solicitudes asincrónicas)
- **XAMPP** (Servidor local)

---

## 📦 Funcionalidades

- Crear usuarios manualmente desde un formulario.
- Leer todos los usuarios en una tabla interactiva.
- Actualizar datos de usuarios existentes.
- Eliminar usuarios.
- Importar múltiples usuarios desde un archivo `.csv`.
- Ordenar por columnas.

---

## 📁 Estructura del proyecto

crud-usuarios/
├── db.php
├── index.php
├── create.php
├── read.php
├── update.php
├── delete.php
├── import_csv.php
├── style.css
├── script.js


---

## 📄 Formato del archivo CSV

El archivo `.csv` debe tener este formato y sin encabezado:

Apellido y Nombre,CUIT o DNI,Razón Social,Teléfono,Correo Electrónico,Rubro
Ejemplo Uno,20300123456,Empresa Uno,1122334455,uno@email.com,Comercio
Ejemplo Dos,27333444556,Empresa Dos,1199887766,dos@email.com,Industria


---

## 🚀 Cómo ejecutar el proyecto

1. Cloná o descargá el repositorio.
2. Colocá los archivos en el directorio `htdocs` de XAMPP.
3. Iniciá **Apache** y **MySQL** desde el panel de XAMPP.
4. Creá una base de datos llamada `crud_usuarios` y ejecutá este SQL:

#sql
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  apellido_nombre VARCHAR(100),
  cuit_dni VARCHAR(20),
  razon_social VARCHAR(100),
  telefono VARCHAR(20),
  correo VARCHAR(100),
  rubro VARCHAR(50)
);

5.Accedé desde tu navegador:
http://localhost/crud-usuarios

✅ Estado del proyecto
✔️ Completado y funcionando.
📥 Admite importación masiva por CSV.
🛠️ Puede expandirse con login, exportación o paginación.

🤝 Contribuciones
¡Las contribuciones son bienvenidas! Podés hacer un fork del proyecto y enviar un pull request.

🧑‍💻 Autor:
Desarrollado por Leonel.
