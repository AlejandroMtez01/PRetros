<?php
class Centro {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerPorCliente($idCliente, $idEmpresa) {
        $stmt = $this->conexion->prepare("SELECT * FROM CentrosCliente WHERE idCliente = ? AND idEmpresa = ?");
        $stmt->bind_param("ii", $idCliente, $idEmpresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM CentrosCliente WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function crearCentro($datos) {
        $sql = "INSERT INTO CentrosCliente (idCliente, direccion, poblado, idUsuario, idEmpresa) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        
        // i = integer, s = string (issii = int, string, string, int, int)
        $stmt->bind_param("issii", 
            $datos['idCliente'], 
            $datos['direccion'], 
            $datos['poblado'], 
            $datos['idUsuario'], 
            $datos['idEmpresa']
        );
        return $stmt->execute();
    }

    public function actualizarCentro($id, $datos) {
        $sql = "UPDATE CentrosCliente 
                SET direccion = ?, poblado = ?, idUsuario = ? 
                WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        
        $stmt->bind_param("ssii", 
            $datos['direccion'], 
            $datos['poblado'], 
            $datos['idUsuario'], 
            $id
        );
        return $stmt->execute();
    }

    public function eliminarCentro($id) {
        $stmt = $this->conexion->prepare("DELETE FROM CentrosCliente WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>