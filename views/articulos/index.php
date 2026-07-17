<div class="cabecera-modulo">
    <h1>Directorio de Inventario</h1>
    <!-- Este botón lleva al formulario de alta -->
    <a href="/index.php?controller=articulo&action=crear" class="btn-primario">+ Nuevo Inventario</a>
</div>
<div class="contenedor-tabla">
    <table class="tabla-datos">
        <thead>
            <tr>
                <th>Tipo (Prefijo)</th>
                <th>Denominación</th>
                <th>Atributos / Características</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($articulos)): foreach ($articulos as $art): ?>
                <tr>
                    <td>
                        <span class="badge-prefijo"><?php echo htmlspecialchars($art['prefijo_tipo']); ?></span>
                    </td>
                    <td><strong><?php echo htmlspecialchars($art['denominacion']); ?></strong></td>
                    
                    <!-- NUEVA CELDA DE ATRIBUTOS FORMATEADOS -->
                    <td style="max-width: 400px;">
                        <div class="contenedor-atributos">
                            <?php 
                            $datos = json_decode($art['datos_dinamicos'], true);
                            if (is_array($datos) && !empty($datos)): 
                                foreach ($datos as $clave => $valor): 
                                    // Ocultamos los campos vacíos para que quede más limpio
                                    if ($valor !== '' && $valor !== null):
                                        // Formateamos la clave (ej: "marca_coche" -> "Marca Coche")
                                        $etiqueta = ucwords(str_replace('_', ' ', $clave));
                            ?>
                                        <span class="attr-badge">
                                            <strong><?php echo htmlspecialchars($etiqueta); ?>:</strong> 
                                            <?php echo htmlspecialchars($valor); ?>
                                        </span>
                            <?php 
                                    endif;
                                endforeach; 
                            else: 
                            ?>
                                <span class="attr-vacio">Sin atributos extra</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    
                    <td class="celda-acciones">
                        <a href="/index.php?controller=articulo&action=editar&prefijo=<?php echo urlencode($art['prefijo_tipo']); ?>&denominacion=<?php echo urlencode($art['denominacion']); ?>" class="btn-sm btn-editar">
                            <i class="fa-solid fa-pen"></i> Editar
                        </a>
                        <a href="/index.php?controller=articulo&action=eliminar&prefijo=<?php echo urlencode($art['prefijo_tipo']); ?>&denominacion=<?php echo urlencode($art['denominacion']); ?>" onclick="return confirm('¿Borrar registro?');" class="btn-sm btn-eliminar">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="4" class="tabla-vacia">No hay inventario registrado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
    /* Estilos para que el prefijo se vea como en tu imagen */
    .badge-prefijo {
        background: #e2e8f0; 
        color: #1e293b;
        padding: 4px 10px; 
        border-radius: 4px; 
        font-size: 0.85em; 
        font-weight: bold;
        letter-spacing: 0.5px;
    }

    /* Contenedor flexible para que los atributos se acomoden bien */
    .contenedor-atributos {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    /* Diseño de píldora para cada par Clave: Valor */
    .attr-badge {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.85em;
        padding: 3px 8px;
        border-radius: 6px;
        white-space: nowrap;
    }

    .attr-badge strong {
        color: #0f4c81;
        font-weight: 600;
        margin-right: 2px;
    }

    .attr-vacio {
        color: #94a3b8;
        font-style: italic;
        font-size: 0.85em;
    }
</style>