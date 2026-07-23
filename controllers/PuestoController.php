<?php
require_once '../models/Puesto.php';

class PuestoController {
    private $conexion;
    private $modeloPuesto;

    public function __construct(mysqli $conexion) {
        $this->conexion = $conexion;
        $this->modeloPuesto = new Puesto($conexion);
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;

        $puestos = $this->modeloPuesto->obtenerTodos($idEmpresaActiva);

        $contenido_vista = '../views/puestos/index.php';
        require_once '../views/layout/master.php'; 
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;

            $datos = [
                'id' => isset($_POST['id']) ? intval($_POST['id']) : 0,
                'descripcion' => trim($_POST['descripcion']),
                'precioHora' => empty($_POST['precioHora']) ? 0.00 : floatval($_POST['precioHora']),
                'idEmpresa' => $idEmpresaActiva
            ];

            $resultado = $this->modeloPuesto->guardar($datos);

            if ($resultado === true) {
                header("Location: /index.php?controller=puesto");
            } else {
                $_SESSION['error_guardado'] = $resultado; 
                header("Location: /index.php?controller=puesto");
            }
            exit;
        }
    }

    public function eliminar() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;
        $id = intval($_GET['id'] ?? 0);

        if ($id > 0) {
            // 1. Recuperamos el puesto para saber su descripción exacta
            $puesto = $this->modeloPuesto->obtenerPorId($id, $idEmpresaActiva);

            if ($puesto) {
                // 2. Comprobamos si el puesto está en uso
                $enUso = $this->modeloPuesto->comprobarDependencias($puesto['descripcion'], $idEmpresaActiva);

                if ($enUso) {
                    $_SESSION['error_guardado'] = "No se puede eliminar el puesto <strong>" . htmlspecialchars($puesto['descripcion']) . "</strong> porque ya está asignado a uno o más Albaranes / Partes de Trabajo.";
                } else {
                    // 3. Si no está en uso, eliminamos
                    $resultado = $this->modeloPuesto->eliminar($id, $idEmpresaActiva);
                    if ($resultado !== true) {
                        $_SESSION['error_guardado'] = "Error al eliminar: " . $resultado;
                    }
                }
            }
        }
        
        header("Location: /index.php?controller=puesto");
        exit;
    }
}
?>