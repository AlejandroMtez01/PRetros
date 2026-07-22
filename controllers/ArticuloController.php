<?php
require_once '../models/Articulo.php';
require_once '../models/Inventario.php'; // Para leer el JSON del catálogo
require_once '../models/Tabla.php';      // Para leer los lookups

class ArticuloController {
    private $modelo;
    private $modeloCatalogo;
    private $modeloTablas;

    public function __construct($conexion) {
        $this->modelo = new Articulo($conexion);
        $this->modeloCatalogo = new Inventario($conexion);
        $this->modeloTablas = new Tabla($conexion);
    }

public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;

        // Extraemos los artículos
        $articulos = $this->modelo->obtenerTodos($idEmpresaActiva);
        
        // ¡CRUCIAL PARA EL LISTADO! Extraemos las opciones para traducir los códigos en la tabla
        $opciones_tablas = $this->modelo->obtenerTodasTablasLineas($idEmpresaActiva);

        $contenido_vista = '../views/articulos/index.php';
        require_once '../views/layout/master.php';
    }

public function ver()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Recogemos las claves primarias enviadas por la URL
        $prefijo = $_GET['prefijo'] ?? null;
        $denominacion = $_GET['denominacion'] ?? null;
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;

        // Validamos que no falte nada
        if (!$prefijo || !$denominacion) {
            die("Error: Faltan parámetros para mostrar el registro.");
        }

        // 2. Buscamos el artículo exacto en la base de datos
        // *Nota: Asegúrate de usar el nombre del método correcto que tengas en tu modelo
        $articulo = $this->modelo->obtenerPorId($prefijo, $denominacion, $idEmpresaActiva);

        if (!$articulo) {
            die("Error: El registro de inventario que intentas ver no existe.");
        }

        // 3. Obtenemos los diccionarios para que la vista pueda traducir COD (DESCRIPCION)
        // *Nota: Utiliza la misma función que usas en el método crear() o editar() de este controlador
        $opciones_tablas = $this->modelo->obtenerTodasTablasLineas($idEmpresaActiva);

        // 4. Cargamos la vista de la ficha
        $contenido_vista = '../views/articulos/ver.php';
        require_once '../views/layout/master.php';
    }

    // Método de 2 pasos para crear
    public function crear() {
        // PASO 1: Si no hemos elegido qué tipo de inventario vamos a crear
        if (!isset($_GET['prefijo'])) {
            $catalogos = $this->modeloCatalogo->obtenerTodos($_SESSION['idEmpresa']);
            $contenido_vista = '../views/articulos/seleccionar.php';
            require_once '../views/layout/master.php';
            return;
        }

        // PASO 2: Ya hemos elegido el tipo (Ej: COMB)
        $prefijo = $_GET['prefijo'];
        $catalogo = $this->modeloCatalogo->obtenerPorPrefijo($prefijo, $_SESSION['idEmpresa']);
        
        if (!$catalogo) {
            die("Error: El tipo de inventario seleccionado no existe.");
        }

        $esquema = json_decode($catalogo['esquema_configuracion'], true);
        $esquema = is_array($esquema) ? $esquema : [];
        
        // Cargamos los datos de los desplegables (Lookups)
        $opciones_tablas = $this->cargarLookups($esquema);

        $titulo_formulario = "Nuevo Registro: " . $catalogo['nombre_tipo'];
        $accion_url = "/index.php?controller=articulo&action=guardar&prefijo=" . $prefijo;
        $es_edicion = false;
        
        $contenido_vista = '../views/articulos/form.php';
        require_once '../views/layout/master.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['prefijo'])) {
            $errores = [];
            $prefijo = $_GET['prefijo'];
            
            $denominacion = trim($_POST['denominacion']);
            
            // Recogemos todo el array de campos dinámicos que viene del formulario
            $datos_dinamicos = isset($_POST['datos']) ? $_POST['datos'] : [];

            if (empty($denominacion)) {
                $errores['denominacion'] = "La denominación es obligatoria.";
            }

            if (empty($errores)) {
                $datos_a_guardar = [
                    'prefijo_tipo'    => $prefijo,
                    'denominacion'    => $denominacion,
                    'datos_dinamicos' => json_encode($datos_dinamicos, JSON_UNESCAPED_UNICODE),
                    'idEmpresa'       => $_SESSION['idEmpresa']
                ];

                try {
                    $this->modelo->crear($datos_a_guardar);
                    header("Location: /index.php?controller=articulo&action=index");
                    exit;
                } catch (Exception $e) {
                    $errores['general'] = "Error al guardar (¿Denominación duplicada?): " . $e->getMessage();
                }
            }

            // Si falla, volvemos a cargar el formulario
            $catalogo = $this->modeloCatalogo->obtenerPorPrefijo($prefijo, $_SESSION['idEmpresa']);
            $esquema = json_decode($catalogo['esquema_configuracion'], true);
            $opciones_tablas = $this->cargarLookups($esquema);
            
            $titulo_formulario = "Nuevo Registro: " . $catalogo['nombre_tipo'];
            $accion_url = "/index.php?controller=articulo&action=guardar&prefijo=" . $prefijo;
            $es_edicion = false;
            
            // Le devolvemos los datos para que no los pierda
            $articulo_temp = ['denominacion' => $denominacion, 'datos' => $datos_dinamicos];
            
            $contenido_vista = '../views/articulos/form.php';
            require_once '../views/layout/master.php';
        }
    }

    public function editar() {
        if (!isset($_GET['prefijo']) || !isset($_GET['denominacion'])) {
            die("Faltan parámetros.");
        }
        
        $prefijo = $_GET['prefijo'];
        $denominacion = $_GET['denominacion'];
        
        // Traemos el registro guardado
        $registro = $this->modelo->obtenerPorId($prefijo, $denominacion, $_SESSION['idEmpresa']);
        // Traemos el catálogo para saber cómo pintar el formulario
        $catalogo = $this->modeloCatalogo->obtenerPorPrefijo($prefijo, $_SESSION['idEmpresa']);
        
        $esquema = json_decode($catalogo['esquema_configuracion'], true);
        $opciones_tablas = $this->cargarLookups($esquema);

        $titulo_formulario = "Editar Registro: " . htmlspecialchars($denominacion);
        // Pasamos por URL las claves primarias
        $accion_url = "/index.php?controller=articulo&action=actualizar&prefijo=" . urlencode($prefijo) . "&denominacion=" . urlencode($denominacion);
        $es_edicion = true;
        
        // Preparamos los datos para inyectarlos en la vista
        $articulo_temp = [
            'denominacion' => $registro['denominacion'],
            'datos'        => json_decode($registro['datos_dinamicos'], true)
        ];
        
        $contenido_vista = '../views/articulos/form.php';
        require_once '../views/layout/master.php';
    }

    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $prefijo = $_GET['prefijo'];
            $denominacion_original = $_GET['denominacion']; // PK original
            
            $datos_dinamicos = isset($_POST['datos']) ? $_POST['datos'] : [];

            $datos_a_actualizar = [
                'datos_dinamicos' => json_encode($datos_dinamicos, JSON_UNESCAPED_UNICODE),
                'idEmpresa'       => $_SESSION['idEmpresa']
            ];

            $this->modelo->actualizar($prefijo, $denominacion_original, $datos_a_actualizar);
            header("Location: /index.php?controller=articulo&action=index");
            exit;
        }
    }

    public function eliminar() {
        $this->modelo->eliminar($_GET['prefijo'], $_GET['denominacion'], $_SESSION['idEmpresa']);
        header("Location: /index.php?controller=articulo&action=index");
        exit;
    }

    // --- FUNCIÓN PRIVADA DE APOYO ---
    // Recorre el esquema JSON y busca qué tablas hay que cargar en memoria para los desplegables
    private function cargarLookups($esquema) {
        $opciones = [];
        if (is_array($esquema)) {
            foreach ($esquema as $campo) {
                if ($campo['tipo_dato'] === 'lookup' && !empty($campo['tabla_ayuda'])) {
                    $codigo_tabla = $campo['tabla_ayuda'];
                    $opciones[$codigo_tabla] = $this->modeloTablas->obtenerLineas($codigo_tabla, $_SESSION['idEmpresa']);
                }
            }
        }
        return $opciones;
    }
}
?>