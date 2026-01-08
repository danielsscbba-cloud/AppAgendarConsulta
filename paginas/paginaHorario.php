<?php
// Incluye las funciones de conexión API, asumiendo que ya tienes este código
// require_once 'api_functions.php';


// Función real para consumir la API REST de horarios
function apiRequest($method, $url, $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    switch (strtoupper($method)) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            break;
        case 'PUT':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
    }

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) {
        return ['error' => 'Error de cURL: ' . $error];
    }
    $decoded = json_decode($response, true);
    return $decoded === null ? [] : $decoded;
}

// URL base de la API
$api_base_url = 'http://localhost/proyectofinalMW/horarios';


$edit_horario = null;

// Crear o actualizar horario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $data = [
        'dia_semana_num' => $_POST['dia_semana_num'] ?? '',
        'es_laborable' => isset($_POST['es_laborable']) ? 1 : 0,
        'hora_inicio' => $_POST['hora_inicio'] ?? '',
        'hora_fin' => $_POST['hora_fin'] ?? '',
        'inicio_descanso' => $_POST['inicio_descanso'] ?? '',
        'fin_descanso' => $_POST['fin_descanso'] ?? ''
    ];
    if ($id) {
        apiRequest('PUT', $api_base_url . '?id=' . $id, $data);
    } else {
        apiRequest('POST', $api_base_url, $data);
    }
    header('Location: paginaHorario.php');
    exit;
}

// Eliminar horario
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    apiRequest('DELETE', $api_base_url . '?id=' . $_GET['id']);
    header('Location: paginaHorario.php');
    exit;
}

// Obtener todos los horarios
$horarios = apiRequest('GET', $api_base_url);

