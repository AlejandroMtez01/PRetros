<div class="encabezado-seccion" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h2 style="margin-bottom: 5px;">Auditoría y Conciliación de Horas</h2>
        <p style="color: #64748b; margin: 0; font-size: 1.1rem;">
            Detecta trabajos no facturados o partes de horas faltantes.
        </p>
    </div>
</div>

<!-- BARRA DE FILTROS -->
<div class="contenedor-filtros">
    <form action="/index.php" method="GET" style="margin: 0;">
        <input type="hidden" name="controller" value="auditoria">
        <input type="hidden" name="action" value="index">
        <div class="filtros-flex">
            <div class="rango-fechas">
                <label>Desde:</label>
                <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($fechaInicio); ?>">
                <label>Hasta:</label>
                <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($fechaFin); ?>">
                <button type="submit" class="btn-secundario"><i class="fa-solid fa-magnifying-glass"></i> &nbsp;Analizar</button>
            </div>
        </div>
    </form>
</div>

<!-- RESULTADOS -->
<?php if (empty($inconsistencias)): ?>
    <div style="background: #ecfdf5; border: 1px solid #10b981; border-radius: 12px; padding: 40px; text-align: center; color: #047857;">
        <i class="fa-solid fa-check-double" style="font-size: 3rem; margin-bottom: 15px;"></i>
        <h3>¡Todo Cuadra Perfectamente!</h3>
        <p>No se han detectado inconsistencias entre Albaranes y Partes en este periodo.</p>
    </div>
<?php else: ?>
    <div class="alertas-grid">
        <?php foreach ($inconsistencias as $inc): ?>
            <div class="tarjeta-alerta <?php echo $inc['gravedad']; ?>">
                <div class="alerta-cabecera">
                    <span class="badge <?php echo $inc['gravedad']; ?>"><?php echo $inc['alerta']; ?></span>
                    <strong><?php echo date('d/m/Y', strtotime($inc['fecha'])); ?></strong>
                </div>
                
                <p class="alerta-mensaje"><?php echo $inc['mensaje']; ?></p>
                
                <div class="alerta-datos">
                    <div><i class="fa-solid fa-user"></i> <?php echo $inc['empleado']; ?></div>
                    <div><i class="fa-solid fa-building"></i> <?php echo $inc['cliente']; ?></div>
                    <div><i class="fa-regular fa-clock"></i> <?php echo $inc['horario']; ?></div>
                </div>

                <div class="alerta-pie">
                    <span class="origen"><i class="fa-solid fa-file-lines"></i> <?php echo $inc['origen']; ?></span>
                    <a href="<?php echo $inc['enlace']; ?>" class="btn-solucionar">Analizar &nbsp;<i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
    .contenedor-filtros { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 15px 20px; margin-bottom: 25px; }
    .filtros-flex { display: flex; gap: 15px; align-items: center; }
    .rango-fechas { display: flex; gap: 10px; align-items: center; font-weight: 600; color: #334155; }
    .rango-fechas input { padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; }
    .btn-secundario { background: #475569; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; }
    
    .alertas-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px; }
    
    .tarjeta-alerta { background: white; border: 1px solid #e2e8f0; border-left: 5px solid; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .tarjeta-alerta.warning { border-left-color: #f59e0b; }
    .tarjeta-alerta.danger { border-left-color: #ef4444; }
    
    .alerta-cabecera { display: flex; justify-content: space-between; margin-bottom: 15px; align-items: center; }
    .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; }
    .badge.warning { background: #fef3c7; color: #b45309; }
    .badge.danger { background: #fee2e2; color: #b91c1c; }
    
    .alerta-mensaje { font-size: 0.95rem; color: #334155; font-weight: 500; margin-bottom: 15px; line-height: 1.4; }
    
    .alerta-datos { background: #f8fafc; padding: 10px 15px; border-radius: 6px; font-size: 0.85rem; color: #475569; margin-bottom: 15px; display: grid; gap: 5px; }
    .alerta-datos i { width: 20px; color: #94a3b8; text-align: center; }
    
    .alerta-pie { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 15px; }
    .origen { font-size: 0.85rem; font-weight: bold; color: #64748b; }
    .btn-solucionar { background: transparent; border: 1px solid #cbd5e1; color: #0f4c81; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: bold; transition: all 0.2s; }
    .btn-solucionar:hover { background: #f1f5f9; border-color: #0f4c81; }
</style>