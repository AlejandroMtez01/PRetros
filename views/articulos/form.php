<div class="encabezado-modulo">
    <h2><?php echo $titulo_formulario; ?></h2>
</div>

<div class="contenedor-formulario">
    <div class="tarjeta-formulario" style="max-width: 1000px;">
        
        <?php if (isset($errores['general'])): ?>
            <div class="alerta-errores" style="margin-bottom: 20px;"><?php echo $errores['general']; ?></div>
        <?php endif; ?>

        <form action="<?php echo $accion_url; ?>" method="POST">
            
            <div class="form-group" style="margin-bottom: 30px; background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <label for="denominacion" style="font-size: 1.1rem; color: #0f4c81;">Denominación (Nombre Único del Registro) *</label>
                <input type="text" id="denominacion" name="denominacion" required 
                       value="<?php echo isset($articulo_temp) ? htmlspecialchars($articulo_temp['denominacion']) : ''; ?>"
                       placeholder="Ej: Furgoneta Mercedes Matrícula 1234ABC"
                       style="font-size: 1.1rem; padding: 12px;"
                       <?php echo $es_edicion ? 'readonly style="background-color: #e2e8f0; cursor: not-allowed;" title="No se puede cambiar la denominación una vez creada"' : ''; ?>>
            </div>
            
            <div class="form-grid">
                <?php 
                if (!empty($esquema)): 
                    foreach ($esquema as $campo): 
                        $id_c = $campo['id_campo'];
                        $valor_actual = isset($articulo_temp['datos'][$id_c]) ? $articulo_temp['datos'][$id_c] : '';
                ?>
                    
                    <div class="form-group">
                        <label for="campo_<?php echo htmlspecialchars($id_c); ?>">
                            <?php echo htmlspecialchars($campo['descripcion']); ?>
                            <?php echo $campo['es_obligatorio'] ? ' *' : ''; ?>
                        </label>

                        <?php if ($campo['tipo_dato'] === 'text'): ?>
                            <input type="text" id="campo_<?php echo htmlspecialchars($id_c); ?>" name="datos[<?php echo htmlspecialchars($id_c); ?>]" 
                                   value="<?php echo htmlspecialchars($valor_actual); ?>"
                                   <?php echo $campo['es_obligatorio'] ? 'required' : ''; ?>>
                        
                        <?php elseif ($campo['tipo_dato'] === 'number'): ?>
                            <input type="number" step="any" id="campo_<?php echo htmlspecialchars($id_c); ?>" name="datos[<?php echo htmlspecialchars($id_c); ?>]" 
                                   value="<?php echo htmlspecialchars($valor_actual); ?>"
                                   <?php echo $campo['es_obligatorio'] ? 'required' : ''; ?>>
                        
                        <?php elseif ($campo['tipo_dato'] === 'date'): ?>
                            <input type="date" id="campo_<?php echo htmlspecialchars($id_c); ?>" name="datos[<?php echo htmlspecialchars($id_c); ?>]" 
                                   value="<?php echo htmlspecialchars($valor_actual); ?>"
                                   <?php echo $campo['es_obligatorio'] ? 'required' : ''; ?>>
                        
                       <!-- NUEVO BLOQUE LOOKUP MODAL CON VALIDACIÓN NATIVA HTML5 -->
                        <?php elseif ($campo['tipo_dato'] === 'lookup'): 
                            $cod_tabla = $campo['tabla_ayuda'];
                            
                            // Buscamos la descripción actual para mostrarla en el input visible
                            $desc_actual = $valor_actual; 
                            if (isset($opciones_tablas[$cod_tabla])) {
                                foreach($opciones_tablas[$cod_tabla] as $opt) {
                                    if ($opt['codigo'] === $valor_actual) {
                                        $desc_actual = $opt['descripcion'];
                                        break;
                                    }
                                }
                            }
                        ?>
                            <div class="input-grupo-modal" style="position: relative;">
                                
                                <!-- 1. INPUT REAL (Invisible pero validable por el navegador) -->
                                <!-- Al ser type="text" con opacidad 0, HTML5 obliga a rellenarlo y bloquea el botón Guardar -->
                                <input type="text" id="hidden_<?php echo htmlspecialchars($id_c); ?>" 
                                       name="datos[<?php echo htmlspecialchars($id_c); ?>]" 
                                       value="<?php echo htmlspecialchars($valor_actual); ?>"
                                       style="position: absolute; opacity: 0; width: 1px; height: 1px; bottom: 10px; left: 10px; pointer-events: none; z-index: -1;"
                                       tabindex="-1"
                                       <?php echo $campo['es_obligatorio'] ? 'required title="Debe seleccionar un valor del diccionario"' : ''; ?>>
                                
                                <!-- 2. INPUT VISIBLE PARA EL USUARIO -->
                                <input type="text" id="visible_<?php echo htmlspecialchars($id_c); ?>" 
                                       value="<?php echo htmlspecialchars($desc_actual); ?>" 
                                       readonly placeholder="Seleccionar..." 
                                       onclick="abrirModalDiccionario('<?php echo htmlspecialchars($id_c); ?>', '<?php echo htmlspecialchars($cod_tabla); ?>')"
                                       style="cursor: pointer; background-color: #ffffff; <?php echo $campo['es_obligatorio'] ? 'border-left: 3px solid #ef4444;' : ''; ?>">
                                       
                                <button type="button" onclick="abrirModalDiccionario('<?php echo htmlspecialchars($id_c); ?>', '<?php echo htmlspecialchars($cod_tabla); ?>')">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                
                <?php 
                    endforeach; 
                else: 
                ?>
                    <div style="grid-column: 1 / -1; padding: 20px; background: #fffbeb; color: #b45309; border: 1px solid #fde68a; border-radius: 8px;">
                        Este catálogo no tiene campos dinámicos configurados.
                    </div>
                <?php endif; ?>
            </div>

            <div class="acciones-formulario" style="margin-top: 40px;">
                <a href="/index.php?controller=articulo&action=index" class="btn-secundario">
                    <i class="fa-solid fa-xmark"></i>&nbsp;Cancelar
                </a>
                <button type="submit" class="btn-primario">
                    <i class="fa-solid fa-floppy-disk"></i>&nbsp;Guardar Registro
                </button>
            </div>
            
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL MAESTRO MULTI-TABLA                  -->
<!-- ========================================== -->
<div id="modalDiccionario" class="modal-overlay" style="display: none;">
    <div class="modal-contenido">
        <div class="modal-cabecera">
            <h3>Seleccionar Valor</h3>
            <button type="button" class="btn-cerrar-modal" onclick="cerrarModalDiccionario()">&times;</button>
        </div>
        <div class="modal-cuerpo">
            <input type="text" id="buscadorDiccionario" class="buscador-input" placeholder="Buscar..." onkeyup="filtrarDiccionario()">
            <ul id="listaDiccionario" class="lista-seleccion">
                <!-- Se inyecta por JS -->
            </ul>
        </div>
    </div>