// Editar horario
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    foreach ($horarios as $h) {
        if ($h['id'] == $_GET['id']) {
            $edit_horario = $h;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Horarios</title>
    <!-- Incluimos Tailwind CSS para el diseño -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Estilos personalizados para el contenedor principal y el fondo */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f9; /* Fondo muy claro para contrastar con la barra */
        }
        /* Estilo para que la barra lateral y el contenido principal se repartan el espacio */
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
    </style>
    <script>
        // Configuración de Tailwind para los colores del diseño Stitch
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary": "#13a4ec",
                        "background-light": "#f4f7f9", /* Usando un color más claro */
                    },
                    borderRadius: {
                        "lg": "0.5rem",
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
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
</head>


<body>

<!-- INICIO: Estructura de Admin Layout (Sidebar y Contenido) -->
<div class="admin-layout">

    <!-- BARRA LATERAL (Simulación de tu menú) -->
    <aside class="w-64 bg-white border-r border-gray-200 p-4 flex flex-col gap-6 shadow-xl">
        <h1 class="text-xl font-bold text-primary">Admin Panel</h1>
        <nav class="flex-1 px-4 py-2 space-y-2">
        <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800" href="paginaUsuarios.php">
          <svg fill="currentColor" height="24" viewBox="0 0 256 256" width="24" xmlns="http://www.w3.org/2000/svg">
            <path d="M117.25,157.92a60,60,0,1,0-66.5,0A95.83,95.83,0,0,0,3.53,195.63a8,8,0,1,0,13.4,8.74,80,80,0,0,1,134.14,0,8,8,0,0,0,13.4-8.74A95.83,95.83,0,0,0,117.25,157.92ZM40,108a44,44,0,1,1,44,44A44.05,44.05,0,0,1,40,108Zm210.14,98.7a8,8,0,0,1-11.07-2.33A79.83,79.83,0,0,0,172,168a8,8,0,0,1,0-16,44,44,0,1,0-16.34-84.87,8,8,0,1,1-5.94-14.85,60,60,0,0,1,55.53,105.64,95.83,95.83,0,0,1,47.22,37.71A8,8,0,0,1,250.14,206.7Z"></path>
          </svg>
          <span class="text-sm font-medium">Usuarios</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800" href="paginaTablaCitas.php">
          <svg fill="currentColor" height="24" viewBox="0 0 256 256" width="24" xmlns="http://www.w3.org/2000/svg">
            <path d="M208,32H184V24a8,8,0,0,0-16,0v8H88V24a8,8,0,0,0-16,0v8H48A16,16,0,0,0,32,48V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V48A16,16,0,0,0,208,32ZM112,184a8,8,0,0,1-16,0V132.94l-4.42,2.22a8,8,0,0,1-7.16-14.32l16-8A8,8,0,0,1,112,120Zm56-8a8,8,0,0,1,0,16H136a8,8,0,0,1-6.4-12.8l28.78-38.37A8,8,0,1,0,145.07,132a8,8,0,1,1-13.85-8A24,24,0,0,1,176,136a23.76,23.76,0,0,1-4.84,14.45L152,176ZM48,80V48H72v8a8,8,0,0,0,16,0V48h80v8a8,8,0,0,0,16,0V48h24V80Z"></path>
          </svg>
          <span class="text-sm font-medium">Citas</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary dark:bg-primary/20" href="paginaHorario.php">
          <svg fill="currentColor" height="24" viewBox="0 0 256 256" width="24" xmlns="http://www.w3.org/2000/svg">
            <path d="M208,32H184V24a8,8,0,0,0-16,0v8H88V24a8,8,0,0,0-16,0v8H48A16,16,0,0,0,32,48V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V48A16,16,0,0,0,208,32ZM112,184a8,8,0,0,1-16,0V132.94l-4.42,2.22a8,8,0,0,1-7.16-14.32l16-8A8,8,0,0,1,112,120Zm56-8a8,8,0,0,1,0,16H136a8,8,0,0,1-6.4-12.8l28.78-38.37A8,8,0,1,0,145.07,132a8,8,0,1,1-13.85-8A24,24,0,0,1,176,136a23.76,23.76,0,0,1-4.84,14.45L152,176ZM48,80V48H72v8a8,8,0,0,0,16,0V48h80v8a8,8,0,0,0,16,0V48h24V80Z"></path>
          </svg>
          <span class="text-sm font-medium">Horario</span>
        </a>
      </nav>
      <div class="mt-8">
        <button onclick="window.location.href='login.php'" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-800 font-medium transition-colors">
          <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1, 'wght' 700, 'GRAD' 0, 'opsz' 24;">logout</span>
          Cerrar sesión
        </button>
      </div>
    </aside>

    <!-- CONTENIDO PRINCIPAL (Donde va tu página de horarios) -->
    <main class="flex-1 p-8 bg-background-light">
        <div class="max-w-7xl mx-auto">
            <!-- Título de la Sección -->
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Gestión de Horarios</h1>

         <!-- CONTENEDOR PRINCIPAL: Formulario y Tabla de Horarios -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                   
                <!-- Columna 1: Formulario de Añadir/Editar Horario -->
                <div class="lg:col-span-1 p-6 bg-white rounded-xl shadow-lg h-fit">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4"><?= $edit_horario ? 'Editar Horario' : 'Añadir Nuevo Horario' ?></h2>
                    
                    <form action="paginaHorario.php" method="POST" class="space-y-4">
                        <input type="hidden" name="id" value="<?= $edit_horario['id'] ?? '' ?>">
                        
                        <!-- Día de la Semana -->
                        <div class="flex flex-col">
                            <label for="dia_semana_num" class="text-sm font-medium text-gray-600 mb-1">Día de la Semana</label>
                            <select id="dia_semana_num" name="dia_semana_num" required class="border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary">
                                <option value="" disabled <?= empty($edit_horario['dia_semana_num']) ? 'selected' : '' ?>>Seleccione un día</option>
                                <?php
                                $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
                                $selected_num = $edit_horario['dia_semana_num'] ?? '';
                                foreach ($dias as $num => $nombre) {
                                    $selected = ($selected_num == $num) ? 'selected' : '';
                                    echo "<option value='$num' $selected>$nombre</option>";
                                }
                                ?>
                            </select>
                        </div>
                        

                        <!-- Horario de Inicio (Turno) -->
                        <div class="flex flex-col">
                            <label for="hora_inicio" class="text-sm font-medium text-gray-600 mb-1">Hora de Inicio (Turno)</label>
                            <input type="time" id="hora_inicio" name="hora_inicio" value="<?= $edit_horario['hora_inicio'] ?? '' ?>" required class="border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary">
                        </div>

                        <!-- Horario de Fin (Turno) -->
                        <div class="flex flex-col">
                            <label for="hora_fin" class="text-sm font-medium text-gray-600 mb-1">Hora de Fin (Turno)</label>
                            <input type="time" id="hora_fin" name="hora_fin" value="<?= $edit_horario['hora_fin'] ?? '' ?>" required class="border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary">
                        </div>

                        <!-- Inicio Descanso -->
                        <div class="flex flex-col">
                            <label for="inicio_descanso" class="text-sm font-medium text-gray-600 mb-1">Inicio Descanso</label>
                            <input type="time" id="inicio_descanso" name="inicio_descanso" value="<?= $edit_horario['inicio_descanso'] ?? '' ?>" class="border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary">
                        </div>

                        <!-- Fin Descanso -->
                        <div class="flex flex-col">
                            <label for="fin_descanso" class="text-sm font-medium text-gray-600 mb-1">Fin Descanso</label>
                            <input type="time" id="fin_descanso" name="fin_descanso" value="<?= $edit_horario['fin_descanso'] ?? '' ?>" class="border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary">
                        </div>

                        <!-- Es Laborable (checkbox) -->
                        <div class="flex flex-col">
                            <label class="inline-flex items-center mt-2">
                                <input type="checkbox" id="es_laborable" name="es_laborable" value="1" class="rounded text-primary focus:ring-primary" <?= (isset($edit_horario['es_laborable']) && $edit_horario['es_laborable']) ? 'checked' : '' ?>>
                                <span class="ml-2 text-sm text-gray-600">¿Día laborable?</span>
                            </label>
                        </div>

                        <!-- Botón de Guardar/Actualizar -->
                        <button type="submit" name="submit" class="w-full py-2 px-4 bg-primary text-white font-semibold rounded-lg shadow-md hover:bg-opacity-90 transition duration-150">
                            <?= $edit_horario ? 'Actualizar Horario' : 'Guardar Horario' ?>
                        </button>

                        <?php if ($edit_horario): ?>
                            <!-- Botón de Cancelar Edición -->
                            <a href="paginaHorario.php" class="block text-center text-sm text-gray-600 hover:text-primary mt-2">Cancelar Edición</a>
                        <?php endif; ?>

                    </form>
                </div>

                <!-- Columna 2 & 3: Tabla de Horarios y Disponibilidad -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Sección de Tabla -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Horarios Configurados</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Día</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inicio</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fin</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descanso</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($horarios as $horario): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php
                                                $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
                                                $num = $horario['dia_semana_num'] ?? null;
                                                echo isset($dias[$num]) ? $dias[$num] : 'Desconocido';
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $horario['hora_inicio'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $horario['hora_fin'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php
                                                $inicio = $horario['inicio_descanso'] ?? '';
                                                $fin = $horario['fin_descanso'] ?? '';
                                                echo ($inicio && $fin) ? "$inicio - $fin" : 'No Aplica';
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?= isset($horario['es_laborable']) && $horario['es_laborable'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                <?= isset($horario['es_laborable']) && $horario['es_laborable'] ? 'Disponible' : 'No Disponible' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="?action=edit&id=<?= $horario['id'] ?>" class="text-primary hover:text-primary-600 transition duration-150 mr-3">Editar</a>
                                            <!-- En la vida real, este sería un formulario de DELETE -->
                                            <a href="?action=delete&id=<?= $horario['id'] ?>" class="text-red-600 hover:text-red-900 transition duration-150">Eliminar</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </main>
</div>
<!-- FIN: Estructura de Admin Layout -->

</body>
</html>
