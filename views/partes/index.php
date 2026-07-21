<?php
// 1. Aseguramos que la sesión está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 2. Rescatamos mensajes de error (por ejemplo, si falla la eliminación)
$mensaje_error_eliminar = $_SESSION['error_guardado'] ?? null;
unset($_SESSION['error_guardado']);
?>

<!-- 3. Pintamos el error si existe -->
<?php if ($mensaje_error_eliminar): ?>
    <div class="alerta-error" style="background-color: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px; border: 1px solid #f87171; margin-bottom: 20px;">
        <i class="fa-solid fa-triangle-exclamation"></i> <strong>Detalle del Error:</strong> <br><br>
        <?php echo htmlspecialchars($mensaje_error_eliminar); ?>
    </div>
<?php endif; ?>

<div class="encabezado-seccion" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Listado de Partes de Trabajo</h2>

    <a href="/index.php?controller=partes&action=crear" class="btn-primario" style="text-decoration: none; background: #0f4c81; color: white; padding: 10px 20px; border-radius: 6px; font-weight: bold;">
        <i class="fa-solid fa-plus"></i> &nbsp;Nuevo Parte
    </a>
</div>

<!-- ========================================== -->
<!-- PANEL DE FILTROS                           -->
<!-- ========================================== -->
<div class="panel-filtros formulario-estandar" style="background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0;">
    <form action="/index.php" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">

        <!-- Input oculto para mantener el controlador -->
        <input type="hidden" name="controller" value="parte">

        <div class="form-group" style="flex: 1; min-width: 120px; margin-bottom: 0;">
            <label>ID Parte</label>
            <input type="number" name="idParte" value="<?php echo htmlspecialchars($_GET['idParte'] ?? ''); ?>" placeholder="Buscar ID...">
        </div>

        <div class="form-group" style="flex: 2; min-width: 200px; margin-bottom: 0;">
            <label>Empleado</label>
            <select name="idEmpleado" style="padding: 10px; border: 1px solid #94a3b8; border-radius: 6px; width: 100%;">
                <option value="">Todos los empleados...</option>
                <?php if (!empty($empleados)): foreach ($empleados as $emp): ?>
                    <option value="<?php echo $emp['id']; ?>" <?php echo (isset($_GET['idEmpleado']) && $_GET['idEmpleado'] == $emp['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($emp['nombre'] . ' ' . $emp['apellido1']); ?>
                    </option>
                <?php endforeach; endif; ?>
            </select>
        </div>

        <div class="form-group" style="flex: 2; min-width: 130px; margin-bottom: 0;">
            <label>Fecha Desde</label>
            <input type="date" name="fechaDesde" value="<?php echo htmlspecialchars($_GET['fechaDesde'] ?? ''); ?>">
        </div>

        <div class="form-group" style="flex: 2; min-width: 130px; margin-bottom: 0;">
            <label>Fecha Hasta</label>
            <input type="date" name="fechaHasta" value="<?php echo htmlspecialchars($_GET['fechaHasta'] ?? ''); ?>">
        </div>

        <!-- Botonera de Filtros -->
        <div class="form-group" style="display: flex; gap: 10px; margin-bottom: 0;">
            <button type="submit" class="btn-secundario" style="padding: 10px 15px; height: 42px; background: #475569; color: white; border: none; border-radius: 6px; cursor: pointer;">
                <i class="fa-solid fa-filter"></i> Filtrar
            </button>
            <a href="/index.php?controller=partes" class="btn-eliminar" style="display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; background: #ef4444; color: white; border-radius: 6px; text-decoration: none;" title="Limpiar filtros">
                <i class="fa-solid fa-eraser"></i>
            </a>
        </div>
    </form>
</div>

<!-- ========================================== -->
<!-- TABLA DE RESULTADOS                        -->
<!-- ========================================== -->
<div class="contenedor-tabla">
    <table class="tabla-datos" style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <thead style="background: #f1f5f9; border-bottom: 2px solid #e2e8f0;">
            <tr>
                <th style="padding: 12px 15px; text-align: center; color: #475569; width: 10%;">ID PARTE</th>
                <th style="padding: 12px 15px; text-align: left; color: #475569;">EMPLEADO</th>
                <th style="padding: 12px 15px; text-align: center; color: #475569;">INICIO RANGO</th>
                <th style="padding: 12px 15px; text-align: center; color: #475569;">FIN RANGO</th>
                <th style="padding: 12px 15px; text-align: center; color: #475569; width: 15%;">ACCIONES</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($partes)): ?>
                <?php foreach ($partes as $p): ?>
                    <tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.2s;">
                        <td style="padding: 12px 15px; text-align: center;"><strong>#<?php echo htmlspecialchars($p['id']); ?></strong></td>
                        
                        <td style="padding: 12px 15px;">
                            <?php echo htmlspecialchars($p['nombreEmpleado'] ?? 'Desconocido'); ?>
                        </td>

                        <!-- Formateamos la fecha a formato europeo -->
                        <td style="padding: 12px 15px; text-align: center;">
                            <?php echo !empty($p['fechaDesde']) ? date('d/m/Y', strtotime($p['fechaDesde'])) : '-'; ?>
                        </td>
                        
                        <td style="padding: 12px 15px; text-align: center;">
                            <?php echo !empty($p['fechaHasta']) ? date('d/m/Y', strtotime($p['fechaHasta'])) : '-'; ?>
                        </td>

                        <!-- Botonera de acciones (Ver, Editar, Eliminar) -->
                        <td class="celda-acciones" style="padding: 12px 15px; display: flex; gap: 8px; justify-content: center;">
                            <a href="/index.php?controller=partes&action=ver&id=<?php echo $p['id']; ?>" class="btn-secundario" style="background: #475569; color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.9rem;" title="Ver Detalle">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="/index.php?controller=partes&action=editar&id=<?php echo $p['id']; ?>" class="btn-principal" style="background-color: #0f4c81; color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.9rem;" title="Editar Parte">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <!-- NUEVO BOTÓN DE ELIMINAR CON CONFIRMACIÓN -->
                            <a href="/index.php?controller=partes&action=eliminar&id=<?php echo $p['id']; ?>"
                                class="btn-eliminar"
                                style="background: #ef4444; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none;"
                                onclick="return confirm('¿Estás totalmente seguro de que deseas eliminar este parte de trabajo? Esta acción borrará la cabecera y todas sus líneas, y no se puede deshacer.');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="tabla-vacia" style="text-align: center; padding: 30px; color: #64748b;">
                        <i class="fa-solid fa-folder-open" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                        No se han encontrado partes de trabajo con estos filtros.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
    /* Estilos para que las filas de la tabla se iluminen al pasar el ratón */
    .tabla-datos tbody tr:hover {
        background-color: #f8fafc;
    }
    .form-group label {
        display: block;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
        font-size: 0.85rem;
    }
    .formulario-estandar input:not([type="hidden"]),
    .formulario-estandar select {
        padding: 10px;
        border: 1px solid #94a3b8;
        border-radius: 6px;
        font-size: 0.95rem;
        font-family: inherit;
        background: #ffffff;
        width: 100%;
        box-sizing: border-box;
    }
    .btn-secundario:hover { opacity: 0.9; }
    .btn-principal:hover { opacity: 0.9; }
    .btn-eliminar:hover { opacity: 0.9; }
</style>