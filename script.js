document.addEventListener("DOMContentLoaded", () => {
  // Obtenemos los parámetros de la URL
  const urlParams = new URLSearchParams(window.location.search);
  const tabla = urlParams.get("tabla");
  
  // Obtenemos las referencias a elementos DOM
  const csvForm = document.getElementById("csvForm");
  const csvFile = document.getElementById("csvFile");
  const userTableBody = document.getElementById("userTableBody");
  
  console.log("Parámetros de URL:", Object.fromEntries(urlParams));
  console.log("Tabla seleccionada:", tabla);
  console.log("Elementos encontrados:", {
    csvForm: !!csvForm,
    csvFile: !!csvFile,
    userTableBody: !!userTableBody
  });

  function loadUsers() {
    if (!tabla || !userTableBody) {
      console.error("No se puede cargar usuarios: falta tabla o contenedor");
      return;
    }

    console.log("Intentando cargar usuarios para tabla:", tabla);
    
    fetch(`read.php?tabla=${tabla}`)
      .then(res => {
        if (!res.ok) {
          throw new Error(`Error HTTP: ${res.status}`);
        }
        return res.json();
      })
      .then(data => {
        console.log("Datos recibidos:", data);
        
        // Limpiamos la tabla antes de añadir nuevos datos
        userTableBody.innerHTML = "";

        if (!Array.isArray(data)) {
          console.error("Los datos recibidos no son un array:", data);
          return;
        }

        if (data.length === 0) {
          console.log("No hay registros para mostrar");
          const emptyRow = document.createElement("tr");
          emptyRow.innerHTML = '<td colspan="7" style="text-align: center">No hay registros disponibles</td>';
          userTableBody.appendChild(emptyRow);
          return;
        }

        // Para cada registro, creamos una fila
        data.forEach(user => {
          const row = document.createElement("tr");
          row.setAttribute("data-id", user.id);

          // Creamos el contenido HTML de la fila
          row.innerHTML = `
            <td>${user.apellido_nombre || ''}</td>
            <td>${user.cuit_dni || ''}</td>
            <td>${user.razon_social || ''}</td>
            <td>${user.telefono || ''}</td>
            <td>${user.correo || ''}</td>
            <td>${user.rubro || ''}</td>
            <td>
              <button class="editBtn">Editar</button>
              <button class="deleteBtn">Eliminar</button>
            </td>
          `;

          userTableBody.appendChild(row);
        });

        // Activamos los botones de edición y eliminación
        activarBotones();
      })
      .catch(error => {
        console.error("Error al cargar datos:", error);
        if (userTableBody) {
          userTableBody.innerHTML = `<tr><td colspan="7" style="color: red; text-align: center">
            Error al cargar datos: ${error.message}</td></tr>`;
        }
      });
  }

  function activarBotones() {
    if (!userTableBody) return;
    
    // Activar botones de eliminación
    document.querySelectorAll(".deleteBtn").forEach(btn => {
      btn.removeEventListener("click", handleDelete); // Evitar duplicados
      btn.addEventListener("click", handleDelete);
    });

    // Activar botones de edición
    document.querySelectorAll(".editBtn").forEach(btn => {
      btn.removeEventListener("click", handleEdit); // Evitar duplicados
      btn.addEventListener("click", handleEdit);
    });
  }

  // Manejador para el botón de eliminar
  function handleDelete() {
    const tr = this.closest("tr");
    const id = tr.dataset.id;

    if (confirm("¿Seguro que desea eliminar este registro?")) {
      fetch(`delete_from_environment.php?tabla=${tabla}&id=${id}`)
        .then(res => {
          if (!res.ok) {
            throw new Error(`Error HTTP: ${res.status}`);
          }
          return res.text();
        })
        .then(msg => {
          alert(msg);
          loadUsers(); // Recargar la tabla
        })
        .catch(error => {
          console.error("Error al eliminar:", error);
          alert("Error al eliminar: " + error.message);
        });
    }
  }

  // Manejador para el botón de editar
  function handleEdit() {
    const tr = this.closest("tr");
    const id = tr.dataset.id;
    const cells = tr.querySelectorAll("td");
    const values = Array.from(cells).slice(0, 6).map(td => td.textContent);
    const campos = ["Apellido y Nombre", "CUIT o DNI", "Razón Social", "Teléfono", "Correo", "Rubro"];

    const nuevos = values.map((val, i) => prompt(`Editar ${campos[i]}:`, val));
    if (nuevos.includes(null)) return; // El usuario canceló

    const formData = new FormData();
    formData.append("id", id);
    formData.append("tabla", tabla);
    formData.append("apellido_nombre", nuevos[0]);
    formData.append("cuit_dni", nuevos[1]);
    formData.append("razon_social", nuevos[2]);
    formData.append("telefono", nuevos[3]);
    formData.append("correo", nuevos[4]);
    formData.append("rubro", nuevos[5]);

    fetch("update_from_environment.php", {
      method: "POST",
      body: formData
    })
      .then(res => {
        if (!res.ok) {
          throw new Error(`Error HTTP: ${res.status}`);
        }
        return res.text();
      })
      .then(msg => {
        alert(msg);
        loadUsers(); // Recargar la tabla
      })
      .catch(error => {
        console.error("Error al actualizar:", error);
        alert("Error al actualizar: " + error.message);
      });
  }

  // Iniciar carga de datos si estamos en la página correcta
  if (tabla && userTableBody) {
    console.log("Iniciando carga de datos para tabla:", tabla);
    loadUsers();
  } else {
    console.warn("No se encontró tabla o elemento userTableBody");
  }

  // Configurar el formulario de importación CSV si existe
  if (csvForm && csvFile) {
    console.log("Configurando formulario CSV");
    
    csvForm.addEventListener("submit", function(e) {
      e.preventDefault();
      console.log("Formulario CSV enviado");
      
      const file = csvFile.files[0];
      if (!file) {
        alert("Por favor, seleccione un archivo .CSV");
        return;
      }

      console.log("Archivo seleccionado:", file.name);
      const formData = new FormData(csvForm);

      fetch(`import_csv_to_environment.php?tabla=${tabla}`, {
        method: "POST",
        body: formData
      })
        .then(res => {
          if (!res.ok) {
            throw new Error(`Error HTTP: ${res.status}`);
          }
          return res.text();
        })
        .then(msg => {
          alert(msg);
          csvForm.reset();
          loadUsers(); // Recargar datos después de importar
        })
        .catch(err => {
          console.error("Error al importar CSV:", err);
          alert("Error al importar el CSV: " + err.message);
        });
    });
  } else {
    console.warn("No se encontró el formulario CSV");
  }
});