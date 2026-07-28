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

        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');

        $partes = $this->modelo->obtenerHorasPartes($idEmpresaActiva, $fechaInicio, $fechaFin);
        $albaranes = $this->modelo->obtenerHorasAlbaranes($idEmpresaActiva, $fechaInicio, $fechaFin);

        // 1. Preparar las líneas calculando sus minutos totales
        foreach ($partes as &$p) {
            $p['minutos_totales'] = $this->calcularDiferenciaMinutos($p['horaDesde'], $p['horaHasta']);
            $p['minutos_restantes'] = $p['minutos_totales'];
            $p['solapes'] = []; 
        }
        foreach ($albaranes as &$a) {
            $a['minutos_totales'] = $this->calcularDiferenciaMinutos($a['horaDesde'], $a['horaHasta']);
            $a['minutos_restantes'] = $a['minutos_totales'];
            $a['solapes'] = [];
        }
        unset($p, $a);

        // 2. Cruzar líneas buscando solapamientos (Restricción total: Fecha, Empleado, Cliente, Puesto y Vehículo)
        foreach ($partes as &$p) {
            foreach ($albaranes as &$a) {
                
                // Normalizamos textos para evitar que mayúsculas/minúsculas o espacios rompan el cruce
                $mismoPuesto = trim(strtolower($p['categoriaProfesional'] ?? '')) === trim(strtolower($a['categoriaProfesional'] ?? ''));
                $mismoVehiculo = trim(strtolower($p['vehiculoUtilizado'] ?? '')) === trim(strtolower($a['vehiculoUtilizado'] ?? ''));

                if ($p['fecha'] === $a['fecha'] && 
                    $p['idEmpleado'] === $a['idEmpleado'] && 
                    $p['idCliente'] === $a['idCliente'] &&
                    $mismoPuesto && 
                    $mismoVehiculo) {
                    
                    $overlapMin = $this->calcularSolapamientoMinutos($p['horaDesde'], $p['horaHasta'], $a['horaDesde'], $a['horaHasta']);
                    
                    if ($overlapMin > 0) {
                        $minutosARestar = min($overlapMin, $p['minutos_restantes'], $a['minutos_restantes']);
                        
                        if ($minutosARestar > 0) {
                            $p['minutos_restantes'] -= $minutosARestar;
                            $a['minutos_restantes'] -= $minutosARestar;
                            $p['solapes'][] = "Albarán Nº {$a['numAlbaran']}";
                            $a['solapes'][] = "Parte #{$p['idDocumento']}";
                        }
                    }
                }
            }
        }
        unset($p, $a);

        // 3. Generar Inconsistencias con alta precisión
        $inconsistencias = [];

        // A. PARTES (Faltan por facturar o el puesto/vehículo no cuadra)
        foreach ($partes as $p) {
            if ($p['minutos_restantes'] > 2) { 
                $faltan = $this->formatearMinutos($p['minutos_restantes']);
                $total = $this->formatearMinutos($p['minutos_totales']);
                $horario = substr($p['horaDesde'], 0, 5) . ' a ' . substr($p['horaHasta'], 0, 5);
                $vehiculo = !empty($p['vehiculoUtilizado']) ? " [{$p['vehiculoUtilizado']}]" : "";
                $puesto = $p['categoriaProfesional'] ?? 'Sin Categoría';

                $inconsistencias[] = [
                    'tipo' => 'warning',
                    'alerta' => 'NECESITA ALBARÁN',
                    'linea_horario' => $horario,
                    'linea_detalle' => "{$puesto}{$vehiculo}",
                    'razon' => "Se ha incluido en (Parte), pero no ha sido facturado (Albarán).",
                    'pendiente' => $faltan,
                    'total_original' => $total,
                    'solapes' => array_unique($p['solapes']),
                    'fecha' => $p['fecha'],
                    'empleado' => trim($p['nombre'] . ' ' . $p['apellido1']),
                    'cliente' => $p['cliente'],
                    'documento' => 'Parte #' . $p['idDocumento'],
                    'enlace' => "/index.php?controller=partes&action=ver&id=" . $p['idDocumento'],
                    'texto_enlace' => "Revisar Parte"
                ];
            }
        }

        // B. ALBARANES (Facturadas sin justificar o con puesto/vehículo distinto)
        foreach ($albaranes as $a) {
            if ($a['minutos_restantes'] > 2) {
                $sobran = $this->formatearMinutos($a['minutos_restantes']);
                $total = $this->formatearMinutos($a['minutos_totales']);
                $horario = substr($a['horaDesde'], 0, 5) . ' a ' . substr($a['horaHasta'], 0, 5);
                $vehiculo = !empty($a['vehiculoUtilizado']) ? " [{$a['vehiculoUtilizado']}]" : "";
                $puesto = $a['categoriaProfesional'] ?? 'Sin Categoría';

                $inconsistencias[] = [
                    'tipo' => 'danger',
                    'alerta' => 'NECESITA PARTE',
                    'linea_horario' => $horario,
                    'linea_detalle' => "{$puesto}{$vehiculo}",
                    'razon' => "Se ha facturado el importe (Albarán), pero no ha sido reflejado en un (Parte).",
                    'pendiente' => $sobran,
                    'total_original' => $total,
                    'solapes' => array_unique($a['solapes']),
                    'fecha' => $a['fecha'],
                    'empleado' => trim($a['nombre'] . ' ' . $a['apellido1']),
                    'cliente' => $a['cliente'],
                    'documento' => 'Albarán Nº ' . $a['numAlbaran'],
                    'enlace' => "/index.php?controller=albaran&action=ver&id=" . $a['idDocumento'],
                    'texto_enlace' => "Revisar Albarán"
                ];
            }
        }

        usort($inconsistencias, function($a, $b) {
            return strtotime($a['fecha']) - strtotime($b['fecha']);
        });

        $contenido_vista = '../views/auditoria/index.php';
        require_once '../views/layout/master.php';
    }

    private function calcularDiferenciaMinutos($inicio, $fin) {
        $s = strtotime($inicio);
        $e = strtotime($fin);
        if ($e <= $s) $e += 86400; 
        return floor(($e - $s) / 60);
    }

    private function calcularSolapamientoMinutos($inicio1, $fin1, $inicio2, $fin2) {
        $s1 = strtotime($inicio1); 
        $e1 = strtotime($fin1); 
        if ($e1 <= $s1) $e1 += 86400;

        $s2 = strtotime($inicio2); 
        $e2 = strtotime($fin2); 
        if ($e2 <= $s2) $e2 += 86400;

        $maxStart = max($s1, $s2);
        $minEnd = min($e1, $e2);

        if ($maxStart < $minEnd) {
            return floor(($minEnd - $maxStart) / 60);
        }
        return 0;
    }

    private function formatearMinutos($minutos_totales) {
        $horas = floor($minutos_totales / 60);
        $minutos = $minutos_totales % 60;
        return $horas . 'h ' . ($minutos > 0 ? $minutos . 'm' : '');
    }
}
?>