<?php
require_once '../models/Usuario.php';

class LoginController {
    private $modelo;

    // Constructor: Recibe la conexión a la base de datos
    public function __construct(mysqli $conexion) {
        $this->modelo = new Usuario($conexion);
    }

    // Mostrar la pantalla de Login
    public function index() {
        if (isset($_SESSION['usuario_id'])) {
            header("Location: /index.php?controller=cliente");
            exit;
        }
        require_once '../views/login/index.php';
    }

    // Procesar el formulario de Login
    public function autenticar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            // Asegúrate de que el input de tu HTML tenga name="contraseña"
            $passwordIntento = trim($_POST['contraseña'] ?? ''); 

            $usuario = $this->modelo->obtenerPorEmail($email);

            // Verificamos el hash guardado en la columna 'contraseña'
            if ($usuario && password_verify($passwordIntento, $usuario['contraseña'])) {
                
                session_start();
                $_SESSION['usuario_id'] = $usuario['id'];
                // Concatenamos el nombre y el primer apellido para el panel operativo
                $_SESSION['usuario_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido1'];
                $_SESSION['idEmpresa'] = $usuario['idEmpresa']; 

                header("Location: index.php/?controller=empleado");
                exit;
            } else {
                $error = "Credenciales incorrectas.";
                require_once '../views/login/index.php';
            }
        }
    }

    // Procesar el registro de un nuevo usuario
    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Recogemos la contraseña plana y la encriptamos (hash)
            $passwordPlana = $_POST['contraseña'];
            $passwordHasheada = password_hash($passwordPlana, PASSWORD_DEFAULT);

            // Mapeo exacto a las columnas de tu imagen
            $datos = [
                'nombre'     => trim($_POST['nombre']),
                'apellido1'  => trim($_POST['apellido1']),
                'apellido2'  => !empty($_POST['apellido2']) ? trim($_POST['apellido2']) : null,
                'email'      => trim($_POST['email']),
                'contraseña' => $passwordHasheada,
                'idEmpresa'  => intval($_POST['idEmpresa'] ?? 1) 
            ];

            if ($this->modelo->crearUsuario($datos)) {
                header("Location: /?controller=login&action=index");
                exit;
            } else {
                $error = "Hubo un error al registrar el usuario.";
                require_once '../views/login/registro.php';
            }
        } else {
            require_once '../views/login/registro.php';
        }
    }

    // Cerrar sesión
    public function salir() {
        session_start();
        session_unset();
        session_destroy();
        
        header("Location: /?controller=login");
        exit;
    }
}
?>