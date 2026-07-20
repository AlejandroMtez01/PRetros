<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$mensaje_error = $_SESSION['error_guardado'] ?? null;
unset($_SESSION['error_guardado']);

// ==========================================
// DETECCIÓN DE MODO: CREAR O EDITAR
// ==========================================
$esEdicion = isset($albaran) && !empty($albaran['id']);
$tituloPantalla = $esEdicion ? 'Editar Albarán: ' . htmlspecialchars($albaran['numAlbaran']) : 'Crear Nuevo Albarán';
$accionFormulario = $esEdicion ? 'actualizar' : 'guardar';
$iconoBoton = $esEdicion ? 'fa-arrows-rotate' : 'fa-floppy-disk';
$textoBoton = $esEdicion ? 'Actualizar Albarán' : 'Guardar Albarán';
?>

<div class="contenedor-albaran">
    <div class="encabezado-seccion" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><?php echo $tituloPantalla; ?></h2>
        <a href="/index.php?controller=albaran" class="btn-secundario" style="text-decoration: none;">
            <i class="fa-solid fa-xmark"></i> Cancelar
        </a>
    </div>

    <!-- Bloque de Error Preciso -->
    <?php if ($mensaje_error): ?>
        <div class="alerta-error">
            <i class="fa-solid fa-triangle-exclamation"></i> <strong>Error de Base de Datos:</strong> <?= htmlspecialchars($mensaje_error) ?>
        </div>
    <?php endif; ?>

    <form action="/index.php?controller=albaran&action=<?php echo $accionFormulario; ?>" method="POST" id="formAlbaran" class="formulario-estandar">

        <?php if ($esEdicion): ?>
            <input type="hidden" name="idAlbaran" value="<?php echo $albaran['id']; ?>">
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- CABECERA DEL ALBARÁN                       -->
        <!-- ========================================== -->
        <fieldset>
            <legend>Datos del Albarán</legend>
            <div class="grid-3">
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" name="fecha" value="<?php echo $esEdicion ? htmlspecialchars(substr($albaran['fecha'], 0, 10)) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Cliente</label>
                    <div class="input-con-boton">
                        <input type="hidden" name="idCliente" id="idClienteInput" value="<?php echo $esEdicion ? $albaran['idCliente'] : ''; ?>" required>
                        <input type="text" id="nombreClienteInput" value="<?php echo $esEdicion ? htmlspecialchars($albaran['nombreCliente']) : ''; ?>" placeholder="Seleccione cliente..." readonly required>
                        <button type="button" class="btn-secundario btn-icono" onclick="abrirModal('modalClientes')">
                            <i class="fa-solid fa-building"></i> Buscar
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Centro de Trabajo</label>
                    <div class="input-con-boton">
                        <input type="hidden" name="idCentro" id="idCentroInput" value="<?php echo $esEdicion ? $albaran['idCentro'] : ''; ?>" required>
                        <input type="text" id="nombreCentroInput" value="<?php echo $esEdicion ? htmlspecialchars($albaran['nombreCentro']) : ''; ?>" placeholder="Seleccione primero un cliente..." readonly required>
                        <button type="button" id="btnBuscarCentro" class="btn-secundario btn-icono" onclick="abrirModal('modalCentros')" <?php echo $esEdicion ? '' : 'disabled'; ?>>
                            <i class="fa-solid fa-location-dot"></i> Buscar
                        </button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="observaciones" rows="2" placeholder="Añade notas o indicaciones aquí..."><?php echo $esEdicion ? htmlspecialchars($albaran['observaciones']) : ''; ?></textarea>
            </div>
        </fieldset>

        <br>

        <!-- ========================================== -->
        <!-- LÍNEAS DEL ALBARÁN                         -->
        <!-- ========================================== -->
        <fieldset>
            <legend>Líneas de Trabajo</legend>
            <button type="button" class="btn-secundario" onclick="abrirModal('modalEmpleados')" style="margin-bottom: 15px;">
                <i class="fa-solid fa-user-plus"></i> Añadir Empleado
            </button>

            <table class="tabla-datos" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Hora Desde</th>
                        <th>Hora Hasta</th>
                        <th>Categoría / Puesto</th>
                        <th>Vehículo (Maquinistas)</th>
                        <th>Importe</th>
                        <th style="text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody id="cuerpo-lineas-albaran">
                    <?php
                    $contadorL = 0;
                    if ($esEdicion && !empty($lineas)):
                        foreach ($lineas as $l):
                            $contadorL++;

                            $nombreVehiculoCargado = '';
                            if (!empty($l['vehiculoUtilizado'])) {
                                foreach ($vehiculos_precio_hora as $veh) {
                                    $idVehActual = $veh['id'] ?? $veh['idArticulo'] ?? $veh['codigo'] ?? 0;
                                    if ($idVehActual == $l['vehiculoUtilizado']) {
                                        $nombreVehiculoCargado = htmlspecialchars($veh['denominacion']);
                                        break;
                                    }
                                }
                            }

                            // Comprobamos si la categoría guardada es "maquinista"
                            $esMaq = (strtolower($l['categoriaProfesional'] ?? '') === 'maquinista');
                    ?>
                            <tr id="linea_<?php echo $contadorL; ?>">
                                <td>
                                    <input type="hidden" name="lineas[<?php echo $contadorL; ?>][idEmpleado]" value="<?php echo $l['idEmpleado']; ?>">
                                    <strong><?php echo htmlspecialchars($l['empNombre'] . ' ' . $l['empApellido']); ?></strong>
                                </td>
                                <td><input type="time" name="lineas[<?php echo $contadorL; ?>][horaDesde]" value="<?php echo substr($l['horaDesde'], 0, 5); ?>" required></td>
                                <td><input type="time" name="lineas[<?php echo $contadorL; ?>][horaHasta]" value="<?php echo substr($l['horaHasta'], 0, 5); ?>" required></td>

                                <!-- NUEVO SELECTOR DE CATEGORÍA POR MODAL -->
                                <td>
                                    <div class="input-con-boton">
                                        <input type="text" name="lineas[<?php echo $contadorL; ?>][categoriaProfesional]" id="categoria_nombre_<?php echo $contadorL; ?>" value="<?php echo htmlspecialchars($l['categoriaProfesional']); ?>" readonly required placeholder="Categoría..." style="min-width: 120px;">
                                        <button type="button" class="btn-secundario btn-icono" onclick="abrirModalCategoria(<?php echo $contadorL; ?>)" style="padding: 10px;">
                                            <i class="fa-solid fa-list"></i>
                                        </button>
                                    </div>
                                </td>

                                <td>
                                    <div class="input-con-boton" id="vehiculo_container_<?php echo $contadorL; ?>" style="<?php echo $esMaq ? 'display:flex;' : 'display:none;'; ?>">
                                        <input type="hidden" name="lineas[<?php echo $contadorL; ?>][vehiculoUtilizado]" id="vehiculo_id_<?php echo $contadorL; ?>" value="<?php echo $l['vehiculoUtilizado']; ?>">
                                        <input type="text" id="vehiculo_nombre_<?php echo $contadorL; ?>" value="<?php echo $nombreVehiculoCargado; ?>" readonly placeholder="Vehículo..." style="min-width: 100px;" <?php echo $esMaq ? 'required' : ''; ?>>
                                        <button type="button" class="btn-secundario btn-icono" onclick="abrirModalVehiculo(<?php echo $contadorL; ?>)" style="padding: 10px;">
                                            <i class="fa-solid fa-truck"></i>
                                        </button>
                                    </div>
                                </td>
                                <td><input type="number" step="0.01" name="lineas[<?php echo $contadorL; ?>][importe]" value="<?php echo $l['importe']; ?>" placeholder="0.00" style="width: 100px;"></td>
                                <td style="text-align:center;">
                                    <button type="button" class="btn-sm btn-eliminar" onclick="eliminarLinea(<?php echo $contadorL; ?>)"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </tbody>
            </table>
        </fieldset>

        <br>
        <button type="submit" class="btn-principal"><i class="fa-solid <?php echo $iconoBoton; ?>"></i> <?php echo $textoBoton; ?></button>
    </form>
