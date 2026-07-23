<?php
require_once '../models/Auditoria.php';

class AuditoriaController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new Auditoria($conexion);
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $idEmpresaActiva = $_SESSION['idEmpresa'] ?? 0;

        // Filtros por defecto: Este mes
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');

        $partes = $this->modelo->obtenerHorasPartes($idEmpresaActiva, $fechaInicio, $fechaFin);
        $albaranes = $this->modelo->obtenerHorasAlbaranes($idEmpresaActiva, $fechaInicio, $fechaFin);

        $inconsistencias = [];
        $albaranesMatcheados = [];

        // 1. Revisar qué Partes no tienen Albarán (Riesgo: Trabajado pero NO facturado)
        foreach ($partes as $parte) {
            $matchFound = false;
            
            foreach ($albaranes as $albaran) {
                if ($parte['fecha'] === $albaran['fecha'] &&
                    $parte['idEmpleado'] === $albaran['idEmpleado'] &&
                    $parte['idCliente'] === $albaran['idCliente']) {
                    
                    // Comprobar si las horas se solapan
                    $pStart = strtotime($parte['horaDesde']);
                    $pEnd = strtotime($parte['horaHasta']);
                    $aStart = strtotime($albaran['horaDesde']);
                    $aEnd = strtotime($albaran['horaHasta']);
                    
                    if ($pStart < $aEnd && $pEnd > $aStart) {
                        $matchFound = true;
                        $albaranesMatcheados[] = $albaran['idLinea']; // Guardamos el match
                        break;
                    }
                }
            }
            
            if (!$matchFound) {
                $inconsistencias[] = [
                    'tipo' => 'PARTE_HUERFANO',
                    'gravedad' => 'warning',
                    'alerta' => '¡Falta Reflejar en Albarán!',
                    'mensaje' => 'El empleado ha trabajado estas horas, pero no se ha incluido en ningún albarán del cliente.',
                    'fecha' => $parte['fecha'],
                    'empleado' => $parte['nombre'] . ' ' . $parte['apellido1'],
                    'cliente' => $parte['cliente'],
                    'horario' => substr($parte['horaDesde'],0,5) . ' - ' . substr($parte['horaHasta'],0,5),
                    'enlace' => '/index.php?controller=partes&action=ver&id=' . $parte['idDocumento'],
                    'origen' => 'Parte de Trabajo #' . $parte['idDocumento']
                ];
            }
        }

        // 2. Revisar qué Albaranes no están en los Partes (Riesgo: Facturado pero NO pagado al empleado)
        foreach ($albaranes as $albaran) {
            if (!in_array($albaran['idLinea'], $albaranesMatcheados)) {
                $inconsistencias[] = [
                    'tipo' => 'ALBARAN_HUERFANO',
                    'gravedad' => 'danger',
                    'alerta' => '¡Falta Reflejar en Parte!',
                    'mensaje' => 'Se ha facturado este trabajo al cliente, pero el empleado no tiene el parte registrado.',
                    'fecha' => $albaran['fecha'],
                    'empleado' => $albaran['nombre'] . ' ' . $albaran['apellido1'],
                    'cliente' => $albaran['cliente'],
                    'horario' => substr($albaran['horaDesde'],0,5) . ' - ' . substr($albaran['horaHasta'],0,5),
                    'enlace' => '/index.php?controller=albaran&action=ver&id=' . $albaran['idDocumento'],
                    'origen' => 'Albarán Nº ' . $albaran['numAlbaran']
                ];
            }
        }

        // Ordenar el array por fecha
        usort($inconsistencias, function($a, $b) {
            return strtotime($a['fecha']) - strtotime($b['fecha']);
        });

        $contenido_vista = '../views/auditoria/index.php';
        require_once '../views/layout/master.php';
    }
}
?>