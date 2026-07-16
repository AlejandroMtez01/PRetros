<div class="encabezado-modulo">
    <h2>Valores de: <?php echo htmlspecialchars($cabecera['descripcion']); ?> (<?php echo htmlspecialchars($cabecera['codigo']); ?>)</h2>
    <a href="/index.php?controller=tabla&action=index" class="btn-secundario">
        <i class="fa-solid fa-arrow-left"></i> Volver a Tablas
    </a>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
    
    <div class="tarjeta-formulario" style="height: fit-content; padding: 20px;">
        <h3 style="margin-top:0;">Añadir Valor</h3>
        <form action="/index.php?controller=tabla&action=guardar_linea" method="POST">
            <input type="hidden" name="codigoCabecera" value="<?php echo htmlspecialchars($cabecera['codigo']); ?>">
            
            <div class="form-group" style="margin-bottom: 15px;">
                <!-- Ojo aquí al maxlength=3 que manda la BD -->
                <label>Código (Máx 3 letras) *</label>
                <input type="text" name="codigo" required maxlength="3" placeholder="Ej: ES" style="width: 100%; padding: 8px;">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Descripción *</label>
                <input type="text" name="descripcion" required maxlength="50" placeholder="Ej: España" style="width: 100%; padding: 8px;">
            </div>
            <button type="submit" class="btn-primario" style="width: 100%;">Añadir Línea</button>
        </form>
    </div>

    <div class="contenedor-tabla">
        <table class="tabla-datos">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($lineas)): foreach ($lineas as $l): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($l['codigo']); ?></strong></td>
                        <td><?php echo htmlspecialchars($l['descripcion']); ?></td>
                        <td class="celda-acciones">
                            <a href="/index.php?controller=tabla&action=eliminar_linea&id=<?php echo $l['id']; ?>&codigo=<?php echo urlencode($cabecera['codigo']); ?>" class="btn-sm btn-eliminar" onclick="return confirm('¿Borrar este valor?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="3" class="tabla-vacia">Esta tabla no tiene valores aún.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>