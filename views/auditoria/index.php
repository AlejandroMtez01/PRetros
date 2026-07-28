<div class="encabezado-seccion" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h2 style="margin-bottom: 5px;">Auditoría: Precisión por Líneas</h2>
        <p style="color: #64748b; margin: 0; font-size: 1.1rem;">
            Analiza el solapamiento exacto de cada línea trabajada frente a las facturadas.
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
                <button type="submit" class="btn-secundario"><i class="fa-solid fa-microscope"></i> &nbsp;Analizar Líneas</button>
            </div>
        </div>
    </form>
</div>

<!-- RESULTADOS -->
<?php if (empty($inconsistencias)): ?>
    <div style="background: #ecfdf5; border: 1px solid #10b981; border-radius: 12px; padding: 40px; text-align: center; color: #047857;">
        <i class="fa-solid fa-list-check" style="font-size: 3rem; margin-bottom: 15px;"></i>
        <h3>¡Auditoría Limpia!</h3>
        <p>No hay ni un minuto de descuadre. Todas las líneas de los Partes tienen su reflejo exacto en los Albaranes.</p>
    </div>
<?php else: ?>
    <div class="alertas-grid">
        <?php foreach ($inconsistencias as $inc): ?>
            <div class="tarjeta-alerta <?php echo $inc['tipo']; ?>">
                <div class="alerta-cabecera">
                    <span class="badge <?php echo $inc['tipo']; ?>"><?php echo $inc['alerta']; ?></span>
                    <strong><?php echo date('d/m/Y', strtotime($inc['fecha'])); ?></strong>
                </div>
                
                <div class="alerta-datos">
                    <div><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($inc['empleado']); ?></div>
                    <div><i class="fa-solid fa-building"></i> <?php echo htmlspecialchars($inc['cliente']); ?></div>
                    <p style="font-size: 13px;"><?php echo htmlspecialchars($inc['razon']); ?></p>
                </div>

                <!-- IDENTIFICADOR EXACTO DE LA LÍNEA Y TIEMPO PENDIENTE -->
                <div class="linea-conflicto <?php echo $inc['tipo']; ?>">
                    <div class="conflicto-datos">
                        <span class="detalle"><i class="fa-solid fa-briefcase"></i> <?php echo htmlspecialchars($inc['linea_detalle']); ?></span>
                        <span class="horario"><i class="fa-regular fa-clock"></i> <?php echo $inc['linea_horario']; ?> <small style="color: #94a3b8; font-weight: normal;">(Línea original: <?php echo $inc['total_original']; ?>)</small></span>
                    </div>
                    <div class="conflicto-pendiente <?php echo $inc['tipo']; ?>">
                        <strong><?php echo $inc['pendiente']; ?> Pendiente</strong>
                    </div>
                </div>

                <?php if (!empty($inc['solapes'])): ?>
                    <p class="nota-solapes">
                        <i class="fa-solid fa-code-merge"></i> El resto del tiempo de esta línea sí cruzó correctamente con: <?php echo implode(', ', $inc['solapes']); ?>
                    </p>
                <?php endif; ?>

                <div class="alerta-pie">
                    <span class="origen <?php echo $inc['tipo']; ?>"><i class="fa-solid fa-file-invoice"></i> <?php echo $inc['documento']; ?></span>
                    <a href="<?php echo $inc['enlace']; ?>" class="btn-solucionar <?php echo $inc['tipo']; ?>"><?php echo $inc['texto_enlace']; ?> <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
    .contenedor-filtros { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 15px 20px; margin-bottom: 25px; }
    .filtros-flex { display: flex; gap: 15px; align-items: center; }
    .rango-fechas { display: flex; gap: 10px; align-items: center; font-weight: 600; color: #334155; }
    .rango-fechas input { padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; }
    .btn-secundario { background: #0f4c81; color: white; border: none; padding: 9px 15px; border-radius: 6px; cursor: pointer; font-weight: bold; }
    
    .alertas-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(420px, 1fr)); gap: 20px; }
    
    .tarjeta-alerta { background: white; border: 1px solid #e2e8f0; border-left: 5px solid; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; flex-direction: column; }
    .tarjeta-alerta.warning { border-left-color: #f59e0b; }
    .tarjeta-alerta.danger { border-left-color: #ef4444; }
    
    .alerta-cabecera { display: flex; justify-content: space-between; margin-bottom: 15px; align-items: center; }
    .badge { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
    .badge.warning { background: #fef3c7; color: #d97706; }
    .badge.danger { background: #fee2e2; color: #dc2626; }
    
    .alerta-datos { display: flex; flex-direction: column; gap: 8px; font-size: 0.95rem; color: #334155; font-weight: 500; margin-bottom: 15px; }
    .alerta-datos i { width: 20px; color: #94a3b8; text-align: center; margin-right: 5px; }

    /* NUEVA SECCIÓN DE LÍNEA EXACTA */
    .linea-conflicto { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; border-radius: 6px; margin-bottom: 10px; border: 1px solid transparent; }
    .linea-conflicto.warning { background-color: #fffbeb; border-color: #fde68a; }
    .linea-conflicto.danger { background-color: #fef2f2; border-color: #fecaca; }
    
    .conflicto-datos { display: flex; flex-direction: column; gap: 5px; }
    .conflicto-datos .detalle { font-weight: 700; color: #1e293b; font-size: 1.05rem; }
    .conflicto-datos .horario { font-weight: 600; color: #475569; font-size: 0.9rem; }
    .conflicto-datos i { color: #64748b; margin-right: 3px; }

    .conflicto-pendiente { padding: 8px 12px; border-radius: 6px; font-size: 1rem; text-align: center; }
    .conflicto-pendiente.warning { background-color: #fef3c7; color: #b45309; border: 1px dashed #f59e0b; }
    .conflicto-pendiente.danger { background-color: #fee2e2; color: #b91c1c; border: 1px dashed #ef4444; }

    .nota-solapes { font-size: 0.8rem; color: #64748b; margin-bottom: 15px; margin-top: 5px; padding-left: 5px; }
    
    .alerta-pie { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 15px; margin-top: auto; }
    
    .origen { font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 5px; color: #475569; }
    
    .btn-solucionar { background: transparent; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 700; transition: all 0.2s; white-space: nowrap; }
    .btn-solucionar.warning { color: #d97706; border-color: #fcd34d; }
    .btn-solucionar.warning:hover { background: #fef3c7; }
    .btn-solucionar.danger { color: #dc2626; border-color: #fca5a5; }
    .btn-solucionar.danger:hover { background: #fee2e2; }
</style>