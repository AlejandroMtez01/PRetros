<?php
class Albaran {
    private $conexion;

    public function __construct(mysqli $conexion) {
        $this->conexion = $conexion;
    }

    // ====================================================
    // OBTENER ALBARANES (CON FILTROS ENCADENADOS)
    // ====================================================
    public function obtenerTodosFiltrados($idEmpresa, $filtros) {
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
    public function guardarAlbaranCompleto($cabecera, $lineas) {
        // Iniciamos transacción para que no se guarde el albarán si fallan las líneas
        $this->conexion->begin_transaction();

        try {
            // 1. Insertamos la Cabecera del Albarán
            $sqlCabecera = "INSERT INTO Albaranes (numAlbaran, idCliente, observaciones, idCentro, fecha, idUsuario, idEmpresa) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmtCabecera = $this->conexion->prepare($sqlCabecera);
            $stmtCabecera->bind_param("sisssii", 
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
                    $stmtLinea->bind_param("iisssssii", 
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
            return false;
        }
    }
}
?>