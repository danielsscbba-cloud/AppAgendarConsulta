<?php
class ConexionDB {
    private $host = "127.0.0.1";
    private $user = "root"; // Cambia esto por tu usuario de DB
    private $password = "123456"; // Cambia esto por tu contraseña de DB
    private $dbname = "dbpsico"; // Cambia esto por el nombre de tu base de datos
    private $conn;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->dbname);
        if ($this->conn->connect_error) {
            die("Error de conexión: " . $this->conn->connect_error);
        }
    }

    public function __destruct() {
        $this->conn->close();
    }

    // Método para crear (insertar) un registro
    public function create($table, $data) {
        $columns = implode(", ", array_keys($data));
        $values = "'" . implode("', '", array_values($data)) . "'";
        $sql = "INSERT INTO $table ($columns) VALUES ($values)";
        return $this->conn->query($sql);
    }

    // Método para leer (seleccionar) registros
    public function read($table, $condition = "") {
        $sql = "SELECT * FROM $table";
        if (!empty($condition)) {
            $sql .= " WHERE " . $condition;
        }
        $result = $this->conn->query($sql);
        $rows = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    // Método para actualizar un registro
    public function update($table, $data, $condition) {
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "$column = '$value'";
        }
        $set = implode(", ", $set);
        $sql = "UPDATE $table SET $set WHERE $condition";
        return $this->conn->query($sql);
    }

    // Método para borrar un registro
    public function delete($table, $condition) {
        $sql = "DELETE FROM $table WHERE $condition";
        return $this->conn->query($sql);
    }

        // Método para ejecutar consultas personalizadas (JOINs, etc.)
    public function queryJoin($sql) {
        $result = $this->conn->query($sql);
        $rows = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
        // Permitir acceso a la conexión para escapes avanzados
    public function getConn() {
        return $this->conn;
    }
}

?>