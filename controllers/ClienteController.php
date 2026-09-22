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
            
            $errores = []; 
            
            // 1. Tratamos el CIF: si está vacío, lo convertimos en null
            $cifInput = trim($_POST['CIF']);
            $cifFinal = empty($cifInput) ? null : $cifInput;
            
            $datos = [
                'razonSocial' => trim($_POST['razonSocial']),
                'CIF'         => $cifFinal,
                'sedeFiscal'  => trim($_POST['sedeFiscal']),
                'idUsuario'   => $_SESSION['usuario_id'],
                'idEmpresa'   => $_SESSION['idEmpresa']
            ];
            
            // 2. Solo validamos el CIF si el usuario ha escrito algo
            if ($datos['CIF'] !== null) {
                if (!Validador::validarCIF($datos['CIF'])) {
                    $errores['CIF'] = "El CIF/NIF introducido no tiene un formato válido.";
                }
            }
            
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
            
            require_once '../helpers/Validador.php';
            
            $errores = []; 
            
            // 1. Tratamos el CIF: si está vacío, lo convertimos en null
            $cifInput = trim($_POST['CIF']);
            $cifFinal = empty($cifInput) ? null : $cifInput;
            
            $datos = [
                'razonSocial' => trim($_POST['razonSocial']),
                'CIF'         => $cifFinal,
                'sedeFiscal'  => trim($_POST['sedeFiscal']),
                'idUsuario'   => $_SESSION['usuario_id']
            ];
            
            // 2. Solo validamos el CIF si el usuario ha escrito algo
            if ($datos['CIF'] !== null) {
                if (!Validador::validarCIF($datos['CIF'])) {
                    $errores['CIF'] = "El CIF/NIF introducido no tiene un formato válido.";
                }
            }
            
            if (empty($datos['razonSocial'])) {
                $errores['razonSocial'] = "La razón social es obligatoria.";
            }
            
            if (empty($datos['sedeFiscal'])) {
                $errores['sedeFiscal'] = "La sede fiscal es obligatoria.";
            }
            
            // 3. SI NO HAY ERRORES, INTENTAMOS ACTUALIZAR
            if (empty($errores)) {
                try {
                    $this->modelo->actualizarCliente($id, $datos);
                    header("Location: /index.php?controller=cliente&action=index");
                    exit;
                } catch (Exception $e) {
                    $errores['general'] = "Error al actualizar en BD: " . $e->getMessage();
                }
            }
            
            // 4. SI LLEGAMOS AQUÍ, ES QUE HAY ERRORES (Validación o MySQL)
            $titulo_formulario = "Modificar Cliente";
            $accion_url = "/index.php?controller=cliente&action=actualizar&id=" . $id;
            
            $cliente = $datos; 
            $cliente['id'] = $id; 
            
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