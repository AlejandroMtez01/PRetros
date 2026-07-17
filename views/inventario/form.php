<!-- Añadir esto en la sección de <style> -->
<style>
    /* Estructura principal de la tarjeta a prueba de desbordes */
    .fila-campo { 
        position: relative; 
        display: flex; 
        align-items: stretch; 
        background: #ffffff; 
        border: 1px solid #e2e8f0; 
        border-radius: 8px; 
        margin-bottom: 20px; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: box-shadow 0.2s;
    }
    .fila-campo:hover { box-shadow: 0 4px 8px rgba(0,0,0,0.05); border-color: #cbd5e1; }

    

    /* El asa lateral izquierda (tamaño FIJO) */
    .handle { 
        flex: 0 0 45px; /* No crece, no encoje, siempre 45px */
        display: flex; 
        align-items: center; 
        justify-content: center; 
        background: #f8fafc; 
        color: #cbd5e1; 
        font-size: 1.1rem;
        cursor: grab; 
        border-right: 1px solid #e2e8f0;
        border-top-left-radius: 7px;
        border-bottom-left-radius: 7px;
    }
    .handle:hover { color: #0f4c81; background: #f1f5f9; }
    .handle:active { cursor: grabbing; color: #0f4c81; }

    /* El botón rojo posicionado absolutamente en la esquina */
    .btn-eliminar-fila { 
        position: absolute; 
        top: 20px; 
        right: 20px; 
        background: #fee2e2; 
        color: #ef4444; 
        border: 1px solid #fca5a5; 
        width: 26px; 
        height: 26px; 
        border-radius: 50%; 
        font-size: 0.85rem;
        font-weight: bold; 
        cursor: pointer; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        transition: all 0.2s; 
        z-index: 10;
    }
    .btn-eliminar-fila:hover { background: #ef4444; color: white; }

    /* Contenedor derecho (donde van los inputs) */
    .contenido-fila { 
        flex: 1; 
        min-width: 0; /* CRUCIAL: Evita que los inputs rompan el diseño */
        padding: 20px 60px 20px 20px; /* Deja 60px libres a la derecha para que no choque con la X */
        box-sizing: border-box;
    }
    /*AAA

/* 1. Volvemos a poner el grid en stretch para que todas las celdas midan lo mismo de alto */
    .grid-principal { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; align-items: stretch; }
    
    /* 2. LA MAGIA: Hacemos que el contenedor del input sea flex vertical y empuje todo hacia abajo */
    .form-group { display: flex; flex-direction: column; justify-content: flex-end; height: 100%; }
    
    /* 3. Tus estilos de label y inputs que ya tenías */
    .form-group label { margin-bottom: 8px; color: #475569; font-weight: 600; font-size: 0.9rem; }
    .form-group input[type="text"], .form-group select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }

    bbbbb */

    /* Grids internos */
    
    /* Inputs y Checkboxes */
    .opciones-checkbox { display: flex; gap: 20px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #f1f5f9; }
    .checkbox-item { display: flex; align-items: center; gap: 8px; font-size: 0.9rem; color: #475569; cursor: pointer; }

    /* Modal y Lookups */
    .input-grupo-modal { display: flex; align-items: stretch; gap: 5px; width: 100%; }
    .input-grupo-modal input { flex-grow: 1; cursor: pointer; background-color: #ffffff; }
    .input-grupo-modal button { padding: 0 15px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; color: #475569; transition: all 0.2s; }
    .input-grupo-modal button:hover { background: #e2e8f0; color: #0f4c81; }
    
    /* Estilos del modal que ya tenías... */
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
    
    .sortable-ghost { opacity: 0.4; background-color: #f8fafc !important; border: 1px dashed #cbd5e1; }
</style>
<!-- Añadir esto justo antes de cerrar el </body> -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>



<div class="encabezado-modulo">
    <h2><?php echo $titulo_formulario; ?></h2>
</div>

<div class="contenedor-formulario">
    <div class="tarjeta-formulario" style="max-width: 1000px;">
        
        <?php if (isset($errores) && !empty($errores)): ?>
            <div class="alerta-errores">
                <h4>Por favor, revisa lo siguiente:</h4>
                <ul>
                    <?php foreach ($errores as $campo_id => $mensaje): ?>
                        <li>
                            <?php echo htmlspecialchars($mensaje); ?> 
                            <span class="enlace-wrapper">
                                (<a href="#<?php echo htmlspecialchars($campo_id); ?>" class="enlace-ir-error">Ir al error <i class="fa-solid fa-arrow-turn-down"></i></a>)
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo $accion_url; ?>" method="POST" id="formCatalogoAvanzado">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="prefijo">Prefijo (Máx 5 letras) *</label>
                    <input type="text" id="prefijo" name="prefijo" required 
                           value="<?php echo isset($inventario) ? htmlspecialchars($inventario['prefijo']) : ''; ?>"
                           class="<?php echo isset($errores['prefijo']) ? 'input-error' : ''; ?>"
                           maxlength="5"
                           placeholder="Ej: LAP"
                           <?php echo isset($es_edicion) && $es_edicion ? 'readonly style="background-color: #f1f5f9; cursor: not-allowed;" title="El prefijo no se puede modificar"' : ''; ?>>
                </div>

                <div class="form-group">
                    <label for="nombre_tipo">Nombre del Tipo *</label>
                    <input type="text" id="nombre_tipo" name="nombre_tipo" required 
                           value="<?php echo isset($inventario) ? htmlspecialchars($inventario['nombre_tipo']) : ''; ?>"
                           class="<?php echo isset($errores['nombre_tipo']) ? 'input-error' : ''; ?>"
                           maxlength="50"
                           placeholder="Ej: Ordenador Portátil">
                </div>
            </div>

            <hr style="margin: 30px 0; border: 0; border-top: 1px dashed #cbd5e1;">

            <div style="display: flex; justify-content: space-between; margin-bottom: 20px; align-items: center;">
                <h3 style="margin: 0; font-size: 1.1rem; color: #1e293b;">Estructura de Datos del Catálogo</h3>
                <button type="button" class="btn-secundario" onclick="agregarCampoAvanzado()" style="display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-plus"></i> Añadir Campo
                </button>
            </div>
            
            <div id="contenedorConstructor">
                <!-- Las filas dinámicas se inyectan aquí -->
            </div>

            <input type="hidden" id="esquema_configuracion" name="esquema_configuracion" 
                   value="<?php echo isset($inventario) ? htmlspecialchars($inventario['esquema_configuracion']) : ''; ?>">

            <div class="acciones-formulario" style="margin-top: 40px;">
                <a href="/index.php?controller=catalogo_inventario&action=index" class="btn-secundario">
                    <i class="fa-solid fa-xmark"></i> &nbsp;Cancelar
                </a>
                <button type="submit" class="btn-primario">
                    <i class="fa-solid fa-floppy-disk"></i> &nbsp;Guardar Catálogo
                </button>
            </div>
            
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL DE SELECCIÓN DE GTablasCabecera      -->
<!-- ========================================== -->
<div id="modalTablas" class="modal-overlay" style="display: none;">
    <div class="modal-contenido">
        <div class="modal-cabecera">
            <h3>Seleccionar Tabla de Referencia</h3>
            <button type="button" class="btn-cerrar-modal" onclick="cerrarModalTablas()">&times;</button>
        </div>
        <div class="modal-cuerpo">
            <input type="text" id="buscadorTablas" class="buscador-input" placeholder="Buscar tabla por nombre o código..." onkeyup="filtrarTablas()">
            <ul id="listaTablas" class="lista-seleccion">
                <?php if (isset($gtablas) && !empty($gtablas)): ?>
                    <?php foreach ($gtablas as $tabla): ?>
                        <li onclick="seleccionarTabla('<?php echo htmlspecialchars($tabla['codigo']); ?>')">
                            <strong><?php echo htmlspecialchars($tabla['descripcion']); ?></strong>
                            <span>(<?php echo htmlspecialchars($tabla['codigo']); ?>)</span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li style="padding: 15px; color: #64748b; text-align: center;">No hay tablas configuradas en el sistema.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- ESTILOS (Formulario + Modal)               -->
<!-- ========================================== -->
<style>
    /* Estilos del constructor */
    .fila-campo { background: #f8fafc; padding: 25px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px; position: relative; transition: all 0.3s ease; }
    .fila-campo:hover { border-color: #cbd5e1; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
    .grid-principal { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; align-items: start; }
    .grid-condicional { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #cbd5e1; display: none; }
    .opciones-checkbox { display: flex; gap: 20px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e2e8f0; }
    .checkbox-item { display: flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 600; color: #475569; cursor: pointer; }
    .btn-eliminar-fila { position: absolute; top: -10px; right: -10px; background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; width: 28px; height: 28px; border-radius: 50%; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(239,68,68,0.2); }
    .btn-eliminar-fila:hover { background: #ef4444; color: white; }
    
    /* Grupo de Input + Botón para el Modal */
    .input-grupo-modal { display: flex; align-items: stretch; gap: 5px; width: 100%; }
    .input-grupo-modal input { flex-grow: 1; cursor: pointer; background-color: #ffffff; }
    .input-grupo-modal button { padding: 0 15px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; color: #475569; transition: all 0.2s; }
    .input-grupo-modal button:hover { background: #e2e8f0; color: #0f4c81; }

    /* Estilos del Modal */
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

<!-- ========================================== -->
<!-- JAVASCRIPT                                 -->
<!-- ========================================== -->
<script>

document.addEventListener("DOMContentLoaded", () => {
    // ... (tu código de carga existente)
    
    // Inicializar SortableJS sobre el contenedor
    new Sortable(document.getElementById('contenedorConstructor'), {
        handle: '.handle', // Se arrastra solo desde el icono de grip
        animation: 150,
        ghostClass: 'sortable-ghost'
    });
});

    // Variables globales para el modal
    let inputDestinoActual = null;

    document.addEventListener("DOMContentLoaded", () => {
        const jsonInput = document.getElementById('esquema_configuracion').value;
        if (jsonInput && jsonInput.trim() !== '' && jsonInput.trim() !== '{}') {
            try {
                const esquema = JSON.parse(jsonInput);
                if (Array.isArray(esquema) && esquema.length > 0) {
                    esquema.forEach(campo => agregarCampoAvanzado(campo));
                } else {
                    agregarCampoAvanzado(); 
                }
            } catch (e) {
                console.error("Error al leer JSON", e);
                agregarCampoAvanzado(); 
            }
        } else {
            agregarCampoAvanzado();
        }
    });

    // ---------------------------------------------
    // LÓGICA DEL CONSTRUCTOR
    // ---------------------------------------------



function agregarCampoAvanzado(datos = null) {
        const fila = document.createElement('div');
        fila.className = 'fila-campo';
        
        const valDesc = datos ? datos.descripcion : '';
        const valTipo = datos ? datos.tipo_dato : 'text';
        const isOblig = datos && datos.es_obligatorio ? 'checked' : (datos === null ? 'checked' : '');
        const isCalc  = datos && datos.es_calculado ? 'checked' : '';
        const valMod  = datos && datos.modulo_validacion ? datos.modulo_validacion : '';
        const valTabl = datos && datos.tabla_ayuda ? datos.tabla_ayuda : '';
        const valMCalc= datos && datos.modulo_calculado ? datos.modulo_calculado : '';

        fila.innerHTML = `
            <!-- EL ASA -->
            <div class="handle" title="Arrastrar para reordenar">
                <i class="fa-solid fa-grip-vertical"></i>
            </div>
            
            <!-- EL BOTÓN X ABSOLUTO -->
            <button type="button" class="btn-eliminar-fila" onclick="this.closest('.fila-campo').remove()" title="Eliminar campo">
                <i class="fa-solid fa-xmark"></i>
            </button>
            
            <!-- EL CONTENIDO -->
            <div class="contenido-fila">
                <div class="grid-principal">
                    <div class="form-group">
                        <label>Descripción del Campo *</label>
                        <input type="text" class="c-descripcion" placeholder="Ej: Matrícula, Tara..." value="${valDesc}" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Tipo de Dato *</label>
                        <select class="c-tipo" onchange="evaluarCondicionales(this)" required>
                            <option value="text" ${valTipo === 'text' ? 'selected' : ''}>Texto Corto</option>
                            <option value="number" ${valTipo === 'number' ? 'selected' : ''}>Numérico</option>
                            <option value="date" ${valTipo === 'date' ? 'selected' : ''}>Fecha</option>
                            <option value="lookup" ${valTipo === 'lookup' ? 'selected' : ''}>Gestor de Tablas (Ayuda)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Módulo Validación (Opcional)</label>
                        <input type="text" class="c-mod-validacion" placeholder="Ej: val_matricula" value="${valMod}">
                    </div>
                </div>

                <div class="opciones-checkbox">
                    <label class="checkbox-item">
                        <input type="checkbox" class="c-obligatorio" ${isOblig}>
                        Es Obligatorio
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" class="c-calculado" onchange="evaluarCondicionales(this)" ${isCalc}>
                        Es Calculado
                    </label>
                </div>

                <div class="grid-condicional">
                    <div class="form-group conf-tabla" style="display: none;">
                        <label>Tabla de Referencia (Lookup) *</label>
                        <div class="input-grupo-modal">
                            <input type="text" class="c-tabla-ayuda" placeholder="Seleccionar tabla..." value="${valTabl}" readonly onclick="abrirModalTablas(this)">
                            <button type="button" onclick="abrirModalTablas(this.previousElementSibling)" title="Buscar Tabla">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group conf-calculo" style="display: none;">
                        <label>Módulo Calculado (Autocompletar) *</label>
                        <input type="text" class="c-mod-calculado" placeholder="Ej: calc_tara_max" value="${valMCalc}">
                    </div>
                </div>
            </div>
        `;
        document.getElementById('contenedorConstructor').appendChild(fila);
        setTimeout(() => evaluarCondicionales(fila.querySelector('.c-tipo')), 0);
    }
    function evaluarCondicionales(elemento) {
        const fila = elemento.closest('.fila-campo');
        const tipoSelect = fila.querySelector('.c-tipo').value;
        const isCalculado = fila.querySelector('.c-calculado').checked;
        const zonaCondicional = fila.querySelector('.grid-condicional');
        
        const divTabla = fila.querySelector('.conf-tabla');
        const divCalculo = fila.querySelector('.conf-calculo');

        let mostrarZona = false;

        if (tipoSelect === 'lookup') {
            divTabla.style.display = 'flex';
            fila.querySelector('.c-tabla-ayuda').required = true;
            mostrarZona = true;
        } else {
            divTabla.style.display = 'none';
            fila.querySelector('.c-tabla-ayuda').required = false;
        }

        if (isCalculado) {
            divCalculo.style.display = 'flex';
            fila.querySelector('.c-mod-calculado').required = true;
            mostrarZona = true;
        } else {
            divCalculo.style.display = 'none';
            fila.querySelector('.c-mod-calculado').required = false;
        }

        zonaCondicional.style.display = mostrarZona ? 'grid' : 'none';
    }

    // ---------------------------------------------
    // LÓGICA DEL MODAL DE TABLAS
    // ---------------------------------------------
    function abrirModalTablas(inputElement) {
        inputDestinoActual = inputElement; // Guardamos en qué input hizo clic el usuario
        const modal = document.getElementById('modalTablas');
        modal.style.display = 'flex';
        
        // Reseteamos el buscador
        document.getElementById('buscadorTablas').value = '';
        filtrarTablas();
        
        // Hacemos focus en el buscador al abrir
        setTimeout(() => document.getElementById('buscadorTablas').focus(), 100);
    }

    function cerrarModalTablas() {
        document.getElementById('modalTablas').style.display = 'none';
        inputDestinoActual = null;
    }

    function seleccionarTabla(codigoTabla) {
        if (inputDestinoActual) {
            inputDestinoActual.value = codigoTabla;
        }
        cerrarModalTablas();
    }

    function filtrarTablas() {
        const filtro = document.getElementById('buscadorTablas').value.toLowerCase();
        const items = document.querySelectorAll('#listaTablas li');
        
        items.forEach(item => {
            const texto = item.textContent.toLowerCase();
            item.style.display = texto.includes(filtro) ? 'flex' : 'none';
        });
    }

    // Cerrar el modal haciendo clic fuera de la caja blanca
    window.onclick = function(event) {
        const modal = document.getElementById('modalTablas');
        if (event.target == modal) {
            cerrarModalTablas();
        }
    }

    // ---------------------------------------------
    // INTERCEPTOR DE GUARDADO
    // ---------------------------------------------
    document.getElementById('formCatalogoAvanzado').addEventListener('submit', function(e) {
        const esquema = [];
        
        document.querySelectorAll('.fila-campo').forEach(fila => {
            const descripcion = fila.querySelector('.c-descripcion').value;
            const tipo = fila.querySelector('.c-tipo').value;
            const obligatorio = fila.querySelector('.c-obligatorio').checked;
            const calculado = fila.querySelector('.c-calculado').checked;
            const modValidacion = fila.querySelector('.c-mod-validacion').value;

            let campoConfig = {
                id_campo: descripcion.toLowerCase().replace(/ /g, '_').normalize("NFD").replace(/[\u0300-\u036f]/g, ""),
                descripcion: descripcion,
                tipo_dato: tipo,
                es_obligatorio: obligatorio,
                es_calculado: calculado
            };

            if (modValidacion.trim() !== '') campoConfig.modulo_validacion = modValidacion.trim();
            if (tipo === 'lookup') campoConfig.tabla_ayuda = fila.querySelector('.c-tabla-ayuda').value;
            if (calculado) campoConfig.modulo_calculado = fila.querySelector('.c-mod-calculado').value;

            esquema.push(campoConfig);
        });

        document.getElementById('esquema_configuracion').value = JSON.stringify(esquema);
    });
</script>