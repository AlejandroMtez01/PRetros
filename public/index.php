<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// 1. INICIAMOS LA SESIÓN LO PRIMERO DE TODO (Obligatorio para poder leer $_SESSION)
session_start();

$controladorSolicitado = $_GET['controller'] ?? 'login';
$accionSolicitada = $_GET['action'] ?? 'index';

// =================================================================
// 2. EL PORTERO: VALIDACIÓN ESTRICTA DE SEGURIDAD
// =================================================================
// Si el usuario no está intentando acceder al login o registrarse...
if ($controladorSolicitado !== 'login') {

    // Comprobamos que existan TODOS los datos obligatorios en su sesión
    if (
        !isset($_SESSION['usuario_id']) ||
        !isset($_SESSION['usuario_nombre']) ||
        !isset($_SESSION['idEmpresa'])
    ) {

        // Si le falta cualquiera de ellos, limpiamos restos y lo mandamos al login
        session_unset();
        session_destroy();
        header("Location: /index.php?controller=login&action=index");
        exit; // El exit es vital para que PHP deje de procesar la página
    }
}
// =================================================================


// 3. CONEXIÓN A BD Y CARGA DEL CONTROLADOR SOLICITADO
require_once '../config/database.php';

switch ($controladorSolicitado) {
    case 'login':
        require_once '../controllers/LoginController.php';
        $controller = new LoginController($conexion);
        break;

    case 'empleado':
        require_once '../controllers/EmpleadoController.php';
        $controller = new EmpleadoController($conexion);
        break;

    case 'cliente':
        require_once '../controllers/ClienteController.php';
        $controller = new ClienteController($conexion);
        break;

    case 'centro':
        require_once '../controllers/CentroController.php';
        $controller = new CentroController($conexion);
        break;

    case 'catalogo_inventario':
        require_once '../controllers/InventarioController.php';
        $controller = new InventarioController($conexion);
        break;

    case 'tabla':
        require_once '../controllers/TablaController.php'; // Apuntamos un nivel atrás
        $controller = new TablaController($conexion);
        break;
    case 'articulo':
        require_once '../controllers/ArticuloController.php';
        $controller = new ArticuloController($conexion);
        break;
    case 'albaran':
        require_once '../controllers/AlbaranController.php';
        $controller = new AlbaranController($conexion);
        break;

    case 'puesto':
        require_once '../controllers/PuestoController.php';
        $controller = new PuestoController($conexion);
        break;





        break;
    // (Aquí puedes añadir los case para cliente, vehiculo, proyecto, etc.)

    default:
        // ERROR: EL MÓDULO (CONTROLADOR) NO EXISTE
        http_response_code(404); // Enviamos el código 404 al navegador
        $contenido_vista = '../views/errores/404.php'; // Cargamos nuestra vista bonita
        require_once '../views/layout/master.php'; // La incrustamos en el diseño general
        exit;
}

// 4. EJECUCIÓN DE LA ACCIÓN CONTEMPLANDO PARÁMETROS
if (method_exists($controller, $accionSolicitada)) {

    // Si la URL trae un ID, se lo pasamos a la función (útil para editar/borrar)
    if (isset($_GET['id'])) {
        $controller->$accionSolicitada($_GET['id']);
    } else {
        $controller->$accionSolicitada();
    }
} else {
    // Si la acción no existe, mostramos el error personalizado
    // Si la acción no existe, mostramos el error personalizado
    http_response_code(404);
    $contenido_vista = '../views/errores/404.php';
    require_once '../views/layout/master.php';
    exit;
}