</div>

<!-- ========================================== -->
<!-- MODALES DEL SISTEMA                        -->
<!-- ========================================== -->

<!-- Modal Categorías (Puestos + Especial) -->
<div id="modalCategorias" class="modal" style="display: none;">
    <div class="modal-contenido">
        <h3>Seleccionar Categoría / Puesto</h3>
        <div class="tabla-contenedor-scroll">
            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- OPCIÓN EXTRA REQUERIDA (Maquinista) -->
                    <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <td>
                            <strong>Maquinista</strong>
                            <span style="font-size: 0.8rem; color: #b91c1c; margin-left: 8px;"><i class="fa-solid fa-circle-exclamation"></i> Requiere Vehículo</span>
                        </td>
                        <td style="text-align: center;">
                            <button type="button" class="btn-principal" style="padding: 5px 10px;" onclick="seleccionarCategoria('Maquinista', true)">Seleccionar</button>
                        </td>
                    </tr>

                    <!-- PUESTOS DESDE LA BASE DE DATOS -->
                    <?php if (!empty($puestos)): foreach ($puestos as $puesto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($puesto['descripcion']); ?></td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-sm btn-editar" onclick="seleccionarCategoria('<?php echo htmlspecialchars(addslashes($puesto['descripcion'])); ?>', false)">Seleccionar</button>
                                </td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn-eliminar mt-15" onclick="cerrarModal('modalCategorias')">Cancelar</button>
    </div>
</div>

<!-- Modal Clientes -->
<div id="modalClientes" class="modal" style="display: none;">
    <div class="modal-contenido">
        <h3>Seleccionar Cliente</h3>
        <div class="tabla-contenedor-scroll">
            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th>Razón Social</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($clientes)): foreach ($clientes as $cli): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cli["denominacion"] ?? $cli["razonSocial"]); ?></td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-sm btn-editar" onclick="seleccionarCliente(<?php echo $cli['id']; ?>, '<?php echo htmlspecialchars(addslashes($cli['denominacion'] ?? $cli['razonSocial'])); ?>')">Seleccionar</button>
                                </td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn-eliminar mt-15" onclick="cerrarModal('modalClientes')">Cancelar</button>
    </div>
