<?php
require_once '../models/Usuario.php';

class LoginController
{
    private $modelo;

    // Constructor: Recibe la conexión a la base de datos
    public function __construct(mysqli $conexion)
    {
        $this->modelo = new Usuario($conexion);
    }

    // Mostrar la pantalla de Login
    public function index()
    {
        if (isset($_SESSION['usuario_id'])) {
            header("Location: /index.php?controller=cliente");
            exit;
        }
        require_once '../views/login/index.php';
    }

    // Procesar el formulario de Login
    // Procesar el formulario de Login
    public function autenticar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $passwordIntento = trim($_POST['contraseña'] ?? ''); 

            $usuario = $this->modelo->obtenerPorEmail($email);

            if ($usuario && password_verify($passwordIntento, $usuario['contraseña'])) {
                
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido1'];
                
                // Pedimos las empresas del usuario a la tabla intermedia
                $empresas = $this->modelo->obtenerEmpresasDelUsuario($usuario['id']);
                
                if (!empty($empresas)) {
                    $_SESSION['empresas_usuario'] = $empresas;
                    
                    // Como ya no hay 'idEmpresa' en el perfil del usuario, 
                    // asignamos la primera empresa del array como la activa por defecto al iniciar sesión.
                    $_SESSION['idEmpresa'] = $empresas[0]['id'];
                    $_SESSION['nombreEmpresaActiva'] = $empresas[0]['nombre'];
                } else {
                    // Si un usuario no tiene empresas asignadas (por error de BD), lo gestionamos
                    $_SESSION['idEmpresa'] = 0;
                    $_SESSION['nombreEmpresaActiva'] = 'Sin Empresa Asignada';
                    $_SESSION['empresas_usuario'] = [];
                }

                header("Location: /index.php?controller=empleado");
                exit;
            } else {
                $error = "Credenciales incorrectas.";
                require_once '../views/login/index.php';
            }
        }
    }

    // Procesar el registro de un nuevo usuario
    // Añade un método en tu modelo (Usuario.php) si no lo tienes, para obtener todas:
    // SELECT id, denominacion FROM Empresas ORDER BY denominacion ASC
    
    public function registrar() {
        // Obtenemos todas las empresas para llenar el <select>
        $empresas = $this->modelo->obtenerTodasLasEmpresas();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // ... (tu código actual de validación y hash) ...

            if ($this->modelo->crearUsuario($datos, $idEmpresaInicial)) {
                header("Location: /index.php?controller=login&action=index");
                exit;
            } else {
                $error = "Hubo un error al registrar el usuario.";
                // Aquí ya está disponible $empresas para el select
                require_once '../views/login/registro.php';
            }
        } else {
            // Cuando carga la pantalla por primera vez
            require_once '../views/login/registro.php';
        }
    }

    // Cerrar sesión
    public function salir()
    {
        session_start();
        session_unset();
        session_destroy();

        header("Location: /index.php?controller=login");
        exit;
    }

    // Procesar el cambio de empresa desde el panel lateral
    public function cambiar_empresa() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva_empresa_id'])) {
            $nueva_id = $_POST['nueva_empresa_id'];
            $acceso_valido = false;
            $nombre_nueva_empresa = '';
            
            // Validamos que el usuario tiene acceso a esa empresa
            if (isset($_SESSION['empresas_usuario'])) {
                foreach ($_SESSION['empresas_usuario'] as $empresa) {
                    if ($empresa['id'] == $nueva_id) {
                        $acceso_valido = true;
                        $nombre_nueva_empresa = $empresa['nombre'];
                        break;
                    }
                }
            }

            // Si es válido, actualizamos las variables de sesión
            if ($acceso_valido) {
                $_SESSION['idEmpresa'] = $nueva_id;
                $_SESSION['nombreEmpresaActiva'] = $nombre_nueva_empresa;
            }

            // Redirigimos a la página donde estaba el usuario
            $origen = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/index.php';
            header("Location: " . $origen);
            exit;
        }
    }
}

