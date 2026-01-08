<?php
// ==============================================================================
// CONFIGURACIÓN DE LA API
// ------------------------------------------------------------------------------
// !!! CAMBIA 'tu_proyecto' por el nombre de la carpeta de tu API en XAMPP !!!
$api_base_url = 'http://localhost/proyectofinalMW/usuarios';

$message = '';
$edit_user = null;
$users = [];

// ==============================================================================
// FUNCIÓN PARA REALIZAR PETICIONES A LA API (CRUD)
// ==============================================================================
function apiRequest($url, $method, $data = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Para entornos de desarrollo (XAMPP), se puede deshabilitar la verificación SSL
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    switch (strtoupper($method)) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            break;
        case 'PUT':
            // En tu router PHP, si usas file_get_contents para leer PUT/POST, funciona igual.
            // Asumiendo que tu router espera el ID en el body o en el query string.
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['error' => 'Error de cURL: ' . $error];
    }
    
    // Si la respuesta es JSON, la decodificamos; si es solo texto, la devolvemos.
    $decoded_response = json_decode($response, true);
    return $decoded_response === null ? ['message' => $response, 'status' => $http_code] : $decoded_response;
}

// ==============================================================================
// MANEJO DE LÓGICA CRUD
// ==============================================================================

// 1. Manejo de POST (Crear/Actualizar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si hay un ID oculto, es una actualización
    if (!empty($_POST['idusuario'])) {
        $id = $_POST['idusuario'];
        $update_data = [
            'idusuario' => $id,
            'nombre' => $_POST['nombre'],
            'correo_electronico' => $_POST['correo_electronico'],
            'password' => $_POST['password']
        ];
        // Nota: Adaptamos la URL para pasar el ID al API para la actualización
        $response = apiRequest($api_base_url . "?id=$id", 'PUT', $update_data);
        $message = "Usuario con ID $id actualizado.";
    } else {
        // Si no hay ID, es una creación
        $new_user_data = [
            'nombre' => $_POST['nombre'],
            'correo_electronico' => $_POST['correo_electronico'],
            'password' => $_POST['password']
        ];
        $response = apiRequest($api_base_url, 'POST', $new_user_data);
        $message = "Usuario creado con éxito.";
    }
}

// 2. Manejo de GET para acciones (Editar/Eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = $_GET['id'] ?? null;

    if ($id) {
        if ($action === 'delete') {
            // Eliminar usuario
            $response = apiRequest($api_base_url . "?id=$id", 'DELETE');
            $message = "Usuario con ID $id eliminado.";
        } elseif ($action === 'edit') {
            // Obtener el usuario específico para cargar el formulario
            $users_temp = apiRequest($api_base_url, 'GET');
            if (is_array($users_temp)) {
                foreach ($users_temp as $user) {
                    if ($user['idusuario'] == $id) {
                        $edit_user = $user;
                        break;
                    }
                }
            }
        }
    }
}

