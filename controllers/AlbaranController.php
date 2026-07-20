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
    // LISTADO DE ALBARANES CON FILTROS
    // ==========================================
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;

        // Recogemos los filtros de la URL si existen
        $filtros = [
            'numAlbaran' => $_GET['numAlbaran'] ?? '',
            'idCliente'  => $_GET['idCliente'] ?? '',
            'idCentro'   => $_GET['idCentro'] ?? '',
            'fechaDesde' => $_GET['fechaDesde'] ?? '',
            'fechaHasta' => $_GET['fechaHasta'] ?? ''
        ];

        // Listas para el buscador
        $clientes = $this->obtenerClientes($idEmpresaActiva);
        $centros = $this->obtenerCentros($idEmpresaActiva);

        // Albaranes filtrados
        $albaranes = $this->modeloAlbaran->obtenerTodosFiltrados($idEmpresaActiva, $filtros);

        $contenido_vista = '../views/albaranes/index.php';
        require_once '../views/layout/master.php';
    }

    // ==========================================
    // VISTA PARA CREAR UN ALBARÁN NUEVO
    // ==========================================
     public function crear() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;

        // Necesitamos estos datos para llenar los modales
        $empleados = $this->modeloEmpleado->obtenerTodos($idEmpresaActiva);
        $clientes = $this->obtenerClientes($idEmpresaActiva);
        $vehiculos_precio_hora = $this->obtenerVehiculosPrecioHora($idEmpresaActiva);

        // EXTRACCIÓN DE PUESTOS PARA EL MODAL
        $sqlPuestos = "SELECT id, descripcion, precioHora FROM Puestos WHERE idEmpresa = ? ORDER BY descripcion ASC";
        $stmtP = $this->conexion->prepare($sqlPuestos);
        if ($stmtP) {
            $stmtP->bind_param("i", $idEmpresaActiva);
            $stmtP->execute();
            $puestos = $stmtP->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $puestos = [];
        }

        $contenido_vista = '../views/albaranes/formulario.php';
        require_once '../views/layout/master.php';
    }

    // ==========================================
    // PROCESAR GUARDADO DEL ALBARÁN (POST)
    // ==========================================
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;
            $idUsuarioActivo = $_SESSION['usuario_id'] ?? 0;

            // Generamos un número de albarán automático (Ejemplo: ALB-20260717-101119)
            // Puedes cambiar esto si en tu empresa llevan otra numeración
            //$numAlbaranAutogenerado = 'ALB-' . date('Ymd-His');

            // Preparamos los datos de la cabecera
            $cabecera = [
                'numAlbaran'    => $_POST['numAlbaran'],
                'idCliente'     => intval($_POST['idCliente']),
                'idCentro'      => intval($_POST['idCentro']),
                'fecha'         => $_POST['fecha'],
                'observaciones' => $_POST['observaciones'] ?? null,
                'idUsuario'     => $idUsuarioActivo,
                'idEmpresa'     => $idEmpresaActiva
            ];

            // Recogemos el array de líneas que envió JavaScript. Si no hay, asignamos array vacío.
            $lineas = isset($_POST['lineas']) ? $_POST['lineas'] : [];

            // Mandamos todo al modelo para que haga la transacción
            $resultado = $this->modeloAlbaran->guardarAlbaranCompleto($cabecera, $lineas);
            if ($resultado === true) {
                header("Location: /index.php?controller=albaran");
            } else {
                session_start();
                $_SESSION['error_guardado'] = $resultado; // "Cantamos" el error exacto
                header("Location: /index.php?controller=albaran&action=crear");
            }
        }
    }

    // ==========================================
    // MÉTODOS AUXILIARES DE CONSULTA INTERNA
    // ==========================================
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
        // Asegúrate de que el ID llega por GET
        $idCliente = $_GET['idCliente'] ?? 0;

        $sql = "SELECT id, direccion FROM CentrosCliente WHERE idCliente = ? ORDER BY direccion ASC";
        $stmt = $this->conexion->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $idCliente);
            $stmt->execute();
            $resultado = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Es vital enviar esto como JSON para que el fetch lo entienda
            header('Content-Type: application/json');
            echo json_encode($resultado);
            exit;
        }
        echo json_encode([]);
        exit;
    }

    private function obtenerVehiculosPrecioHora($idEmpresa) {
        // Añadimos prefijo_tipo y datos_dinamicos a la consulta
        $sql = "SELECT prefijo_tipo, denominacion,  datos_dinamicos 
                FROM Inventario 
                WHERE idEmpresa = ? 
                AND datos_dinamicos LIKE '%\"precio_hora\"%' 
                ORDER BY denominacion ASC";
                
        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $idEmpresa);
            $stmt->execute();
            $vehiculos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            // Decodificamos el JSON para sacar el precio_hora fácilmente a la vista
            foreach ($vehiculos as &$veh) {
                $datosJson = json_decode($veh['datos_dinamicos'], true);
                $veh['precio_hora_extraido'] = isset($datosJson['precio_hora']) ? $datosJson['precio_hora'] : 0;
            }
            
            return $vehiculos;
        }
        return [];
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
        $idAlbaran = intval($_GET['id'] ?? 0);

        $albaran = $this->modeloAlbaran->obtenerPorId($idAlbaran, $idEmpresaActiva);
        if (!$albaran) {
            header("Location: /index.php?controller=albaran");
            exit;
        }

        $lineas = $this->modeloAlbaran->obtenerLineas($idAlbaran, $idEmpresaActiva);

        $contenido_vista = '../views/albaranes/ver.php';
        require_once '../views/layout/master.php';
    }

    // ==========================================
    // VISTA PARA EDITAR
    // ==========================================
   // ==========================================
    // VISTA PARA CREAR UN ALBARÁN NUEVO
    // ==========================================
   

    // ==========================================
    // VISTA PARA EDITAR
    // ==========================================
    public function editar() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;
        $idAlbaran = intval($_GET['id'] ?? 0);

        // Rescatamos los datos actuales
        $albaran = $this->modeloAlbaran->obtenerPorId($idAlbaran, $idEmpresaActiva);
        if (!$albaran) {
            header("Location: /index.php?controller=albaran");
            exit;
        }
        $lineas = $this->modeloAlbaran->obtenerLineas($idAlbaran, $idEmpresaActiva);

        // Listas auxiliares para los modales
        $empleados = $this->modeloEmpleado->obtenerTodos($idEmpresaActiva);
        $clientes = $this->obtenerClientes($idEmpresaActiva);
        $vehiculos_precio_hora = $this->obtenerVehiculosPrecioHora($idEmpresaActiva);

        // Cargamos los centros específicos del cliente seleccionado actualmente
        $sqlCentros = "SELECT id, direccion as denominacion FROM CentrosCliente WHERE idCliente = ?";
        $stmtC = $this->conexion->prepare($sqlCentros);
        if ($stmtC) {
            $stmtC->bind_param("i", $albaran['idCliente']);
            $stmtC->execute();
            $centros_actuales = $stmtC->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $centros_actuales = [];
        }

        // EXTRACCIÓN DE PUESTOS PARA EL MODAL
        $sqlPuestos = "SELECT id, descripcion, precioHora FROM Puestos WHERE idEmpresa = ? ORDER BY descripcion ASC";
        $stmtP = $this->conexion->prepare($sqlPuestos);
        if ($stmtP) {
            $stmtP->bind_param("i", $idEmpresaActiva);
            $stmtP->execute();
            $puestos = $stmtP->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $puestos = [];
        }

        $contenido_vista = '../views/albaranes/formulario.php';
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

            $resultado = $this->modeloAlbaran->actualizarAlbaranCompleto($idAlbaran, $cabecera, $lineas);

            if ($resultado === true) {
                header("Location: /index.php?controller=albaran");
            } else {
                $_SESSION['error_guardado'] = $resultado;
                header("Location: /index.php?controller=albaran&action=editar&id=" . $idAlbaran);
            }
            exit;
        }
    }
}
