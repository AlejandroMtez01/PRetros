<?php
require_once '../models/Inventario.php';

class InventarioController {
    private $modelo;
    private $conexion;

    public function __construct($conexion) {
        $this->modelo = new Inventario($conexion);
        $this->conexion=$conexion;
    }

    public function index() {
        $tipos = $this->modelo->obtenerTodos($_SESSION['idEmpresa']);
        $contenido_vista = '../views/inventario/index.php';
        require_once '../views/layout/master.php';
    }

    public function crear() {

        // 1. Cargamos el modelo de tablas para alimentar el modal
    require_once '../models/Tabla.php';
    $modeloTablas = new Tabla($this->conexion); // Asumiendo que $this->conexion es accesible
    $gtablas = $modeloTablas->obtenerCabeceras($_SESSION['idEmpresa']);

    $titulo_formulario = "Nuevo Tipo de Inventario";
    $accion_url = "/index.php?controller=catalogo_inventario&action=guardar";
    $es_edicion = false;
    
    $contenido_vista = '../views/inventario/form.php';
    require_once '../views/layout/master.php';



    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errores = [];

            // Limpiamos datos y forzamos el prefijo a mayúsculas para mantener un estándar
            $datos = [
                'prefijo'               => strtoupper(trim($_POST['prefijo'])),
                'nombre_tipo'           => trim($_POST['nombre_tipo']),
                'esquema_configuracion' => trim($_POST['esquema_configuracion']),
                'idEmpresa'             => $_SESSION['idEmpresa']
            ];

            // Validaciones
            if (empty($datos['prefijo'])) {
                $errores['prefijo'] = "El prefijo es obligatorio.";
            } elseif (strlen($datos['prefijo']) > 5) {
                $errores['prefijo'] = "El prefijo no puede tener más de 5 caracteres.";
            }

            if (empty($datos['nombre_tipo'])) {
                $errores['nombre_tipo'] = "El nombre del tipo es obligatorio.";
            }

            // Validar que el texto sea un JSON válido
            if (!empty($datos['esquema_configuracion'])) {
                json_decode($datos['esquema_configuracion']);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errores['esquema_configuracion'] = "El formato de configuración no es un JSON válido.";
                }
            } else {
                $datos['esquema_configuracion'] = '{}'; // JSON vacío por defecto
            }

            if (empty($errores)) {
                try {
                    $this->modelo->crearTipo($datos);
                    header("Location: /index.php?controller=catalogo_inventario&action=index");
                    exit;
                } catch (Exception $e) {
                    // Si el prefijo ya existe, MySQL dará error de duplicado (Primary Key)
                    $errores['prefijo'] = "Error: Es posible que este prefijo ya exista. (" . $e->getMessage() . ")";
                }
            }

            $titulo_formulario = "Nuevo Tipo de Inventario";
            $accion_url = "/index.php?controller=catalogo_inventario&action=guardar";
            $inventario = $datos; 
            $es_edicion = false;
            
            $contenido_vista = '../views/inventario/form.php';
            require_once '../views/layout/master.php';
        }
    }

   public function editar($prefijo) {
        $inventario = $this->modelo->obtenerPorPrefijo($prefijo, $_SESSION['idEmpresa']);
        if (!$inventario) {
            die("Error: El tipo de inventario no existe.");
        }
        
        // --- AÑADIMOS ESTO PARA QUE EL MODAL TENGA LAS TABLAS ---
        require_once '../models/Tabla.php';
        $modeloTablas = new Tabla($this->conexion);
        $gtablas = $modeloTablas->obtenerCabeceras($_SESSION['idEmpresa']);
        // --------------------------------------------------------

        $titulo_formulario = "Modificar Tipo de Inventario";
        $accion_url = "/index.php?controller=catalogo_inventario&action=actualizar&id=" . urlencode($prefijo);
        $es_edicion = true; // Flag para bloquear el input del prefijo
        
        $contenido_vista = '../views/inventario/form.php';
        require_once '../views/layout/master.php';
    }

   public function actualizar($prefijo_original) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errores = [];

            $datos = [
                'nombre_tipo'           => trim($_POST['nombre_tipo']),
                'esquema_configuracion' => trim($_POST['esquema_configuracion']),
                'idEmpresa'             => $_SESSION['idEmpresa']
            ];

            if (empty($datos['nombre_tipo'])) {
                $errores['nombre_tipo'] = "El nombre del tipo es obligatorio.";
            }

            if (!empty($datos['esquema_configuracion'])) {
                json_decode($datos['esquema_configuracion']);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errores['esquema_configuracion'] = "El formato de configuración no es un JSON válido.";
                }
            }

            if (empty($errores)) {
                try {
                    $this->modelo->actualizarTipo($prefijo_original, $datos);
                    header("Location: /index.php?controller=catalogo_inventario&action=index");
                    exit;
                } catch (Exception $e) {
                    $errores['general'] = "Error de BD: " . $e->getMessage();
                }
            }

            $titulo_formulario = "Modificar Tipo de Inventario";
            $accion_url = "/index.php?controller=catalogo_inventario&action=actualizar&id=" . urlencode($prefijo_original);
            
            // Reconstruimos los datos para la vista
            $inventario = $datos; 
            $inventario['prefijo'] = $prefijo_original;
            $es_edicion = true;
            
            // --- AÑADIMOS ESTO PARA QUE EL MODAL NO SE VACÍE SI HAY ERRORES ---
            require_once '../models/Tabla.php';
            $modeloTablas = new Tabla($this->conexion);
            $gtablas = $modeloTablas->obtenerCabeceras($_SESSION['idEmpresa']);
            // ------------------------------------------------------------------
            
            $contenido_vista = '../views/inventario/form.php';
            require_once '../views/layout/master.php';
        }
    }

    public function eliminar($prefijo) {
        $this->modelo->eliminarTipo($prefijo, $_SESSION['idEmpresa']);
        header("Location: /index.php?controller=catalogo_inventario&action=index");
        exit;
    }
}
?>