<div class="cabecera-modulo">
    <h1>Directorio de Catalogo de Inventario</h1>
    <!-- Este botón lleva al formulario de alta -->
    <a href="/index.php?controller=catalogo_inventario&action=crear" class="btn-primario">+ Nuevo Catálogo de Inventario</a>
</div>
<div class="contenedor-tabla">
    <table class="tabla-datos">
        <thead>
            <tr>
                <th>Prefijo</th>
                <th>Nombre del Tipo</th>
                <th>Configuración (Campos)</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="lista-orden-inventario">
            <?php if (!empty($tipos)): ?>
                <?php foreach ($tipos as $tipo): ?>
                    <tr data-prefijo="<?php echo htmlspecialchars($tipo['prefijo']); ?>">
                        
                      
                        
                        <td><span class="badge-prefijo"><?php echo htmlspecialchars($tipo['prefijo']); ?></span></td>
                        <td><strong><?php echo htmlspecialchars($tipo['nombre_tipo']); ?></strong></td>
                        
                        <!-- NUEVA CELDA DE CAMPOS CONFIGURADOS CON ICONOS DINÁMICOS -->
                        <td style="max-width: 400px;">
                            <div class="contenedor-atributos">
                                <?php 
                                $esquema = json_decode($tipo['esquema_configuracion'], true);
                                if (is_array($esquema) && !empty($esquema)): 
                                    foreach ($esquema as $campo): 
                                        
                                        // 1. Definimos el icono y el color según el tipo de dato
                                        $icono = 'fa-solid fa-cube'; // Icono por defecto
                                        $color_icono = '#94a3b8';    // Color gris por defecto
                                        
                                        if ($campo['tipo_dato'] === 'text') {
                                            $icono = 'fa-solid fa-align-left'; // Icono de texto
                                            $color_icono = '#3b82f6';          // Azul
                                        } elseif ($campo['tipo_dato'] === 'number') {
                                            $icono = 'fa-solid fa-hashtag';    // Icono numérico
                                            $color_icono = '#10b981';          // Verde
                                        } elseif ($campo['tipo_dato'] === 'date') {
                                            $icono = 'fa-solid fa-calendar-days'; // Icono de calendario
                                            $color_icono = '#f59e0b';             // Naranja
                                        } elseif ($campo['tipo_dato'] === 'lookup') {
                                            $icono = 'fa-solid fa-table-list';    // Icono de tabla vinculada
                                            $color_icono = '#8b5cf6';             // Morado
                                        }
                                ?>
                                        <span class="attr-badge" title="Tipo: <?php echo htmlspecialchars($campo['tipo_dato']); ?>">
                                            
                                            <!-- 2. Imprimimos el icono dinámico -->
                                            <i class="<?php echo $icono; ?>" style="color: <?php echo $color_icono; ?>; font-size: 0.85em; margin-right: 5px;"></i>
                                            
                                            <?php echo htmlspecialchars($campo['descripcion']); ?>
                                            
                                            <?php if(!empty($campo['es_obligatorio'])) echo '<span style="color: #ef4444; font-weight: bold; margin-left: 2px;">*</span>'; ?>
                                        </span>
                                <?php 
                                    endforeach; 
                                else: 
                                ?>
                                    <span class="attr-vacio">Sin campos configurados</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        
                        <td class="celda-acciones">
                            <a href="/index.php?controller=catalogo_inventario&action=editar&id=<?php echo urlencode($tipo['prefijo']); ?>" class="btn-sm btn-editar">
                                <i class="fa-solid fa-pen"></i> Editar
                            </a>
                            <a href="/index.php?controller=catalogo_inventario&action=eliminar&id=<?php echo urlencode($tipo['prefijo']); ?>" onclick="return confirm('¿Seguro que deseas eliminar este tipo de inventario?');" class="btn-sm btn-eliminar">
                                <i class="fa-solid fa-xmark"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="tabla-vacia">
                        No hay tipos de inventario configurados.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
    /* Estilos compartidos para las píldoras (badges) */
    .badge-prefijo {
        background: #e2e8f0; 
        color: #1e293b;
        padding: 4px 10px; 
        border-radius: 4px; 
        font-size: 0.85em; 
        font-weight: bold;
        letter-spacing: 0.5px;
    }

    .contenedor-atributos {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .attr-badge {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.85em;
        padding: 4px 8px;
        border-radius: 6px;
        white-space: nowrap;
        display: flex;
        align-items: center;
    }

    .attr-vacio {
        color: #94a3b8;
        font-style: italic;
        font-size: 0.85em;
    }

    /* Efectos visuales durante el arrastre (SortableJS) */
    .sortable-ghost { 
        opacity: 0.5; 
        background-color: #f8fafc !important; 
    }
    .handle:active { 
        cursor: grabbing !important; 
        color: #0f4c81 !important;
    }
</style>