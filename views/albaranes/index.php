<?php
// 1. Aseguramos que la sesión está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 2. Rescatamos el error
$mensaje_error_eliminar = $_SESSION['error_guardado'] ?? null;
unset($_SESSION['error_guardado']);
?>

<!-- 3. Pintamos el error si existe -->
<?php if ($mensaje_error_eliminar): ?>
    <div class="alerta-error" style="background-color: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px; border: 1px solid #f87171; margin-bottom: 20px;">
        <i class="fa-solid fa-triangle-exclamation"></i> <strong>Detalle del Error de Base de Datos:</strong> <br><br>
        <?php echo htmlspecialchars($mensaje_error_eliminar); ?>
    </div>
<?php endif; ?>
<div class="encabezado-seccion" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Listado de Albaranes</h2>

    <a href="/index.php?controller=albaran&action=crear" class="btn-primario" style="text-decoration: none;">
        <i class="fa-solid fa-plus"></i> &nbsp;Nuevo Albarán
    </a>
</div>

<!-- ========================================== -->
<!-- PANEL DE FILTROS REDISEÑADO                -->
<!-- ========================================== -->
<div class="panel-filtros formulario-estandar" style="background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0;">
    <form action="/index.php" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">

        <!-- Input oculto para mantener el controlador -->
        <input type="hidden" name="controller" value="albaran">

        <div class="form-group" style="flex: 1; min-width: 140px; margin-bottom: 0;">
            <label>Nº Albarán</label>
            <input type="text" name="numAlbaran" value="<?php echo htmlspecialchars($_GET['numAlbaran'] ?? ''); ?>" placeholder="Buscar número...">
        </div>

        <div class="form-group" style="flex: 2; min-width: 200px; margin-bottom: 0;">
            <label>Cliente</label>
            <select name="idCliente">
                <option value="">Todos los clientes...</option>
                <?php foreach ($clientes as $cli): ?>
                    <option value="<?php echo $cli['id']; ?>" <?php echo (isset($_GET['idCliente']) && $_GET['idCliente'] == $cli['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cli['denominacion'] ?? $cli['razonSocial'] ?? $cli['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="flex: 2; min-width: 200px; margin-bottom: 0;">
            <label>Centro de Trabajo</label>
            <select name="idCentro">
                <option value="">Todos los centros...</option>
                <?php foreach ($centros as $cen): ?>
                    <option value="<?php echo $cen['id']; ?>" <?php echo (isset($_GET['idCentro']) && $_GET['idCentro'] == $cen['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cen['denominacion'] ?? $cen['direccion']); ?>
                    </option>
                <?php endforeach; ?>
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
            <button type="submit" class="btn-secundario" style="padding: 10px 15px; height: 42px;">
                <i class="fa-solid fa-filter"></i> Filtrar
            </button>
            <a href="/index.php?controller=albaran" class="btn-eliminar" style="display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; text-decoration: none; padding: 0;" title="Limpiar filtros">
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
                <th style="padding: 12px 15px; text-align: left; color: #475569;">Nº ALBARÁN</th>
                <th style="padding: 12px 15px; text-align: left; color: #475569;">FECHA</th>
                <th style="padding: 12px 15px; text-align: left; color: #475569;">CLIENTE</th>
                <th style="padding: 12px 15px; text-align: left; color: #475569;">CENTRO</th>
                <th style="padding: 12px 15px; text-align: center; color: #475569;">ACCIONES</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($albaranes)): ?>
                <?php foreach ($albaranes as $alb): ?>
                    <tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.2s;">
                        <td style="padding: 12px 15px;"><strong><?php echo htmlspecialchars($alb['numAlbaran']); ?></strong></td>

                        <!-- Formateamos la fecha a formato europeo -->
                        <td style="padding: 12px 15px;"><?php echo date('d/m/Y', strtotime($alb['fecha'])); ?></td>

                        <td style="padding: 12px 15px;"><?php echo htmlspecialchars($alb['nombreCliente']); ?></td>
                        <td style="padding: 12px 15px;"><?php echo htmlspecialchars($alb['nombreCentro']); ?></td>

                        <!-- Botonera de acciones (Ver y Editar) -->
                        <td class="celda-acciones" style="padding: 12px 15px; display: flex; gap: 8px; justify-content: center;">
                            <a href="/index.php?controller=albaran&action=ver&id=<?php echo $alb['id']; ?>" class="btn-secundario" style="text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.9rem;" title="Ver Detalle">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="/index.php?controller=albaran&action=editar&id=<?php echo $alb['id']; ?>" class="btn-principal" style="text-decoration: none; color: white; padding: 6px 12px; border-radius: 4px; font-size: 0.9rem; background-color: #0f4c81;" title="Editar Albarán">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <!-- En tu bucle foreach donde pintas las filas de los albaranes -->

                            <!-- Tus otros botones (Ver, Editar, etc.) -->

                            <!-- NUEVO BOTÓN DE ELIMINAR CON CONFIRMACIÓN -->
                            <a href="/index.php?controller=albaran&action=eliminar&id=<?php echo $alb['id']; ?>"
                                class="btn-eliminar"
                                style="padding: 6px 12px; text-decoration: none;"
                                onclick="return confirm('¿Estás totalmente seguro de que deseas eliminar este albarán? Esta acción borrará la cabecera y todas sus líneas, y no se puede deshacer.');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>

                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="tabla-vacia" style="text-align: center; padding: 30px; color: #64748b;">
                        <i class="fa-solid fa-folder-open" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                        No se han encontrado albaranes con estos filtros.
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
</style>