<?php
class Parte
{
    private $conexion;

    public function __construct(mysqli $conexion)
    {
        $this->conexion = $conexion;
    }

    // ====================================================
    // OBTENER PARTES (CON FILTROS)
    // ====================================================
    public function obtenerTodosFiltrados($idEmpresa, $filtros)
    {
        // Unimos con Empleados para tener el nombre en la cabecera
        $sql = "SELECT p.*, CONCAT(e.nombre, ' ', e.apellido1) as nombreEmpleado 
                FROM Partes p
                LEFT JOIN Empleados e ON p.idEmpleado = e.id
                WHERE p.idEmpresa = ?";

        $tipos = "i";
        $valores = [$idEmpresa];

        if (!empty($filtros['idParte'])) {
            $sql .= " AND p.id = ?";
            $tipos .= "i";
            $valores[] = $filtros['idParte'];
        }

        if (!empty($filtros['idEmpleado'])) {
            $sql .= " AND p.idEmpleado = ?";
            $tipos .= "i";
            $valores[] = $filtros['idEmpleado'];
        }

        if (!empty($filtros['fechaDesde'])) {
            $sql .= " AND p.fechaDesde >= ?";
            $tipos .= "s";
            $valores[] = $filtros['fechaDesde'];
        }

        if (!empty($filtros['fechaHasta'])) {
            $sql .= " AND p.fechaHasta <= ?";
            $tipos .= "s";
            $valores[] = $filtros['fechaHasta'];
        }

        $sql .= " ORDER BY p.fechaDesde DESC, p.id DESC";

        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($tipos, ...$valores);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log("Error en consulta de partes: " . $this->conexion->error);
            return [];
        }
    }

    // ====================================================
    // GUARDAR PARTE Y SUS LÍNEAS (TRANSACCIÓN)
    // ====================================================
    public function guardarParteCompleto($cabecera, $lineas)
    {
        $this->conexion->begin_transaction();
        try {
            // 1. Insertamos la Cabecera del Parte
            $sqlCabecera = "INSERT INTO Partes (idEmpleado, fechaDesde, fechaHasta, observaciones, idUsuario, idEmpresa) 
                            VALUES (?, ?, ?, ?, ?, ?)";
            $stmtCabecera = $this->conexion->prepare($sqlCabecera);
            
            if (!$stmtCabecera) throw new Exception("Error preparando cabecera: " . $this->conexion->error);

            $stmtCabecera->bind_param(
                "isssii",
                $cabecera['idEmpleado'],
                $cabecera['fechaDesde'],
                $cabecera['fechaHasta'],
                $cabecera['observaciones'],
                $cabecera['idUsuario'],
                $cabecera['idEmpresa']
            );
            
            if (!$stmtCabecera->execute()) throw new Exception("Error insertando cabecera: " . $stmtCabecera->error);

            $idParte = $this->conexion->insert_id;

            // 2. Insertamos las Líneas del Parte
            if (!empty($lineas)) {
                $sqlLinea = "INSERT INTO lineasPartes (idParte, horaDesde, horaHasta, idCliente, idCentro, categoriaProfesional, vehiculoUtilizado, observaciones, idUsuario, idEmpresa) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmtLinea = $this->conexion->prepare($sqlLinea);
                
                if (!$stmtLinea) throw new Exception("Error preparando líneas: " . $this->conexion->error);

                foreach ($lineas as $linea) {
                    $vehiculo = !empty($linea['vehiculoUtilizado']) ? $linea['vehiculoUtilizado'] : null;
                    $observacionesLinea = !empty($linea['observaciones']) ? $linea['observaciones'] : null;
                    
                    // Tipos: idParte(i), horaDesde(s), horaHasta(s), idCliente(i), idCentro(i), categoriaProfesional(s), vehiculoUtilizado(s), observaciones(s), idUsuario(i), idEmpresa(i)
                    $stmtLinea->bind_param(
                        "issiisssii",
                        $idParte,
                        $linea['horaDesde'],
                        $linea['horaHasta'],
                        $linea['idCliente'],
                        $linea['idCentro'],
                        $linea['categoriaProfesional'], // Usamos la descripción textual
                        $vehiculo,
                        $observacionesLinea,
                        $cabecera['idUsuario'],
                        $cabecera['idEmpresa']
                    );
                    
                    if (!$stmtLinea->execute()) throw new Exception("Error insertando línea: " . $stmtLinea->error);
                }
            }

            $this->conexion->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conexion->rollback();
            return $e->getMessage();
        }
    }

    // ====================================================
    // OBTENER UN PARTE Y SUS LÍNEAS POR ID
    // ====================================================
    public function obtenerPorId($idParte, $idEmpresa)
    {
        $sql = "SELECT p.*, CONCAT(e.nombre, ' ', e.apellido1) as nombreEmpleado 
                FROM Partes p
                LEFT JOIN Empleados e ON p.idEmpleado = e.id
                WHERE p.id = ? AND p.idEmpresa = ?";

        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $idParte, $idEmpresa);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }
        return null;
    }

    public function obtenerLineas($idParte, $idEmpresa)
    {
        // Unimos para tener nombreCliente y nombreCentro y poder pintarlos en la edición
        $sql = "SELECT l.*, c.razonSocial as nombreCliente, ce.direccion as nombreCentro 
                FROM lineasPartes l
                LEFT JOIN Clientes c ON l.idCliente = c.id
                LEFT JOIN CentrosCliente ce ON l.idCentro = ce.id
                WHERE l.idParte = ? AND l.idEmpresa = ?";

        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $idParte, $idEmpresa);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    // ====================================================
    // ACTUALIZAR PARTE (TRANSACCIÓN)
    // ====================================================
    public function actualizarParteCompleto($idParte, $cabecera, $lineas)
    {
        $this->conexion->begin_transaction();
        try {
            // 1. Actualizamos la Cabecera
            $sqlCabecera = "UPDATE Partes 
                            SET idEmpleado = ?, fechaDesde = ?, fechaHasta = ?, observaciones = ? 
                            WHERE id = ? AND idEmpresa = ?";
            $stmtCabecera = $this->conexion->prepare($sqlCabecera);
            
            if (!$stmtCabecera) throw new Exception("Error preparando update cabecera: " . $this->conexion->error);

            $stmtCabecera->bind_param(
                "isssii",
                $cabecera['idEmpleado'],
                $cabecera['fechaDesde'],
                $cabecera['fechaHasta'],
                $cabecera['observaciones'],
                $idParte,
                $cabecera['idEmpresa']
            );
            
            if (!$stmtCabecera->execute()) throw new Exception("Error actualizando cabecera: " . $stmtCabecera->error);

            // 2. Borramos las líneas antiguas
            $sqlDelete = "DELETE FROM lineasPartes WHERE idParte = ? AND idEmpresa = ?";
            $stmtDelete = $this->conexion->prepare($sqlDelete);
            $stmtDelete->bind_param("ii", $idParte, $cabecera['idEmpresa']);
            if (!$stmtDelete->execute()) throw new Exception("Error borrando líneas antiguas: " . $stmtDelete->error);

            // 3. Insertamos las líneas nuevas/modificadas
            if (!empty($lineas)) {
                $sqlLinea = "INSERT INTO lineasPartes (idParte, horaDesde, horaHasta, idCliente, idCentro, categoriaProfesional, vehiculoUtilizado, observaciones, idUsuario, idEmpresa) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmtLinea = $this->conexion->prepare($sqlLinea);

                foreach ($lineas as $linea) {
                    $vehiculo = !empty($linea['vehiculoUtilizado']) ? $linea['vehiculoUtilizado'] : null;
                    $observacionesLinea = !empty($linea['observaciones']) ? $linea['observaciones'] : null;

                    $stmtLinea->bind_param(
                        "issiisssii",
                        $idParte,
                        $linea['horaDesde'],
                        $linea['horaHasta'],
                        $linea['idCliente'],
                        $linea['idCentro'],
                        $linea['categoriaProfesional'],
                        $vehiculo,
                        $observacionesLinea,
                        $cabecera['idUsuario'],
                        $cabecera['idEmpresa']
                    );
                    
                    if (!$stmtLinea->execute()) throw new Exception("Error insertando nueva línea: " . $stmtLinea->error);
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
    // ELIMINAR PARTE COMPLETO (ESTRICTO)
    // ==========================================
    public function eliminar($idParte, $idEmpresa) {
        $this->conexion->begin_transaction();
        try {
            // 1. Borramos primero las líneas dependientes
            $sqlDeleteLineas = "DELETE FROM lineasPartes WHERE idParte = ? AND idEmpresa = ?";
            $stmtLineas = $this->conexion->prepare($sqlDeleteLineas);
            if (!$stmtLineas) throw new Exception("Error preparando borrado de líneas: " . $this->conexion->error);
            
            $stmtLineas->bind_param("ii", $idParte, $idEmpresa);
            if (!$stmtLineas->execute()) throw new Exception("Error ejecutando borrado de líneas: " . $stmtLineas->error);

            // 2. Borramos la cabecera del parte
            $sqlDeleteCabecera = "DELETE FROM Partes WHERE id = ? AND idEmpresa = ?";
            $stmtCabecera = $this->conexion->prepare($sqlDeleteCabecera);
            if (!$stmtCabecera) throw new Exception("Error preparando borrado de cabecera: " . $this->conexion->error);

            $stmtCabecera->bind_param("ii", $idParte, $idEmpresa);
            if (!$stmtCabecera->execute()) throw new Exception("Error ejecutando borrado de cabecera: " . $stmtCabecera->error);

            $this->conexion->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conexion->rollback();
            return $e->getMessage();
        }
    }
}