</div>

<!-- Modal Centros -->
<div id="modalCentros" class="modal" style="display: none;">
    <div class="modal-contenido">
        <h3>Seleccionar Centro de Trabajo</h3>
        <div class="tabla-contenedor-scroll">
            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th>Dirección / Denominación</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="cuerpo-tabla-centros">
                    <?php if ($esEdicion && !empty($centros_actuales)): foreach ($centros_actuales as $cen): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cen['denominacion']); ?></td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-sm btn-editar" onclick="seleccionarCentro(<?php echo $cen['id']; ?>, '<?php echo htmlspecialchars(addslashes($cen['denominacion'])); ?>')">Seleccionar</button>
                                </td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn-eliminar mt-15" onclick="cerrarModal('modalCentros')">Cancelar</button>
    </div>
</div>

<!-- Modal Empleados -->
<div id="modalEmpleados" class="modal" style="display: none;">
    <div class="modal-contenido">
        <h3>Seleccionar Empleado</h3>
        <div class="tabla-contenedor-scroll">
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
                                <td style="text-align: center;">
                                    <button type="button" class="btn-sm btn-editar" onclick="agregarLineaEmpleado(<?php echo $emp['id']; ?>, '<?php echo htmlspecialchars(addslashes($emp['nombre'] . ' ' . $emp['apellido1'])); ?>')">Seleccionar</button>
                                </td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn-eliminar mt-15" onclick="cerrarModal('modalEmpleados')">Cancelar</button>
    </div>
