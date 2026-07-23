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

    // --- MÓDULO DE ELIMINACIÓN Y DEPENDENCIAS DE CABECERA ---
    public function comprobarDependenciasCatalogo($codigoCabecera, $idEmpresa) {
        $sql = "SELECT prefijo, nombre_tipo FROM CatalogoInventario 
                WHERE idEmpresa = ? AND esquema_configuracion LIKE ?";
        $stmt = $this->conexion->prepare($sql);
        
        $busqueda_json = '%"' . $codigoCabecera . '"%';
        $stmt->bind_param("is", $idEmpresa, $busqueda_json);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function comprobarDependenciasCabeceraDocumentos($codigoCabecera, $idEmpresa) {
        $lineas = $this->obtenerLineas($codigoCabecera, $idEmpresa);
        if (empty($lineas)) return false;

        foreach ($lineas as $linea) {
            if ($this->comprobarDependenciasLinea($linea['codigo'], $idEmpresa)) {
                return true; 
            }
        }
        return false;
    }

    public function eliminarCabecera($codigo, $idEmpresa) {
        $this->conexion->begin_transaction();
        try {
            $stmtLineas = $this->conexion->prepare("DELETE FROM GTablasLineas WHERE codigoCabecera = ? AND idEmpresa = ?");
            $stmtLineas->bind_param("si", $codigo, $idEmpresa);
            $stmtLineas->execute();

            $stmtCabecera = $this->conexion->prepare("DELETE FROM GTablasCabecera WHERE codigo = ? AND idEmpresa = ?");
            $stmtCabecera->bind_param("si", $codigo, $idEmpresa);
            $stmtCabecera->execute();

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollback();
            return $e->getMessage();
        }
    }

    // --- GESTIÓN DE LÍNEAS ---
    public function obtenerLineas($codigoCabecera, $idEmpresa) {
        $stmt = $this->conexion->prepare("SELECT * FROM GTablasLineas WHERE codigoCabecera = ? AND idEmpresa = ? ORDER BY codigo ASC");
        $stmt->bind_param("si", $codigoCabecera, $idEmpresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerLineaPorId($id, $idEmpresa) {
        $stmt = $this->conexion->prepare("SELECT * FROM GTablasLineas WHERE id = ? AND idEmpresa = ?");
        $stmt->bind_param("ii", $id, $idEmpresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
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

    // --- MÓDULO DE DEPENDENCIAS DE LÍNEAS ---
    public function comprobarDependenciasLinea($codigoLinea, $idEmpresa) {
        $busqueda_str = $codigoLinea;
        $busqueda_json = '%"' . $codigoLinea . '"%';

        // 1. Inventario (Busca dentro del JSON datos_dinamicos)
        $sqlInv = "SELECT 1 FROM Inventario WHERE idEmpresa = ? AND datos_dinamicos LIKE ? LIMIT 1";
        $stmtInv = $this->conexion->prepare($sqlInv);
        if ($stmtInv) {
            $stmtInv->bind_param("is", $idEmpresa, $busqueda_json);
            $stmtInv->execute();
            if ($stmtInv->get_result()->num_rows > 0) return true;
        }

        // 2. Albaranes (Categoría o Vehículo)
        $sqlLA = "SELECT 1 FROM lineasAlbaran WHERE idEmpresa = ? AND (categoriaProfesional = ? OR vehiculoUtilizado = ?) LIMIT 1";
        $stmtLA = $this->conexion->prepare($sqlLA);
        if ($stmtLA) {
            $stmtLA->bind_param("iss", $idEmpresa, $busqueda_str, $busqueda_str);
            $stmtLA->execute();
            if ($stmtLA->get_result()->num_rows > 0) return true;
        }

        // 3. Partes (Categoría o Vehículo)
        $sqlLP = "SELECT 1 FROM lineasPartes WHERE idEmpresa = ? AND (categoriaProfesional = ? OR vehiculoUtilizado = ?) LIMIT 1";
        $stmtLP = $this->conexion->prepare($sqlLP);
        if ($stmtLP) {
            $stmtLP->bind_param("iss", $idEmpresa, $busqueda_str, $busqueda_str);
            $stmtLP->execute();
            if ($stmtLP->get_result()->num_rows > 0) return true;
        }

        return false;
    }
}
?>