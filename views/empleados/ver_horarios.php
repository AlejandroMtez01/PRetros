<div class="contenedor-albaran">
    
    <!-- 1. ENCABEZADO (Separado de los filtros) -->
    <div class="encabezado-seccion" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px;">
        <div>
            <h2 style="margin-bottom: 5px;">Registro de Horas</h2>
            <p style="color: #64748b; margin: 0; font-size: 1.1rem;">
                <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido1']. ' ' . $empleado['apellido2']); ?>
            </p>
        </div>
        <a href="/index.php?controller=empleado&action=index" class="btn-secundario" style="text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i> &nbsp;Volver a Empleados
        </a>
    </div>

    <!-- 2. BARRA DE FILTROS DE FECHA -->
    <div class="contenedor-filtros">
        <form action="/index.php" method="GET" id="formFechas" style="margin: 0;">
            <input type="hidden" name="controller" value="empleado">
            <input type="hidden" name="action" value="verHorarios">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($empleado['id']); ?>">

            <div class="filtros-flex">
                <div class="botones-rapidos">
                    <button type="button" class="btn-filtro" onclick="setFiltro('hoy')">Hoy</button>
                    <button type="button" class="btn-filtro" onclick="setFiltro('semana')">Esta Semana</button>
                    <button type="button" class="btn-filtro" onclick="setFiltro('mes')">Este Mes</button>
                    <button type="button" class="btn-filtro" onclick="setFiltro('año')">Este Año</button>
                    <button type="button" class="btn-filtro btn-limpiar" onclick="setFiltro('todo')">Todo</button>
                </div>

                <div class="rango-fechas">
                    <label for="fecha_inicio">Desde:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fechaInicio ?? ''); ?>">
                    
                    <label for="fecha_fin">Hasta:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fechaFin ?? ''); ?>">
                    
                    <button type="submit" class="btn-secundario" style="padding: 8px 15px; margin-left: 10px;">
                        <i class="fa-solid fa-filter"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- 3. RESULTADOS DE HORARIOS -->
    <?php if (empty($dias_trabajados)): ?>
        <div class="alerta-vacia">
            <i class="fa-regular fa-calendar-xmark" style="font-size: 2rem; margin-bottom: 10px; color: #94a3b8;"></i><br>
            Este empleado no tiene horas registradas en ningún Parte o Albarán.
        </div>
    <?php else: ?>
        <div class="timeline-horarios">
            <?php foreach ($dias_trabajados as $fecha => $datosDia): 
                $horas_completas = floor($datosDia['minutos_totales'] / 60);
                $minutos_restantes = $datosDia['minutos_totales'] % 60;
                $texto_sumatorio = $horas_completas . 'h ' . ($minutos_restantes > 0 ? $minutos_restantes . 'm' : '');
            ?>
                <div class="tarjeta-dia">
                    <div class="cabecera-dia">
                        <h3 class="fecha-dia">
                            <i class="fa-regular fa-calendar-check"></i> 
                            <?php echo date('d/m/Y', strtotime($fecha)); ?>
                        </h3>
                        <div class="sumatorio-badge">
                            <span class="etiqueta-total">TOTAL DÍA</span>
                            <span class="valor-total"><?php echo $texto_sumatorio; ?></span>
                        </div>
                    </div>

                    <div class="cuerpo-dia">
                        <table class="tabla-lineas-dia">
                            <thead>
                                <tr>
                                    <th>Origen</th>
                                    <th>Cliente</th>
                                    <th>Puesto Asignado</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th style="text-align: right;">Duración</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datosDia['lineas'] as $linea): 
                                    $ini = strtotime($linea['horaDesde']);
                                    $fi = strtotime($linea['horaHasta']);
                                    if ($fi < $ini) $fi += 86400;
                                    $mins = round(($fi - $ini) / 60);
                                    $h = floor($mins / 60);
                                    $m = $mins % 60;
                                ?>
                                    <tr>
                                        <td>
                                            <a href="/index.php?controller=partes&action=editar&id=<?php echo $linea['id_documento']; ?>" 
                                               class="badge-origen parte" 
                                               style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;"
                                               title="Ir al Parte de Trabajo">
                                                <?php echo htmlspecialchars($linea['documento_origen'] . ' #' . $linea['id_documento']); ?>&nbsp;
                                                <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.8em; opacity: 0.8;"></i>
                                            </a>
                                        </td>
                                        <td style="font-weight: 500; color: #334155;">
                                            <?php echo htmlspecialchars($linea['cliente'] ?? 'Sin asignar'); ?>
                                        </td>
                                        <td style="color: #64748b;">
                                            <?php echo htmlspecialchars($linea['puesto']); ?>
                                        </td>
                                        <td><strong><?php echo date('H:i', $ini); ?></strong></td>
                                        <td><strong><?php echo date('H:i', $fi); ?></strong></td>
                                        <td style="text-align: right; color: #0f4c81; font-weight: 600;">
                                            <?php echo $h . 'h ' . ($m > 0 ? $m . 'm' : ''); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ========================================== -->
