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
    public function eliminar_cabecera() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        if (!isset($_GET['codigo'])) {
            header("Location: /index.php?controller=tabla&action=index");
            exit;
        }

        $codigo = $_GET['codigo'];
        $idEmpresa = $_SESSION['idEmpresa'];

        // 1. Comprobamos si está asignada en el Catálogo de Inventario
        $dependencias = $this->modelo->comprobarDependenciasCatalogo($codigo, $idEmpresa);
        
        if (!empty($dependencias)) {
            // Si hay dependencias, construimos el mensaje de error con los sitios exactos
            $nombres_catalogos = [];
            foreach ($dependencias as $dep) {
                $nombres_catalogos[] = "<strong>" . htmlspecialchars($dep['nombre_tipo']) . " (" . htmlspecialchars($dep['prefijo']) . ")</strong>";
            }
            
            $mensaje = "No se puede eliminar la tabla auxiliar <strong>" . htmlspecialchars($codigo) . "</strong> porque está vinculada actualmente a los siguientes catálogos: " . implode(", ", $nombres_catalogos) . ". <br><br>Debes desvincularla en el catálogo de inventario antes de proceder.";
            
            $_SESSION['error_eliminar'] = $mensaje;
        } else {
            // 2. Si está libre de dependencias, la eliminamos
            $resultado = $this->modelo->eliminarCabecera($codigo, $idEmpresa);
            
            if ($resultado !== true) {
                $_SESSION['error_eliminar'] = "Error interno de Base de Datos al eliminar: " . $resultado;
            }
        }

        header("Location: /index.php?controller=tabla&action=index");
        exit;
    }
}
?>