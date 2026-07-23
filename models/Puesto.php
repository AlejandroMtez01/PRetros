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

    // NUEVO: Obtener un puesto específico para leer su descripción
    public function obtenerPorId($id, $idEmpresa) {
        $sql = "SELECT * FROM Puestos WHERE id = ? AND idEmpresa = ?";
        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $id, $idEmpresa);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }
        return null;
    }

    // NUEVO: Buscar si el puesto se ha usado en Partes o Albaranes
    public function comprobarDependencias($descripcionPuesto, $idEmpresa) {
        // 1. Comprobar en Albaranes
        $sqlAlbaran = "SELECT 1 FROM lineasAlbaran WHERE idEmpresa = ? AND categoriaProfesional = ? LIMIT 1";
        $stmtA = $this->conexion->prepare($sqlAlbaran);
        if ($stmtA) {
            $stmtA->bind_param("is", $idEmpresa, $descripcionPuesto);
            $stmtA->execute();
            if ($stmtA->get_result()->num_rows > 0) return true;
        }

        // 2. Comprobar en Partes
        $sqlPartes = "SELECT 1 FROM lineasPartes WHERE idEmpresa = ? AND categoriaProfesional = ? LIMIT 1";
        $stmtP = $this->conexion->prepare($sqlPartes);
        if ($stmtP) {
            $stmtP->bind_param("is", $idEmpresa, $descripcionPuesto);
            $stmtP->execute();
            if ($stmtP->get_result()->num_rows > 0) return true;
        }

        return false;
    }

    public function guardar($datos) {
        if (empty($datos['id'])) {
            $sql = "INSERT INTO Puestos (idEmpresa, descripcion, precioHora) VALUES (?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("isd", $datos['idEmpresa'], $datos['descripcion'], $datos['precioHora']);
        } else {
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