<!-- SCRIPT DE FILTROS                          -->
<!-- ========================================== -->
<script>
    function setFiltro(rango) {
        const hoy = new Date();
        let inicio = '';
        let fin = '';

        if (rango !== 'todo') {
            const formatearFecha = (fecha) => {
                const year = fecha.getFullYear();
                const month = String(fecha.getMonth() + 1).padStart(2, '0');
                const day = String(fecha.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            fin = formatearFecha(hoy);

            if (rango === 'hoy') {
                inicio = fin;
            } else if (rango === 'semana') {
                const diaSemana = hoy.getDay(); 
                const diferencia = hoy.getDate() - diaSemana + (diaSemana === 0 ? -6 : 1);
                const lunes = new Date(hoy.setDate(diferencia));
                inicio = formatearFecha(lunes);
            } else if (rango === 'mes') {
                const primerDiaMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                inicio = formatearFecha(primerDiaMes);
            } else if (rango === 'año') {
                const primerDiaAnio = new Date(hoy.getFullYear(), 0, 1);
                inicio = formatearFecha(primerDiaAnio);
            }
        }

        document.getElementById('fecha_inicio').value = inicio;
        document.getElementById('fecha_fin').value = fin;
        document.getElementById('formFechas').submit();
    }
</script>

<!-- ========================================== -->
<!-- ESTILOS                                    -->
<!-- ========================================== -->
<style>
    .alerta-vacia {
        background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px;
        padding: 50px 20px; text-align: center; color: #64748b; font-size: 1.1rem;
    }
    
    .timeline-horarios {
        display: flex; flex-direction: column; gap: 25px;
    }

    .tarjeta-dia {
        background: #ffffff; border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        border: 1px solid #e2e8f0; overflow: hidden;
    }

    .cabecera-dia {
        display: flex; justify-content: space-between; align-items: center;
        background: #f8fafc; padding: 15px 20px; border-bottom: 1px solid #e2e8f0;
    }

    .fecha-dia {
        margin: 0; font-size: 1.25rem; color: #0f4c81; font-weight: 700;
        display: flex; align-items: center; gap: 10px;
    }

    .sumatorio-badge {
        background: #ecfdf5; border: 1px solid #10b981; border-radius: 8px;
        display: flex; overflow: hidden; align-items: stretch;
    }

    .etiqueta-total {
        background: #10b981; color: white; padding: 6px 12px;
        font-size: 0.75rem; font-weight: bold; letter-spacing: 1px;
        display: flex; align-items: center; justify-content: center;
    }

    .valor-total {
        padding: 6px 15px; color: #047857; font-weight: 800;
        font-size: 1.1rem; display: flex; align-items: center;
    }

    .tabla-lineas-dia {
        width: 100%; border-collapse: collapse;
    }

    .tabla-lineas-dia th {
        text-align: left; padding: 12px 20px; font-size: 0.85rem;
        color: #94a3b8; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;
    }

    .tabla-lineas-dia td {
        padding: 15px 20px; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem;
    }

    .tabla-lineas-dia tr:last-child td {
        border-bottom: none;
    }

    .tabla-lineas-dia tr:hover {
        background: #f8fafc;
    }

    .badge-origen {
        padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;
    }
    .badge-origen.parte { background: #e0e7ff; color: #4338ca; }
    .badge-origen.albaran { background: #ffedd5; color: #c2410c; }

    .contenedor-filtros {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 15px 20px;
        margin-bottom: 25px;
    }
    
    .filtros-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .botones-rapidos {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .btn-filtro {
        background: #ffffff;
        border: 1px solid #94a3b8;
        color: #475569;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-filtro:hover {
        background: #e2e8f0;
        color: #0f4c81;
        border-color: #0f4c81;
    }
    
    .btn-limpiar {
        background: #fee2e2;
        color: #ef4444;
        border-color: #f87171;
    }
    
    .btn-limpiar:hover {
        background: #fecaca;
        color: #b91c1c;
        border-color: #ef4444;
    }
    
    .rango-fechas {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.9rem;
        color: #334155;
        font-weight: 600;
        flex-wrap: wrap;
    }
    
    .rango-fechas input[type="date"] {
        padding: 6px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-family: inherit;
        color: #1e293b;
    }
</style>