<?php
require_once '../models/Empleado.php';

class EmpleadoController
{
    private $modelo;

    public function __construct(mysqli $conexion)
    {
        $this->modelo = new Empleado($conexion);
    }

    // 1. Mostrar Listado
    public function index()
    {
        // 1. Aseguramos que la sesión está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Rescatamos la empresa activa en la que está el usuario
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;

        // 3. Pasamos esa empresa al modelo para que filtre la consulta SQL
        $empleados = $this->modelo->obtenerTodos($idEmpresaActiva);

        // 4. Cargamos tus vistas habituales...
        $contenido_vista = '../views/empleados/index.php';
        require_once '../views/layout/master.php'; // (O el nombre de tu plantilla base)
    }

    // 2. Mostrar Formulario Vacío (ALTA)
    public function crear()
    {
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
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Recogemos todos los datos del formulario
            $datos = [
                'nombre'    => $_POST['nombre'],
                'apellido1' => $_POST['apellido1'],
                'apellido2' => $_POST['apellido2'],
                'DNI'       => $_POST['DNI'],
                'numSS'     => $_POST['numSS'],
                'fechaAlta' => $_POST['fechaAlta'],
                'fechaBaja' => !empty($_POST['fechaBaja']) ? $_POST['fechaBaja'] : null,
                'idUsuario' => $_SESSION['usuario_id'], 
                
                // CRUCIAL: Le inyectamos la empresa activa actual
                'idEmpresa' => $_SESSION['idEmpresa'] 
            ];

            // Llamamos al modelo que actualizamos antes
            $this->modelo->crearEmpleado($datos);

            // Redirigimos al listado
            header("Location: /index.php?controller=empleado");
            exit;
        }
    }
    // 4. Mostrar Formulario Lleno (MODIFICACIÓN)
    public function editar($id)
    {
        // Buscamos los datos actuales del empleado en la BD
        $empleado = $this->modelo->obtenerPorId($id);

        if (!$empleado) {
            die("Error: El empleado que intentas editar no existe.");
        }

        $titulo_formulario = "Modificar Datos del Empleado";

        // Fíjate que la URL ahora apunta a 'actualizar' y le pasa el ID
        $accion_url = "/index.php?controller=empleado&action=actualizar&id=" . $id;

        $contenido_vista = '../views/empleados/form.php';
        require_once '../views/layout/master.php';
    }

    // 5. Recibir los datos modificados y hacer el UPDATE en la BD
    public function actualizar($id)
    {
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
            header("Location: /index.php?controller=empleado&action=index");
            exit;
        }
    }
    // 6. Eliminar un empleado de la BD
    public function eliminar($id)
    {
        // Nos aseguramos de que nos llega un ID antes de intentar borrar
        if (!empty($id)) {
            $this->modelo->eliminarEmpleado($id);
        }

        // Una vez borrado (o si no había ID), redirigimos de vuelta a la tabla
        header("Location: /index.php?controller=empleado&action=index");
        exit;
    }
    // ==========================================
    // VER HORARIOS Y SUMATORIOS DEL EMPLEADO
    // ==========================================
    // ==========================================
    // VER HORARIOS Y SUMATORIOS DEL EMPLEADO
    // ==========================================
    public function verHorarios($id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;

        // 1. Obtenemos los datos del empleado
        $empleado = $this->modelo->obtenerPorId($id);
        if (!$empleado) {
            die("Error: El empleado no existe.");
        }

        // 2. Recogemos los filtros de fecha si el usuario ha buscado algo
        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin = $_GET['fecha_fin'] ?? null;

        // 3. Extraemos las líneas pasando las fechas al modelo
        $lineas = $this->modelo->obtenerHorasEmpleado($id, $idEmpresaActiva, $fechaInicio, $fechaFin);

        // 4. Agrupamos las horas por día
        $dias_trabajados = [];
        
        foreach ($lineas as $linea) {
            $fecha = $linea['fecha'];
            
            if (!isset($dias_trabajados[$fecha])) {
                $dias_trabajados[$fecha] = [
                    'lineas' => [],
                    'minutos_totales' => 0
                ];
            }
            
            $dias_trabajados[$fecha]['lineas'][] = $linea;
            
            $inicio = strtotime($linea['horaDesde']);
            $fin = strtotime($linea['horaHasta']);
            
            if ($fin < $inicio) {
                $fin += 86400; 
            }
            
            $minutos = round(($fin - $inicio) / 60);
            $dias_trabajados[$fecha]['minutos_totales'] += $minutos;
        }

        // 5. Cargamos la vista pasando también las variables de fecha
        $contenido_vista = '../views/empleados/ver_horarios.php';
        require_once '../views/layout/master.php';
    }
}
