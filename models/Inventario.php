<?php
class Inventario {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerTodos($idEmpresa) {
        $stmt = $this->conexion->prepare("SELECT * FROM CatalogoInventario WHERE idEmpresa = ?");
        $stmt->bind_param("i", $idEmpresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPorPrefijo($prefijo, $idEmpresa) {
        $stmt = $this->conexion->prepare("SELECT * FROM CatalogoInventario WHERE prefijo = ? AND idEmpresa = ?");
        $stmt->bind_param("si", $prefijo, $idEmpresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function crearTipo($datos) {
        $sql = "INSERT INTO CatalogoInventario (prefijo, nombre_tipo, esquema_configuracion, idEmpresa) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        
        // sssi = string, string, string, integer
        $stmt->bind_param("sssi", 
            $datos['prefijo'], 
            $datos['nombre_tipo'], 
            $datos['esquema_configuracion'], 
            $datos['idEmpresa']
        );
        return $stmt->execute();
    }

    public function actualizarTipo($prefijo_original, $datos) {
        // En base de datos no se suele permitir cambiar la Primary Key una vez creada, 
        // pero por si acaso, actualizamos los demás campos basados en el prefijo original.
        $sql = "UPDATE CatalogoInventario 
                SET nombre_tipo = ?, esquema_configuracion = ? 
                WHERE prefijo = ? AND idEmpresa = ?";
        $stmt = $this->conexion->prepare($sql);
        
        $stmt->bind_param("sssi", 
            $datos['nombre_tipo'], 
            $datos['esquema_configuracion'], 
            $prefijo_original,
            $datos['idEmpresa']
        );
        return $stmt->execute();
    }

// --- MÓDULO DE DEPENDENCIAS ---
    public function comprobarDependencias($prefijo, $idEmpresa) {
        // Buscamos qué elementos del inventario usan este prefijo
        $sql = "SELECT denominacion FROM Inventario WHERE prefijo_tipo = ? AND idEmpresa = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("si", $prefijo, $idEmpresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function eliminarTipo($prefijo, $idEmpresa) {
        try {
            $stmt = $this->conexion->prepare("DELETE FROM CatalogoInventario WHERE prefijo = ? AND idEmpresa = ?");
            $stmt->bind_param("si", $prefijo, $idEmpresa);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    
}
?>