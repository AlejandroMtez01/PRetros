<?php
require_once '../models/Empleado.php';
require_once '../models/Albaran.php';

class AlbaranController {
    private $conexion;
    private $modeloEmpleado;
    private $modeloAlbaran;

    public function __construct(mysqli $conexion) {
        $this->conexion = $conexion;
        $this->modeloEmpleado = new Empleado($conexion);
        $this->modeloAlbaran = new Albaran($conexion);
    }

    // ==========================================
    // LISTADO DE ALBARANES CON FILTROS
    // ==========================================
    public function index() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
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

        // Necesitamos estos datos para llenar los selectores y el modal
        $empleados = $this->modeloEmpleado->obtenerTodos($idEmpresaActiva);
        $clientes = $this->obtenerClientes($idEmpresaActiva);
        $centros = $this->obtenerCentros($idEmpresaActiva);
        $vehiculos_precio_hora = $this->obtenerVehiculosPrecioHora($idEmpresaActiva);

        $contenido_vista = '../views/albaranes/crear.php';
        require_once '../views/layout/master.php';
    }

    // ==========================================
    // PROCESAR GUARDADO DEL ALBARÁN (POST)
    // ==========================================
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            
            $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;
            $idUsuarioActivo = $_SESSION['usuario_id'] ?? 0;

            // Generamos un número de albarán automático (Ejemplo: ALB-20260717-101119)
            // Puedes cambiar esto si en tu empresa llevan otra numeración
            $numAlbaranAutogenerado = 'ALB-' . date('Ymd-His');

            // Preparamos los datos de la cabecera
            $cabecera = [
                'numAlbaran'    => $numAlbaranAutogenerado,
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
            if ($this->modeloAlbaran->guardarAlbaranCompleto($cabecera, $lineas)) {
                // Si va bien, volvemos al listado principal de albaranes
                header("Location: /index.php?controller=albaran");
                exit;
            } else {
                // Si falla, podrías redirigir con un mensaje de error
                header("Location: /index.php?controller=albaran&action=crear&error=guardado");
                exit;
            }
        }
    }

    // ==========================================
    // MÉTODOS AUXILIARES DE CONSULTA INTERNA
    // ==========================================
    private function obtenerClientes($idEmpresa) {
        $sql = "SELECT id, razonSocial FROM Clientes WHERE idEmpresa = ? ORDER BY razonSocial ASC";
        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $idEmpresa);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    private function obtenerCentros($idEmpresa) {
        $sql = "SELECT id, direccion FROM CentrosCliente WHERE idEmpresa = ? ORDER BY direccion ASC";
        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $idEmpresa);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    private function obtenerVehiculosPrecioHora($idEmpresa) {
        $sql = "SELECT prefijo_tipo, denominacion 
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
?>