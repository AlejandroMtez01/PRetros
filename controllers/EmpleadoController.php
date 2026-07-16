<?php
require_once '../models/Empleado.php';

class EmpleadoController {
    private $modelo;

    public function __construct(mysqli $conexion) {
        $this->modelo = new Empleado($conexion);
    }

    // 1. Mostrar Listado
    public function index() {
        $empleados = $this->modelo->obtenerTodos();
        $contenido_vista = '../views/empleados/index.php';
        require_once '../views/layout/master.php';
    }

    // 2. Mostrar Formulario Vacío (ALTA)
    public function crear() {
        $empleado = null; // No hay datos porque es un alta nueva
        $titulo_formulario = "Alta de Nuevo Empleado";
        
        // A dónde enviará los datos el formulario al pulsar 'Guardar'
        $accion_url = "?controller=empleado&action=guardar";
        
        $contenido_vista = '../views/empleados/form.php';
        require_once '../views/layout/master.php';
    }

    // 3. Recibir los datos del formulario e insertar en la BD
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Empaquetamos los datos que vienen del formulario HTML
            $datos = [
                'nombre'    => trim($_POST['nombre']),
                'apellido1' => trim($_POST['apellido1']),
                'apellido2' => trim($_POST['apellido2'] ?? null),
                'DNI'       => trim($_POST['DNI']),
                'numSS'     => trim($_POST['numSS']),
                'fechaAlta' => $_POST['fechaAlta'],
                
                // NOTA: En un entorno real, estos dos IDs se cogen de $_SESSION
                'idUsuario' => $_SESSION['usuario_id'], 
                'idEmpresa' => $_SESSION['idEmpresa']  
            ];

            // Se los pasamos al modelo para que haga el INSERT
            $this->modelo->crearEmpleado($datos);

            // Redirigimos de vuelta a la tabla principal
            header("Location: /?controller=empleado&action=index");
            exit;
        }
    }
    // 4. Mostrar Formulario Lleno (MODIFICACIÓN)
    public function editar($id) {
        // Buscamos los datos actuales del empleado en la BD
        $empleado = $this->modelo->obtenerPorId($id);
        
        if (!$empleado) {
            die("Error: El empleado que intentas editar no existe.");
        }

        $titulo_formulario = "Modificar Datos del Empleado";
        
        // Fíjate que la URL ahora apunta a 'actualizar' y le pasa el ID
        $accion_url = "/PRetros/public/index.php?controller=empleado&action=actualizar&id=" . $id;
        
        $contenido_vista = '../views/empleados/form.php';
        require_once '../views/layout/master.php';
    }

    // 5. Recibir los datos modificados y hacer el UPDATE en la BD
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $datos = [
                'nombre'    => trim($_POST['nombre']),
                'apellido1' => trim($_POST['apellido1']),
                'apellido2' => !empty($_POST['apellido2']) ? trim($_POST['apellido2']) : null,
                'DNI'       => trim($_POST['DNI']),
                'numSS'     => trim($_POST['numSS']),
                'fechaAlta' => $_POST['fechaAlta'],
                'fechaBaja' => !empty($_POST['fechaBaja']) ? $_POST['fechaBaja'] : null,
                
                // Actualizamos el idUsuario para saber quién fue el último en modificarlo
                'idUsuario' => $_SESSION['usuario_id']
            ];

            // Pasamos el ID y los nuevos datos al modelo
            $this->modelo->actualizarEmpleado($id, $datos);

            // Redirigimos al directorio principal
            header("Location: /PRetros/public/index.php?controller=empleado&action=index");
            exit;
        }
    }
    // 6. Eliminar un empleado de la BD
    public function eliminar($id) {
        // Nos aseguramos de que nos llega un ID antes de intentar borrar
        if (!empty($id)) {
            $this->modelo->eliminarEmpleado($id);
        }

        // Una vez borrado (o si no había ID), redirigimos de vuelta a la tabla
        header("Location: /PRetros/public/index.php?controller=empleado&action=index");
        exit;
    }
}
?>