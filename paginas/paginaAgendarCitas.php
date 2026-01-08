<?php
// ==============================================================================
// Lógica PHP para generar el calendario
// ==============================================================================

// URL de la API de horarios (DEBE SER LA URL REAL DE TU ENDPOINT)
$HORARIO_API_URL = 'http://localhost/proyectofinalMW/horarios';

// Configuración de fecha actual
$monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

$today = new DateTime();
// Establecer la zona horaria a la de México, por ejemplo, para evitar desfases.
// Si no se define una zona horaria, PHP usará la configuración predeterminada del servidor.
// $today->setTimezone(new DateTimeZone('America/Mexico_City')); 

$currentYear = $today->format('Y');
$currentMonthNum = $today->format('n'); // 1-12 sin ceros iniciales
$currentDayNum = $today->format('j'); // Día del mes sin ceros iniciales

// Crear objeto de fecha para el primer día del mes
$date = new DateTime("$currentYear-$currentMonthNum-01");
$monthName = $monthNames[$currentMonthNum - 1];
$lastDay = $date->format('t'); // Número de días en el mes
$startDayOfWeek = $date->format('w'); // 0 (Domingo) a 6 (Sábado)

// Definir la fecha que se cargará por defecto al iniciar la página
$initialSelectedDate = $today->format('Y-m-d');

// Obtener días laborables para el mes actual (1=Lunes, ..., 7=Domingo) desde el endpoint
$laborables = [];
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $HORARIO_API_URL);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($curl);
curl_close($curl);
$horariosTodos = json_decode($response, true);
if (is_array($horariosTodos)) {
    foreach ($horariosTodos as $h) {
        if (!empty($h['es_laborable']) && $h['es_laborable']) {
            $laborables[(int)$h['dia_semana_num']] = true;
        }
    }
}

// Obtener usuarios desde el endpoint para el combo
$usuarios = [];
$curlUsuarios = curl_init();
curl_setopt($curlUsuarios, CURLOPT_URL, 'http://localhost/proyectofinalMW/usuarios');
curl_setopt($curlUsuarios, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curlUsuarios, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curlUsuarios, CURLOPT_SSL_VERIFYHOST, false);
$responseUsuarios = curl_exec($curlUsuarios);
curl_close($curlUsuarios);
$usuarios = json_decode($responseUsuarios, true);
if (!is_array($usuarios)) $usuarios = [];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&amp;display=swap" rel="stylesheet" />
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

