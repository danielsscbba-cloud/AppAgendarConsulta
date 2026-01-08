<?php
require_once "conexionDB.php";
class CitasControlador
{
    private $db;
    private $table = "cita";
    public function __construct($baseDeDatos)
    {
        $this->db = $baseDeDatos;
    }

    // Obtener todas las citas (con nombre de usuario)
    public function ObtenerCitas()
    {
        $sql = "SELECT c.*, u.nombre AS nombre_usuario FROM cita c LEFT JOIN usuario u ON c.idusuario = u.idusuario";
        return $this->db->queryJoin($sql);
    }

    // Crear una cita
    public function CrearCita($data)
    {
        if ($this->db->create($this->table, $data)) {
            return ["message" => "Cita creada con exito."];
        } else {
            return ["error" => "Error al crear cita."];
        }
    }

    // Obtener una cita por ID
    public function ObtenerCitaPorId($id)
    {
        $sql = "SELECT c.*, u.nombre AS nombre_usuario FROM cita c LEFT JOIN usuario u ON c.idusuario = u.idusuario WHERE c.id = '" . intval($id) . "'";
        $rows = $this->db->queryJoin($sql);
        if (count($rows) > 0) {
            return $rows[0];
        } else {
            return ["error" => "Cita no encontrada."];
        }
    }

    // Actualizar una cita por ID
    public function ActualizarCita($id, $data)
    {
        if ($this->db->update($this->table, $data, "id = '" . intval($id) . "'")) {
            return "Cita actualizada con éxito.";
        } else {
            return "Error al actualizar cita.";
        }
    }

    // Eliminar una cita por ID
    public function EliminarCita($id)
    {
        if ($this->db->delete($this->table, "id = '" . intval($id) . "'")) {
            return "Cita eliminada con éxito.";
        } else {
            return "Error al eliminar cita.";
        }
    }

    public function ObtenerCitasPorIdUsuario($idusuario)
    {
        $sql = "SELECT c.* FROM cita c 
                WHERE c.idusuario = '" . intval($idusuario) . "' 
                AND c.fecha>= NOW() and c.estado != 'cancelado' 
                ORDER BY c.fecha ASC";
        return $this->db->queryJoin($sql);
    }
    public function ObtenerTodasCitasPorIdUsuario($idusuario)
    {
        $sql = "SELECT c.* FROM cita c 
                WHERE c.idusuario = '" . intval($idusuario) . "' 
                ORDER BY c.fecha asc";
        return $this->db->queryJoin($sql);
    }
}
