<?php
class Tabla {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // --- GESTIÓN DE CABECERAS ---
    public function obtenerCabeceras($idEmpresa) {
        $stmt = $this->conexion->prepare("SELECT * FROM GTablasCabecera WHERE idEmpresa = ?");
        $stmt->bind_param("i", $idEmpresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerCabecera($codigo, $idEmpresa) {
        $stmt = $this->conexion->prepare("SELECT * FROM GTablasCabecera WHERE codigo = ? AND idEmpresa = ?");
        $stmt->bind_param("si", $codigo, $idEmpresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function crearCabecera($datos) {
        $sql = "INSERT INTO GTablasCabecera (codigo, descripcion, idEmpresa) VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ssi", $datos['codigo'], $datos['descripcion'], $datos['idEmpresa']);
        return $stmt->execute();
    }

    // --- GESTIÓN DE LÍNEAS ---
    public function obtenerLineas($codigoCabecera, $idEmpresa) {
        $stmt = $this->conexion->prepare("SELECT * FROM GTablasLineas WHERE codigoCabecera = ? AND idEmpresa = ? ORDER BY codigo ASC");
        $stmt->bind_param("si", $codigoCabecera, $idEmpresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function crearLinea($datos) {
        $sql = "INSERT INTO GTablasLineas (codigoCabecera, codigo, descripcion, idEmpresa) VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("sssi", $datos['codigoCabecera'], $datos['codigo'], $datos['descripcion'], $datos['idEmpresa']);
        return $stmt->execute();
    }

    public function eliminarLinea($id, $idEmpresa) {
        $stmt = $this->conexion->prepare("DELETE FROM GTablasLineas WHERE id = ? AND idEmpresa = ?");
        $stmt->bind_param("ii", $id, $idEmpresa);
        return $stmt->execute();
    }
}
?>