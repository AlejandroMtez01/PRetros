<?php
class Usuario {
    private $conexion;

    public function __construct(mysqli $conexion) {
        $this->conexion = $conexion;
    }

   // Búsqueda por email (para el Login)
    public function obtenerPorEmail($email) {
        // Quitamos idEmpresa del SELECT porque la columna ya no existe
        $stmt = $this->conexion->prepare("SELECT id, nombre, apellido1, apellido2, email, contraseña FROM Usuarios WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    // Inserción en dos pasos (Usuarios y Usuarios_Empresas)
    public function crearUsuario($datos, $idEmpresaInicial) {
        // Iniciamos una transacción para asegurar que ambos inserts funcionen juntos
        $this->conexion->begin_transaction();
        
        try {
            // 1. Insertamos en la tabla principal de Usuarios
            $sqlUser = "INSERT INTO Usuarios (nombre, apellido1, apellido2, email, contraseña) 
                        VALUES (?, ?, ?, ?, ?)";
            $stmtUser = $this->conexion->prepare($sqlUser);
            $stmtUser->bind_param("sssss", 
                $datos['nombre'], 
                $datos['apellido1'], 
                $datos['apellido2'], 
                $datos['email'], 
                $datos['contraseña']
            );
            $stmtUser->execute();
            
            // Obtenemos el ID del usuario que se acaba de crear
            $idUsuarioNuevo = $this->conexion->insert_id;
            
            // 2. Insertamos en la tabla intermedia Usuarios_Empresas
            $sqlEmpresa = "INSERT INTO Usuarios_Empresas (id_usuario, id_empresa) VALUES (?, ?)";
            $stmtEmpresa = $this->conexion->prepare($sqlEmpresa);
            $stmtEmpresa->bind_param("ii", $idUsuarioNuevo, $idEmpresaInicial);
            $stmtEmpresa->execute();
            
            // Si todo fue bien, confirmamos los cambios en la BD
            $this->conexion->commit();
            return true;
            
        } catch (Exception $e) {
            // Si falla algo, deshacemos todos los cambios
            $this->conexion->rollback();
            error_log("Error al crear usuario y asignar empresa: " . $e->getMessage());
            return false;
        }
    }

    // ====================================================
    // OBTENER EMPRESAS (Múltiples empresas por usuario)
    // ====================================================
    public function obtenerEmpresasDelUsuario($idUsuario) {
        
        // CORRECCIÓN: Usamos e.denominacion en lugar de e.nombre
        $sql = "SELECT e.id as id, e.denominacion as nombre 
                FROM Empresas e
                INNER JOIN Usuarios_Empresas ue ON e.id = ue.id_empresa
                WHERE ue.id_usuario = ?";

        $stmt = $this->conexion->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("i", $idUsuario);
            $stmt->execute();
            $resultado = $stmt->get_result();
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log("Error en la consulta de empresas múltiples: " . $this->conexion->error);
            return [];
        }
    }
    public function obtenerTodasLasEmpresas() {
        // Hacemos una consulta directa para traer todas las empresas ordenadas alfabéticamente
        $sql = "SELECT id, denominacion FROM Empresas ORDER BY denominacion ASC";
        $resultado = $this->conexion->query($sql);
        
        $empresas = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $empresas[] = $fila;
            }
        } else {
            error_log("Error al obtener el listado general de empresas: " . $this->conexion->error);
        }
        
        return $empresas;
    }
}
?>