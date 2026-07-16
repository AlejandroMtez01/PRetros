<?php
class Cliente {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerTodos($idEmpresa) {
        $stmt = $this->conexion->prepare("SELECT * FROM Clientes WHERE idEmpresa = ?");
        $stmt->bind_param("i", $idEmpresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM Clientes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function crearCliente($datos) {
        $sql = "INSERT INTO Clientes (razonSocial, CIF, sedeFiscal, idUsuario, idEmpresa) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        
        // "sssii": 3 strings (razonSocial, CIF, sedeFiscal) y 2 integers (idUsuario, idEmpresa)
        $stmt->bind_param("sssii", 
            $datos['razonSocial'], 
            $datos['CIF'], 
            $datos['sedeFiscal'], 
            $datos['idUsuario'], 
            $datos['idEmpresa']
        );
        return $stmt->execute();
    }

    public function actualizarCliente($id, $datos) {
        $sql = "UPDATE Clientes 
                SET razonSocial = ?, CIF = ?, sedeFiscal = ?, idUsuario = ? 
                WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        
        // "sssii": 3 strings y 2 integers (idUsuario y el id del WHERE)
        $stmt->bind_param("sssii", 
            $datos['razonSocial'], 
            $datos['CIF'], 
            $datos['sedeFiscal'], 
            $datos['idUsuario'], 
            $id
        );
        return $stmt->execute();
    }

    public function eliminarCliente($id) {
        $stmt = $this->conexion->prepare("DELETE FROM Clientes WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>