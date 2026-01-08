<?php

require_once "Controladores/UsuarioControlador.php";
require_once "Controladores/HorarioControlador.php";
require_once "Controladores/CitasControlador.php";
require_once "Controladores/AdminControlador.php";
require_once "conexionDB.php";
$bd = new ConexionDB();
$usuarioControlador = new UsuarioControlador($bd);
$horarioControlador = new HorarioControlador($bd);
$citasControlador = new CitasControlador($bd);
$adminControlador = new AdminControlador($bd);

$metodo = $_SERVER["REQUEST_METHOD"];
$path=parse_url($_SERVER["REQUEST_URI"],PHP_URL_PATH);
$path=str_replace("/proyectofinalMW","",$path);

$id = $_GET['id'] ?? null;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

switch(true){
    // ----------------------------------------------------
    // GET ALL /usuarios
    // ----------------------------------------------------
    case $metodo == "GET" && $path == "/usuarios" && $id === null:
        $response = $usuarioControlador->ObtenerUsuarios();
        break;

    // ----------------------------------------------------
    // GET BY ID /usuarios?id=X
    // ----------------------------------------------------
    case $metodo == "GET" && $path == "/usuarios" && $id !== null:
        // Se asume que el controlador tiene un método para obtener por ID
        $response = $usuarioControlador->ObtenerUsuarioPorId($id);
        break;

    // ----------------------------------------------------
    // POST (Crear) /usuarios
    // ----------------------------------------------------
    case $metodo == "POST" && $path == "/usuarios":
        // Lee el JSON del cuerpo de la solicitud
        $datos = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $response = $usuarioControlador->CrearUsuario($datos);
        break;

    case $metodo == "POST" && $path == "/usuarios/login":
        $datos = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $correo = $datos['correo'] ?? '';
        $password = $datos['password'] ?? '';
        $response = $usuarioControlador->Login($correo, $password);
        break;
    // ----------------------------------------------------
    // PUT (Actualizar) /usuarios?id=X
    // ----------------------------------------------------
    case $metodo == "PUT" && $path == "/usuarios" && $id !== null:
        // Lee el JSON del cuerpo para obtener los datos de actualización
        $datos = json_decode(file_get_contents("php://input"), true);
        // Se asume que el controlador tiene un método ActualizarUsuario que requiere el ID y los datos
        $response = $usuarioControlador->ActualizarUsuario($id, $datos);
        break;

    // ----------------------------------------------------
    // DELETE (Eliminar) /usuarios?id=X
    // ----------------------------------------------------
    case $metodo == "DELETE" && $path == "/usuarios" && $id !== null:
        // Se asume que el controlador tiene un método EliminarUsuario que requiere el ID
        $response = $usuarioControlador->EliminarUsuario($id);
        break;

    // ----------------------------------------------------
    // GET ALL /horarios
    // ----------------------------------------------------
    case $metodo == "GET" && $path == "/horarios" && $id === null:
        $response = $horarioControlador->ObtenerHorarios();
        break;

    // ----------------------------------------------------
    // GET BY ID /horarios?id=X
    // ----------------------------------------------------
    case $metodo == "GET" && $path == "/horarios" && $id !== null:
        $response = $horarioControlador->ObtenerHorarioPorId($id);
        break;

    case $metodo == "GET" && $path == "/horarios/dia" && isset($_GET['dia']):
        $diaSemanaNum = $_GET['dia'];
        $response = $horarioControlador->ObtenerHorariosPorDia($diaSemanaNum);
        break;
    // ----------------------------------------------------
    // POST (Crear) /horarios
    // ----------------------------------------------------
    case $metodo == "POST" && $path == "/horarios":
        $datos = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $response = $horarioControlador->CrearHorario($datos);
        break;

    // ----------------------------------------------------
    // PUT (Actualizar) /horarios?id=X
    // ----------------------------------------------------
    case $metodo == "PUT" && $path == "/horarios" && $id !== null:
        $datos = json_decode(file_get_contents("php://input"), true);
        $response = $horarioControlador->ActualizarHorario($id, $datos);
        break;

    // ----------------------------------------------------
    // DELETE (Eliminar) /horarios?id=X
    // ----------------------------------------------------
    case $metodo == "DELETE" && $path == "/horarios" && $id !== null:
        $response = $horarioControlador->EliminarHorario($id);
        break;
    // citas
    // ----------------------------------------------------
    // GET ALL /citas
    // ----------------------------------------------------
    case $metodo == "GET" && $path == "/citas" && $id === null:
        $response = $citasControlador->ObtenerCitas();
        break;

    // ----------------------------------------------------
    // GET BY ID /citas?id=X
    // ----------------------------------------------------
    case $metodo == "GET" && $path == "/citas" && $id !== null:
        $response = $citasControlador->ObtenerCitaPorId($id);
        break;

    // ----------------------------------------------------
    // GET BY IDUsuario /citas?idusuario=X citas futuras no canceladas
    // ----------------------------------------------------
    case $metodo == "GET" && $path == "/citas/usuario" && $id === null:
        $idusuario = $_GET['idusuario'] ?? null;
        $response = $citasControlador->ObtenerCitasPorIdUsuario($idusuario);
        break;
    
     // ----------------------------------------------------
    // GET BY IDUsuario /citas/general/usuario=X   todas las citas
    // ----------------------------------------------------
    case $metodo == "GET" && $path == "/citas/general/usuario" && $id === null:
        $idusuario = $_GET['idusuario'] ?? null;
        $response = $citasControlador->ObtenerTodasCitasPorIdUsuario($idusuario);
        break;

    // ----------------------------------------------------
    // POST (Crear) /citas
    // ----------------------------------------------------
    case $metodo == "POST" && $path == "/citas":
        $datos = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $response = $citasControlador->CrearCita($datos);
        break;

    // ----------------------------------------------------
    // PUT (Actualizar) /citas?id=X
    // ----------------------------------------------------
    case $metodo == "PUT" && $path == "/citas" && $id !== null:
        $datos = json_decode(file_get_contents("php://input"), true);
        $response = $citasControlador->ActualizarCita($id, $datos);
        break;

    // ----------------------------------------------------
    // DELETE (Eliminar) /citas?id=X
    // ----------------------------------------------------
    case $metodo == "DELETE" && $path == "/citas" && $id !== null:
        $response = $citasControlador->EliminarCita($id);
        break;

    case $metodo == "POST" && $path == "/admin/login":
        $datos = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $correo = $datos['correo'] ?? '';
        $password = $datos['password'] ?? '';
        $response = $adminControlador->Login($correo, $password);
        break;

    // ----------------------------------------------------
    // DEFAULT (404)
    // ----------------------------------------------------
    default: 
        $response = ["error" => "Endpoint no encontrado o método no permitido para esta ruta."];
        http_response_code(404);
        break;
}

header("Content-Type: application/json");
echo json_encode($response);
?>
