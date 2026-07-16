<div class="encabezado-modulo">
    <h2>Gestor de Tablas Auxiliares</h2>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
    
    <div class="tarjeta-formulario" style="height: fit-content; padding: 20px;">
        <h3 style="margin-top:0;">Nueva Tabla</h3>
        <form action="/index.php?controller=tabla&action=guardar_cabecera" method="POST">
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Código Cabecera *</label>
                <input type="text" name="codigo" required maxlength="45" placeholder="Ej: MARCAS_COCHE" style="width: 100%; padding: 8px;">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Descripción *</label>
                <input type="text" name="descripcion" required maxlength="45" placeholder="Ej: Marcas de Vehículos" style="width: 100%; padding: 8px;">
            </div>
            <button type="submit" class="btn-primario" style="width: 100%;">Crear Cabecera</button>
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
                <?php if (!empty($tablas)): foreach ($tablas as $t): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($t['codigo']); ?></strong></td>
                        <td><?php echo htmlspecialchars($t['descripcion']); ?></td>
                        <td class="celda-acciones">
                            <a href="/index.php?controller=tabla&action=lineas&codigo=<?php echo urlencode($t['codigo']); ?>" class="btn-sm btn-editar" style="background:#0ea5e9;">
                                <i class="fa-solid fa-list"></i> Ver Líneas
                            </a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="3" class="tabla-vacia">No hay tablas creadas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>