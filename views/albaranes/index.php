<div class="encabezado-seccion">
    <h2>Listado de Albaranes</h2>
    <a href="/index.php?controller=albaran&action=crear" class="btn-principal">
        <i class="fa-solid fa-plus"></i> Nuevo Albarán
    </a>
</div>

<!-- ========================================== -->
<!-- PANEL DE FILTROS ENCADENADOS               -->
<!-- ========================================== -->
<div class="panel-filtros" style="background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0;">
    <form action="/index.php" method="GET" class="grid-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
        
        <!-- Input oculto para mantener el controlador -->
        <input type="hidden" name="controller" value="albaran">
        
        <div class="form-group">
            <label>Nº Albarán</label>
            <input type="text" name="numAlbaran" value="<?php echo htmlspecialchars($_GET['numAlbaran'] ?? ''); ?>" placeholder="Buscar número...">
        </div>

        <div class="form-group">
            <label>Cliente</label>
            <select name="idCliente">
                <option value="">Todos los clientes...</option>
                <?php foreach ($clientes as $cli): ?>
                    <option value="<?php echo $cli['id']; ?>" <?php echo (isset($_GET['idCliente']) && $_GET['idCliente'] == $cli['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cli['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Centro de Trabajo</label>
            <select name="idCentro">
                <option value="">Todos los centros...</option>
                <?php foreach ($centros as $cen): ?>
                    <option value="<?php echo $cen['id']; ?>" <?php echo (isset($_GET['idCentro']) && $_GET['idCentro'] == $cen['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cen['denominacion']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Fecha Desde</label>
            <input type="date" name="fechaDesde" value="<?php echo htmlspecialchars($_GET['fechaDesde'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label>Fecha Hasta</label>
            <input type="date" name="fechaHasta" value="<?php echo htmlspecialchars($_GET['fechaHasta'] ?? ''); ?>">
        </div>

        <div class="form-group" style="display: flex; gap: 10px;">
            <button type="submit" class="btn-secundario" style="width: 100%;"><i class="fa-solid fa-filter"></i> Filtrar</button>
            <a href="/index.php?controller=albaran" class="btn-eliminar" style="padding: 10px; text-align: center;" title="Limpiar filtros">
                <i class="fa-solid fa-eraser"></i>
            </a>
        </div>
    </form>
</div>

<!-- ========================================== -->
<!-- TABLA DE RESULTADOS                        -->
<!-- ========================================== -->
<div class="contenedor-tabla">
    <table class="tabla-datos">
        <thead>
            <tr>
                <th>Nº Albarán</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Centro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($albaranes)): ?>
                <?php foreach ($albaranes as $alb): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($alb['numAlbaran']); ?></strong></td>
                        
                        <!-- Formateamos la fecha para que se vea como DD/MM/YYYY -->
                        <td><?php echo date('d/m/Y', strtotime($alb['fecha'])); ?></td>
                        
                        <td><?php echo htmlspecialchars($alb['nombreCliente']); ?></td>
                        <td><?php echo htmlspecialchars($alb['nombreCentro']); ?></td>
                        
                        <td class="celda-acciones">
                            <!-- Aquí podrás añadir botones para ver detalles, generar PDF, editar, etc. -->
                            <button class="btn-sm btn-editar" title="Ver Detalle"><i class="fa-solid fa-eye"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="tabla-vacia" style="text-align: center; padding: 20px; color: #64748b;">
                        No se han encontrado albaranes con estos filtros.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>