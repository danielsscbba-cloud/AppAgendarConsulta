<?php
require_once "conexionDB.php";
class AdminControlador
{
    private $db;
    private $table = "dradmin";
    public function __construct($baseDeDatos)
    {
        $this->db = $baseDeDatos;
    }

    // Obtener un usuario por ID
    public function Login($correo, $password)
    {
        $conn = $this->db->getConn();
        $correoEsc = $conn->real_escape_string($correo);
        $passwordEsc = $conn->real_escape_string($password);
        $usuarios = $this->db->read($this->table, "(correo = '" . $correoEsc ."' or username = '". $correoEsc."') AND password = '" . $passwordEsc . "'");
        if (count($usuarios) > 0) {
            return $usuarios[0];
        } else {
            return ["error" => "Credenciales inválidas."];
        }
    }

}
