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
    public function guardarAlbaranCompleto($cabecera, $lineas)
    {

       



        // Iniciamos transacción para que no se guarde el albarán si fallan las líneas
        $this->conexion->begin_transaction();

        try {
            // 1. Insertamos la Cabecera del Albarán
            $sqlCabecera = "INSERT INTO Albaranes (numAlbaran, idCliente, observaciones, idCentro, fecha, idUsuario, idEmpresa) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmtCabecera = $this->conexion->prepare($sqlCabecera);
            $stmtCabecera->bind_param(
                "sisssii",
                $cabecera['numAlbaran'],
                $cabecera['idCliente'],
                $cabecera['observaciones'],
                $cabecera['idCentro'],
                $cabecera['fecha'],
                $cabecera['idUsuario'],
                $cabecera['idEmpresa']
            );
            $stmtCabecera->execute();

            // Recuperamos el ID del albarán recién creado
            $idAlbaran = $this->conexion->insert_id;

            // 2. Insertamos las Líneas del Albarán
            if (!empty($lineas)) {
                $sqlLinea = "INSERT INTO lineasAlbaran (idAlbaran, idEmpleado, horaDesde, horaHasta, categoriaProfesional, vehiculoUtilizado, importe, idUsuario, idEmpresa) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmtLinea = $this->conexion->prepare($sqlLinea);

                foreach ($lineas as $linea) {
                    // Validamos los datos opcionales (por si el empleado no es maquinista o no hay importe)
                    $vehiculo = !empty($linea['vehiculoUtilizado']) ? $linea['vehiculoUtilizado'] : null;
                    $importe = !empty($linea['importe']) ? $linea['importe'] : null;

                    // "iissssdsii" -> Integer, Integer, String, String, String, Integer/Null, Double/Null, Integer, Integer
                    $stmtLinea->bind_param(
                        "iisssssii",
                        $idAlbaran,
                        $linea['idEmpleado'],
                        $linea['horaDesde'],
                        $linea['horaHasta'],
                        $linea['categoriaProfesional'],
                        $vehiculo,
                        $importe,
                        $cabecera['idUsuario'],
                        $cabecera['idEmpresa']
                    );
                    $stmtLinea->execute();
                }
            }

            // Confirmamos que todo ha ido bien
            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            // Si algo falla, deshacemos todos los cambios en la base de datos
            $this->conexion->rollback();
            error_log("Error al guardar el albarán completo: " . $e->getMessage());
            return $e->getMessage();
        }
    }

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
    // ACTUALIZAR ALBARÁN (TRANSACCIÓN)
    // ====================================================
    public function actualizarAlbaranCompleto($idAlbaran, $cabecera, $lineas)
    {


        $this->conexion->begin_transaction();
        try {
            // 1. Actualizamos la Cabecera
            $sqlCabecera = "UPDATE Albaranes 
                            SET idCliente = ?, observaciones = ?, idCentro = ?, fecha = ? 
                            WHERE id = ? AND idEmpresa = ?";

            $stmtCabecera = $this->conexion->prepare($sqlCabecera);
            $stmtCabecera->bind_param(
                "isssii",
                $cabecera['idCliente'],
                $cabecera['observaciones'],
                $cabecera['idCentro'],
                $cabecera['fecha'],
                $idAlbaran,
                $cabecera['idEmpresa']
            );
            $stmtCabecera->execute();

            // 2. Borramos las líneas antiguas
            $sqlDelete = "DELETE FROM lineasAlbaran WHERE idAlbaran = ? AND idEmpresa = ?";
            $stmtDelete = $this->conexion->prepare($sqlDelete);
            $stmtDelete->bind_param("ii", $idAlbaran, $cabecera['idEmpresa']);
            $stmtDelete->execute();

            // 3. Insertamos las líneas nuevas/modificadas
            if (!empty($lineas)) {
                $sqlLinea = "INSERT INTO lineasAlbaran (idAlbaran, idEmpleado, horaDesde, horaHasta, categoriaProfesional, vehiculoUtilizado, importe, idUsuario, idEmpresa) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmtLinea = $this->conexion->prepare($sqlLinea);

                foreach ($lineas as $linea) {
                    $vehiculo = !empty($linea['vehiculoUtilizado']) ? $linea['vehiculoUtilizado'] : null;
                    $importe = !empty($linea['importe']) ? $linea['importe'] : null;

                    $stmtLinea->bind_param(
                        "iisssssii",
                        $idAlbaran,
                        $linea['idEmpleado'],
                        $linea['horaDesde'],
                        $linea['horaHasta'],
                        $linea['categoriaProfesional'],
                        $vehiculo,
                        $importe,
                        $cabecera['idUsuario'],
                        $cabecera['idEmpresa']
                    );
                    $stmtLinea->execute();
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
} // <-- Fin de la clase Albaran