</div>

<!-- Modal Vehículos -->
<div id="modalVehiculos" class="modal" style="display: none;">
    <div class="modal-contenido" style="max-width: 900px;">
        <h3>Seleccionar Vehículo</h3>
        <div class="tabla-contenedor-scroll">
            <table class="tabla-datos" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="text-align: left;">Denominación</th>
                        <th style="text-align: center;">Tipo</th>
                        <th style="text-align: center;">Precio/Hora</th>
                        <th style="text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($vehiculos_precio_hora)): foreach ($vehiculos_precio_hora as $veh):
                            $idVeh = $veh['id'] ?? $veh['idArticulo'] ?? $veh['codigo'] ?? 0;
                            $prefijo = isset($veh['prefijo_tipo']) ? htmlspecialchars($veh['prefijo_tipo']) : 'VEHÍCULO';
                            $precioH = isset($veh['precio_hora_extraido']) ? number_format((float)$veh['precio_hora_extraido'], 2, ',', '.') . ' €' : 'N/A';
                    ?>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td><strong><?php echo htmlspecialchars($veh['denominacion']); ?></strong></td>
                                <td style="text-align: center;"><span style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; color: #475569; font-weight: bold;"><?php echo $prefijo; ?></span></td>
                                <td style="text-align: center; color: #0f4c81; font-weight: bold;"><?php echo $precioH; ?></td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-sm btn-editar" onclick="seleccionarVehiculo(<?php echo $idVeh; ?>, '<?php echo htmlspecialchars(addslashes($veh['denominacion'])); ?>')">Seleccionar</button>
                                </td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn-eliminar mt-15" onclick="cerrarModal('modalVehiculos')">Cancelar</button>
    </div>
</div>

