<div class="encabezado-modulo">
    <h2>Registros de Inventario</h2>
    <a href="/index.php?controller=articulo&action=crear" class="btn-primario">
        <i class="fa-solid fa-plus"></i> Nuevo Registro
    </a>
</div>

<div class="contenedor-tabla">
    <table class="tabla-datos">
        <thead>
            <tr>
                <th>Tipo (Prefijo)</th>
                <th>Denominación</th>
                <th>Datos (JSON Bruto)</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($articulos)): foreach ($articulos as $art): ?>
                <tr>
                    <td><span style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold;"><?php echo htmlspecialchars($art['prefijo_tipo']); ?></span></td>
                    <td><strong><?php echo htmlspecialchars($art['denominacion']); ?></strong></td>
                    <td><span style="color: #64748b; font-size: 0.8em; font-family: monospace;"><?php echo substr($art['datos_dinamicos'], 0, 50) . '...'; ?></span></td>
                    <td class="celda-acciones">
                        <a href="/index.php?controller=articulo&action=editar&prefijo=<?php echo urlencode($art['prefijo_tipo']); ?>&denominacion=<?php echo urlencode($art['denominacion']); ?>" class="btn-sm btn-editar">
                            <i class="fa-solid fa-pen"></i> Editar
                        </a>
                        <a href="/index.php?controller=articulo&action=eliminar&prefijo=<?php echo urlencode($art['prefijo_tipo']); ?>&denominacion=<?php echo urlencode($art['denominacion']); ?>" onclick="return confirm('¿Borrar registro?');" class="btn-sm btn-eliminar">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="4" class="tabla-vacia">No hay inventario registrado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>