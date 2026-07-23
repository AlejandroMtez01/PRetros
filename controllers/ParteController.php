<?php
require_once '../models/Empleado.php';
require_once '../models/Parte.php';

class ParteController
{
    private $conexion;
    private $modeloEmpleado;
    private $modeloParte;

    public function __construct(mysqli $conexion)
    {
        $this->conexion = $conexion;
        $this->modeloEmpleado = new Empleado($conexion);
        $this->modeloParte = new Parte($conexion);
    }

    // ==========================================
    // LISTADO DE PARTES CON FILTROS
    // ==========================================
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;

        $filtros = [
            'idParte'    => $_GET['idParte'] ?? '',
            'idEmpleado' => $_GET['idEmpleado'] ?? '',
            'fechaDesde' => $_GET['fechaDesde'] ?? '',
            'fechaHasta' => $_GET['fechaHasta'] ?? ''
        ];

        $empleados = $this->modeloEmpleado->obtenerTodos($idEmpresaActiva);
        $partes = $this->modeloParte->obtenerTodosFiltrados($idEmpresaActiva, $filtros);

        $contenido_vista = '../views/partes/index.php';
        require_once '../views/layout/master.php';
    }

    // ==========================================
    // VISTA PARA CREAR UN PARTE NUEVO
    // ==========================================
    public function crear()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;

        $empleados = $this->modeloEmpleado->obtenerTodos($idEmpresaActiva);
        $clientes = $this->obtenerClientes($idEmpresaActiva);
        $vehiculos = $this->obtenerVehiculos($idEmpresaActiva);

        $sqlPuestos = "SELECT id, descripcion FROM Puestos WHERE idEmpresa = ? ORDER BY descripcion ASC";
        $stmtP = $this->conexion->prepare($sqlPuestos);
        if ($stmtP) {
            $stmtP->bind_param("i", $idEmpresaActiva);
            $stmtP->execute();
            $puestos = $stmtP->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $puestos = [];
        }

        // Recuperar datos si hubo un error de validación
        $parte = [];
        $lineas = [];
        $erroresLineas = $_SESSION['errores_lineas'] ?? [];

        if (isset($_SESSION['datos_pendientes'])) {
            $datosPost = $_SESSION['datos_pendientes'];
            $parte['idEmpleado'] = $datosPost['idEmpleado'] ?? '';
            $parte['nombreEmpleado'] = $datosPost['nombreEmpleado'] ?? '';
            $parte['fechaDesde'] = $datosPost['fechaDesde'] ?? '';
            $parte['fechaHasta'] = $datosPost['fechaDesde'] ?? '';
            $parte['observaciones'] = $datosPost['observaciones'] ?? '';

            $lineas = $datosPost['lineas'] ?? [];
            unset($_SESSION['datos_pendientes'], $_SESSION['errores_lineas']);
        }

        $contenido_vista = '../views/partes/formulario.php';
        require_once '../views/layout/master.php';
    }

    // ==========================================
    // PROCESAR GUARDADO DEL PARTE (POST)
    // ==========================================
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;
            $idUsuarioActivo = $_SESSION['usuario_id'] ?? 0;

            $cabecera = [
                'idEmpleado'    => intval($_POST['idEmpleado']),
                'fechaDesde'    => $_POST['fechaDesde'],
                'fechaHasta'    => $_POST['fechaDesde'],
                'observaciones' => $_POST['observaciones'] ?? null,
                'idUsuario'     => $idUsuarioActivo,
                'idEmpresa'     => $idEmpresaActiva
            ];
            $lineas = isset($_POST['lineas']) ? $_POST['lineas'] : [];

            // Pasamos las líneas, el empleado, la fecha y un 0 (porque es nuevo)
            $erroresLineas = $this->validarLineas($lineas, $cabecera['idEmpleado'], $cabecera['fechaDesde'], 0);

            if (!empty($erroresLineas)) {
                $_SESSION['errores_lineas'] = $erroresLineas;
                $_SESSION['datos_pendientes'] = $_POST;
                $_SESSION['error_guardado'] = "Hay errores en las líneas. Por favor, revísalas.";
                header("Location: /index.php?controller=partes&action=crear");
                exit;
            }

            $resultado = $this->modeloParte->guardarParteCompleto($cabecera, $lineas);
            if ($resultado === true) {
                header("Location: /index.php?controller=partes");
            } else {
                $_SESSION['error_guardado'] = "Error BD: " . $resultado;
                $_SESSION['datos_pendientes'] = $_POST;
                header("Location: /index.php?controller=partes&action=crear");
            }
            exit;
        }
    }

    // ==========================================
    // VISTA DE SOLO LECTURA
    // ==========================================
    public function ver()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;
        $idParte = intval($_GET['id'] ?? 0);

        $parte = $this->modeloParte->obtenerPorId($idParte, $idEmpresaActiva);
        if (!$parte) {
            header("Location: /index.php?controller=partes");
            exit;
        }

        $lineas = $this->modeloParte->obtenerLineas($idParte, $idEmpresaActiva);

        $contenido_vista = '../views/partes/ver.php';
        require_once '../views/layout/master.php';
    }

    // ==========================================
    // VISTA PARA EDITAR
    // ==========================================
    public function editar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;
        $idParte = intval($_GET['id'] ?? 0);

        $parte = $this->modeloParte->obtenerPorId($idParte, $idEmpresaActiva);
        if (!$parte) {
            header("Location: /index.php?controller=partes");
            exit;
        }
        $lineas = $this->modeloParte->obtenerLineas($idParte, $idEmpresaActiva);

        $empleados = $this->modeloEmpleado->obtenerTodos($idEmpresaActiva);
        $clientes = $this->obtenerClientes($idEmpresaActiva);
        $vehiculos = $this->obtenerVehiculos($idEmpresaActiva);

        $sqlPuestos = "SELECT id, descripcion FROM Puestos WHERE idEmpresa = ? ORDER BY descripcion ASC";
        $stmtP = $this->conexion->prepare($sqlPuestos);
        if ($stmtP) {
            $stmtP->bind_param("i", $idEmpresaActiva);
            $stmtP->execute();
            $puestos = $stmtP->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $puestos = [];
        }

        // Recuperar si hubo error en edición
        $erroresLineas = $_SESSION['errores_lineas'] ?? [];
        if (isset($_SESSION['datos_pendientes'])) {
            $datosPost = $_SESSION['datos_pendientes'];
            $parte['idEmpleado'] = $datosPost['idEmpleado'] ?? $parte['idEmpleado'];
            $parte['nombreEmpleado'] = $datosPost['nombreEmpleado'] ?? $parte['nombreEmpleado'];
            $parte['fechaDesde'] = $datosPost['fechaDesde'] ?? $parte['fechaDesde'];
            $parte['fechaHasta'] = $datosPost['fechaDesde'] ?? $parte['fechaHasta'];
            $parte['observaciones'] = $datosPost['observaciones'] ?? $parte['observaciones'];

            $lineas = $datosPost['lineas'] ?? [];
            unset($_SESSION['datos_pendientes'], $_SESSION['errores_lineas']);
        }

        $contenido_vista = '../views/partes/formulario.php';
        require_once '../views/layout/master.php';
    }

    // ==========================================
    // PROCESAR ACTUALIZACIÓN (POST)
    // ==========================================
    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;
            $idUsuarioActivo = $_SESSION['usuario_id'] ?? 0;
            $idParte = intval($_POST['idParte']);

            $cabecera = [
                'idEmpleado'    => intval($_POST['idEmpleado']),
                'fechaDesde'    => $_POST['fechaDesde'],
                'fechaHasta'    => $_POST['fechaDesde'],
                'observaciones' => $_POST['observaciones'] ?? null,
                'idUsuario'     => $idUsuarioActivo,
                'idEmpresa'     => $idEmpresaActiva
            ];
            $lineas = isset($_POST['lineas']) ? $_POST['lineas'] : [];

            // Le pasamos el ID del parte actual para que lo excluya de la búsqueda en BD
            $erroresLineas = $this->validarLineas($lineas, $cabecera['idEmpleado'], $cabecera['fechaDesde'], $idParte);

            if (!empty($erroresLineas)) {
                $_SESSION['errores_lineas'] = $erroresLineas;
                $_SESSION['datos_pendientes'] = $_POST;
                $_SESSION['error_guardado'] = "Hay errores en las líneas. Por favor, revísalas.";
                header("Location: /index.php?controller=partes&action=editar&id=" . $idParte);
                exit;
            }

            $resultado = $this->modeloParte->actualizarParteCompleto($idParte, $cabecera, $lineas);

            if ($resultado === true) {
                header("Location: /index.php?controller=partes&action=ver&id=".$idParte);
            } else {
                $_SESSION['error_guardado'] = "Error BD: " . $resultado;
                $_SESSION['datos_pendientes'] = $_POST;
                header("Location: /index.php?controller=partes&action=editar&id=" . $idParte);
            }
            exit;
        }
    }

    // ==========================================
    // ELIMINAR PARTE
    // ==========================================
    public function eliminar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;
        $idParte = intval($_GET['id'] ?? 0);

        if ($idParte > 0) {
            $resultado = $this->modeloParte->eliminar($idParte, $idEmpresaActiva);
            if ($resultado !== true) {
                $_SESSION['error_guardado'] = "Error al eliminar el parte: " . $resultado;
            }
        }

        header("Location: /index.php?controller=partes");
        exit;
    }

    // ==========================================
    // MÉTODO EXTERNALIZADO DE VALIDACIÓN
    // ==========================================
    // ==========================================
    // MÉTODO EXTERNALIZADO DE VALIDACIÓN (CON CONTROL DE SOLAPAMIENTO)
    // ==========================================
    private function validarLineas($lineas, $idEmpleado, $fecha, $idParteActual = 0)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;

        $erroresLineas = [];
        $tramosEnMemoria = []; // Controla los horarios dentro del propio formulario para el empleado actual



        foreach ($lineas as $i => $l) {
            // 1. Validar Cliente y Centro
            if (empty($l['idCliente']) || empty($l['idCentro'])) {
                $erroresLineas[$i] = "Cliente y Centro son obligatorios.";
                continue;
            }

            // 2. Validación de Horario Básica
            if (empty($l['horaDesde']) || empty($l['horaHasta'])) {
                $erroresLineas[$i] = "Las horas de inicio y fin son obligatorias.";
                continue;
            }
            if (strtotime($l['horaHasta']) <= strtotime($l['horaDesde'])) {
                $erroresLineas[$i] = "La hora 'Hasta' debe ser mayor a la hora 'Desde'.";
                continue;
            }

            // 3. Validar Categoría
            if (empty($l['categoriaProfesional'])) {
                $erroresLineas[$i] = "Debe seleccionar una Categoría o Puesto.";
                continue;
            }

            // 4. Validación Maquinistas
            if (strtolower(trim($l['categoriaProfesional'])) === 'maquinista' && empty($l['vehiculoUtilizado'])) {
                $erroresLineas[$i] = "Un maquinista debe tener un vehículo asociado obligatoriamente.";
                continue;
            }

            // 5. MÓDULO DE VALIDACIÓN DE SOLAPAMIENTOS

            // Consulta preparada para comprobar si el empleado ya está en otro parte en ese tramo
            // Lógica de solapamiento: (InicioExistente < FinNuevo) AND (FinExistente > InicioNuevo)
            $sqlSolapamiento = "SELECT p.id 
                            FROM lineasPartes lp 
                            INNER JOIN Partes p ON lp.idParte = p.id 
                            WHERE p.idEmpleado = ? 
                            AND p.fechaDesde = ? 
                            AND p.idEmpresa = ? 
                            AND lp.horaDesde < ? 
                            AND lp.horaHasta > ? 
                            AND p.id != ?";

            $stmtSolapamiento = $this->conexion->prepare($sqlSolapamiento);

            $horaDesdeStr = substr($l['horaDesde'], 0, 5); // Normalizamos formato a HH:MM
            $horaHastaStr = substr($l['horaHasta'], 0, 5);
            $horaDesdeTs = strtotime($horaDesdeStr);
            $horaHastaTs = strtotime($horaHastaStr);

            // 5.1 Solapamiento en memoria (dentro del mismo formulario que estás rellenando)
            $solapamientoInterno = false;
            foreach ($tramosEnMemoria as $tramo) {
                if ($horaDesdeTs < $tramo['hasta'] && $horaHastaTs > $tramo['desde']) {
                    $erroresLineas[$i] = "Error: Este horario se solapa con otra línea que has añadido en este mismo parte.";
                    $solapamientoInterno = true;
                    break;
                }
            }
            if ($solapamientoInterno) {
                continue; // Pasamos a validar la siguiente línea
            }

            // Guardamos el tramo actual en memoria por si el usuario añade otra línea más abajo
            $tramosEnMemoria[] = ['desde' => $horaDesdeTs, 'hasta' => $horaHastaTs];

            // 5.2 Solapamiento en Base de Datos (partes antiguos ya guardados)
            if ($stmtSolapamiento && !empty($idEmpleado)) {
                // Pasamos: idEmpleado, fechaDesde, idEmpresa, horaHastaNueva, horaDesdeNueva, idParteActual
                $stmtSolapamiento->bind_param(
                    "isissi",
                    $idEmpleado,
                    $fecha,
                    $idEmpresaActiva,
                    $horaHastaStr,
                    $horaDesdeStr,
                    $idParteActual
                );

                $stmtSolapamiento->execute();
                $resultadoSolapamiento = $stmtSolapamiento->get_result();

                if ($resultadoSolapamiento->num_rows > 0) {
                    $parteConflicto = $resultadoSolapamiento->fetch_assoc();
                    $erroresLineas[$i] = "El empleado ya está asignado en ese horario en el Parte #" . htmlspecialchars($parteConflicto['id']) . ".";
                    continue;
                }
            }
        }

        return $erroresLineas;
    }

    // ==========================================
    // MÉTODOS AUXILIARES DE CONSULTA INTERNA
    // ==========================================
    private function obtenerClientes($idEmpresa)
    {
        $sql = "SELECT id, razonSocial as denominacion FROM Clientes WHERE idEmpresa = ? ORDER BY razonSocial ASC";
        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $idEmpresa);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    private function obtenerVehiculos($idEmpresa)
    {
        $sql = "SELECT denominacion, datos_dinamicos 
                FROM Inventario 
                WHERE idEmpresa = ? 
                AND datos_dinamicos LIKE '%\"precio_hora\"%' 
                ORDER BY denominacion ASC";

        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $idEmpresa);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }
}