<!-- ========================================== -->
<!-- SCRIPTS Y LÓGICA DINÁMICA                  -->
<!-- ========================================== -->
<script>
    const tbodyLineas = document.getElementById('cuerpo-lineas-albaran');
    let contadorLineas = <?php echo isset($contadorL) ? $contadorL : 0; ?>;
    
    let filaVehiculoActiva = null;
    let filaCategoriaActiva = null;

    // --- Objetos de Precios ---
    const preciosPuestos = {
        <?php foreach ($puestos as $p): ?> 
            "<?php echo addslashes($p['descripcion']); ?>": <?php echo (float)($p['precioHora'] ?? 0); ?>,
        <?php endforeach; ?>
    };

    const preciosVehiculos = {
        <?php foreach ($vehiculos_precio_hora as $v):
            $idVeh = $v['id'] ?? $v['idArticulo'] ?? $v['codigo'] ?? 0;
        ?> 
            "<?php echo $idVeh; ?>": <?php echo (float)($v['precio_hora_extraido'] ?? 0); ?>,
        <?php endforeach; ?>
    };

    // --- Modales ---
    function abrirModal(idModal) { document.getElementById(idModal).style.display = 'flex'; }
    function cerrarModal(idModal) { document.getElementById(idModal).style.display = 'none'; }

    // --- Lógica Clientes/Centros ---
    function seleccionarCliente(id, nombre) {
        document.getElementById('idClienteInput').value = id;
        document.getElementById('nombreClienteInput').value = nombre;
        document.getElementById('idCentroInput').value = '';
        document.getElementById('nombreCentroInput').value = 'Seleccione un centro...';
        cerrarModal('modalClientes');
        cargarCentros(id);
    }

    function cargarCentros(idCliente) {
        const tbody = document.getElementById('cuerpo-tabla-centros');
        const btnCentro = document.getElementById('btnBuscarCentro');
        btnCentro.disabled = false;
        tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;">Cargando centros...</td></tr>';
        
        fetch(`/index.php?controller=albaran&action=obtenerCentrosPorCliente&idCliente=${idCliente}`)
            .then(async response => {
                if (!response.ok) throw new Error('Error en el servidor');
                return response.json();
            })
            .then(data => {
                tbody.innerHTML = '';
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;">No hay centros asignados.</td></tr>';
                    return;
                }
                data.forEach(centro => {
                    const nombreCentro = centro.denominacion || centro.direccion;
                    const nombreSeguro = nombreCentro.replace(/'/g, "\\'");
                    tbody.innerHTML += `
                        <tr>
                            <td>${nombreCentro}</td>
                            <td style="text-align: center;"><button type="button" class="btn-sm btn-editar" onclick="seleccionarCentro(${centro.id}, '${nombreSeguro}')">Seleccionar</button></td>
                        </tr>`;
                });
            });
    }

    function seleccionarCentro(id, direccion) {
        document.getElementById('idCentroInput').value = id;
        document.getElementById('nombreCentroInput').value = direccion;
        cerrarModal('modalCentros');
    }

    // --- Lógica Categoría ---
    function abrirModalCategoria(idFila) {
        filaCategoriaActiva = idFila;
        abrirModal('modalCategorias');
    }

    function seleccionarCategoria(nombreCategoria, esMaquinista) {
        if (filaCategoriaActiva !== null) {
            document.getElementById('categoria_nombre_' + filaCategoriaActiva).value = nombreCategoria;
            
            const contenedorVehiculo = document.getElementById('vehiculo_container_' + filaCategoriaActiva);
            const inputVehiculoNombre = document.getElementById('vehiculo_nombre_' + filaCategoriaActiva);
            
            if (esMaquinista) {
                contenedorVehiculo.style.display = 'flex';
                inputVehiculoNombre.required = true;
            } else {
                contenedorVehiculo.style.display = 'none';
                inputVehiculoNombre.required = false;
                document.getElementById('vehiculo_id_' + filaCategoriaActiva).value = '';
                inputVehiculoNombre.value = '';
            }
            
            calcularImporte(filaCategoriaActiva);
            cerrarModal('modalCategorias');
            filaCategoriaActiva = null;
        }
    }

    // --- Lógica Vehículos ---
    function abrirModalVehiculo(idFila) {
        filaVehiculoActiva = idFila;
        abrirModal('modalVehiculos');
    }

    function seleccionarVehiculo(id, nombre) {
        if (filaVehiculoActiva !== null) {
            document.getElementById('vehiculo_id_' + filaVehiculoActiva).value = id;
            document.getElementById('vehiculo_nombre_' + filaVehiculoActiva).value = nombre;
            calcularImporte(filaVehiculoActiva);
            cerrarModal('modalVehiculos');
            filaVehiculoActiva = null; 
        }
    }

    // --- Cálculo Automático ---
    function calcularImporte(idFila) {
        const inputPuesto = document.getElementById('categoria_nombre_' + idFila).value;
        const inputVehiculoId = document.getElementById('vehiculo_id_' + idFila).value;
        const inputImporte = document.querySelector(`input[name="lineas[${idFila}][importe]"]`);
        
        let importe = 0;

        if (inputPuesto.toLowerCase() === 'maquinista' && inputVehiculoId) {
            importe = preciosVehiculos[inputVehiculoId] || 0;
        } else if (preciosPuestos[inputPuesto]) {
            importe = preciosPuestos[inputPuesto];
        }
        inputImporte.value = parseFloat(importe).toFixed(2);
    }

    // --- Gestión de Filas ---
    function agregarLineaEmpleado(idEmpleado, nombreEmpleado) {
        contadorLineas++;
        const fila = document.createElement('tr');
        fila.id = 'linea_' + contadorLineas;
        fila.innerHTML = `
            <td><input type="hidden" name="lineas[${contadorLineas}][idEmpleado]" value="${idEmpleado}"><strong>${nombreEmpleado}</strong></td>
            <td><input type="time" name="lineas[${contadorLineas}][horaDesde]" required></td>
            <td><input type="time" name="lineas[${contadorLineas}][horaHasta]" required></td>
            <td>
                <div class="input-con-boton">
                    <input type="text" name="lineas[${contadorLineas}][categoriaProfesional]" id="categoria_nombre_${contadorLineas}" readonly required placeholder="Categoría..." style="min-width: 120px;">
                    <button type="button" class="btn-secundario btn-icono" onclick="abrirModalCategoria(${contadorLineas})" style="padding: 10px;"><i class="fa-solid fa-list"></i></button>
                </div>
            </td>
            <td>
                <div class="input-con-boton" id="vehiculo_container_${contadorLineas}" style="display:none;">
                    <input type="hidden" name="lineas[${contadorLineas}][vehiculoUtilizado]" id="vehiculo_id_${contadorLineas}">
                    <input type="text" id="vehiculo_nombre_${contadorLineas}" readonly placeholder="Vehículo..." style="min-width: 100px;">
                    <button type="button" class="btn-secundario btn-icono" onclick="abrirModalVehiculo(${contadorLineas})" style="padding: 10px;"><i class="fa-solid fa-truck"></i></button>
                </div>
            </td>
            <td><input type="number" step="0.01" name="lineas[${contadorLineas}][importe]" placeholder="0.00" style="width: 100px;"></td>
            <td style="text-align:center;"><button type="button" class="btn-sm btn-eliminar" onclick="eliminarLinea(${contadorLineas})"><i class="fa-solid fa-trash"></i></button></td>
        `;
        tbodyLineas.appendChild(fila);
        cerrarModal('modalEmpleados');
    }

    function eliminarLinea(idFila) {
        const fila = document.getElementById('linea_' + idFila);
        if (fila) fila.remove();
    }
