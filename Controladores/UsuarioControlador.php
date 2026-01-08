<?php
require_once "conexionDB.php";
class UsuarioControlador
{
    private $db;
    private $table = "usuario";
    public function __construct($baseDeDatos)
    {
        $this->db = $baseDeDatos;
    }

    public function ObtenerUsuarios()
    {
        $usuarios = $this->db->read($this->table);
        return $usuarios;
    }
    public function CrearUsuario($data)
    {
        if ($this->db->create($this->table,$data)) {
            return ["message" => "Usuario creado con éxito."];
        } else {
            return ["error" => "Error al crear usuario."];
        }
    }

    // Obtener un usuario por ID
    public function ObtenerUsuarioPorId($id)
    {
        $usuarios = $this->db->read($this->table, "idusuario = '" . intval($id) . "'");
        if (count($usuarios) > 0) {
            return $usuarios[0];
        } else {
            return ["error" => "Usuario no encontrado."];
        }
    }

    // Actualizar un usuario por ID
    public function ActualizarUsuario($id, $data)
    {
        if ($this->db->update($this->table, $data, "idusuario = '" . intval($id) . "'")) {
            return "Usuario actualizado con éxito.";
        } else {
            return "Error al actualizar usuario.";
        }
    }

    // Eliminar un usuario por ID
    public function EliminarUsuario($id)
    {
        if ($this->db->delete($this->table, "idusuario = '" . intval($id) . "'")) {
            return "Usuario eliminado con éxito.";
        } else {
            return "Error al eliminar usuario.";
        }
    }

    public function Login($correo, $password)
    {
        $conn = $this->db->getConn();
        $correoEsc = $conn->real_escape_string($correo);
        $passwordEsc = $conn->real_escape_string($password);
        $usuarios = $this->db->read($this->table, "(correo_electronico = '" . $correoEsc ."' or username = '". $correoEsc."') AND password = '" . $passwordEsc . "'");
        if (count($usuarios) > 0) {
            return $usuarios[0];
        } else {
            return ["error" => "Credenciales inválidas."];
        }
    }
}