// 3. Obtener todos los usuarios para mostrar la tabla (Se ejecuta siempre al final)
$users_data = apiRequest($api_base_url, 'GET');
if (isset($users_data['error'])) {
    $message = "Error al conectar con la API: " . $users_data['error'];
} elseif (is_array($users_data)) {
    $users = $users_data;
} else {
    // Si la respuesta no es un array (e.g., error 404), la tratamos como un error.
    $message = "Error en la respuesta de la API. Código HTTP: " . ($users_data['status'] ?? 'Desconocido');
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Admin Panel - Gestión de Usuarios</title>
<!-- Carga de Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
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
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
</head>
<body class="bg-background-light dark:bg-background-dark font-display">
<div class="flex min-h-screen">
    <!-- Barra Lateral -->
    <aside class="w-64 bg-white dark:bg-background-dark p-6 flex flex-col justify-between border-r border-background-light dark:border-background-dark shadow-lg">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-8">Admin Panel</h1>
           <nav class="flex-1 px-4 py-2 space-y-2">
        <a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary dark:bg-primary/20" href="paginaUsuarios.php">
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
        <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800" href="paginaHorario.php">
          <svg fill="currentColor" height="24" viewBox="0 0 256 256" width="24" xmlns="http://www.w3.org/2000/svg">
            <path d="M208,32H184V24a8,8,0,0,0-16,0v8H88V24a8,8,0,0,0-16,0v8H48A16,16,0,0,0,32,48V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V48A16,16,0,0,0,208,32ZM112,184a8,8,0,0,1-16,0V132.94l-4.42,2.22a8,8,0,0,1-7.16-14.32l16-8A8,8,0,0,1,112,120Zm56-8a8,8,0,0,1,0,16H136a8,8,0,0,1-6.4-12.8l28.78-38.37A8,8,0,1,0,145.07,132a8,8,0,1,1-13.85-8A24,24,0,0,1,176,136a23.76,23.76,0,0,1-4.84,14.45L152,176ZM48,80V48H72v8a8,8,0,0,0,16,0V48h80v8a8,8,0,0,0,16,0V48h24V80Z"></path>
          </svg>
          <span class="text-sm font-medium">Horario</span>
        </a>
            </nav>
            
        </div>
        <div class="mt-8">
                <button onclick="window.location.href='login.php'" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-800 font-medium transition-colors">
                    <span class="material-symbols-outlined">logout</span>
                    Cerrar sesión
                </button>
            </div>
    </aside>
    
    <!-- Contenido Principal -->
    <main class="flex-1 p-8">
        <header class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white">Gestión de Usuarios</h2>
        </header>

        <!-- Mensajes de estado -->
        <?php if ($message): ?>
            <div class="p-4 mb-6 rounded-lg <?php echo strpos($message, 'Error') !== false ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'; ?> transition-opacity duration-300">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de Creación / Edición -->
        <div id="user-form-section" class="bg-white dark:bg-background-dark rounded-xl shadow-xl p-6 mb-8 border border-gray-100 dark:border-gray-800">
            <h3 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">
                <?php echo $edit_user ? 'Editar Usuario ID: ' . htmlspecialchars($edit_user['idusuario']) : 'Añadir Nuevo Usuario'; ?>
            </h3>
            <form action="paginaUsuarios.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php if ($edit_user): ?>
                    <input type="hidden" name="idusuario" value="<?php echo htmlspecialchars($edit_user['idusuario']); ?>">
                <?php endif; ?>

                <!-- Campo Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($edit_user['nombre'] ?? ''); ?>" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-primary focus:border-primary dark:bg-background-dark dark:text-white">
                </div>
                
                <!-- Campo Email -->
                <div>
                    <label for="correo_electronico" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Correo Electrónico</label>
                    <input type="email" id="correo_electronico" name="correo_electronico" value="<?php echo htmlspecialchars($edit_user['correo_electronico'] ?? ''); ?>" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-primary focus:border-primary dark:bg-background-dark dark:text-white">
                </div>
                
                <!-- Campo Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contraseña (Necesaria para Crear/Actualizar)</label>
                    <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($edit_user['password'] ?? ''); ?>" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-primary focus:border-primary dark:bg-background-dark dark:text-white">
                </div>
                
                <!-- Botones de Acción -->
                <div class="md:col-span-3 flex justify-end gap-3 mt-4">
                    <?php if ($edit_user): ?>
                        <a href="paginaUsuarios.php" class="flex items-center gap-2 bg-gray-500 text-white px-5 py-2 rounded-lg hover:bg-gray-600 transition-colors shadow-md">
                            <span class="material-symbols-outlined text-base">close</span>
                            Cancelar Edición
                        </a>
                    <?php endif; ?>
                    <button type="submit" class="flex items-center gap-2 bg-primary text-white px-5 py-2 rounded-lg hover:bg-primary/90 transition-colors shadow-md">
                        <span class="material-symbols-outlined text-base"><?php echo $edit_user ? 'save' : 'add'; ?></span>
                        <?php echo $edit_user ? 'Guardar Cambios' : 'Crear Usuario'; ?>
                    </button>
                </div>
            </form>
        </div>


        <!-- Sección de Listado de Usuarios -->
        <div class="bg-white dark:bg-background-dark rounded-lg shadow-xl overflow-hidden border border-gray-100 dark:border-gray-800">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3" scope="col">ID</th>
                            <th class="px-6 py-3" scope="col">Nombre</th>
                            <th class="px-6 py-3" scope="col">Email</th>
                            <th class="px-6 py-3" scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr class="bg-white dark:bg-background-dark">
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No hay usuarios registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr class="bg-white dark:bg-background-dark border-b dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                        <?php echo htmlspecialchars($user['idusuario'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                        <?php echo htmlspecialchars($user['nombre'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php echo htmlspecialchars($user['correo_electronico'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-3">
                                        <a href="paginaUsuarios.php?action=edit&id=<?php echo htmlspecialchars($user['idusuario']); ?>" class="font-medium text-green-600 dark:text-green-400 hover:underline transition-colors">
                                            <span class="material-symbols-outlined text-sm align-middle mr-1">edit</span>
                                            Editar
                                        </a>
                                        <a href="paginaUsuarios.php?action=delete&id=<?php echo htmlspecialchars($user['idusuario']); ?>" onclick="return confirm('¿Confirmas la eliminación del usuario con ID: <?php echo htmlspecialchars($user['idusuario']); ?>?')" class="font-medium text-red-600 dark:text-red-400 hover:underline transition-colors">
                                            <span class="material-symbols-outlined text-sm align-middle mr-1">delete</span>
                                            Eliminar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
