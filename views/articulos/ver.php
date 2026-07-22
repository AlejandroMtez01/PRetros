<div class="contenedor-albaran">
    <div class="encabezado-seccion" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Ficha de Inventario</h2>
        <a href="/index.php?controller=articulo&action=index" class="btn-secundario" style="text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i>&nbsp;Volver al Listado
        </a>
    </div>

    <!-- Usamos tu clase de formulario estándar para mantener el diseño -->
    <div class="formulario-estandar">
        
        <!-- ========================================== -->
        <!-- BLOQUE 1: DATOS PRINCIPALES                -->
        <!-- ========================================== -->
        <fieldset>
            <legend>Datos Principales</legend>
            <div class="grid-2">
                <div class="form-group">
                    <label>Familia / Prefijo</label>
                    <div class="dato-readonly"><?php echo htmlspecialchars($articulo['prefijo_tipo']); ?></div>
                </div>
                <div class="form-group">
                    <label>Denominación del Registro</label>
                    <div class="dato-readonly"><?php echo htmlspecialchars($articulo['denominacion']); ?></div>
                </div>
            </div>
        </fieldset>

        <br>

        <!-- ========================================== -->
        <!-- BLOQUE 2: ESPECIFICACIONES TÉCNICAS        -->
        <!-- ========================================== -->
        <fieldset>
            <legend>Atributos y Características</legend>
            
            <?php 
            $datos = json_decode($articulo['datos_dinamicos'], true);
            
            if (is_array($datos) && !empty($datos)):
            ?>
                <!-- Usamos tu grid-3 clásico -->
                <div class="grid-3">
                    <?php
                    foreach ($datos as $clave => $valor):
                        if ($valor !== '' && $valor !== null):
                            $etiqueta = ucwords(str_replace('_', ' ', $clave));
                            $valor_mostrar = htmlspecialchars($valor);
                            
                            // 1. Formato Fecha Español
                            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
                                $valor_mostrar = date('d/m/Y', strtotime($valor));
                            } 
                            // 2. Formato Diccionario COD (DESCRIPCION)
                            elseif (isset($opciones_tablas)) {
                                foreach ($opciones_tablas as $tabla => $opciones) {
                                    foreach ($opciones as $opt) {
                                        if ($opt['codigo'] === $valor) {
                                            $valor_mostrar = htmlspecialchars($valor . ' (' . $opt['descripcion'] . ')');
                                            break 2;
                                        }
                                    }
                                }
                            }
                    ?>
                        <div class="form-group">
                            <label><?php echo htmlspecialchars($etiqueta); ?></label>
                            <div class="dato-readonly"><?php echo $valor_mostrar; ?></div>
                        </div>
                    <?php 
                        endif;
                    endforeach;
                    ?>
                </div>
            <?php else: ?>
                <div style="padding: 10px; color: #64748b; font-style: italic;">
                    No hay atributos dinámicos adicionales registrados.
                </div>
            <?php endif; ?>
        </fieldset>

        <br>
        
        <!-- Botón de acción con tu estilo principal -->
        <a href="/index.php?controller=articulo&action=editar&prefijo=<?php echo urlencode($articulo['prefijo_tipo']); ?>&denominacion=<?php echo urlencode($articulo['denominacion']); ?>" class="btn-principal" style="text-decoration: none; display: inline-block;">
            <i class="fa-solid fa-pen"></i> Modificar Inventario
        </a>
    </div>
</div>

<!-- ========================================== -->
<!-- ESTILOS UNIFICADOS DE ALBARÁN/FORMULARIO   -->
<!-- ========================================== -->
<style>
    /* Estilos copiados exactamente de tu form.php para mantener la coherencia */
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

    /* Caja que imita a un input, pero de solo lectura (fondo gris) */
    .dato-readonly {
        padding: 10px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.95rem;
        font-family: inherit;
        background: #e2e8f0; 
        color: #1e293b;
        font-weight: 500;
        min-height: 20px;
        box-sizing: border-box;
    }

    .grid-3 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .grid-2 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .btn-principal {
        background: #0f4c81;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 1rem;
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