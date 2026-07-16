<?php
require_once '../models/Tabla.php';

class TablaController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new Tabla($conexion);
    }

    public function index() {
        $tablas = $this->modelo->obtenerCabeceras($_SESSION['idEmpresa']);
        $contenido_vista = '../views/tablas/index.php';
        require_once '../views/layout/master.php';
    }

    public function guardar_cabecera() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'codigo'      => strtoupper(trim($_POST['codigo'])),
                'descripcion' => trim($_POST['descripcion']),
                'idEmpresa'   => $_SESSION['idEmpresa']
            ];
            
            try {
                $this->modelo->crearCabecera($datos);
            } catch (Exception $e) {
                // Si el código ya existe, fallará silenciosamente y volverá al index
                // En el futuro puedes añadir aquí el array de $errores si lo deseas
            }
            header("Location: /index.php?controller=tabla&action=index");
            exit;
        }
    }

    public function lineas() {
        if (!isset($_GET['codigo'])) {
            die("Error: Código de cabecera no proporcionado.");
        }
        
        $codigoCabecera = $_GET['codigo'];
        $cabecera = $this->modelo->obtenerCabecera($codigoCabecera, $_SESSION['idEmpresa']);
        $lineas = $this->modelo->obtenerLineas($codigoCabecera, $_SESSION['idEmpresa']);
        
        $contenido_vista = '../views/tablas/lineas.php';
        require_once '../views/layout/master.php';
    }

    public function guardar_linea() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigoCabecera = $_POST['codigoCabecera'];
            $datos = [
                'codigoCabecera' => $codigoCabecera,
                'codigo'         => strtoupper(trim($_POST['codigo'])), // Lo forzamos a mayúsculas
                'descripcion'    => trim($_POST['descripcion']),
                'idEmpresa'      => $_SESSION['idEmpresa']
            ];
            
            $this->modelo->crearLinea($datos);
            header("Location: /index.php?controller=tabla&action=lineas&codigo=" . urlencode($codigoCabecera));
            exit;
        }
    }

    public function eliminar_linea($id) {
        if (isset($_GET['codigo'])) {
            $this->modelo->eliminarLinea($id, $_SESSION['idEmpresa']);
            header("Location: /index.php?controller=tabla&action=lineas&codigo=" . urlencode($_GET['codigo']));
            exit;
        }
    }
}
?>