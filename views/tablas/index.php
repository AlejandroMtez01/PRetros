<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Capturamos el error
$mensaje_error_cabecera = $_SESSION['error_eliminar'] ?? null;
unset($_SESSION['error_eliminar']);
?>

<!-- Si hay error, pintamos la alerta -->
<?php if ($mensaje_error_cabecera): ?>
    <div class="alerta-error" style="background-color: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px; border: 1px solid #f87171; margin-bottom: 20px; font-weight: 500;">
        <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $mensaje_error_cabecera; ?>
    </div>
<?php endif; ?>
<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
    
    <div class="tarjeta-formulario" style="height: fit-content; padding: 20px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc;">
        <h3 style="margin-top:0; color: #0f4c81; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">Nueva Tabla</h3>
        <form action="/index.php?controller=tabla&action=guardar_cabecera" method="POST">
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: bold; font-size: 0.85rem; color: #334155;">Código Cabecera *</label>
                <input type="text" name="codigo" required maxlength="45" placeholder="Ej: MARCAS_COCHE" style="width: 100%; padding: 10px; border: 1px solid #94a3b8; border-radius: 6px; margin-top: 5px;">
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: bold; font-size: 0.85rem; color: #334155;">Descripción *</label>
                <input type="text" name="descripcion" required maxlength="45" placeholder="Ej: Marcas de Vehículos" style="width: 100%; padding: 10px; border: 1px solid #94a3b8; border-radius: 6px; margin-top: 5px;">
            </div>
            <button type="submit" class="btn-primario" style="width: 100%; background: #0f4c81; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                Crear Cabecera
            </button>
        </form>
    </div>

    <div class="contenedor-tabla" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
        <table class="tabla-datos" style="width: 100%; border-collapse: collapse; background: white;">
            <thead style="background: #f1f5f9;">
                <tr>
                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #e2e8f0;">CÓDIGO</th>
                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #e2e8f0;">DESCRIPCIÓN</th>
                    <th style="padding: 15px; text-align: center; border-bottom: 2px solid #e2e8f0;">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($tablas)): foreach ($tablas as $t): ?>
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 15px;"><strong><?php echo htmlspecialchars($t['codigo']); ?></strong></td>
                        <td style="padding: 15px;"><?php echo htmlspecialchars($t['descripcion']); ?></td>
                        <td class="celda-acciones" style="padding: 15px; text-align: center; display: flex; gap: 8px; justify-content: center;">
                            
                            <!-- Botón Ver Líneas -->
                            <a href="/index.php?controller=tabla&action=lineas&codigo=<?php echo urlencode($t['codigo']); ?>" class="btn-sm" style="background:#0ea5e9; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none;">
                                <i class="fa-solid fa-list"></i> Ver Líneas
                            </a>
                            
                            <!-- Botón Eliminar -->
                            <a href="/index.php?controller=tabla&action=eliminar_cabecera&codigo=<?php echo urlencode($t['codigo']); ?>" class="btn-sm" style="background:#ef4444; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none;" onclick="return confirm('¿Estás seguro de eliminar la tabla <?php echo htmlspecialchars($t['codigo']); ?> y todo su contenido?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>

                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="3" style="padding: 30px; text-align: center; color: #64748b; font-style: italic;">No hay tablas creadas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>