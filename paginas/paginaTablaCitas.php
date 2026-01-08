<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Gestion de citas</title>
  <link href="data:image/x-icon;base64," rel="icon" type="image/x-icon" />
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com" rel="preconnect" />
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet" />
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "primary": "#13a4ec",
            "background-light": "#f6f7f8",
            "background-dark": "#101c22",
          },
          fontFamily: {
            "display": ["Inter"]
          },
          borderRadius: {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
        },
      },
    }
  </script>
  <!-- Carga de Material Symbols Outlined (Iconos) -->
  <style>
    .material-symbols-outlined {
      font-variation-settings:
        'FILL' 0,
        'wght' 400,
        'GRAD' 0,
        'opsz' 24
    }
  </style>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-slate-800 dark:text-slate-200">
  <!-- Barra de Notificación -->
  <div id="notification-bar" class="fixed top-0 right-0 m-4 p-3 rounded-lg shadow-xl text-white hidden z-50 transition-all duration-300"></div>

  <div class="flex min-h-screen">
    <aside class="w-64 flex flex-col bg-background-light dark:bg-background-dark border-r border-slate-200 dark:border-slate-800">
      <div class="p-6">
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">Admin Panel</h1>
      </div>
      <nav class="flex-1 px-4 py-2 space-y-2">
        <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800" href="paginaUsuarios.php">
          <svg fill="currentColor" height="24" viewBox="0 0 256 256" width="24" xmlns="http://www.w3.org/2000/svg">
            <path d="M117.25,157.92a60,60,0,1,0-66.5,0A95.83,95.83,0,0,0,3.53,195.63a8,8,0,1,0,13.4,8.74,80,80,0,0,1,134.14,0,8,8,0,0,0,13.4-8.74A95.83,95.83,0,0,0,117.25,157.92ZM40,108a44,44,0,1,1,44,44A44.05,44.05,0,0,1,40,108Zm210.14,98.7a8,8,0,0,1-11.07-2.33A79.83,79.83,0,0,0,172,168a8,8,0,0,1,0-16,44,44,0,1,0-16.34-84.87,8,8,0,1,1-5.94-14.85,60,60,0,0,1,55.53,105.64,95.83,95.83,0,0,1,47.22,37.71A8,8,0,0,1,250.14,206.7Z"></path>
          </svg>
          <span class="text-sm font-medium">Usuarios</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary dark:bg-primary/20" href="paginaTablaCitas.php">
          <svg fill="currentColor" height="24" viewBox="0 0 256 256" width="24" xmlns="http://www.w3.org/2000/svg">
            <path d="M208,32H184V24a8,8,0,0,0-16,0v8H88V24a8,8,0,0,0-16,0v8H48A16,16,0,0,0,32,48V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V48A16,16,0,0,0,208,32ZM112,184a8,8,0,0,1-16,0V132.94l-4.42,2.22a8,8,0,0,1-7.16-14.32l16-8A8,8,0,0,1,112,120Zm56-8a8,8,0,0,1,0,16H136a8,8,0,0,1-6.4-12.8l28.78-38.37A8,8,0,1,0,145.07,132a8,8,0,1,1-13.85-8A24,24,0,0,1,176,136a23.76,23.76,0,0,1-4.84,14.45L152,176ZM48,80V48H72v8a8,8,0,0,0,16,0V48h80v8a8,8,0,0,0,16,0V48h24V80Z"></path>
          </svg>
          <span class="text-sm font-medium">Citas</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800" href="paginaHorario.php">
          <svg fill="currentColor" height="24" viewBox="0 0 256 256" width="24" xmlns="http://www.w3.org/2000/svg">
            <path d="M208,32H184V24a8,8,0,0,0-16,0v8H88V24a8,8,0,0,0-16,0v8H48A16,16,0,0,0,32,48V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V48A16,16,0,0,0,208,32ZM112,184a8,8,0,0,1-16,0V132.94l-4.42,2.22a8,8,0,0,1-7.16-14.32l16-8A8,8,0,0,1,112,120Zm56-8a8,8,0,0,1,0,16H136a8,8,0,0,1-6.4-12.8l28.78-38.37A8,8,0,1,0,145.07,132a8,8,0,1,1-13.85-8A24,24,0,0,1,176,136a23.76,23.76,0,0,1-4.84,14.45L152,176ZM48,80V48H72v8a8,8,0,0,0,16,0V48h80v8a8,8,0,0,0,16,0V48h24V80Z"></path>
          </svg>
          <span class="text-sm font-medium">Horario</span>
        </a>
      </nav>
      <div class="mt-8">
        <button onclick="window.location.href='login.php'" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-800 font-medium transition-colors">
          <span class="material-symbols-outlined">logout</span>
          Cerrar sesión
        </button>
      </div>
    </aside>
    <main class="flex-1 p-8">
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Citas</h2>
        <button class="bg-primary text-white px-4 py-2 rounded-lg font-medium hover:bg-primary/90" onclick="window.location.href='paginaagendarCitas.php'">Nueva cita</button>
      </div>
      <div class="mb-6">
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="text-slate-400 dark:text-slate-500" fill="currentColor" height="20" viewBox="0 0 256 256" width="20" xmlns="http://www.w3.org/2000/svg">
              <path d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"></path>
            </svg>
          </div>
          <input class="w-full pl-10 pr-4 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500" placeholder="Buscar citas..." type="text" />
        </div>
      </div>
      <div class="bg-white dark:bg-slate-800/50 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm text-left text-slate-500 dark:text-slate-400">
          <?php
          // Obtener citas desde el endpoint
          $curlCitas = curl_init();
          curl_setopt($curlCitas, CURLOPT_URL, 'http://localhost/proyectofinalMW/citas');
          curl_setopt($curlCitas, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($curlCitas, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($curlCitas, CURLOPT_SSL_VERIFYHOST, false);
          $responseCitas = curl_exec($curlCitas);
          curl_close($curlCitas);
          $citas = json_decode($responseCitas, true);
          if (!is_array($citas)) $citas = [];
          ?>
          <thead class="text-xs text-slate-700 dark:text-slate-300 uppercase bg-slate-50 dark:bg-slate-900/50">
            <tr>
              <th class="px-6 py-3" scope="col">Usuario</th>
              <th class="px-6 py-3" scope="col">Fecha</th>
              <th class="px-6 py-3" scope="col">Hora</th>
              <th class="px-6 py-3" scope="col">Tipo</th>
              <th class="px-6 py-3" scope="col">Estado</th> <!-- Nueva Columna -->
              <th class="px-6 py-3 text-center" scope="col">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($citas as $cita) : ?>
              <tr class="bg-white dark:bg-transparent border-b dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900/30">
                <th class="px-6 py-4 font-medium text-slate-900 dark:text-white whitespace-nowrap" scope="row">
                  <?php
                  // Mostrar nombre_usuario si existe, si no mostrar idusuario
                  if (!empty($cita['nombre_usuario'])) {
                    echo htmlspecialchars($cita['nombre_usuario']);
                  } else if (!empty($cita['idusuario'])) {
                    echo htmlspecialchars($cita['idusuario']);
                  } else {
                    echo 'Sin usuario';
                  }
                  ?>
                </th>
                <td class="px-6 py-4">
                  <?php
                  $fechaHora = $cita['fecha'] ?? '';
                  $fechaObj = $fechaHora ? new DateTime($fechaHora) : null;
                  echo $fechaObj ? $fechaObj->format('d \d\e F \d\e Y') : '';
                  ?>
                </td>
                <td class="px-6 py-4">
                  <?php
                  echo $fechaObj ? $fechaObj->format('g:i A') : '';
                  ?>
                </td>
                <td class="px-6 py-4">
                  <span class="bg-primary/10 text-primary text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-primary/20">
                    <?= htmlspecialchars($cita['tipo_sesion'] ?? '') ?>
                  </span>
                </td>
                <td class="px-6 py-4">
                  <?php
                  $estado = htmlspecialchars($cita['estado'] ?? 'confirmado');
                  $colorClass = '';
                  switch ($estado) {
                    case 'confirmado':
                      $colorClass = 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300';
                      break;
                    case 'cancelado':
                      $colorClass = 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
                      break;
                    case 'reprogramado':
                      $colorClass = 'bg-yellow-200 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-200';
                      break;
                    default:
                      $colorClass = 'bg-primary/10 text-primary dark:bg-primary/20';
                      break;
                  }
                  ?>
                  <span class="<?= $colorClass ?> text-xs font-medium px-2.5 py-0.5 rounded-full">
                    <?= ucfirst($estado) ?>
                  </span>
                </td>
                <td class="px-6 py-4 text-center flex gap-2 justify-center">
                  <button class="font-medium text-blue-600 hover:underline" onclick="abrirModalEditar(<?= htmlspecialchars($cita['id']) ?>, '<?= htmlspecialchars($cita['fecha']) ?>', '<?= htmlspecialchars($cita['estado'] ?? 'confirmado') ?>')">Editar</button>
                  <button class="font-medium text-red-600 hover:underline" onclick="abrirModalEliminar(<?= htmlspecialchars($cita['id']) ?>)">Eliminar</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <!-- Modal para editar cita (Actualizado con campo Estado) -->
  <div id="modal-editar" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white dark:bg-background-dark rounded-xl shadow-lg p-8 w-full max-w-md relative">
      <button class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 dark:hover:text-white text-2xl font-bold" onclick="cerrarModalEditar()">&times;</button>
      <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Editar Cita</h2>
      <form id="form-editar-cita" class="space-y-4">
        <input type="hidden" id="editar-idcita" name="id" />
        <div>
          <label class="block text-sm font-medium mb-1" for="editar-fecha">Fecha</label>
          <input type="date" id="editar-fecha" name="fecha" class="w-full px-3 py-2 border rounded-lg dark:bg-slate-800 dark:text-white border-slate-300 dark:border-slate-700" required />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1" for="editar-hora">Hora</label>
          <input type="time" id="editar-hora" name="hora" class="w-full px-3 py-2 border rounded-lg dark:bg-slate-800 dark:text-white border-slate-300 dark:border-slate-700" required />
        </div>
        <!-- CAMPO ESTADO AÑADIDO -->
        <div>
          <label class="block text-sm font-medium mb-1" for="editar-estado">Estado</label>
          <select id="editar-estado" name="estado" class="w-full px-3 py-2 border rounded-lg dark:bg-slate-800 dark:text-white border-slate-300 dark:border-slate-700" required>
            <option value="confirmado">Confirmado</option>
            <option value="cancelado">Cancelado</option>
            <option value="reprogramado">reprogramado</option>
          </select>
        </div>
        <div class="flex justify-end gap-2">
          <button type="button" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-medium" onclick="cerrarModalEditar()">Cancelar</button>
          <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white font-medium hover:bg-primary/90">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal para confirmar eliminación -->
  <div id="modal-eliminar" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white dark:bg-background-dark rounded-xl shadow-lg p-8 w-full max-w-sm relative">
      <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Confirmar Eliminación</h2>
      <p class="text-slate-600 dark:text-slate-400 mb-6">¿Estás seguro de que deseas eliminar la cita con ID: <strong id="eliminar-id-text"></strong>? Esta acción no se puede deshacer.</p>
      <input type="hidden" id="eliminar-id-cita" />
      <div class="flex justify-end gap-2">
        <button type="button" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-medium" onclick="cerrarModalEliminar()">Cancelar</button>
        <button type="button" class="px-4 py-2 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700" onclick="ejecutarEliminacion()">Eliminar</button>
      </div>
    </div>
  </div>


  <script>
    // --- LÓGICA DE NOTIFICACIONES ---
    function mostrarNotificacion(mensaje, isError = false) {
      const bar = document.getElementById('notification-bar');
      bar.textContent = mensaje;
      bar.classList.remove('hidden');

      if (isError) {
        bar.classList.remove('bg-green-500');
        bar.classList.add('bg-red-600');
      } else {
        bar.classList.remove('bg-red-600');
        bar.classList.add('bg-green-500');
      }

      setTimeout(() => {
        bar.classList.add('hidden');
      }, 4000);
    }

    // --- MODAL EDITAR (Actualizado) ---
    function abrirModalEditar(id, fechaCompleta, estadoActual) {
      // fechaCompleta: 'YYYY-MM-DD HH:MM:SS'
      const [fecha, horaFull] = fechaCompleta.split(' ');
      const hora = horaFull ? horaFull.slice(0, 5) : '';

      document.getElementById('editar-idcita').value = id;
      document.getElementById('editar-fecha').value = fecha;
      document.getElementById('editar-hora').value = hora;
      document.getElementById('editar-estado').value = estadoActual; // Establece el valor del select
      document.getElementById('modal-editar').classList.remove('hidden');
    }

    function cerrarModalEditar() {
      document.getElementById('modal-editar').classList.add('hidden');
    }

    // --- SUBMIT MODAL EDITAR (Actualizado) ---
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('form-editar-cita').addEventListener('submit', async function(e) {
        e.preventDefault();
        const id = document.getElementById('editar-idcita').value;
        const fecha = document.getElementById('editar-fecha').value;
        const hora = document.getElementById('editar-hora').value;
        const estado = document.getElementById('editar-estado').value; // Obtiene el nuevo estado

        if (!fecha || !hora || !estado) {
          mostrarNotificacion('Debes seleccionar fecha, hora y estado.', true);
          return;
        }

        const fechaHora = `${fecha} ${hora}:00`;
        const body = {
          fecha: fechaHora,
          estado: estado // Nuevo campo en el cuerpo de la solicitud
        };

        try {
          const response = await fetch(`/proyectofinalMW/citas?id=${id}`, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(body)
          });
          const result = await response.json();
          if (response.ok) {
            mostrarNotificacion('Cita actualizada con éxito.');
            cerrarModalEditar();
            setTimeout(() => location.reload(), 500); // Recargar después de cerrar el modal
          } else {
            mostrarNotificacion('Error al actualizar cita: ' + (result.error || JSON.stringify(result)), true);
          }
        } catch (err) {
          mostrarNotificacion('Error de red al actualizar cita: ' + err.message, true);
        }
      });
    });

    // --- MODAL ELIMINAR (Reemplaza confirm()) ---
    function abrirModalEliminar(id) {
      document.getElementById('eliminar-id-cita').value = id;
      document.getElementById('eliminar-id-text').textContent = id;
      document.getElementById('modal-eliminar').classList.remove('hidden');
    }

    function cerrarModalEliminar() {
      document.getElementById('modal-eliminar').classList.add('hidden');
    }

    // --- ELIMINAR CITA (Ejecución) ---
    async function ejecutarEliminacion() {
      const id = document.getElementById('eliminar-id-cita').value;
      cerrarModalEliminar();

      try {
        const response = await fetch(`/proyectofinalMW/citas?id=${id}`, {
          method: 'DELETE'
        });
        const result = await response.json();
        if (response.ok) {
          mostrarNotificacion('Cita eliminada con éxito.');
          setTimeout(() => location.reload(), 500);
        } else {
          mostrarNotificacion('Error al eliminar cita: ' + (result.error || JSON.stringify(result)), true);
        }
      } catch (err) {
        mostrarNotificacion('Error de red al eliminar cita: ' + err.message, true);
      }
    }
  </script>
</body>

</html>
