<?php
class Albaran
{
    private $conexion;

    public function __construct(mysqli $conexion)
    {
        $this->conexion = $conexion;
    }

    // ====================================================
    // OBTENER ALBARANES (CON FILTROS ENCADENADOS)
    // ====================================================
    public function obtenerTodosFiltrados($idEmpresa, $filtros)
    {
        $sql = "SELECT a.*, c.razonSocial as nombreCliente, ce.direccion as nombreCentro 
                FROM Albaranes a
                LEFT JOIN Clientes c ON a.idCliente = c.id
                LEFT JOIN CentrosCliente ce ON a.idCentro = ce.id
                WHERE a.idEmpresa = ?";

        $tipos = "i";
        $valores = [$idEmpresa];

        if (!empty($filtros['numAlbaran'])) {
            $sql .= " AND a.numAlbaran LIKE ?";
            $tipos .= "s";
            $valores[] = "%" . $filtros['numAlbaran'] . "%";
        }

        if (!empty($filtros['idCliente'])) {
            $sql .= " AND a.idCliente = ?";
            $tipos .= "i";
            $valores[] = $filtros['idCliente'];
        }

        if (!empty($filtros['idCentro'])) {
            $sql .= " AND a.idCentro = ?";
            $tipos .= "i";
            $valores[] = $filtros['idCentro'];
        }

        if (!empty($filtros['fechaDesde'])) {
            $sql .= " AND a.fecha >= ?";
            $tipos .= "s";
            $valores[] = $filtros['fechaDesde'];
        }

        if (!empty($filtros['fechaHasta'])) {
            $sql .= " AND a.fecha <= ?";
            $tipos .= "s";
            $valores[] = $filtros['fechaHasta'];
        }

        $sql .= " ORDER BY a.fecha DESC, a.numAlbaran DESC";

        $stmt = $this->conexion->prepare($sql);

        if ($stmt) {
            $stmt->bind_param($tipos, ...$valores);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log("Error en la consulta de albaranes: " . $this->conexion->error);
            return [];
        }
    }

    // ====================================================
    // GUARDAR ALBARÁN Y SUS LÍNEAS (TRANSACCIÓN)
    // ====================================================
    

