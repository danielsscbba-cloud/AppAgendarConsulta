<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Stitch Design</title>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
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
<style>
    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200">
<div class="flex flex-col min-h-screen">
<header class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
<div class="flex items-center gap-3">
<div class="w-8 h-8 text-primary">
<svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
<path clip-rule="evenodd" d="M24 4H42V17.3333V30.6667H24V44H6V30.6667V17.3333H24V4Z" fill="currentColor" fill-rule="evenodd"></path>
</svg>
</div>
<h1 class="text-xl font-bold text-gray-900 dark:text-white">Viviendo en resiliencia</h1>
</div>
</header>
<main class="flex flex-1 items-center justify-center p-4">
<div class="w-full max-w-md space-y-8">
<div class="text-center">
<h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Iniciar sesión</h2>
<p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Bienvenido! accede a tu cuenta.</p>
</div>
<?php
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';
    // Llamar al endpoint de login usando cURL
    $url = 'http://localhost/proyectofinalMW/admin/login';
    $data = ['correo' => $correo, 'password' => $password];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $result = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($result, true);
    if (is_array($response) && empty($response['error'])) {
        header('Location: paginaTablaCitas.php');
        exit;
    } else {
        $error = $response['error'] ?? 'Credenciales incorrectas';
    }
}
?>
<form class="mt-8 space-y-6" method="POST" autocomplete="off">
  <input name="remember" type="hidden" value="true"/>
  <div class="rounded-lg shadow-sm -space-y-px">
    <div>
      <label class="sr-only" for="correo">Correo</label>
      <input class="form-input appearance-none rounded-none relative block w-full px-3 py-4 border border-gray-300 dark:border-gray-700 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white bg-background-light dark:bg-background-dark rounded-t-lg focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" id="correo" name="correo" placeholder="Usuario o Correo electrónico"/>
    </div>
    <div>
      <label class="sr-only" for="password">Contraseña</label>
      <input autocomplete="current-password" class="form-input appearance-none rounded-none relative block w-full px-3 py-4 border border-gray-300 dark:border-gray-700 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white bg-background-light dark:bg-background-dark rounded-b-lg focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" id="password" name="password" placeholder="Contraseña" required type="password"/>
    </div>
  </div>
  <?php if (!empty($error)): ?>
    <div class="text-red-600 text-center font-medium py-2"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <div>
    <button class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary" type="submit">
      Iniciar sesión
    </button>
  </div>
</form>
</div>
</main>
</div>

</body></html>