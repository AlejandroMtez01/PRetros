<?php
class Auditoria {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerHorasPartes($idEmpresa, $fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    lp.idLinea, p.id as idDocumento, p.fechaDesde as fecha, 
                    p.idEmpleado, emp.nombre, emp.apellido1, 
                    lp.idCliente, cli.razonSocial as cliente, 
                    lp.horaDesde, lp.horaHasta 
                FROM Partes p 
                JOIN lineasPartes lp ON p.id = lp.idParte 
                JOIN Empleados emp ON p.idEmpleado = emp.id
                JOIN Clientes cli ON lp.idCliente = cli.id
                WHERE p.idEmpresa = ? AND p.fechaDesde BETWEEN ? AND ?";
                
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("iss", $idEmpresa, $fechaInicio, $fechaFin);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerHorasAlbaranes($idEmpresa, $fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    la.idLinea, a.id as idDocumento, a.numAlbaran, a.fecha, 
                    la.idEmpleado, emp.nombre, emp.apellido1, 
                    a.idCliente, cli.razonSocial as cliente, 
                    la.horaDesde, la.horaHasta 
                FROM Albaranes a 
                JOIN lineasAlbaran la ON a.id = la.idAlbaran 
                JOIN Empleados emp ON la.idEmpleado = emp.id
                JOIN Clientes cli ON a.idCliente = cli.id
                WHERE a.idEmpresa = ? AND a.fecha BETWEEN ? AND ?";
                
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("iss", $idEmpresa, $fechaInicio, $fechaFin);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>