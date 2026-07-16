<div class="cabecera-modulo">
    <h1>Directorio de Empleados</h1>
    <!-- Este botón lleva al formulario de alta -->
    <a href="/PRetros/public/index.php?controller=empleado&action=crear" class="btn-primario">+ Nuevo Empleado</a>
</div>

<div class="tarjeta-datos">
    <table class="tabla-estandar">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>DNI</th>
                <th>Nº Seguridad Social</th>
                <th>Fecha Alta</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($empleados)): ?>
                <?php foreach ($empleados as $empleado): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($empleado['id']) ?></strong></td>
                        
                        <!-- Concatenamos nombre y apellidos para que la tabla quede más limpia -->
                        <td>
                            <?= htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido1'] . ' ' . $empleado['apellido2']) ?>
                        </td>
                        
                        <td><?= htmlspecialchars($empleado['DNI']) ?></td>
                        <td><?= htmlspecialchars($empleado['numSS']) ?></td>
                        
                        <!-- Formateamos la fecha a formato europeo -->
                        <td><?= date('d/m/Y', strtotime($empleado['fechaAlta'])) ?></td>
                        
                        <!-- Lógica visual para saber si un empleado está activo o de baja -->
                        <td>
                            <?php if (empty($empleado['fechaBaja'])): ?>
                                <span style="color: #10b981; font-weight: 600;">Activo</span>
                            <?php else: ?>
                                <span style="color: #ef4444; font-weight: 600;">
                                    Baja (<?= date('d/m/Y', strtotime($empleado['fechaBaja'])) ?>)
                                </span>
                            <?php endif; ?>
                        </td>
                        
<!-- Le añadimos la clase celda-acciones al td -->
                        <td class="celda-acciones">
                            
                            <!-- Botón de Editar con sus nuevas clases -->
                            <a href="/index.php?controller=empleado&action=editar&id=<?= $empleado['id'] ?>" class="btn-sm btn-editar">
                                <i class="fa-solid fa-pen-to-square"></i> Editar
                            </a>
                            
                            <!-- Botón de Eliminar con sus nuevas clases -->
                            <a href="index.php?controller=empleado&action=eliminar&id=<?= $empleado['id'] ?>" 
                               onclick="return confirm('¿Estás seguro de que deseas eliminar este empleado? Esta acción no se puede deshacer.');" 
                               class="btn-sm btn-eliminar">
                                <i class="fa-solid fa-trash"></i> Eliminar
                            </a>
                            
                        </td>
                        
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fila vacía si la base de datos no devuelve nada -->
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                        No hay empleados registrados en el sistema.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>