<?php
require_once "conexionDB.php";
class HorarioControlador
{
    private $db;
    private $table = "horario_trabajo";
    public function __construct($baseDeDatos)
    {
        $this->db = $baseDeDatos;
    }

    public function ObtenerHorarios()
    {
        $horarios = $this->db->read($this->table);
        return $horarios;
    }
    public function CrearHorario($data)
    {
        if ($this->db->create($this->table, $data)) {
            return "Horario creado con éxito.";
        } else {
            return "Error al crear horario.";
        }
    }
    public function ObtenerHorarioPorId($id)
    {
        $horarios = $this->db->read($this->table, "id = '" . intval($id) . "'");
        if (count($horarios) > 0) {
            return $horarios[0];
        } else {
            return ["error" => "Horario no encontrado."];
        }
    }
    public function ActualizarHorario($id, $data)
    {
        if ($this->db->update($this->table, $data, "id = '" . intval($id) . "'")) {
            return "Horario actualizado con éxito.";
        } else {
            return "Error al actualizar horario.";
        }
    }
    public function EliminarHorario($id)
    {
        if ($this->db->delete($this->table, "id = '" . intval($id) . "'")) {
            return "Horario eliminado con éxito.";
        } else {
            return "Error al eliminar horario.";
        }
    }
    // Obtener horarios por número de día de la semana (0=domingo, 1=lunes, ... 6=sábado)
    public function ObtenerHorariosPorDia($diaSemanaNum)
    {
        // Asegura que el parámetro sea un número entero válido (0-6)
        $diaSemanaNum = intval($diaSemanaNum);
        $horarios = $this->db->read($this->table, "dia_semana_num = '" . $diaSemanaNum . "'");
        return $horarios;
    }
}
