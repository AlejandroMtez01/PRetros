<div class="contenedor-albaran">
    <h2>Crear Nuevo Albarán</h2>

    <form action="/index.php?controller=albaran&action=guardar" method="POST" id="formAlbaran">
        
        <!-- ========================================== -->
        <!-- CABECERA DEL ALBARÁN                       -->
        <!-- ========================================== -->
        <fieldset>
            <legend>Datos del Albarán</legend>
            <div class="grid-3">
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" name="fecha" required>
                </div>
                <div class="form-group">
                    <label>Cliente</label>
                    <select name="idCliente" required>
                        <option value="">Selecciona cliente...</option>
                        <!-- Aquí iteraremos los $clientes -->
                    </select>
                </div>
                <div class="form-group">
                    <label>Centro de Trabajo</label>
                    <select name="idCentro" required>
                        <option value="">Selecciona centro...</option>
                        <!-- Aquí iteraremos los $centros -->
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="observaciones" rows="2"></textarea>
            </div>
        </fieldset>

        <br>

        <!-- ========================================== -->
        <!-- LÍNEAS DEL ALBARÁN                         -->
        <!-- ========================================== -->
        <fieldset>
            <legend>Líneas de Trabajo</legend>
            
            <!-- Botón para abrir el modal de empleados -->
            <button type="button" class="btn-secundario" onclick="abrirModalEmpleados()">
                <i class="fa-solid fa-user-plus"></i> Añadir Empleado
            </button>

            <table class="tabla-datos" style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Hora Desde</th>
                        <th>Hora Hasta</th>
                        <th>Categoría</th>
                        <th>Vehículo (Solo Maquinistas)</th>
                        <th>Importe</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="cuerpo-lineas-albaran">
                    <!-- Las filas se añadirán aquí dinámicamente con JS -->
                </tbody>
            </table>
        </fieldset>

        <br>
        <button type="submit" class="btn-principal">Guardar Albarán</button>
    </form>
</div>

<!-- ========================================== -->
<!-- MODAL DE EMPLEADOS                         -->
<!-- ========================================== -->
<div id="modalEmpleados" class="modal" style="display: none;">
    <div class="modal-contenido">
        <h3>Seleccionar Empleado</h3>
        <table class="tabla-datos">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>DNI</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($empleados)): foreach ($empleados as $emp): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($emp['nombre'] . ' ' . $emp['apellido1']); ?></td>
                        <td><?php echo htmlspecialchars($emp['DNI']); ?></td>
                        <td>
                            <button type="button" class="btn-sm btn-editar" 
                                onclick="agregarLineaEmpleado(<?php echo $emp['id']; ?>, '<?php echo htmlspecialchars(addslashes($emp['nombre'] . ' ' . $emp['apellido1'])); ?>')">
                                Seleccionar
                            </button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
        <button type="button" class="btn-eliminar" onclick="cerrarModalEmpleados()" style="margin-top: 15px;">Cancelar</button>
    </div>
</div>

<!-- ========================================== -->
<!-- SCRIPTS PARA LÓGICA DINÁMICA               -->
<!-- ========================================== -->
<script>
    // Variables para el modal
    const modal = document.getElementById('modalEmpleados');
    const tbodyLineas = document.getElementById('cuerpo-lineas-albaran');
    let contadorLineas = 0;

    // Funciones del Modal
    function abrirModalEmpleados() { modal.style.display = 'block'; }
    function cerrarModalEmpleados() { modal.style.display = 'none'; }

    // Generar las opciones de vehículos con "Precio Hora" pasadas desde PHP
    // Esto asume que el controlador te manda un JSON o generas el HTML aquí
    const opcionesVehiculos = `
        <option value="">Seleccione vehículo...</option>
        <?php if(!empty($vehiculos_precio_hora)): foreach($vehiculos_precio_hora as $veh): ?>
            <option value="<?php echo $veh['id']; ?>"><?php echo htmlspecialchars($veh['denominacion']); ?></option>
        <?php endforeach; endif; ?>
    `;

    // Función principal: Añadir fila a la tabla al seleccionar un empleado
    function agregarLineaEmpleado(idEmpleado, nombreEmpleado) {
        contadorLineas++;
        
        const fila = document.createElement('tr');
        fila.id = 'linea_' + contadorLineas;
        
        fila.innerHTML = `
            <td>
                <!-- Input oculto para enviar al POST -->
                <input type="hidden" name="lineas[${contadorLineas}][idEmpleado]" value="${idEmpleado}">
                <strong>${nombreEmpleado}</strong>
            </td>
            <td><input type="time" name="lineas[${contadorLineas}][horaDesde]" required></td>
            <td><input type="time" name="lineas[${contadorLineas}][horaHasta]" required></td>
            <td>
                <select name="lineas[${contadorLineas}][categoriaProfesional]" class="select-categoria" onchange="comprobarCategoria(this, ${contadorLineas})" required>
                    <option value="">Seleccionar...</option>
                    <option value="peon">Peón</option>
                    <option value="oficial">Oficial</option>
                    <option value="maquinista">Maquinista</option>
                </select>
            </td>
            <td>
                <select name="lineas[${contadorLineas}][vehiculoUtilizado]" id="vehiculo_${contadorLineas}" style="display: none;" disabled>
                    ${opcionesVehiculos}
                </select>
            </td>
            <td><input type="number" step="0.01" name="lineas[${contadorLineas}][importe]"></td>
            <td>
                <button type="button" class="btn-sm btn-eliminar" onclick="eliminarLinea(${contadorLineas})">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;
        
        tbodyLineas.appendChild(fila);
        cerrarModalEmpleados();
    }

    // Lógica para mostrar/ocultar el vehículo si es maquinista
    function comprobarCategoria(selectElement, idFila) {
        const selectVehiculo = document.getElementById('vehiculo_' + idFila);
        
        if (selectElement.value === 'maquinista') {
            selectVehiculo.style.display = 'block';
            selectVehiculo.disabled = false;
            selectVehiculo.required = true;
        } else {
            selectVehiculo.style.display = 'none';
            selectVehiculo.disabled = true;
            selectVehiculo.required = false;
            selectVehiculo.value = ""; // Reseteamos el valor por si cambia de opinión
        }
    }

    // Función para borrar la fila de la vista
    function eliminarLinea(idFila) {
        const fila = document.getElementById('linea_' + idFila);
        if (fila) { fila.remove(); }
    }
</script>

<style>
    /* Estilos básicos para el modal */
    .modal { position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
    .modal-contenido { background-color: #fff; margin: 5% auto; padding: 20px; width: 80%; max-width: 700px; border-radius: 8px; }
</style>