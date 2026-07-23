<?php
require_once '../models/Tabla.php';

class TablaController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new Tabla($conexion);
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $tablas = $this->modelo->obtenerCabeceras($_SESSION['idEmpresa']);
        $contenido_vista = '../views/tablas/index.php';
        require_once '../views/layout/master.php';
    }

    public function guardar_cabecera() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'codigo'      => strtoupper(trim($_POST['codigo'])),
                'descripcion' => trim($_POST['descripcion']),
                'idEmpresa'   => $_SESSION['idEmpresa']
            ];
            
            try {
                $this->modelo->crearCabecera($datos);
            } catch (Exception $e) {
                // Si ya existe, falla silenciosamente
            }
            header("Location: /index.php?controller=tabla&action=index");
            exit;
        }
    }

    public function lineas() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
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
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigoCabecera = $_POST['codigoCabecera'];
            $datos = [
                'codigoCabecera' => $codigoCabecera,
                'codigo'         => strtoupper(trim($_POST['codigo'])),
                'descripcion'    => trim($_POST['descripcion']),
                'idEmpresa'      => $_SESSION['idEmpresa']
            ];
            
            $this->modelo->crearLinea($datos);
            header("Location: /index.php?controller=tabla&action=lineas&codigo=" . urlencode($codigoCabecera));
            exit;
        }
    }

    public function eliminar_linea($id) {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (isset($_GET['codigo'])) {
            $codigoCabecera = $_GET['codigo'];
            $idEmpresa = $_SESSION['idEmpresa'];

            $linea = $this->modelo->obtenerLineaPorId($id, $idEmpresa);

            if ($linea) {
                $codigoLinea = $linea['codigo'];
                
                $enUso = $this->modelo->comprobarDependenciasLinea($codigoLinea, $idEmpresa);

                if ($enUso) {
                    $_SESSION['error_eliminar_linea'] = "No se puede eliminar el código <strong>" . htmlspecialchars($codigoLinea) . "</strong> porque ya está asignado a un elemento del Inventario o a un Documento.";
                } else {
                    $this->modelo->eliminarLinea($id, $idEmpresa);
                }
            }
            
            header("Location: /index.php?controller=tabla&action=lineas&codigo=" . urlencode($codigoCabecera));
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

        $dependenciasCatalogo = $this->modelo->comprobarDependenciasCatalogo($codigo, $idEmpresa);
        $enUsoDocumentos = $this->modelo->comprobarDependenciasCabeceraDocumentos($codigo, $idEmpresa);
        
        if (!empty($dependenciasCatalogo) || $enUsoDocumentos) {
            $mensaje = "No se puede eliminar la tabla <strong>" . htmlspecialchars($codigo) . "</strong> porque ";
            $motivos = [];
            
            if (!empty($dependenciasCatalogo)) {
                $nombres_catalogos = [];
                foreach ($dependenciasCatalogo as $dep) {
                    $nombres_catalogos[] = "<strong>" . htmlspecialchars($dep['nombre_tipo']) . "</strong>";
                }
                $motivos[] = "está vinculada al esquema del catálogo de inventario: " . implode(", ", $nombres_catalogos);
            }
            
            if ($enUsoDocumentos) {
                $motivos[] = "uno o más de sus valores internos ya están en uso (en el Inventario, Albaranes o Partes)";
            }
            
            $mensaje .= implode(" y ", $motivos) . ".";
            $_SESSION['error_eliminar'] = $mensaje;
            
        } else {
            $resultado = $this->modelo->eliminarCabecera($codigo, $idEmpresa);
            if ($resultado !== true) {
                $_SESSION['error_eliminar'] = "Error de BD al eliminar: " . $resultado;
            }
        }

        header("Location: /index.php?controller=tabla&action=index");
        exit;
    }
}
?>