<?php
require_once '../models/Cliente.php';

class ClienteController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new Cliente($conexion);
    }

    public function index() {
        $clientes = $this->modelo->obtenerTodos($_SESSION['idEmpresa']);
        $contenido_vista = '../views/clientes/index.php';
        require_once '../views/layout/master.php';
    }

    public function crear() {
        $titulo_formulario = "Nuevo Cliente";
        $accion_url = "/index.php?controller=cliente&action=guardar";
        $contenido_vista = '../views/clientes/form.php';
        require_once '../views/layout/master.php';
    }

 public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            require_once '../helpers/Validador.php';
            
            // 1. Ahora los errores serán asociativos: 'id_del_campo' => 'Mensaje'
            $errores = []; 
            
            $datos = [
                'razonSocial' => trim($_POST['razonSocial']),
                'CIF'         => trim($_POST['CIF']),
                'sedeFiscal'  => trim($_POST['sedeFiscal']),
                'idUsuario'   => $_SESSION['usuario_id'],
                'idEmpresa'   => $_SESSION['idEmpresa']
            ];
            
            // 2. Asociamos el error al ID 'CIF'
            if (!Validador::validarCIF($datos['CIF'])) {
                $errores['CIF'] = "El CIF introducido no tiene un formato válido.";
            }
            
            // Si el campo razón social estuviera vacío (ejemplo adicional)
            if (empty($datos['razonSocial'])) {
                $errores['razonSocial'] = "La razón social es obligatoria.";
            }
            
            if (empty($errores)) {
                try {
                    $this->modelo->crearCliente($datos);
                    header("Location: /index.php?controller=cliente&action=index");
                    exit;
                } catch (Exception $e) {
                    $errores['general'] = "Error al guardar en BD: " . $e->getMessage();
                }
            }
            
            $titulo_formulario = "Nuevo Cliente";
            $accion_url = "/index.php?controller=cliente&action=guardar";
            $cliente = $datos; 
            
            $contenido_vista = '../views/clientes/form.php';
            require_once '../views/layout/master.php';
        }
    }

    public function editar($id) {
        $cliente = $this->modelo->obtenerPorId($id);
        if (!$cliente) {
            die("Error: El cliente no existe.");
        }
        $titulo_formulario = "Modificar Cliente";
        $accion_url = "/index.php?controller=cliente&action=actualizar&id=" . $id;
        $contenido_vista = '../views/clientes/form.php';
        require_once '../views/layout/master.php';
    }

    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Incluimos nuestro validador
            require_once '../helpers/Validador.php';
            
            $errores = []; 
            
            // Recogemos los datos (fíjate que no actualizamos el idEmpresa, solo los datos fiscales)
            $datos = [
                'razonSocial' => trim($_POST['razonSocial']),
                'CIF'         => trim($_POST['CIF']),
                'sedeFiscal'  => trim($_POST['sedeFiscal']),
                'idUsuario'   => $_SESSION['usuario_id']
            ];
            
            // 1. VALIDACIONES
            if (!Validador::validarCIF($datos['CIF'])) {
                $errores['CIF'] = "El CIF introducido no tiene un formato válido.";
            }
            
            if (empty($datos['razonSocial'])) {
                $errores['razonSocial'] = "La razón social es obligatoria.";
            }
            
            if (empty($datos['sedeFiscal'])) {
                $errores['sedeFiscal'] = "La sede fiscal es obligatoria.";
            }
            
            // 2. SI NO HAY ERRORES, INTENTAMOS ACTUALIZAR
            if (empty($errores)) {
                try {
                    $this->modelo->actualizarCliente($id, $datos);
                    header("Location: /index.php?controller=cliente&action=index");
                    exit;
                } catch (Exception $e) {
                    // Si falla MySQL (por ejemplo, si intenta poner un CIF que ya tiene otro cliente)
                    $errores['general'] = "Error al actualizar en BD: " . $e->getMessage();
                }
            }
            
            // 3. SI LLEGAMOS AQUÍ, ES QUE HAY ERRORES (Validación o MySQL)
            $titulo_formulario = "Modificar Cliente";
            
            // Mantenemos la URL de acción apuntando a actualizar con el ID correspondiente
            $accion_url = "/index.php?controller=cliente&action=actualizar&id=" . $id;
            
            // Le pasamos a la vista los datos que el usuario intentó guardar para que no los pierda
            $cliente = $datos; 
            $cliente['id'] = $id; // Añadimos el ID original al array por si la vista lo necesita
            
            $contenido_vista = '../views/clientes/form.php';
            require_once '../views/layout/master.php';
        }
    }

    public function eliminar($id) {
        if (!empty($id)) {
            $this->modelo->eliminarCliente($id);
        }
        header("Location: /index.php?controller=cliente&action=index");
        exit;
    }
}
?>