</div>

<style>
    /* Estilos Modal (Reutilizados del Catálogo) */
    .input-grupo-modal { display: flex; align-items: stretch; gap: 5px; width: 100%; }
    .input-grupo-modal input { flex-grow: 1; cursor: pointer; background-color: #ffffff; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; }
    .input-grupo-modal input:focus { outline: none; border-color: #0f4c81; }
    .input-grupo-modal button { padding: 0 15px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; color: #475569; transition: all 0.2s; }
    .input-grupo-modal button:hover { background: #e2e8f0; color: #0f4c81; }

    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 9999; align-items: center; justify-content: center; }
    .modal-contenido { background: #fff; width: 90%; max-width: 500px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; max-height: 85vh; overflow: hidden; }
    .modal-cabecera { padding: 15px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; }
    .modal-cabecera h3 { margin: 0; font-size: 1.1rem; color: #1e293b; }
    .btn-cerrar-modal { background: none; border: none; font-size: 1.5rem; line-height: 1; cursor: pointer; color: #94a3b8; transition: color 0.2s; }
    .btn-cerrar-modal:hover { color: #ef4444; }
    .modal-cuerpo { padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; }
    .buscador-input { width: 100%; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem; box-sizing: border-box; }
    .buscador-input:focus { outline: none; border-color: #0f4c81; box-shadow: 0 0 0 3px rgba(15,76,129,0.1); }
    
    .lista-seleccion { list-style: none; margin: 0; padding: 0; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
    .lista-seleccion li { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: background 0.2s; }
    .lista-seleccion li:last-child { border-bottom: none; }
    .lista-seleccion li:hover { background: #f1f5f9; color: #0f4c81; }
    .lista-seleccion li span { font-size: 0.85rem; color: #64748b; font-family: monospace; }
</style>

<script>
    // Convertimos el array de PHP con TODOS los diccionarios en un objeto JavaScript
    const baseDatosTablas = <?php echo json_encode($opciones_tablas ?? [], JSON_UNESCAPED_UNICODE); ?>;
    
    let inputOcultoDestino = null;
    let inputVisibleDestino = null;

    function abrirModalDiccionario(idCampo, codigoTabla) {
        inputOcultoDestino = document.getElementById('hidden_' + idCampo);
        inputVisibleDestino = document.getElementById('visible_' + idCampo);
        
        const ul = document.getElementById('listaDiccionario');
        ul.innerHTML = '';
        
        // Comprobamos si existen datos para esa tabla específica
        if (baseDatosTablas[codigoTabla] && baseDatosTablas[codigoTabla].length > 0) {
            baseDatosTablas[codigoTabla].forEach(item => {
                const li = document.createElement('li');
                // Al hacer clic, enviamos tanto el código (para guardar) como la descripción (para mostrar)
                li.onclick = () => seleccionarValor(item.codigo, item.descripcion);
                li.innerHTML = `<strong>${item.descripcion}</strong> <span>(${item.codigo})</span>`;
                ul.appendChild(li);
            });
        } else {
            ul.innerHTML = '<li style="padding: 15px; text-align: center; color: #64748b;">No hay valores configurados en esta tabla.</li>';
        }

        document.getElementById('modalDiccionario').style.display = 'flex';
        document.getElementById('buscadorDiccionario').value = '';
        filtrarDiccionario();
        
        setTimeout(() => document.getElementById('buscadorDiccionario').focus(), 100);
    }

    function seleccionarValor(codigoDevuelto, descripcionDevuelta) {
        if (inputOcultoDestino && inputVisibleDestino) {
            inputOcultoDestino.value = codigoDevuelto;
            inputVisibleDestino.value = descripcionDevuelta;
        }
        cerrarModalDiccionario();
    }

    function cerrarModalDiccionario() {
        document.getElementById('modalDiccionario').style.display = 'none';
        inputOcultoDestino = null;
        inputVisibleDestino = null;
    }

    function filtrarDiccionario() {
        const filtro = document.getElementById('buscadorDiccionario').value.toLowerCase();
        const items = document.querySelectorAll('#listaDiccionario li');
        
        items.forEach(item => {
            const texto = item.textContent.toLowerCase();
            item.style.display = texto.includes(filtro) ? 'flex' : 'none';
        });
    }

    window.onclick = function(event) {
        const modal = document.getElementById('modalDiccionario');
        if (event.target == modal) {
            cerrarModalDiccionario();
        }
    }
    // ==========================================
    // VALIDACIÓN AL ENVIAR FORMULARIO
    // ==========================================
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        
        form.addEventListener('submit', function(e) {
            // Buscamos todos los campos ocultos que hemos marcado como requeridos
            const lookupsRequeridos = document.querySelectorAll('input.requerido-lookup');
            let hayError = false;
            let primerError = null;

            lookupsRequeridos.forEach(function(hiddenInput) {
                if (hiddenInput.value.trim() === '') {
                    hayError = true;
                    
                    // Buscamos el input visible asociado para marcarlo en rojo
                    const idVisible = hiddenInput.id.replace('hidden_', 'visible_');
                    const visibleInput = document.getElementById(idVisible);
                    
                    if (visibleInput) {
                        visibleInput.style.borderColor = '#ef4444';
                        visibleInput.style.backgroundColor = '#fee2e2';
                        
                        if (!primerError) {
                            primerError = visibleInput;
                        }
                    }
                } else {
                    // Restauramos los colores si el usuario ya lo rellenó
                    const idVisible = hiddenInput.id.replace('hidden_', 'visible_');
                    const visibleInput = document.getElementById(idVisible);
                    if (visibleInput) {
                        visibleInput.style.borderColor = '#cbd5e1';
                        visibleInput.style.backgroundColor = '#ffffff';
                    }
                }
            });

            if (hayError) {
                e.preventDefault(); // Detenemos el envío al servidor
                alert('Por favor, selecciona un valor en todos los campos desplegables obligatorios marcados con asterisco.');
                if (primerError) {
                    primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Simulamos un click para abrir el modal del campo que falta
                    setTimeout(() => primerError.click(), 500); 
                }
            }
        });
    });
</script>