<?php

class Empleado {
    // 1. Variable para guardar la conexión
    private $conexion;

    // 2. EL CONSTRUCTOR: Recibe la conexión desde el controlador
    public function __construct(mysqli $conexion) {
        $this->conexion = $conexion;
    }

    // 3. Método para obtener todos los empleados filtrados por Empresa
    public function obtenerTodos($idEmpresa) {
        // Usamos prepare() para evitar inyecciones SQL
        $sql = "SELECT * FROM Empleados WHERE idEmpresa = ? ORDER BY nombre ASC";
        $stmt = $this->conexion->prepare($sql);
        
        // Pasamos el idEmpresa ("i" de integer)
        $stmt->bind_param("i", $idEmpresa);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        
        $empleados = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $empleados[] = $fila;
            }
        }
        
        return $empleados;
    }

    // 4. Método para obtener un solo empleado
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM Empleados WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    // 5. Insertar un nuevo empleado en la base de datos
    public function crearEmpleado($datos) {
        $sql = "INSERT INTO Empleados (nombre, apellido1, apellido2, DNI, numSS, fechaAlta, fechaBaja, idUsuario, idEmpresa) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conexion->prepare($sql);
        
        // 9 parámetros: 7 Strings ("s") y 2 Integers ("i") -> "sssssssii"
        $stmt->bind_param("sssssssii", 
            $datos['nombre'], 
            $datos['apellido1'], 
            $datos['apellido2'], 
            $datos['DNI'], 
            $datos['numSS'], 
            $datos['fechaAlta'],
            $datos['fechaBaja'], 
            $datos['idUsuario'],
            $datos['idEmpresa']
        );
        
        return $stmt->execute();
    }

    // 6. Actualizar un empleado existente
    public function actualizarEmpleado($id, $datos) {
        $sql = "UPDATE Empleados 
                SET nombre = ?, apellido1 = ?, apellido2 = ?, DNI = ?, numSS = ?, fechaAlta = ?, fechaBaja = ?, idUsuario = ? 
                WHERE id = ?";
        
        $stmt = $this->conexion->prepare($sql);
        
        // "sssssssii" -> 7 Strings y 2 Integers
        $stmt->bind_param("sssssssii", 
            $datos['nombre'], 
            $datos['apellido1'], 
            $datos['apellido2'], 
            $datos['DNI'], 
            $datos['numSS'], 
            $datos['fechaAlta'],
            $datos['fechaBaja'],
            $datos['idUsuario'],
            $id
        );
        
        return $stmt->execute();
    }

    // 7. Eliminar un empleado por su ID
    public function eliminarEmpleado($id) {
        $sql = "DELETE FROM Empleados WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }

    // ==========================================
    // EXTRAER HISTORIAL DE HORAS (SOLO PARTES FILTRADO)
    // ==========================================
    public function obtenerHorasEmpleado($idEmpleado, $idEmpresa, $fechaInicio = null, $fechaFin = null) {
        $sql = "
            SELECT 
                'Parte' AS documento_origen,
                p.id AS id_documento,
                p.fechaDesde AS fecha,
                lp.horaDesde,
                lp.horaHasta,
                lp.categoriaProfesional AS puesto,
                c.razonSocial AS cliente
            FROM Partes p
            JOIN lineasPartes lp ON p.id = lp.idParte
            LEFT JOIN Clientes c ON lp.idCliente = c.id
            WHERE p.idEmpleado = ? AND p.idEmpresa = ?
        ";

        // Preparamos las variables dinámicas para el bind_param
        $tipos = "ii";
        $parametros = [$idEmpleado, $idEmpresa];

        if (!empty($fechaInicio)) {
            $sql .= " AND p.fechaDesde >= ?";
            $tipos .= "s";
            $parametros[] = $fechaInicio;
        }

        if (!empty($fechaFin)) {
            $sql .= " AND p.fechaDesde <= ?";
            $tipos .= "s";
            $parametros[] = $fechaFin;
        }

        $sql .= " ORDER BY fecha DESC, horaDesde ASC";

        $stmt = $this->conexion->prepare($sql);
        
        // Desempaquetamos el array de parámetros dinámicos (...$parametros)
        $stmt->bind_param($tipos, ...$parametros);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>