<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Asegurarnos de que las variables $parte y $lineas existen (pasadas desde ParteController)
if (!isset($parte) || empty($parte)) {
    echo "<div class='alerta-error' style='background-color: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px; margin: 20px;'><i class='fa-solid fa-triangle-exclamation'></i> No se ha encontrado el parte de trabajo.</div>";
    exit;
}

$tituloPantalla = 'Detalle del Parte de Trabajo #' . htmlspecialchars($parte['id']);
?>

<div class="contenedor-albaran">
    <div class="encabezado-seccion" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><?php echo $tituloPantalla; ?></h2>
        
        <div class="botonera-acciones" style="display: flex; gap: 10px;">
            <a href="/index.php?controller=partes&action=editar&id=<?php echo $parte['id']; ?>" class="btn-principal" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-pen"></i> Editar Parte
            </a>
            <a href="/index.php?controller=partes" class="btn-secundario" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="formulario-estandar">
        <!-- ========================================== -->
        <!-- CABECERA DEL PARTE (Lectura)               -->
        <!-- ========================================== -->
        <fieldset>
            <legend>Datos del Parte</legend>
            <div class="grid-3">
                <div class="form-group">
                    <label>Empleado asignado</label>
                    <div class="campo-lectura">
                        <strong><?php echo htmlspecialchars($parte['nombreEmpleado'] ?? 'Empleado desconocido'); ?></strong>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Fecha Desde</label>
                    <div class="campo-lectura">
                        <?php echo !empty($parte['fechaDesde']) ? date('d/m/Y', strtotime($parte['fechaDesde'])) : '-'; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Fecha Hasta</label>
                    <div class="campo-lectura">
                        <?php echo !empty($parte['fechaHasta']) ? date('d/m/Y', strtotime($parte['fechaHasta'])) : '-'; ?>
                    </div>
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 15px;">
                <label>Observaciones Generales</label>
                <div class="campo-lectura" style="min-height: 42px;">
                    <?php echo nl2br(htmlspecialchars($parte['observaciones'] ?? 'Sin observaciones.')); ?>
                </div>
            </div>
        </fieldset>

        <br>

        <!-- ========================================== -->
        <!-- LÍNEAS DEL PARTE (Lectura)                 -->
        <!-- ========================================== -->
        <fieldset>
            <legend>Líneas de Actividad</legend>

            <div class="contenedor-tabla">
                <table class="tabla-datos" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 18%; text-align: left;">Cliente</th>
                            <th style="width: 18%; text-align: left;">Centro</th>
                            <th style="width: 10%; text-align: center;">H. Desde</th>
                            <th style="width: 10%; text-align: center;">H. Hasta</th>
                            <th style="width: 14%; text-align: left;">Categoría / Puesto</th>
                            <th style="width: 12%; text-align: left;">Vehículo</th>
                            <th style="width: 18%; text-align: left;">Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($lineas)): ?>
                            <?php foreach ($lineas as $l): ?>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td><strong><?php echo htmlspecialchars($l['nombreCliente'] ?? '-'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($l['nombreCentro'] ?? '-'); ?></td>
                                    
                                    <td style="text-align: center; color: #0f4c81; font-weight: bold;">
                                        <?php echo substr($l['horaDesde'], 0, 5); ?>
                                    </td>
                                    <td style="text-align: center; color: #0f4c81; font-weight: bold;">
                                        <?php echo substr($l['horaHasta'], 0, 5); ?>
                                    </td>
                                    
                                    <td>
                                        <span style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; color: #475569; font-weight: bold;">
                                            <?php echo htmlspecialchars($l['categoriaProfesional'] ?? '-'); ?>
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <?php if (!empty($l['vehiculoUtilizado'])): ?>
                                            <i class="fa-solid fa-truck" style="color: #64748b; font-size: 0.8rem; margin-right: 4px;"></i> 
                                            <?php echo htmlspecialchars($l['vehiculoUtilizado']); ?>
                                        <?php else: ?>
                                            <span style="color: #94a3b8;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td>
                                        <span style="font-size: 0.9rem; color: #475569;">
                                            <?php echo htmlspecialchars($l['observaciones'] ?? ''); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px; color: #64748b; background-color: #f8fafc;">
                                    <i class="fa-solid fa-list-check" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                                    Este parte no tiene ninguna línea registrada.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </fieldset>
    </div>
</div>

<!-- ========================================== -->
<!-- ESTILOS UNIFICADOS                         -->
<!-- ========================================== -->
<style>
    /* Estructura Formulario Estándar (Idéntico a Albarán) */
    .formulario-estandar fieldset {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 20px;
        background: #f8fafc;
        margin-bottom: 20px;
    }
    .formulario-estandar legend {
        background: #0f4c81;
        color: #ffffff;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: bold;
    }
    .form-group {
        margin-bottom: 15px;
        display: flex;
        flex-direction: column;
    }
    .form-group label {
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
        font-size: 0.85rem;
    }
    .grid-3 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    
    /* Simulación de Input Readonly */
    .campo-lectura {
        padding: 10px;
        background-color: #e2e8f0;
        border: 1px solid #94a3b8;
        border-radius: 6px;
        font-size: 0.95rem;
        font-family: inherit;
        color: #334155;
        width: 100%;
        box-sizing: border-box;
        cursor: default;
    }

    /* Tabla de Datos */
    .contenedor-tabla {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        overflow: hidden;
    }
    .tabla-datos {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }
    .tabla-datos th {
        background: #f1f5f9;
        border-bottom: 2px solid #e2e8f0;
        padding: 12px 15px;
        color: #475569;
        font-weight: 600;
    }
    .tabla-datos td {
        padding: 12px 15px;
        vertical-align: middle;
    }
    .tabla-datos tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Botones */
    .btn-principal {
        background: #0f4c81;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .btn-secundario {
        background: #475569;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .btn-principal:hover, .btn-secundario:hover {
        opacity: 0.9;
    }
</style>