<body class="bg-background-light dark:bg-background-dark font-display">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white dark:bg-background-dark p-6 flex flex-col justify-between border-r border-background-light dark:border-background-dark shadow-lg">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-8">Admin Panel</h1>
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
            </div>
            <div class="mt-8">
                <button onclick="window.location.href='login.php'" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-800 font-medium transition-colors">
                    <span class="material-symbols-outlined">logout</span>
                    Cerrar sesión
                </button>
            </div>
        </aside>
        <!-- Contenido principal -->
        <main class="flex-1 flex flex-col items-center p-6">
            <!-- Header -->
            <header class="w-full flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Agendar Cita</h1>
            </header>

            <!-- Formulario de Tipo de Sesión y Usuario -->
            <section class="bg-white dark:bg-background-dark shadow-md rounded-2xl p-6 w-full max-w-md mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">1. Selecciona el Tipo de Sesión y Usuario</h3>
                <div class="mb-4">
                    <label for="tipo-sesion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Sesión</label>
                    <select id="tipo-sesion" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-primary focus:border-primary dark:bg-background-dark dark:text-white">
                        <option value="Individual">Individual</option>
                        <option value="Familiar">Familiar</option>
                        <option value="De pareja">De pareja</option>
                        <option value="De fertilidad">De fertilidad</option>
                        <option value="Infantil">Infantil</option>
                    </select>
                </div>
                <div>
                    <label for="usuario-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Usuario</label>
                    <select id="usuario-select" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-primary focus:border-primary dark:bg-background-dark dark:text-white" required>
                        <option value="">Selecciona un usuario</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?= htmlspecialchars($u['idusuario']) ?>"><?= htmlspecialchars($u['nombre']) ?> (<?= htmlspecialchars($u['correo_electronico']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </section>

            <!-- Calendario (Generado con PHP) -->
            <section class="bg-white dark:bg-background-dark shadow-md rounded-2xl p-6 w-full max-w-md mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">2. Selecciona la Fecha</h3>
                <div class="flex justify-center items-center mb-4">
                    <!-- Se eliminan los botones de cambiar mes para simplificar la implementación en PHP puro -->
                    <h2 id="current-month-year" class="text-lg font-semibold text-gray-800 dark:text-white"><?php echo "$monthName $currentYear"; ?></h2>
                </div>
                <!-- Días de la semana -->
                <div class="grid grid-cols-7 text-center text-sm font-medium text-gray-500 mb-2">
                    <span>D</span><span>L</span><span>M</span><span>M</span><span>J</span><span>V</span><span>S</span>
                </div>
                <!-- Días del mes (Generado con PHP) -->
                <div id="calendar-grid" class="grid grid-cols-7 gap-2 text-center">
                    <?php
                    // Rellenar con días vacíos hasta el primer día de la semana
                    for ($i = 0; $i < $startDayOfWeek; $i++) {
                        echo '<div></div>';
                    }
                    // Generar los días del mes
                    for ($day = 1; $day <= $lastDay; $day++) {
                        $fullDate = $currentYear . '-' . str_pad($currentMonthNum, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                        $isToday = ($day == $currentDayNum);
                        $dateObj = new DateTime($fullDate);
                        $diaSemanaNum = (int)$dateObj->format('N'); // 1=Lunes, ..., 7=Domingo
                        $esLaborable = isset($laborables[$diaSemanaNum]);
                        $classes = 'p-2 rounded-full transition calendar-day';
                        if ($isToday) {
                            $classes .= ' bg-primary text-white font-semibold shadow-md';
                        } else {
                            $classes .= ' text-gray-700 dark:text-gray-200 hover:bg-primary/10';
                        }
                        if (!$esLaborable) {
                            $classes .= ' opacity-40 cursor-not-allowed';
                        } else {
                            $classes .= ' cursor-pointer';
                        }
                        $onclick = $esLaborable ? "onclick=\"seleccionarDia('$fullDate', this)\"" : '';
                        echo "<div class='$classes' data-date='$fullDate' $onclick>$day</div>";
                    }
                    ?>
                </div>
            </section>

            <!-- Horarios -->
            <section class="bg-white dark:bg-background-dark shadow-md rounded-2xl p-6 w-full max-w-md mb-8">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">3. Horarios Disponibles para: <span id="fecha-seleccionada-display" class="text-primary font-bold"></span></h3>
                <div id="fecha-seleccionada-oculta" style="display:none;"></div>
                <div id="horarios-list">
                    <!-- Los horarios se cargarán aquí dinámicamente -->
                    <div class="text-center py-4 text-gray-500 dark:text-gray-400">Selecciona una fecha en el calendario.</div>
                </div>
            </section>

            <!-- Botón Agendar Cita -->
            <button id="continuar-btn" class="w-full max-w-md bg-primary text-white text-lg font-semibold py-3 rounded-xl hover:bg-primary/90 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                Agendar Cita
            </button>
        </main>
    </div>

    <script>
        // URL de la API de horarios definida en PHP
        const HORARIO_API_URL = '<?php echo $HORARIO_API_URL; ?>';

        // Variables de estado
        let selectedHora = null;
        let selectedDayElement = document.querySelector('.calendar-day.bg-primary'); // Elemento día seleccionado inicialmente

        // Fecha seleccionada inicialmente por PHP (el día de hoy)
        const initialSelectedDate = '<?php echo $initialSelectedDate; ?>';


        // ==============================================================================
        // LÓGICA DE INTERACCIÓN Y FETCH (se mantiene en JS para la dinámica)
        // ==============================================================================

        /**
         * Maneja el clic en una fecha, la selecciona visualmente y obtiene los horarios.
         * @param {string} fecha - La fecha seleccionada en formato YYYY-MM-DD.
         * @param {HTMLElement} element - El elemento DIV del día que fue presionado (o null si es llamada inicial).
         */
        async function seleccionarDia(fecha, element = null) {
            // 1. Limpiar selección previa (solo si no es la carga inicial y se pasa el elemento)
            if (selectedDayElement) {
                selectedDayElement.classList.remove('bg-primary', 'text-white', 'font-semibold', 'shadow-md');
                selectedDayElement.classList.add('text-gray-700', 'dark:text-gray-200', 'hover:bg-primary/10');
            }

            // 2. Aplicar estilos a la fecha actual
            const clickedElement = element || document.querySelector(`[data-date="${fecha}"]`);
            if (clickedElement) {
                clickedElement.classList.add('bg-primary', 'text-white', 'font-semibold', 'shadow-md');
                clickedElement.classList.remove('text-gray-700', 'dark:text-gray-200', 'hover:bg-primary/10');
                selectedDayElement = clickedElement; // Guardar la referencia del nuevo elemento seleccionado
            }

            // 3. Mostrar indicador de carga y hacer el Fetch
            const horariosContainer = document.getElementById('horarios-list');
            horariosContainer.innerHTML = '<div class="text-center py-4 text-gray-500 dark:text-gray-400">Cargando horarios...</div>';
            selectedHora = null;
            document.getElementById('continuar-btn').disabled = true;

            try {
                const dateObj = new Date(fecha);
                let diaSemanaNum = dateObj.getDay()+1;
                diaSemanaNum = diaSemanaNum === 0 ? 7 : diaSemanaNum;
                const apiUrl = `/proyectofinalMW/horarios/dia?dia=${diaSemanaNum}`;
                const response = await fetch(apiUrl);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                let horarios = [];
                if (Array.isArray(data) && data.length > 0) {
                    const h = data[0];
                    if (h.es_laborable && h.hora_inicio && h.hora_fin) {
                        const inicio = padHora(h.hora_inicio);
                        const fin = padHora(h.hora_fin);
                        const inicioDescanso = padHora(h.inicio_descanso);
                        const finDescanso = padHora(h.fin_descanso);
                        let slots = [];
                        let current = inicio;
                        while (current < fin) {
                            let [h1, m1] = current.split(":").map(Number);
                            let next = new Date(0,0,0,h1,m1);
                            next.setHours(next.getHours() + 1);
                            let nextStr = next.toTimeString().slice(0,5);
                            if (nextStr > fin) break;
                            let enDescanso = false;
                            if (inicioDescanso && finDescanso) {
                                if (current < finDescanso && nextStr > inicioDescanso) {
                                    enDescanso = true;
                                }
                            }
                            if (!enDescanso) {
                                slots.push(`${current} - ${nextStr}`);
                            }
                            current = nextStr;
                        }
                        horarios = slots;
                    }
                }
                mostrarHorarios(horarios, fecha);
            } catch (error) {
                console.error("Error al cargar horarios:", error);
                mostrarHorarios([], fecha, `Error al conectar con el API: ${error.message}`);
            }
        }

        function padHora(hora) {
            // Recibe '08:00:00' y devuelve '08:00'
            return hora ? hora.slice(0,5) : '';
        }

        /**
         * Formatea una fecha YYYY-MM-DD a 'jueves 11 de enero'
         */
        function formatearFechaCompleta(fecha) {
            const dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
            const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
            const partes = fecha.split('-');
            const anio = parseInt(partes[0], 10);
            const mes = parseInt(partes[1], 10) - 1;
            const dia = parseInt(partes[2], 10);
            const dateObj = new Date(anio, mes, dia);
            const diaSemana = dias[dateObj.getDay()];
            return `${diaSemana} ${dia} de ${meses[mes]}`;
        }

        // Sobrescribe mostrarHorarios para mostrar la fecha formateada
        function mostrarHorarios(horarios, fecha, errorMessage = null) {
            const horariosContainer = document.getElementById('horarios-list');
            horariosContainer.innerHTML = '';
            document.getElementById('fecha-seleccionada-display').textContent = formatearFechaCompleta(fecha);
            document.getElementById('fecha-seleccionada-oculta').textContent = fecha;

            if (errorMessage) {
                horariosContainer.innerHTML = `<div class='text-red-500 font-medium py-4 text-center'>${errorMessage}</div>`;
                return;
            }

            if (horarios.length === 0) {
                horariosContainer.innerHTML = '<div class="text-gray-500 dark:text-gray-400 py-4 text-center">No hay horarios disponibles para esta fecha.</div>';
                return;
            }

            horarios.forEach(horaCompleta => {
                let hora = horaCompleta.replace(' (Ocupado)', '').trim();
                const isUnavailable = horaCompleta.includes('(Ocupado)');
                const buttonText = isUnavailable ? 'Ocupado' : 'Seleccionar';
                const buttonClasses = isUnavailable ?
                    'bg-gray-200 dark:bg-gray-700 text-gray-500 cursor-not-allowed border-gray-300 dark:border-gray-600' :
                    'border-primary text-primary hover:bg-primary hover:text-white hover:shadow-lg active:scale-95';

                const html = `
                <div class='flex justify-between items-center py-3 border-b dark:border-gray-800 last:border-none'>
                    <span class='text-gray-800 dark:text-gray-200 text-lg font-medium'>${hora}</span>
                    <button 
                        type="button"
                        class='px-5 py-1.5 rounded-full border text-sm font-medium transition horario-btn ${buttonClasses}'
                        data-hora="${hora}"
                        ${isUnavailable ? 'disabled' : `onclick="seleccionarHora('${hora}', this)"`}>
                        ${buttonText}
                    </button>
                </div>
            `;
                horariosContainer.innerHTML += html;
            });
        }

        /**
         * Maneja la selección visual de una hora específica.
         */
        function seleccionarHora(hora, element) {
            // Limpiar selección previa
            document.querySelectorAll('.horario-btn:not([disabled])').forEach(btn => {
                btn.classList.remove('bg-primary', 'text-white', 'shadow-lg');
                btn.classList.add('border-primary', 'text-primary', 'hover:bg-primary', 'hover:text-white');
                btn.textContent = 'Seleccionar';
            });

            // Aplicar estilos a la hora actual
            element.classList.add('bg-primary', 'text-white', 'shadow-lg');
            element.classList.remove('border-primary', 'text-primary', 'hover:bg-primary', 'hover:text-white');
            element.textContent = 'Seleccionado';

            selectedHora = hora;
            document.getElementById('continuar-btn').disabled = false;
            console.log("Hora seleccionada:", selectedHora);
        }

        // ==============================================================================
        // INICIALIZACIÓN
        // ==============================================================================

        document.addEventListener('DOMContentLoaded', () => {
            // Seleccionar el día inicial (el día de hoy) y cargar sus horarios.
            if (initialSelectedDate) {
                seleccionarDia(initialSelectedDate);
            }

            // Lógica para el botón Agendar Cita
            document.getElementById('continuar-btn').addEventListener('click', async () => {
                if (selectedHora) {
                    const tipoSesion = document.getElementById('tipo-sesion').value;
                    const fechaSeleccionada = document.getElementById('fecha-seleccionada-oculta').textContent;
                    const idusuario = document.getElementById('usuario-select').value;
                    if (!idusuario) {
                        alert('Debes seleccionar un usuario.');
                        return;
                    }
                    // Obtener la hora seleccionada y construir el datetime
                    // selectedHora es del tipo '08:00 - 09:00', tomamos la hora inicial
                    const horaInicio = selectedHora.split(' - ')[0];
                    // Unir fecha y hora en formato 'YYYY-MM-DD HH:MM:00'
                    const fechaHora = `${fechaSeleccionada} ${horaInicio}:00`;
                    // Construir el body para la API
                    const body = {
                        idusuario: idusuario,
                        fecha: fechaHora,
                        tipo_sesion: tipoSesion
                    };
                    try {
                        const response = await fetch('/proyectofinalMW/citas', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(body)
                        });
                        const result = await response.json();
                        if (response.ok) {
                            alert('Cita agendada con éxito.');
                        } else {
                            alert('Error al agendar cita: ' + (result.error || result));
                        }
                    } catch (err) {
                        alert('Error de red al agendar cita: ' + err.message);
                    }
                }
            });
        });
    </script>
</body>

</html>