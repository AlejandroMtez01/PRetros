<?php
class Usuario {
    private $conexion;

    public function __construct(mysqli $conexion) {
        $this->conexion = $conexion;
    }

    // Búsqueda por email (para el Login)
    public function obtenerPorEmail($email) {
        $stmt = $this->conexion->prepare("SELECT id, nombre, apellido1, apellido2, email, contraseña, idEmpresa FROM Usuarios WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    // Inserción exacta con los 6 campos (el ID es automático)
    public function crearUsuario($datos) {
        $sql = "INSERT INTO Usuarios (nombre, apellido1, apellido2, email, contraseña, idEmpresa) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conexion->prepare($sql);
        
        // "sssssi" -> 5 strings (nombre, ap1, ap2, email, hash_contraseña) y 1 entero (idEmpresa)
        $stmt->bind_param("sssssi", 
            $datos['nombre'], 
            $datos['apellido1'], 
            $datos['apellido2'], 
            $datos['email'], 
            $datos['contraseña'], 
            $datos['idEmpresa']
        );
        
        return $stmt->execute();
    }
}
?>