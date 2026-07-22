<?php
// ==========================================
// CÁLCULO DE TOTALES DEL ALBARÁN
// ==========================================
$totalEmpleados = 0;
if (!empty($lineas)) {
    foreach ($lineas as $linea) {
        $totalEmpleados += (float)($linea['importe'] ?? 0);
    }
}

$totalMateriales = 0;
if (!empty($materiales)) {
    foreach ($materiales as $mat) {
        $totalMateriales += (float)($mat['importeTotal'] ?? 0);
    }
}

$granTotal = $totalEmpleados + $totalMateriales;
?>

<div class="contenedor-albaran">
    <div class="encabezado-seccion" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Detalle del Albarán: <?php echo htmlspecialchars($albaran['numAlbaran']); ?></h2>
        <div>
            <a href="/index.php?controller=albaran&action=editar&id=<?php echo $albaran['id']; ?>" class="btn-principal" style="text-decoration: none;">
                <i class="fa-solid fa-pen"></i> Editar
            </a>
            <a href="/index.php?controller=albaran" class="btn-secundario" style="text-decoration: none; margin-left: 10px;">
                <i class="fa-solid fa-arrow-left"></i> Volver al Listado
            </a>
        </div>
    </div>

    <div class="formulario-estandar">
        <!-- ========================================== -->
        <!-- CABECERA DEL ALBARÁN                       -->
        <!-- ========================================== -->
        <fieldset>
            <legend>Datos Principales</legend>
            <div class="grid-3" style="margin-bottom: 15px;">
                <div>
                    <strong style="color: #475569; font-size: 0.85rem; display: block; margin-bottom: 5px;">Fecha</strong>
                    <p style="margin: 0; font-size: 1.1rem; color: #0f4c81; font-weight: bold;">
                        <?php echo date('d/m/Y', strtotime($albaran['fecha'])); ?>
                    </p>
                </div>
                <div>
                    <strong style="color: #475569; font-size: 0.85rem; display: block; margin-bottom: 5px;">Cliente</strong>
                    <p style="margin: 0; font-size: 1.1rem;"><?php echo htmlspecialchars($albaran['nombreCliente']); ?></p>
                </div>
                <div>
                    <strong style="color: #475569; font-size: 0.85rem; display: block; margin-bottom: 5px;">Centro de Trabajo</strong>
                    <p style="margin: 0; font-size: 1.1rem;"><?php echo htmlspecialchars($albaran['nombreCentro']); ?></p>
                </div>
            </div>
            
            <?php if (!empty($albaran['observaciones'])): ?>
                <div style="background: #f1f5f9; padding: 15px; border-radius: 6px; border: 1px solid #cbd5e1; margin-top: 20px;">
                    <strong style="color: #475569; font-size: 0.85rem; display: block; margin-bottom: 5px;">Observaciones</strong>
                    <p style="margin: 0; font-style: italic; color: #334155;"><?php echo nl2br(htmlspecialchars($albaran['observaciones'])); ?></p>
                </div>
            <?php endif; ?>
        </fieldset>

        <!-- ========================================== -->
        <!-- LÍNEAS DE EMPLEADOS                        -->
        <!-- ========================================== -->
        <fieldset style="margin-top: 20px;">
            <legend>Líneas de Trabajo Registradas</legend>
            <div style="overflow-x: auto;">
                <table class="tabla-datos" style="width: 100%; border-collapse: collapse; margin-top: 10px; background: white;">
                    <thead style="background: #f1f5f9; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th style="padding: 12px; text-align: left; color: #475569;">Empleado</th>
                            <th style="padding: 12px; text-align: left; color: #475569;">Horario</th>
                            <th style="padding: 12px; text-align: left; color: #475569;">Categoría</th>
                            <th style="padding: 12px; text-align: left; color: #475569;">Vehículo ID</th>
                            <th style="padding: 12px; text-align: right; color: #475569;">Importe (€)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($lineas)): ?>
                            <?php foreach ($lineas as $linea): ?>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 12px;">
                                        <strong><?php echo htmlspecialchars($linea['empNombre'] . ' ' . $linea['empApellido']); ?></strong>
                                    </td>
                                    <td style="padding: 12px;">
                                        <i class="fa-regular fa-clock" style="color: #64748b; margin-right: 5px;"></i>
                                        <?php echo substr($linea['horaDesde'], 0, 5); ?> - <?php echo substr($linea['horaHasta'], 0, 5); ?>
                                    </td>
                                    <td style="padding: 12px; text-transform: capitalize;">
                                        <?php echo htmlspecialchars($linea['categoriaProfesional']); ?>
                                    </td>
                                    <td style="padding: 12px;">
                                        <?php echo !empty($linea['vehiculoUtilizado']) ? htmlspecialchars($linea['vehiculoUtilizado']) : '<span style="color:#94a3b8;">N/A</span>'; ?>
                                    </td>
                                    <td style="padding: 12px; text-align: right; font-weight: bold; color: #0f4c81;">
                                        <?php echo !empty($linea['importe']) ? number_format($linea['importe'], 2, ',', '.') : '0,00'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="padding: 30px; text-align: center; color: #64748b;">No hay líneas de trabajo registradas en este albarán.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </fieldset>

        <!-- ========================================== -->
        <!-- LÍNEAS DE MATERIALES                       -->
        <!-- ========================================== -->
        <fieldset style="margin-top: 20px;">
            <legend>Materiales Consumidos</legend>
            <div style="overflow-x: auto;">
                <table class="tabla-datos" style="width: 100%; border-collapse: collapse; margin-top: 10px; background: white;">
                    <thead style="background: #f1f5f9; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th style="padding: 12px; text-align: left; color: #475569;">Denominación Artículo</th>
                            <th style="padding: 12px; text-align: right; color: #475569;">Precio Unidad (€)</th>
                            <th style="padding: 12px; text-align: right; color: #475569;">Unidades</th>
                            <th style="padding: 12px; text-align: right; color: #475569;">Importe Total (€)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($materiales)): ?>
                            <?php foreach ($materiales as $mat): ?>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 12px;">
                                        <strong><?php echo htmlspecialchars($mat['denominacionArticulo']); ?></strong>
                                    </td>
                                    <td style="padding: 12px; text-align: right;">
                                        <?php echo number_format($mat['precioUnitario'], 2, ',', '.'); ?>
                                    </td>
                                    <td style="padding: 12px; text-align: right;">
                                        <?php echo number_format($mat['unidades'], 2, ',', '.'); ?>
                                    </td>
                                    <td style="padding: 12px; text-align: right; font-weight: bold; color: #0f4c81;">
                                        <?php echo number_format($mat['importeTotal'], 2, ',', '.'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="padding: 30px; text-align: center; color: #64748b;">No hay materiales registrados en este albarán.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </fieldset>

        <!-- ========================================== -->
        <!-- RESUMEN ECONÓMICO (TOTALES)                -->
        <!-- ========================================== -->
        <div style="margin-top: 30px; display: flex; justify-content: flex-end;">
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 20px; width: 350px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                <h3 style="margin-top: 0; color: #0f4c81; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; font-size: 1.1rem;">
                    <i class="fa-solid fa-calculator"></i> Resumen Económico
                </h3>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: #475569; font-size: 0.95rem;">
                    <span>Mano de Obra:</span>
                    <span><?php echo number_format($totalEmpleados, 2, ',', '.'); ?> €</span>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px; color: #475569; font-size: 0.95rem;">
                    <span>Materiales:</span>
                    <span><?php echo number_format($totalMateriales, 2, ',', '.'); ?> €</span>
                </div>
                
                <div style="display: flex; justify-content: space-between; border-top: 2px solid #e2e8f0; padding-top: 15px; font-size: 1.3rem; font-weight: bold; color: #0f4c81;">
                    <span>TOTAL:</span>
                    <span><?php echo number_format($granTotal, 2, ',', '.'); ?> €</span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ========================================== -->
<!-- ESTILOS UNIFICADOS                         -->
<!-- ========================================== -->
<style>
    .formulario-estandar fieldset { 
        border: 1px solid #cbd5e1; 
        border-radius: 8px; 
        padding: 25px; 
        background: #ffffff; 
        margin-bottom: 20px; 
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .formulario-estandar legend { 
        background: #0f4c81; 
        color: #ffffff; 
        padding: 6px 18px; 
        border-radius: 20px; 
        font-size: 0.95rem; 
        font-weight: bold; 
    }
    .grid-3 { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
        gap: 20px; 
    }
    
    .btn-principal { 
        background: #0f4c81; 
        color: white; 
        padding: 10px 20px; 
        border: none; 
        border-radius: 6px; 
        font-weight: 600; 
        display: inline-block;
        transition: opacity 0.2s;
    }
    .btn-secundario { 
        background: #475569; 
        color: white; 
        padding: 10px 20px; 
        border: none; 
        border-radius: 6px; 
        font-weight: 600;
        display: inline-block;
        transition: opacity 0.2s;
    }
    .btn-principal:hover, .btn-secundario:hover {
        opacity: 0.9;
    }
</style>