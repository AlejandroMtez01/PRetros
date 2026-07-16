<?php

class Empleado {
    // 1. Variable para guardar la conexión
    private $conexion;

    // 2. EL CONSTRUCTOR QUE FALTABA: Recibe la conexión desde el controlador
    public function __construct(mysqli $conexion) {
        $this->conexion = $conexion;
    }

    // 3. Método para obtener todos los empleados (usado en tu public function index)
    public function obtenerTodos() {
        // Hacemos la consulta a la base de datos
        $query = "SELECT * FROM Empleados ORDER BY nombre ASC";
        $resultado = $this->conexion->query($query);
        
        $empleados = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $empleados[] = $fila;
            }
        }
        
        return $empleados;
    }

    // 4. Método para obtener un solo empleado (usado en tu public function editar)
    public function obtenerPorId($id) {
        // Usamos prepare() por seguridad para evitar inyección SQL
        $stmt = $this->conexion->prepare("SELECT * FROM Empleados WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        
        // Devolvemos la fila encontrada, o null si no existe
        return $resultado->fetch_assoc();
    }
    // Insertar un nuevo empleado en la base de datos
    public function crearEmpleado($datos) {
        // Ahora incluimos fechaBaja en el INSERT
        $sql = "INSERT INTO Empleados (nombre, apellido1, apellido2, DNI, numSS, fechaAlta, fechaBaja, idUsuario, idEmpresa) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conexion->prepare($sql);
        
        // Ahora son 9 parámetros: 7 Strings ("s") y 2 Integers ("i") -> "sssssssii"
        $stmt->bind_param("sssssssii", 
            $datos['nombre'], 
            $datos['apellido1'], 
            $datos['apellido2'], 
            $datos['DNI'], 
            $datos['numSS'], 
            $datos['fechaAlta'],
            $datos['fechaBaja'], // Añadido aquí
            $datos['idUsuario'],
            $datos['idEmpresa']
        );
        
        return $stmt->execute();
    }
    public function actualizarEmpleado($id, $datos) {
        $sql = "UPDATE Empleados 
                SET nombre = ?, apellido1 = ?, apellido2 = ?, DNI = ?, numSS = ?, fechaAlta = ?, fechaBaja = ?, idUsuario = ? 
                WHERE id = ?";
        
        $stmt = $this->conexion->prepare($sql);
        
        // "sssssssii" -> 7 Strings y 2 Integers (el idUsuario y el id del empleado)
        $stmt->bind_param("sssssssii", 
            $datos['nombre'], 
            $datos['apellido1'], 
            $datos['apellido2'], 
            $datos['DNI'], 
            $datos['numSS'], 
            $datos['fechaAlta'],
            $datos['fechaBaja'],
            $datos['idUsuario'],
            $id // El ID que va en el WHERE
        );
        
        return $stmt->execute();
    }
    // 6. Eliminar un empleado de la BD
   // Eliminar un empleado por su ID
    public function eliminarEmpleado($id) {
        $sql = "DELETE FROM Empleados WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        
        // "i" porque el ID es un Integer
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }
}
?>