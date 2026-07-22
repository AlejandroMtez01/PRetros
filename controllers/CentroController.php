<?php
require_once '../models/Centro.php';
require_once '../models/Cliente.php'; 

class CentroController {
    private $modelo;
    private $modeloCliente;

    public function __construct($conexion) {
        $this->modelo = new Centro($conexion);
        $this->modeloCliente = new Cliente($conexion);
    }

    public function index() {
        if (!isset($_GET['idCliente'])) {
            die("Error: No se ha especificado el cliente.");
        }
        
        $idCliente = (int)$_GET['idCliente'];
        $cliente = $this->modeloCliente->obtenerPorId($idCliente);
        
        $centros = $this->modelo->obtenerPorCliente($idCliente, $_SESSION['idEmpresa']);
        $contenido_vista = '../views/centros/index.php';
        require_once '../views/layout/master.php';
    }

    public function crear() {
        if (!isset($_GET['idCliente'])) {
            die("Error: No se ha especificado el cliente.");
        }
        $idCliente = (int)$_GET['idCliente'];
        
        $titulo_formulario = "Nuevo Centro";
        $accion_url = "/index.php?controller=centro&action=guardar&idCliente=" . $idCliente;
        
        $contenido_vista = '../views/centros/form.php';
        require_once '../views/layout/master.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['idCliente'])) {
            $idCliente = (int)$_GET['idCliente'];
            $errores = [];

            $datos = [
                'idCliente' => $idCliente,
                'direccion' => ucfirst(trim($_POST['direccion'])),
                'poblado'   => ucfirst(trim($_POST['poblado'])),
                'idUsuario' => $_SESSION['usuario_id'],
                'idEmpresa' => $_SESSION['idEmpresa']
            ];

            if (empty($datos['direccion'])) {
                $errores['direccion'] = "La dirección es obligatoria.";
            }

            if (empty($errores)) {
                try {
                    $this->modelo->crearCentro($datos);
                    header("Location: /index.php?controller=centro&action=index&idCliente=" . $idCliente);
                    exit;
                } catch (Exception $e) {
                    $errores['general'] = "Error de BD: " . $e->getMessage();
                }
            }

            $titulo_formulario = "Nuevo Centro";
            $accion_url = "/index.php?controller=centro&action=guardar&idCliente=" . $idCliente;
            $centro = $datos; 
            
            $contenido_vista = '../views/centros/form.php';
            require_once '../views/layout/master.php';
        }
    }

    public function editar($id) {
        $centro = $this->modelo->obtenerPorId($id);
        if (!$centro) {
            die("Error: El centro no existe.");
        }
        
        $titulo_formulario = "Modificar Centro";
        $accion_url = "/index.php?controller=centro&action=actualizar&id=" . $id . "&idCliente=" . $centro['idCliente'];
        
        $contenido_vista = '../views/centros/form.php';
        require_once '../views/layout/master.php';
    }

    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['idCliente'])) {
            $idCliente = (int)$_GET['idCliente'];
            $errores = [];

            $datos = [
                'direccion' => ucfirst(trim($_POST['direccion'])),
                'poblado'   => ucfirst(trim($_POST['poblado'])),
                'idUsuario' => $_SESSION['usuario_id']
            ];

            if (empty($datos['direccion'])) {
                $errores['direccion'] = "La dirección es obligatoria.";
            }

            if (empty($errores)) {
                try {
                    $this->modelo->actualizarCentro($id, $datos);
                    header("Location: /index.php?controller=centro&action=index&idCliente=" . $idCliente);
                    exit;
                } catch (Exception $e) {
                    $errores['general'] = "Error de BD: " . $e->getMessage();
                }
            }

            $titulo_formulario = "Modificar Centro";
            $accion_url = "/index.php?controller=centro&action=actualizar&id=" . $id . "&idCliente=" . $idCliente;
            $centro = $datos; 
            
            $contenido_vista = '../views/centros/form.php';
            require_once '../views/layout/master.php';
        }
    }

    public function eliminar($id) {
        if (isset($_GET['idCliente'])) {
            $idCliente = (int)$_GET['idCliente'];
            $this->modelo->eliminarCentro($id);
            header("Location: /index.php?controller=centro&action=index&idCliente=" . $idCliente);
            exit;
        }
    }
}
?>