document.addEventListener("DOMContentLoaded", () => {
  // Obtenemos los parámetros de la URL
  const urlParams = new URLSearchParams(window.location.search);
  const tabla = urlParams.get("tabla");
  
  // Obtenemos las referencias a elementos DOM
  const csvForm = document.getElementById("csvForm");
  const csvFile = document.getElementById("csvFile");
  const userTableBody = document.getElementById("userTableBody");
  const paginationControls = document.getElementById("pagination-controls"); // Nuevo
  let currentPage = 1;
  const itemsPerPage = 5; // Puedes ajustar esto

  const buscadorGeneral = document.getElementById("buscadorGeneral");
  let searchTerm = "";

  if (buscadorGeneral) {
    buscadorGeneral.addEventListener("input", function() {
      searchTerm = this.value;
      loadUsers(1); // Buscar siempre desde la página 1
    });
  }

  // Leer permisos del body
  const bodyElement = document.body;
  const puedeEditar = bodyElement.dataset.puedeEditar === 'true';
  const puedeEliminar = bodyElement.dataset.puedeEliminar === 'true';

  // Referencias al modal y formulario de edición
  const editModal = document.getElementById("editModal");
  const closeEditModal = document.getElementById("closeEditModal");
  const editForm = document.getElementById("editForm");
  const editCancelBtn = document.getElementById("editCancelBtn");

  console.log("Parámetros de URL:", Object.fromEntries(urlParams));
  console.log("Tabla seleccionada:", tabla);
  console.log("Elementos encontrados:", {
    csvForm: !!csvForm,
    csvFile: !!csvFile,
    userTableBody: !!userTableBody,
    paginationControls: !!paginationControls // Nuevo
  });

  function loadUsers(page = 1) { // Modificado para aceptar la página
    if (!tabla || !userTableBody) {
      console.error("No se puede cargar usuarios: falta tabla o contenedor");
      return;
    }
    currentPage = page;
    console.log(`Intentando cargar usuarios para tabla: ${tabla}, página: ${page}, límite: ${itemsPerPage}`);
    
    let url = `environments/read.php?tabla=${tabla}&page=${page}&limit=${itemsPerPage}`;
    if (searchTerm && searchTerm.trim() !== "") {
      url += `&search=${encodeURIComponent(searchTerm.trim())}`;
    }
    fetch(url)

      .then(res => {
        if (!res.ok) {
          throw new Error(`Error HTTP: ${res.status}`);
        }
        return res.json();
      })
      .then(response => { // Modificado para manejar la nueva estructura de respuesta
        console.log("Datos recibidos:", response);
        const data = response.data;
        const totalItems = response.total;
        
        userTableBody.innerHTML = ""; // Limpiamos la tabla

        if (!Array.isArray(data)) {
          console.error("Los datos recibidos no son un array:", data);
          userTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center">Error: los datos recibidos no son válidos</td></tr>';
          updatePaginationControls(0, page, itemsPerPage);
          return;
        }

        if (data.length === 0 && page === 1) {
          console.log("No hay registros para mostrar");
          const emptyRow = document.createElement("tr");
          emptyRow.innerHTML = '<td colspan="7" style="text-align: center">No hay registros disponibles</td>';
          userTableBody.appendChild(emptyRow);
          updatePaginationControls(0, page, itemsPerPage);
          return;
        } else if (data.length === 0 && page > 1) {
          // Esto podría pasar si se navega a una página que ya no tiene datos (ej. después de eliminar)
          console.log("No hay registros en esta página, volviendo a la primera.");
          loadUsers(1); // Cargar la primera página
          return;
        }

data.forEach(user => {
  const row = document.createElement("tr");
  row.setAttribute("data-id", user.id);

  // Creamos el contenido HTML de la fila
  let actionsHtml = '';
  if (puedeEditarRegistros) {
    actionsHtml += `
      <button 
        type="button"
        class="btn btn-warning btn-sm edit-btn"
        data-id="${user.id}"
        data-apellido_nombre="${user.apellido_nombre || ''}"
        data-cuit_dni="${user.cuit_dni || ''}"
        data-razon_social="${user.razon_social || ''}"
        data-telefono="${user.telefono || ''}"
        data-correo="${user.correo || ''}"
        data-rubro="${user.rubro || ''}"
        data-bs-toggle="modal"
        data-bs-target="#editModal"
      >
        <i class="bi bi-pencil"></i> Editar
      </button>
    `;
  }
  if (puedeEliminarRegistros) {
    actionsHtml += `
      <button type="button" class="btn btn-danger btn-sm delete-btn">
        <i class="bi bi-trash"></i> Eliminar
      </button>
    `;
  }

row.innerHTML = `
  <td>${user.apellido_nombre || ''}</td>
  <td>${user.cuit_dni || ''}</td>
  <td>${user.razon_social || ''}</td>
  <td>${user.telefono || ''}</td>
  <td>${user.correo || ''}</td>
  <td>${user.rubro || ''}</td>
  ${
    (puedeEditarRegistros || puedeEliminarRegistros)
      ? `<td>${actionsHtml}</td>`
      : ''
  }
`;

  userTableBody.appendChild(row);
});

        // Activamos los botones de edición y eliminación
        activarBotones();
        // Actualizamos los controles de paginación
        updatePaginationControls(totalItems, page, itemsPerPage);
      })
      .catch(error => {
        console.error("Error al cargar datos:", error);
        if (userTableBody) {
          userTableBody.innerHTML = `<tr><td colspan="7" style="color: red; text-align: center">
            Error al cargar datos: ${error.message}</td></tr>`;
        }
        updatePaginationControls(0, currentPage, itemsPerPage); // Limpiar controles en caso de error
      });
  }

  function updatePaginationControls(totalItems, currentPage, itemsPerPage) {
    if (!paginationControls) return;
    paginationControls.innerHTML = ""; // Limpiar controles existentes

    const totalPages = Math.ceil(totalItems / itemsPerPage);

    if (totalPages <= 1) return; // No mostrar controles si hay una sola página o ninguna

    // Botón Anterior
    if (currentPage > 1) {
      const prevButton = document.createElement("button");
      prevButton.textContent = "←"; // Changed to arrow
      prevButton.classList.add("arrow-button"); // Add class for styling
      prevButton.addEventListener("click", () => loadUsers(currentPage - 1));
      paginationControls.appendChild(prevButton);
    }

    // Números de página (simplificado, podrías hacerlo más complejo con elipses)
    // Mostrar hasta 5 números de página alrededor de la actual
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);

    if (currentPage <= 3) {
        endPage = Math.min(totalPages, 5);
    }
    if (currentPage > totalPages - 3) {
        startPage = Math.max(1, totalPages - 4);
    }

    if (startPage > 1) {
        const firstButton = document.createElement("button");
        firstButton.textContent = "1";
        firstButton.addEventListener("click", () => loadUsers(1));
        paginationControls.appendChild(firstButton);
        if (startPage > 2) {
            const ellipsis = document.createElement("span");
            ellipsis.textContent = "...";
            ellipsis.style.margin = "0 5px";
            paginationControls.appendChild(ellipsis);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
      const pageButton = document.createElement("button");
      pageButton.textContent = i;
      if (i === currentPage) {
        pageButton.disabled = true;
        pageButton.style.fontWeight = "bold";
      }
      pageButton.addEventListener("click", () => loadUsers(i));
      paginationControls.appendChild(pageButton);
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const ellipsis = document.createElement("span");
            ellipsis.textContent = "...";
            ellipsis.style.margin = "0 5px";
            paginationControls.appendChild(ellipsis);
        }
        const lastButton = document.createElement("button");
        lastButton.textContent = totalPages;
        lastButton.addEventListener("click", () => loadUsers(totalPages));
        paginationControls.appendChild(lastButton);
    }

    // Botón Siguiente
    if (currentPage < totalPages) {
      const nextButton = document.createElement("button");
      nextButton.textContent = "→"; // Changed to arrow
      nextButton.classList.add("arrow-button"); // Add class for styling
      nextButton.addEventListener("click", () => loadUsers(currentPage + 1));
      paginationControls.appendChild(nextButton);
    }
  }

function activarBotones() {
  if (!userTableBody) return;

  // Activar botones de eliminación
  document.querySelectorAll(".delete-btn").forEach(btn => {
    btn.removeEventListener("click", handleDelete); // Evitar duplicados
    btn.addEventListener("click", handleDelete);
  });

  // Activar botones de edición
  document.querySelectorAll(".edit-btn").forEach(btn => {
    btn.removeEventListener("click", handleEdit); // Evitar duplicados
    btn.addEventListener("click", handleEdit);
  });
}

// Nuevo handler para editar usando los atributos data-*
function handleEdit() {
  document.getElementById("edit_id").value = this.getAttribute("data-id");
  document.getElementById("edit_apellido_nombre").value = this.getAttribute("data-apellido_nombre");
  document.getElementById("edit_cuit_dni").value = this.getAttribute("data-cuit_dni");
  document.getElementById("edit_razon_social").value = this.getAttribute("data-razon_social");
  document.getElementById("edit_telefono").value = this.getAttribute("data-telefono");
  document.getElementById("edit_correo").value = this.getAttribute("data-correo");
  document.getElementById("edit_rubro").value = this.getAttribute("data-rubro");
  // El modal se abre automáticamente por Bootstrap con data-bs-toggle/data-bs-target
}

  // Manejador para el botón de eliminar
  function handleDelete() {
  const tr = this.closest("tr");
  const id = tr.dataset.id;

  if (confirm("¿Seguro que desea eliminar este registro?")) {
    fetch(`environments/delete_from_environment.php?tabla=${tabla}&id=${id}`)
      .then(res => {
        if (!res.ok) {
          throw new Error(`Error HTTP: ${res.status}`);
        }
        return res.text();
      })
      .then(msg => {
        showFloatingMessage(msg); // Mostrar mensaje flotante
        loadUsers(); // Recargar la tabla inmediatamente
      })
      .catch(error => {
        showFloatingMessage("Error al eliminar: " + error.message, true);
      });
  }
}

  // Manejador para el botón de editar (abre el modal y rellena los campos)
  function handleEdit() {
    const tr = this.closest("tr");
    const id = tr.dataset.id;
    const cells = tr.querySelectorAll("td");
    const user = {
      id,
      apellido_nombre: cells[0].textContent,
      cuit_dni: cells[1].textContent,
      razon_social: cells[2].textContent,
      telefono: cells[3].textContent,
      correo: cells[4].textContent,
      rubro: cells[5].textContent
    };
    openEditModal(user);
  }

  // Función para abrir el modal y rellenar los campos
  function openEditModal(user) {
    if (!editModal) return;
    document.getElementById("edit_id").value = user.id;
    document.getElementById("edit_apellido_nombre").value = user.apellido_nombre;
    document.getElementById("edit_cuit_dni").value = user.cuit_dni;
    document.getElementById("edit_razon_social").value = user.razon_social;
    document.getElementById("edit_telefono").value = user.telefono;
    document.getElementById("edit_correo").value = user.correo;
    document.getElementById("edit_rubro").value = user.rubro;
    editModal.style.display = "block";
    setTimeout(() => {
      document.getElementById("edit_apellido_nombre").focus();
    }, 100);
  }

 

  // Manejar el envío del formulario de edición
if (editForm) {
  editForm.onsubmit = function(e) {
    e.preventDefault();
    const formData = new FormData(editForm);
    fetch("environments/update_from_environment.php", {
      method: "POST",
      body: formData
    })
      .then(res => {
        if (!res.ok) throw new Error(`Error HTTP: ${res.status}`);
        return res.text();
      })
      .then(msg => {
        // Cerrar el modal correctamente con Bootstrap 5
        const modalInstance = bootstrap.Modal.getInstance(document.getElementById('editModal'));
        if (modalInstance) {
          modalInstance.hide();
        }
        showFloatingMessage(msg);
        loadUsers();
      })
      .catch(error => {
        showFloatingMessage("Error al actualizar: " + error.message, true);
      });
  };
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
  csvForm.addEventListener("submit", function(e) {
    e.preventDefault();
    const file = csvFile.files[0];
    if (!file) {
      showFloatingMessage("Por favor, seleccione un archivo .CSV", true);
      return;
    }
    const formData = new FormData(csvForm);

    fetch(`environments/import_csv_to_environment.php?tabla=${tabla}`, {
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
        showFloatingMessage(msg);
        csvForm.reset();
        loadUsers(); // Recargar datos después de importar
      })
      .catch(err => {
        showFloatingMessage("Error al importar el CSV: " + err.message, true);
      });
  });
} else {
    console.warn("No se encontró el formulario CSV");
  }

 // Exportar tabla a Excel
const exportBtn = document.getElementById("exportExcelBtn");
if (exportBtn) {
  exportBtn.addEventListener("click", function() {
    // Pedimos todos los registros al backend (sin paginación)
    fetch(`environments/read.php?tabla=${tabla}&page=1&limit=1000000`)
      .then(res => res.json())
      .then(response => {
        const data = response.data;
        if (!Array.isArray(data) || data.length === 0) {
          alert("No hay datos para exportar.");
          return;
        }

        // Creamos una tabla HTML en memoria (sin columna Acciones)
        const table = document.createElement("table");
        const thead = document.createElement("thead");
        const headerRow = document.createElement("tr");
        ["Apellido y Nombre", "CUIT o DNI", "Razón Social", "Teléfono", "Correo", "Rubro"].forEach(text => {
          const th = document.createElement("th");
          th.textContent = text;
          headerRow.appendChild(th);
        });
        thead.appendChild(headerRow);
        table.appendChild(thead);

        const tbody = document.createElement("tbody");
        data.forEach(user => {
          const row = document.createElement("tr");
          row.innerHTML = `
            <td>${user.apellido_nombre || ''}</td>
            <td>${user.cuit_dni || ''}</td>
            <td>${user.razon_social || ''}</td>
            <td>${user.telefono || ''}</td>
            <td>${user.correo || ''}</td>
            <td>${user.rubro || ''}</td>
          `;
          tbody.appendChild(row);
        });
        table.appendChild(tbody);

        // Exportar usando XLSX
        const wb = XLSX.utils.table_to_book(table, {sheet: "Registros"});
        XLSX.writeFile(wb, `registros_${tabla}.xlsx`);
      })
      .catch(err => {
        alert("Error al exportar: " + err.message);
      });
  });
}

// --- Dropdown de entornos asignados en crear usuario ---
const dropdownItems = document.querySelectorAll('.entorno-item');
const seleccionadosList = document.getElementById('entornosSeleccionados');
const hiddenInputsDiv = document.getElementById('entornosHiddenInputs');
let seleccionados = [];

if (dropdownItems.length && seleccionadosList && hiddenInputsDiv) {
  dropdownItems.forEach(item => {
    item.addEventListener('click', function(e) {
      e.preventDefault();
      const nombre = this.getAttribute('data-nombre');
      if (!seleccionados.includes(nombre)) {
        seleccionados.push(nombre);
        actualizarSeleccionados();
      }
    });
  });

  function actualizarSeleccionados() {
    // Limpiar lista y inputs
    seleccionadosList.innerHTML = '';
    hiddenInputsDiv.innerHTML = '';
    seleccionados.forEach(nombre => {
      // Mostrar en la lista
      const li = document.createElement('li');
      li.className = 'list-group-item d-flex justify-content-between align-items-center py-1';
      li.textContent = nombre;
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'btn btn-sm btn-danger ms-2 flex-shrink-0';
      btn.style.width = '70px'; 
      btn.textContent = 'Quitar';
      btn.onclick = function() {
        seleccionados = seleccionados.filter(n => n !== nombre);
        actualizarSeleccionados();
      };
      li.appendChild(btn);
      seleccionadosList.appendChild(li);
      // Crear input oculto
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'entornos_asignados[]';
      input.value = nombre;
      hiddenInputsDiv.appendChild(input);
    });
  }
}

const sidebar = document.getElementById('sidebar');
const toggle = document.getElementById('sidebarToggle');
const overlay = document.getElementById('sidebarOverlay');
toggle.onclick = () => {
  sidebar.classList.toggle('active');
  overlay.style.display = sidebar.classList.contains('active') ? 'block' : 'none';
};
overlay.onclick = () => {
  sidebar.classList.remove('active');
  overlay.style.display = 'none';
};

function showFloatingMessage(msg, isError = false) {
  let alertDiv = document.createElement('div');
  alertDiv.className = "mensaje-alert";
  alertDiv.style.position = "fixed";
  alertDiv.style.top = "20px";
  alertDiv.style.right = "20px";
  alertDiv.style.zIndex = "9999";
  alertDiv.style.background = isError ? "#dc3545" : "#198754";
  alertDiv.style.color = "#fff";
  alertDiv.style.padding = "12px 24px";
  alertDiv.style.borderRadius = "6px";
  alertDiv.style.boxShadow = "0 2px 8px #0002";
  alertDiv.textContent = msg;
  document.body.appendChild(alertDiv);
  setTimeout(() => {
    alertDiv.style.transition = "opacity 0.5s";
    alertDiv.style.opacity = 0;
    setTimeout(() => {
      if (alertDiv.parentNode) alertDiv.parentNode.removeChild(alertDiv);
    }, 500);
  }, 3000);
}

// Ocultar todas las alertas flotantes (PHP o JS) después de 3 segundos
document.querySelectorAll('.mensaje-alert').forEach(alertDiv => {
  setTimeout(() => {
    alertDiv.style.transition = "opacity 0.5s";
    alertDiv.style.opacity = 0;
    setTimeout(() => {
      if (alertDiv.parentNode) alertDiv.parentNode.removeChild(alertDiv);
    }, 500);
  }, 3000);
});
});