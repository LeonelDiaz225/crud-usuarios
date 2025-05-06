document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("userForm");
  const table = document.getElementById("userTable");
  const csvForm = document.getElementById("csvForm");
  const csvFile = document.getElementById("csvFile");

  function loadUsers() {
    fetch("read.php")
      .then(res => res.json())
      .then(data => {
        const tbody = document.getElementById("userTableBody")
        tbody.innerHTML = "";
        data.forEach(user => {
          const row = `
            <tr>
              <td>${user.apellido_nombre}</td>
              <td>${user.cuit_dni}</td>
              <td>${user.razon_social}</td>
              <td>${user.telefono}</td>
              <td>${user.correo}</td>
              <td>${user.rubro}</td>
              <td>
                <button onclick='editUser(${JSON.stringify(user)})'>Editar</button>
                <button onclick='deleteUser(${user.id})'>Eliminar</button>
              </td>
            </tr>
          `;
          tbody.innerHTML += row;
        });

      });
  }

  form.addEventListener("submit", e => {
    e.preventDefault();
    const formData = new FormData(form);
    const id = document.getElementById("id").value;
    const url = id ? "update.php" : "create.php";

    fetch(url, {
      method: "POST",
      body: formData
    }).then(() => {
      form.reset();
      document.getElementById("id").value = "";
      loadUsers();
    });
  });

  window.editUser = (user) => {
    document.getElementById("id").value = user.id;
    document.getElementById("apellido_nombre").value = user.apellido_nombre;
    document.getElementById("cuit_dni").value = user.cuit_dni;
    document.getElementById("razon_social").value = user.razon_social;
    document.getElementById("telefono").value = user.telefono;
    document.getElementById("correo").value = user.correo;
    document.getElementById("rubro").value = user.rubro;
  };

  window.deleteUser = (id) => {
    if (confirm("¿Seguro que desea eliminar este usuario?")) {
      fetch("delete.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `id=${id}`
      }).then(() => loadUsers());
    }
  };

  // 🆕 Funcionalidad de carga de CSV
  csvForm.addEventListener("submit", e => {
    e.preventDefault();
    const file = csvFile.files[0];
    if (!file) {
      alert("Por favor, seleccione un archivo .CSV");
      return;
    }

    const formData = new FormData();
    formData.append("csvFile", file);

    fetch("import_csv.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.text())
      .then(msg => {
        alert(msg);
        csvForm.reset();
        loadUsers();
      })
      .catch(err => {
        alert("Error al importar el CSV");
        console.error(err);
      });
  });

  loadUsers();
});
