<?php
class Articulo {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerTodos($idEmpresa) {
        $stmt = $this->conexion->prepare("SELECT * FROM Inventario WHERE idEmpresa = ?");
        $stmt->bind_param("i", $idEmpresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPorId($prefijo_tipo, $denominacion, $idEmpresa) {
        $stmt = $this->conexion->prepare("SELECT * FROM Inventario WHERE prefijo_tipo = ? AND denominacion = ? AND idEmpresa = ?");
        $stmt->bind_param("ssi", $prefijo_tipo, $denominacion, $idEmpresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function crear($datos) {
        $sql = "INSERT INTO Inventario (prefijo_tipo, denominacion, datos_dinamicos, idEmpresa) VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("sssi", $datos['prefijo_tipo'], $datos['denominacion'], $datos['datos_dinamicos'], $datos['idEmpresa']);
        return $stmt->execute();
    }

    public function actualizar($prefijo_tipo, $denominacion, $datos) {
        // Al ser Primary Key compuesta, actualizamos solo los datos dinámicos usando la clave
        $sql = "UPDATE Inventario SET datos_dinamicos = ? WHERE prefijo_tipo = ? AND denominacion = ? AND idEmpresa = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("sssi", $datos['datos_dinamicos'], $prefijo_tipo, $denominacion, $datos['idEmpresa']);
        return $stmt->execute();
    }

    public function eliminar($prefijo_tipo, $denominacion, $idEmpresa) {
        $stmt = $this->conexion->prepare("DELETE FROM Inventario WHERE prefijo_tipo = ? AND denominacion = ? AND idEmpresa = ?");
        $stmt->bind_param("ssi", $prefijo_tipo, $denominacion, $idEmpresa);
        return $stmt->execute();
    }
}
?>