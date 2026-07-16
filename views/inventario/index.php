<div class="encabezado-modulo">
    <h2>Catálogo de Inventario</h2>
    <a href="/index.php?controller=catalogo_inventario&action=crear" class="btn-primario">
        <i class="fa-solid fa-plus"></i> Nuevo Tipo
    </a>
</div>

<div class="contenedor-tabla">
    <table class="tabla-datos">
        <thead>
            <tr>
                <th>Prefijo</th>
                <th>Nombre del Tipo</th>
                <th>Configuración</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($tipos)): ?>
                <?php foreach ($tipos as $tipo): ?>
                    <tr>
                        <td>
                            <!-- Resaltamos el prefijo visualmente -->
                            <strong><?php echo htmlspecialchars($tipo['prefijo']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($tipo['nombre_tipo']); ?></td>
                        <td>
                            <!-- Mostramos un indicador en lugar de todo el código JSON crudo -->
                            <span style="color: #64748b; font-size: 0.85em;">
                                <?php echo empty(json_decode($tipo['esquema_configuracion'], true)) ? 'Sin esquema' : '{ Configurado }'; ?>
                            </span>
                        </td>
                        
                        <td class="celda-acciones">
                            <!-- Fíjate que pasamos urlencode por si el prefijo tiene caracteres raros -->
                            <a href="/index.php?controller=catalogo_inventario&action=editar&id=<?php echo urlencode($tipo['prefijo']); ?>" class="btn-sm btn-editar">
                                <i class="fa-solid fa-pen"></i> Editar
                            </a>
                            <a href="/index.php?controller=catalogo_inventario&action=eliminar&id=<?php echo urlencode($tipo['prefijo']); ?>" onclick="return confirm('¿Seguro que deseas eliminar este tipo de inventario?');" class="btn-sm btn-eliminar">
                                <i class="fa-solid fa-xmark"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="tabla-vacia">
                        No hay tipos de inventario configurados.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>