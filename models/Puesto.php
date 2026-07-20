<?php
class Puesto {
    private $conexion;

    public function __construct(mysqli $conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerTodos($idEmpresa) {
        $sql = "SELECT * FROM Puestos WHERE idEmpresa = ? ORDER BY descripcion ASC";
        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $idEmpresa);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function guardar($datos) {
        if (empty($datos['id'])) {
            // INSERTAR: i = entero (idEmpresa), s = string (descripcion), d = double (precioHora)
            $sql = "INSERT INTO Puestos (idEmpresa, descripcion, precioHora) VALUES (?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("isd", $datos['idEmpresa'], $datos['descripcion'], $datos['precioHora']);
        } else {
            // ACTUALIZAR: s = string (descripcion), d = double (precioHora), i = entero (id, idEmpresa)
            $sql = "UPDATE Puestos SET descripcion = ?, precioHora = ? WHERE id = ? AND idEmpresa = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("sdii", $datos['descripcion'], $datos['precioHora'], $datos['id'], $datos['idEmpresa']);
        }

        if ($stmt && $stmt->execute()) {
            return true;
        } else {
            return $this->conexion->error;
        }
    }

    public function eliminar($id, $idEmpresa) {
        $sql = "DELETE FROM Puestos WHERE id = ? AND idEmpresa = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ii", $id, $idEmpresa);
        
        if ($stmt && $stmt->execute()) {
            return true;
        } else {
            return $this->conexion->error;
        }
    }
}
?>