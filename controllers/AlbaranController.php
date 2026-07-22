<?php
require_once '../models/Empleado.php';
require_once '../models/Albaran.php';

class AlbaranController
{
    private $conexion;
    private $modeloEmpleado;
    private $modeloAlbaran;

    public function __construct(mysqli $conexion)
    {
        $this->conexion = $conexion;
        $this->modeloEmpleado = new Empleado($conexion);
        $this->modeloAlbaran = new Albaran($conexion);
    }

    // ==========================================
    // LISTADO DE ALBARANES
    // ==========================================
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;

        $filtros = [
            'numAlbaran' => $_GET['numAlbaran'] ?? '',
            'idCliente'  => $_GET['idCliente'] ?? '',
            'idCentro'   => $_GET['idCentro'] ?? '',
            'fechaDesde' => $_GET['fechaDesde'] ?? '',
            'fechaHasta' => $_GET['fechaHasta'] ?? ''
        ];

        $clientes = $this->obtenerClientes($idEmpresaActiva);
        $centros = $this->obtenerCentros($idEmpresaActiva);
        $albaranes = $this->modeloAlbaran->obtenerTodosFiltrados($idEmpresaActiva, $filtros);

        $contenido_vista = '../views/albaranes/index.php';
        require_once '../views/layout/master.php';
    }

    // ==========================================
    // CREAR ALBARÁN
    // ==========================================
    public function crear()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;

        $empleados = $this->modeloEmpleado->obtenerTodos($idEmpresaActiva);
        $clientes = $this->obtenerClientes($idEmpresaActiva);
        $vehiculos_precio_hora = $this->obtenerVehiculosPrecioHora($idEmpresaActiva);
        
        // Cargar Catálogo de Materiales (Filtro MATER)
        $catalogoMateriales = $this->obtenerCatalogoMateriales($idEmpresaActiva);

        $sqlPuestos = "SELECT id, descripcion, precioHora FROM Puestos WHERE idEmpresa = ? ORDER BY descripcion ASC";
        $stmtP = $this->conexion->prepare($sqlPuestos);
        if ($stmtP) {
            $stmtP->bind_param("i", $idEmpresaActiva);
            $stmtP->execute();
            $puestos = $stmtP->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $puestos = [];
        }

        // RECUPERAR DATOS SI HUBO UN ERROR
        $albaran = [];
        $lineas = [];
        $materiales = [];
        $erroresLineas = $_SESSION['errores_lineas'] ?? [];

        if (isset($_SESSION['datos_pendientes'])) {
            $datosPost = $_SESSION['datos_pendientes'];
            $albaran['numAlbaran'] = $datosPost['numAlbaran'] ?? '';
            $albaran['fecha'] = $datosPost['fecha'] ?? '';
            $albaran['idCliente'] = $datosPost['idCliente'] ?? '';
            $albaran['idCentro'] = $datosPost['idCentro'] ?? '';
            $albaran['observaciones'] = $datosPost['observaciones'] ?? '';
            $albaran['nombreCliente'] = $datosPost['nombreCliente'] ?? '';
            $albaran['nombreCentro'] = $datosPost['nombreCentro'] ?? '';

            $lineas = $datosPost['lineas'] ?? [];
            $materiales = $datosPost['materiales'] ?? [];
            unset($_SESSION['datos_pendientes'], $_SESSION['errores_lineas']);
        }

        $contenido_vista = '../views/albaranes/formulario.php';
        require_once '../views/layout/master.php';
    }

    // ==========================================
    // GUARDAR ALBARÁN
    // ==========================================
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;
            $idUsuarioActivo = $_SESSION['usuario_id'] ?? 0;

            $cabecera = [
                'numAlbaran'    => $_POST['numAlbaran'],
                'idCliente'     => intval($_POST['idCliente']),
                'idCentro'      => intval($_POST['idCentro']),
                'fecha'         => $_POST['fecha'],
                'observaciones' => $_POST['observaciones'] ?? null,
                'idUsuario'     => $idUsuarioActivo,
                'idEmpresa'     => $idEmpresaActiva
            ];
            
            $lineas = isset($_POST['lineas']) ? $_POST['lineas'] : [];
            $materiales = isset($_POST['materiales']) ? $_POST['materiales'] : [];

            $erroresLineas = $this->validarLineas($lineas, $_POST['fecha'], 0);

            if (!empty($erroresLineas)) {
                $_SESSION['errores_lineas'] = $erroresLineas;
                $_SESSION['datos_pendientes'] = $_POST;
                $_SESSION['error_guardado'] = "Hay errores en las líneas. Por favor, revísalas.";
                header("Location: /index.php?controller=albaran&action=crear");
                exit;
            }

            // Guardar albarán completo, devuelve el ID del nuevo registro
            $idNuevoAlbaran = $this->modeloAlbaran->guardarAlbaranCompleto($cabecera, $lineas, $materiales);
            
            if (is_numeric($idNuevoAlbaran)) {
                // Redirigir a la vista de detalles
                header("Location: /index.php?controller=albaran&action=ver&id=" . $idNuevoAlbaran);
            } else {
                $_SESSION['error_guardado'] = "Error BD: " . $idNuevoAlbaran;
                $_SESSION['datos_pendientes'] = $_POST;
                header("Location: /index.php?controller=albaran&action=crear");
            }
            exit;
        }
    }

    // ==========================================
    // VISTA DE SOLO LECTURA
    // ==========================================
    public function ver()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;
        $idAlbaran = intval($_GET['id'] ?? 0);

        $albaran = $this->modeloAlbaran->obtenerPorId($idAlbaran, $idEmpresaActiva);
        if (!$albaran) {
            header("Location: /index.php?controller=albaran");
            exit;
        }

        $lineas = $this->modeloAlbaran->obtenerLineas($idAlbaran, $idEmpresaActiva);
        $materiales = $this->modeloAlbaran->obtenerMateriales($idAlbaran, $idEmpresaActiva);

        $contenido_vista = '../views/albaranes/ver.php';
        require_once '../views/layout/master.php';
    }

    // ==========================================
    // EDITAR ALBARÁN
    // ==========================================
    public function editar()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;
        $idAlbaran = intval($_GET['id'] ?? 0);

        $albaran = $this->modeloAlbaran->obtenerPorId($idAlbaran, $idEmpresaActiva);
        if (!$albaran) {
            header("Location: /index.php?controller=albaran");
            exit;
        }
        
        $lineas = $this->modeloAlbaran->obtenerLineas($idAlbaran, $idEmpresaActiva);
        $materiales = $this->modeloAlbaran->obtenerMateriales($idAlbaran, $idEmpresaActiva);

        $empleados = $this->modeloEmpleado->obtenerTodos($idEmpresaActiva);
        $clientes = $this->obtenerClientes($idEmpresaActiva);
        $vehiculos_precio_hora = $this->obtenerVehiculosPrecioHora($idEmpresaActiva);
        
        // Cargar Catálogo de Materiales (Filtro MATER)
        $catalogoMateriales = $this->obtenerCatalogoMateriales($idEmpresaActiva);

        $sqlCentros = "SELECT id, direccion as denominacion FROM CentrosCliente WHERE idCliente = ?";
        $stmtC = $this->conexion->prepare($sqlCentros);
        if ($stmtC) {
            $stmtC->bind_param("i", $albaran['idCliente']);
            $stmtC->execute();
            $centros_actuales = $stmtC->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $centros_actuales = [];
        }

        $sqlPuestos = "SELECT id, descripcion, precioHora FROM Puestos WHERE idEmpresa = ? ORDER BY descripcion ASC";
        $stmtP = $this->conexion->prepare($sqlPuestos);
        if ($stmtP) {
            $stmtP->bind_param("i", $idEmpresaActiva);
            $stmtP->execute();
            $puestos = $stmtP->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $puestos = [];
        }

        // RECUPERAR DATOS SI HUBO UN ERROR
        $erroresLineas = $_SESSION['errores_lineas'] ?? [];
        if (isset($_SESSION['datos_pendientes'])) {
            $datosPost = $_SESSION['datos_pendientes'];
            $albaran['numAlbaran'] = $datosPost['numAlbaran'] ?? $albaran['numAlbaran'];
            $albaran['fecha'] = $datosPost['fecha'] ?? $albaran['fecha'];
            $albaran['idCliente'] = $datosPost['idCliente'] ?? $albaran['idCliente'];
            $albaran['idCentro'] = $datosPost['idCentro'] ?? $albaran['idCentro'];
            $albaran['observaciones'] = $datosPost['observaciones'] ?? $albaran['observaciones'];
            $albaran['nombreCliente'] = $datosPost['nombreCliente'] ?? $albaran['nombreCliente'];
            $albaran['nombreCentro'] = $datosPost['nombreCentro'] ?? $albaran['nombreCentro'];

            $lineas = $datosPost['lineas'] ?? [];
            $materiales = $datosPost['materiales'] ?? [];
            unset($_SESSION['datos_pendientes'], $_SESSION['errores_lineas']);
        }

        $contenido_vista = '../views/albaranes/formulario.php';
        require_once '../views/layout/master.php';
    }

    // ==========================================
    // ACTUALIZAR ALBARÁN
    // ==========================================
    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) { session_start(); }

            $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;
            $idUsuarioActivo = $_SESSION['usuario_id'] ?? 0;
            $idAlbaran = intval($_POST['idAlbaran']);

            $cabecera = [
                'idCliente'     => intval($_POST['idCliente']),
                'idCentro'      => intval($_POST['idCentro']),
                'fecha'         => $_POST['fecha'],
                'observaciones' => $_POST['observaciones'] ?? null,
                'idUsuario'     => $idUsuarioActivo,
                'idEmpresa'     => $idEmpresaActiva
            ];
            
            $lineas = isset($_POST['lineas']) ? $_POST['lineas'] : [];
            $materiales = isset($_POST['materiales']) ? $_POST['materiales'] : [];

            $erroresLineas = $this->validarLineas($lineas, $_POST['fecha'], $idAlbaran);

            if (!empty($erroresLineas)) {
                $_SESSION['errores_lineas'] = $erroresLineas;
                $_SESSION['datos_pendientes'] = $_POST;
                $_SESSION['error_guardado'] = "Hay errores en las líneas. Por favor, revísalas.";
                header("Location: /index.php?controller=albaran&action=editar&id=" . $idAlbaran);
                exit;
            }

            $resultado = $this->modeloAlbaran->actualizarAlbaranCompleto($idAlbaran, $cabecera, $lineas, $materiales);

            if ($resultado === true) {
                // Redirigir a la vista de detalles
                header("Location: /index.php?controller=albaran&action=ver&id=" . $idAlbaran);
            } else {
                $_SESSION['error_guardado'] = "Error BD: " . $resultado;
                $_SESSION['datos_pendientes'] = $_POST;
                header("Location: /index.php?controller=albaran&action=editar&id=" . $idAlbaran);
            }
            exit;
        }
    }

    public function eliminar()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;
        
        $idAlbaran = intval($_GET['id'] ?? 0);

        if ($idAlbaran > 0) {
            $resultado = $this->modeloAlbaran->eliminar($idAlbaran, $idEmpresaActiva);
            if ($resultado !== true) {
                $_SESSION['error_guardado'] = "Error al eliminar el albarán: " . $resultado;
            }
        }
        
        header("Location: /index.php?controller=albaran");
        exit;
    }

    // ==========================================
    // MÉTODOS AUXILIARES DE CONSULTA
    // ==========================================
    private function validarLineas($lineas, $fecha, $idAlbaranActual = 0)
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;
        $erroresLineas = [];
        $empleadosEnMemoria = []; 

        foreach ($lineas as $i => $l) {
            if (empty($l['horaDesde']) || empty($l['horaHasta'])) {
                $erroresLineas[$i] = "Las horas de inicio y fin son obligatorias."; continue;
            }
            if (strtotime($l['horaHasta']) <= strtotime($l['horaDesde'])) {
                $erroresLineas[$i] = "La hora 'Hasta' debe ser mayor a la hora 'Desde'."; continue;
            }
            if (empty($l['categoriaProfesional'])) {
                $erroresLineas[$i] = "Debe seleccionar una Categoría o Puesto."; continue;
            }
            if (strtolower(trim($l['categoriaProfesional'])) === 'maquinista' && empty($l['vehiculoUtilizado'])) {
                $erroresLineas[$i] = "Un maquinista debe tener un vehículo asociado obligatoriamente."; continue;
            }
            if (!isset($l['importe']) || !is_numeric($l['importe']) || $l['importe'] < 0) {
                $erroresLineas[$i] = "El importe debe ser numérico y mayor o igual a 0."; continue;
            }

            $idEmp = $l['idEmpleado'];
            $horaDesdeStr = substr($l['horaDesde'], 0, 5);
            $horaHastaStr = substr($l['horaHasta'], 0, 5);
            $horaDesdeTs = strtotime($horaDesdeStr);
            $horaHastaTs = strtotime($horaHastaStr);

            $sqlSolapamiento = "SELECT a.numAlbaran FROM lineasAlbaran la INNER JOIN Albaranes a ON la.idAlbaran = a.id WHERE la.idEmpleado = ? AND a.fecha = ? AND a.idEmpresa = ? AND la.horaDesde < ? AND la.horaHasta > ? AND a.id != ?";
            $stmtSolapamiento = $this->conexion->prepare($sqlSolapamiento);

            $solapamientoInterno = false;
            if (isset($empleadosEnMemoria[$idEmp])) {
                foreach ($empleadosEnMemoria[$idEmp] as $tramo) {
                    if ($horaDesdeTs < $tramo['hasta'] && $horaHastaTs > $tramo['desde']) {
                        $erroresLineas[$i] = "Horario solapado con otra línea de este mismo albarán.";
                        $solapamientoInterno = true; break;
                    }
                }
            }
            if ($solapamientoInterno) continue;

            $empleadosEnMemoria[$idEmp][] = ['desde' => $horaDesdeTs, 'hasta' => $horaHastaTs];

            if ($stmtSolapamiento && !empty($idEmp)) {
                $stmtSolapamiento->bind_param("isissi", $idEmp, $fecha, $idEmpresaActiva, $horaHastaStr, $horaDesdeStr, $idAlbaranActual);
                $stmtSolapamiento->execute();
                $resultadoSolapamiento = $stmtSolapamiento->get_result();

                if ($resultadoSolapamiento->num_rows > 0) {
                    $albaranConflicto = $resultadoSolapamiento->fetch_assoc();
                    $erroresLineas[$i] = "El empleado ya está trabajando en ese horario (Albarán " . htmlspecialchars($albaranConflicto['numAlbaran']) . ").";
                    continue;
                }
            }
        }
        return $erroresLineas;
    }

    private function obtenerClientes($idEmpresa)
    {
        $sql = "SELECT id, razonSocial FROM Clientes WHERE idEmpresa = ? ORDER BY razonSocial ASC";
        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $idEmpresa);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    private function obtenerCentros($idEmpresa)
    {
        $sql = "SELECT id, direccion FROM CentrosCliente WHERE idEmpresa = ? ORDER BY direccion ASC";
        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $idEmpresa);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function obtenerCentrosPorCliente()
    {
        $idCliente = $_GET['idCliente'] ?? 0;
        $sql = "SELECT id, direccion FROM CentrosCliente WHERE idCliente = ? ORDER BY direccion ASC";
        $stmt = $this->conexion->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $idCliente);
            $stmt->execute();
            $resultado = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            header('Content-Type: application/json');
            echo json_encode($resultado);
            exit;
        }
        echo json_encode([]);
        exit;
    }

    private function obtenerVehiculosPrecioHora($idEmpresa)
    {
        $sql = "SELECT prefijo_tipo, denominacion, datos_dinamicos FROM Inventario WHERE idEmpresa = ? AND datos_dinamicos LIKE '%\"precio_hora\"%' ORDER BY denominacion ASC";
        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $idEmpresa);
            $stmt->execute();
            $vehiculos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            foreach ($vehiculos as &$veh) {
                $datosJson = json_decode($veh['datos_dinamicos'], true);
                $veh['precio_hora_extraido'] = isset($datosJson['precio_hora']) ? $datosJson['precio_hora'] : 0;
            }
            return $vehiculos;
        }
        return [];
    }

    // ==========================================
    // OBTENER CATÁLOGO DE MATERIALES (FILTRO MATER)
    // ==========================================
    private function obtenerCatalogoMateriales($idEmpresa)
    {
        $sql = "SELECT denominacion, datos_dinamicos FROM Inventario WHERE idEmpresa = ? AND prefijo_tipo = 'MATER' ORDER BY denominacion ASC";
        $stmt = $this->conexion->prepare($sql);
        $catalogo = [];
        
        if ($stmt) {
            $stmt->bind_param("i", $idEmpresa);
            $stmt->execute();
            $resultados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            foreach ($resultados as $item) {
                $datosJson = json_decode($item['datos_dinamicos'], true);
                if (!is_array($datosJson)) $datosJson = [];
                
                $nombre = $datosJson['Nombre'] ?? $datosJson['nombre'] ?? $item['denominacion'];
                $unidades = $datosJson['Unidad'] ?? $datosJson['unidad'] ?? $datosJson['Unidades'] ?? $datosJson['unidades'] ?? 0;
                $precio = $datosJson['Precio'] ?? $datosJson['precio'] ?? 0;
                
                $item['nombre_extraido'] = $nombre;
                $item['unidades_extraidas'] = $unidades;
                $item['precio_extraido'] = $precio;
                $catalogo[] = $item;
            }
        }
        return $catalogo;
    }
}