    // ====================================================
    // OBTENER UN ALBARÁN Y SUS LÍNEAS POR ID
    // ====================================================
    public function obtenerPorId($idAlbaran, $idEmpresa)
    {
        $sql = "SELECT a.*, c.razonSocial as nombreCliente, ce.direccion as nombreCentro 
                FROM Albaranes a
                LEFT JOIN Clientes c ON a.idCliente = c.id
                LEFT JOIN CentrosCliente ce ON a.idCentro = ce.id
                WHERE a.id = ? AND a.idEmpresa = ?";

        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $idAlbaran, $idEmpresa);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }
        return null;
    }

    public function obtenerLineas($idAlbaran, $idEmpresa)
    {
        $sql = "SELECT l.*, e.nombre as empNombre, e.apellido1 as empApellido 
                FROM lineasAlbaran l
                LEFT JOIN Empleados e ON l.idEmpleado = e.id
                WHERE l.idAlbaran = ? AND l.idEmpresa = ?";

        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $idAlbaran, $idEmpresa);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }


    // VALIDACIONES
    

   // ====================================================
    // GUARDAR ALBARÁN Y SUS LÍNEAS + MATERIALES (ESTRICTO)
    // ====================================================
    public function guardarAlbaranCompleto($cabecera, $lineas, $materiales = [])
    {
        $this->conexion->begin_transaction();

        try {
            // 1. Cabecera
            $sqlCabecera = "INSERT INTO Albaranes (numAlbaran, idCliente, observaciones, idCentro, fecha, idUsuario, idEmpresa) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtCabecera = $this->conexion->prepare($sqlCabecera);
            if (!$stmtCabecera) throw new Exception("Error preparando cabecera: " . $this->conexion->error);
            
            $stmtCabecera->bind_param("sisssii", $cabecera['numAlbaran'], $cabecera['idCliente'], $cabecera['observaciones'], $cabecera['idCentro'], $cabecera['fecha'], $cabecera['idUsuario'], $cabecera['idEmpresa']);
            if (!$stmtCabecera->execute()) throw new Exception("Error BD (Cabecera): " . $stmtCabecera->error);

            $idAlbaran = $this->conexion->insert_id;

            // 2. Líneas de Empleados
            if (!empty($lineas)) {
                $sqlLinea = "INSERT INTO lineasAlbaran (idAlbaran, idEmpleado, horaDesde, horaHasta, categoriaProfesional, vehiculoUtilizado, importe, idUsuario, idEmpresa) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmtLinea = $this->conexion->prepare($sqlLinea);
                if (!$stmtLinea) throw new Exception("Error SQL Líneas: " . $this->conexion->error);
                
                foreach ($lineas as $linea) {
                    $vehiculo = !empty($linea['vehiculoUtilizado']) ? $linea['vehiculoUtilizado'] : null;
                    $importe = !empty($linea['importe']) ? $linea['importe'] : null;
                    
                    $stmtLinea->bind_param("iisssssii", $idAlbaran, $linea['idEmpleado'], $linea['horaDesde'], $linea['horaHasta'], $linea['categoriaProfesional'], $vehiculo, $importe, $cabecera['idUsuario'], $cabecera['idEmpresa']);
                    if (!$stmtLinea->execute()) throw new Exception("Error BD (Línea Empleado): " . $stmtLinea->error);
                }
            }

            // 3. Líneas de Materiales
            if (!empty($materiales)) {
                $sqlMat = "INSERT INTO lineasAlbaranMateriales (idAlbaran, denominacionArticulo, unidades, precioUnitario, importeTotal, idEmpresa, idUsuario) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmtMat = $this->conexion->prepare($sqlMat);
                if (!$stmtMat) throw new Exception("Error SQL Materiales: " . $this->conexion->error);
                
                foreach ($materiales as $mat) {
                    $stmtMat->bind_param(
                        "isdddii", 
                        $idAlbaran, 
                        $mat['denominacionArticulo'], 
                        $mat['unidades'], 
                        $mat['precioUnitario'], 
                        $mat['importeTotal'], 
                        $cabecera['idEmpresa'], 
                        $cabecera['idUsuario']
                    );
                    if (!$stmtMat->execute()) {
                        throw new Exception("Error BD al insertar Material (" . htmlspecialchars($mat['denominacionArticulo']) . "): " . $stmtMat->error);
                    }
                }
            }

            $this->conexion->commit();
            return $idAlbaran; 
        } catch (Exception $e) {
            $this->conexion->rollback();
            return $e->getMessage(); // Devuelve el error exacto al Controlador
        }
    }

    // ====================================================
    // ACTUALIZAR ALBARÁN Y MATERIALES (ESTRICTO)
    // ====================================================
    public function actualizarAlbaranCompleto($idAlbaran, $cabecera, $lineas, $materiales = [])
    {
        $this->conexion->begin_transaction();
        
        try {
            // 1. Cabecera
            $sqlCabecera = "UPDATE Albaranes SET idCliente = ?, observaciones = ?, idCentro = ?, fecha = ? WHERE id = ? AND idEmpresa = ?";
            $stmtCabecera = $this->conexion->prepare($sqlCabecera);
            if (!$stmtCabecera) throw new Exception("Error SQL Cabecera: " . $this->conexion->error);
            
            $stmtCabecera->bind_param("isssii", $cabecera['idCliente'], $cabecera['observaciones'], $cabecera['idCentro'], $cabecera['fecha'], $idAlbaran, $cabecera['idEmpresa']);
            if (!$stmtCabecera->execute()) throw new Exception("Error BD actualizando Cabecera: " . $stmtCabecera->error);

            // 2. Borrar y reinsertar líneas de empleados
            $stmtDelete = $this->conexion->prepare("DELETE FROM lineasAlbaran WHERE idAlbaran = ? AND idEmpresa = ?");
            if (!$stmtDelete) throw new Exception("Error SQL Borrar Empleados: " . $this->conexion->error);
            $stmtDelete->bind_param("ii", $idAlbaran, $cabecera['idEmpresa']);
            if (!$stmtDelete->execute()) throw new Exception("Error BD borrando Empleados: " . $stmtDelete->error);

            if (!empty($lineas)) {
                $sqlLinea = "INSERT INTO lineasAlbaran (idAlbaran, idEmpleado, horaDesde, horaHasta, categoriaProfesional, vehiculoUtilizado, importe, idUsuario, idEmpresa) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmtLinea = $this->conexion->prepare($sqlLinea);
                if (!$stmtLinea) throw new Exception("Error SQL insertar Empleados: " . $this->conexion->error);
                
                foreach ($lineas as $linea) {
                    $vehiculo = !empty($linea['vehiculoUtilizado']) ? $linea['vehiculoUtilizado'] : null;
                    $importe = !empty($linea['importe']) ? $linea['importe'] : null;
                    
                    $stmtLinea->bind_param("iisssssii", $idAlbaran, $linea['idEmpleado'], $linea['horaDesde'], $linea['horaHasta'], $linea['categoriaProfesional'], $vehiculo, $importe, $cabecera['idUsuario'], $cabecera['idEmpresa']);
                    if (!$stmtLinea->execute()) throw new Exception("Error BD insertando Empleado: " . $stmtLinea->error);
                }
            }

            // 3. Borrar y reinsertar materiales
            $stmtDeleteMat = $this->conexion->prepare("DELETE FROM lineasAlbaranMateriales WHERE idAlbaran = ? AND idEmpresa = ?");
            if (!$stmtDeleteMat) throw new Exception("Error SQL Borrar Materiales: " . $this->conexion->error);
            
            $stmtDeleteMat->bind_param("ii", $idAlbaran, $cabecera['idEmpresa']);
            if (!$stmtDeleteMat->execute()) throw new Exception("Error BD borrando Materiales antiguos: " . $stmtDeleteMat->error);

            if (!empty($materiales)) {
                $sqlMat = "INSERT INTO lineasAlbaranMateriales (idAlbaran, denominacionArticulo, unidades, precioUnitario, importeTotal, idEmpresa, idUsuario) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmtMat = $this->conexion->prepare($sqlMat);
                if (!$stmtMat) throw new Exception("Error SQL insertar Materiales: " . $this->conexion->error);
                
                foreach ($materiales as $mat) {
                    $stmtMat->bind_param(
                        "isdddii", 
                        $idAlbaran, 
                        $mat['denominacionArticulo'], 
                        $mat['unidades'], 
                        $mat['precioUnitario'], 
                        $mat['importeTotal'], 
                        $cabecera['idEmpresa'], 
                        $cabecera['idUsuario']
                    );
                    if (!$stmtMat->execute()) {
                        throw new Exception("Error BD al insertar Material (" . htmlspecialchars($mat['denominacionArticulo']) . "): " . $stmtMat->error);
                    }
                }
            }

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollback();
            return $e->getMessage();
        }
    }
    // ==========================================
    // ELIMINAR ALBARÁN COMPLETO
    // ==========================================
   // ==========================================
    // ELIMINAR ALBARÁN COMPLETO (MODELO - ESTRICTO)
    // ==========================================
    public function eliminar($idAlbaran, $idEmpresa) {
        $this->conexion->begin_transaction();
        try {
            // 1. Borramos primero las líneas dependientes
            $sqlDeleteLineas = "DELETE FROM lineasAlbaran WHERE idAlbaran = ? AND idEmpresa = ?";
            $stmtLineas = $this->conexion->prepare($sqlDeleteLineas);
            
            if (!$stmtLineas) {
                throw new Exception("Error preparando borrado de líneas: " . $this->conexion->error);
            }
            
            $stmtLineas->bind_param("ii", $idAlbaran, $idEmpresa);
            
            if (!$stmtLineas->execute()) {
                throw new Exception("Error ejecutando borrado de líneas: " . $stmtLineas->error);
            }

            // 2. Borramos la cabecera del albarán
            $sqlDeleteCabecera = "DELETE FROM Albaranes WHERE id = ? AND idEmpresa = ?";
            $stmtCabecera = $this->conexion->prepare($sqlDeleteCabecera);
            
            if (!$stmtCabecera) {
                throw new Exception("Error preparando borrado de cabecera: " . $this->conexion->error);
            }

            $stmtCabecera->bind_param("ii", $idAlbaran, $idEmpresa);
            
            if (!$stmtCabecera->execute()) {
                throw new Exception("Error ejecutando borrado de cabecera: " . $stmtCabecera->error);
            }

            $this->conexion->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conexion->rollback();
            return $e->getMessage();
        }
    }
// ====================================================
    // CONSULTAR MATERIALES POR ALBARÁN
    // ====================================================
    public function obtenerMateriales($idAlbaran, $idEmpresa)
    {
        $sql = "SELECT * FROM lineasAlbaranMateriales WHERE idAlbaran = ? AND idEmpresa = ?";
        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $idAlbaran, $idEmpresa);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }
    
} // <-- Fin de la clase Albaran