</script>

<!-- ========================================== -->
<!-- ESTILOS UNIFICADOS DE FORMULARIO           -->
<!-- ========================================== -->
<style>
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

    .formulario-estandar input:not([type="hidden"]),
    .formulario-estandar select,
    .formulario-estandar textarea {
        padding: 10px;
        border: 1px solid #94a3b8;
        border-radius: 6px;
        font-size: 0.95rem;
        font-family: inherit;
        background: #ffffff;
        transition: border-color 0.2s, box-shadow 0.2s;
        width: 100%;
        box-sizing: border-box;
    }

    .formulario-estandar input:focus,
    .formulario-estandar select:focus,
    .formulario-estandar textarea:focus {
        outline: none;
        border-color: #0f4c81;
        box-shadow: 0 0 0 3px rgba(15, 76, 129, 0.15);
    }

    .formulario-estandar input[readonly] {
        background-color: #e2e8f0;
        cursor: not-allowed;
    }

    .grid-3 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .input-con-boton {
        display: flex;
        gap: 10px;
    }

    .input-con-boton input {
        flex-grow: 1;
    }

    .mt-15 {
        margin-top: 15px;
    }

    .alerta-error {
        background-color: #fee2e2;
        color: #b91c1c;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #f87171;
        margin-bottom: 20px;
        font-size: 0.95rem;
    }

    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(15, 23, 42, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-contenido {
        background-color: #ffffff;
        padding: 25px;
        width: 90%;
        max-width: 800px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .modal-contenido h3 {
        margin-top: 0;
        color: #0f4c81;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }

    .tabla-contenedor-scroll {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
    }

    button,
    .btn-secundario,
    .btn-principal {
        font-family: inherit;
        cursor: pointer;
        transition: opacity 0.2s;
    }

    button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    button:hover:not(:disabled),
    .btn-secundario:hover,
    .btn-principal:hover {
        opacity: 0.9;
    }

    .btn-principal {
        background: #0f4c81;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 1rem;
    }

    .btn-secundario {
        background: #475569;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 6px;
        font-weight: 500;
    }

    .btn-eliminar {
        background: #ef4444;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 6px;
        font-weight: 500;
    }

    .btn-icono {
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }
</style>