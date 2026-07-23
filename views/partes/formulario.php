<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$mensaje_error = $_SESSION['error_guardado'] ?? null;
unset($_SESSION['error_guardado']);

$erroresLineas = isset($erroresLineas) ? $erroresLineas : [];

// ==========================================
// DETECCIÓN DE MODO: CREAR O EDITAR
// ==========================================
$esEdicion = isset($parte) && !empty($parte['id']);
$tituloPantalla = $esEdicion ? 'Editar Parte de Trabajo #' . htmlspecialchars($parte['id']) : 'Crear Nuevo Parte';
$accionFormulario = $esEdicion ? 'actualizar' : 'guardar';
$iconoBoton = $esEdicion ? 'fa-arrows-rotate' : 'fa-floppy-disk';
$textoBoton = $esEdicion ? 'Actualizar Parte' : 'Guardar Parte';
?>

<div class="contenedor-albaran">
    <div class="encabezado-seccion" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><?php echo $tituloPantalla; ?></h2>
        <a href="/index.php?controller=partes" class="btn-secundario" style="text-decoration: none;">
            <i class="fa-solid fa-xmark"></i> Cancelar
        </a>
    </div>

    <!-- ERRORES DE PHP (Servidor) -->
    <?php if ($mensaje_error): ?>
        <div class="alerta-error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <?php echo is_array($mensaje_error) ? implode(" ", $mensaje_error) : htmlspecialchars($mensaje_error); ?>
        </div>
    <?php endif; ?>

    <!-- ERRORES DE JAVASCRIPT (Cliente) -->
    <div id="contenedor-errores-js" class="alerta-error" style="display: none;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span id="texto-errores-js"></span>
    </div>

    <form action="/index.php?controller=partes&action=<?php echo $accionFormulario; ?>" method="POST" id="formParte" class="formulario-estandar">

        <?php if ($esEdicion): ?>
            <input type="hidden" name="idParte" value="<?php echo $parte['id']; ?>">
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- CABECERA DEL PARTE                         -->
        <!-- ========================================== -->
        <fieldset>
            <legend>Datos del Parte</legend>
            <div class="grid-3">
                <div class="form-group">
                    <label>Empleado</label>
                    <div class="input-con-boton">
                        <input type="hidden" name="idEmpleado" id="idEmpleadoInput" value="<?php echo $parte['idEmpleado'] ?? ''; ?>">
                        <input type="text" name="nombreEmpleado" id="nombreEmpleadoInput" value="<?php echo htmlspecialchars($parte['nombreEmpleado'] ?? ''); ?>" placeholder="Seleccione empleado..." readonly>
                        <button type="button" class="btn-secundario btn-icono" onclick="abrirModal('modalEmpleados')">
                            <i class="fa-solid fa-user"></i> Buscar
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Fecha</label>
                    <!-- El evento onchange actualiza el campo oculto instantáneamente -->
                    <input type="date" name="fechaDesde" value="<?php echo htmlspecialchars(substr($parte['fechaDesde'] ?? '', 0, 10)); ?>" required onchange="document.getElementById('fechaHastaInput').value = this.value;">
                    
                    <!-- Este campo permanece oculto pero viaja en el POST para que el Controlador no falle -->
                    <!-- <input type="hidden" name="fechaHasta" id="fechaHastaInput" value="<?php echo htmlspecialchars(substr($parte['fechaHasta'] ?? $parte['fechaDesde'] ?? '', 0, 10)); ?>"> -->
                </div>
            </div>

            <div class="form-group">
                <label>Observaciones Generales</label>
                <textarea name="observaciones" rows="2" placeholder="Notas globales del parte..."><?php echo htmlspecialchars($parte['observaciones'] ?? ''); ?></textarea>
            </div>
        </fieldset>

        <br>

        <!-- ========================================== -->
        <!-- LÍNEAS DEL PARTE                           -->
        <!-- ========================================== -->
        <fieldset>
            <legend>Líneas de Actividad</legend>
            <button type="button" class="btn-secundario" onclick="agregarLineaParte()" style="margin-bottom: 15px;">
                <i class="fa-solid fa-plus"></i> Añadir Línea
            </button>

            <table class="tabla-datos" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 15%;">Cliente</th>
                        <th style="width: 15%;">Centro</th>
                        <th style="width: 10%;">H. Desde</th>
                        <th style="width: 10%;">H. Hasta</th>
                        <th style="width: 15%;">Puesto</th>
                        <th style="width: 15%;">Vehículo</th>
                        <th style="width: 15%;">Observaciones</th>
                        <th style="text-align: center; width: 5%;">Acción</th>
                    </tr>
                </thead>
                <tbody id="cuerpo-lineas-parte">
                    <?php
                    $maxIdFila = 0;
                    if (!empty($lineas)):
                        foreach ($lineas as $idFila => $l):
                            $idFila = is_numeric($idFila) ? (int)$idFila : ++$maxIdFila;
                            if ($idFila > $maxIdFila) {
                                $maxIdFila = $idFila;
                            }

                            $esMaq = (strtolower($l['categoriaProfesional'] ?? '') === 'maquinista');
                            $tieneError = isset($erroresLineas[$idFila]);
                    ?>
                            <tr id="linea_<?php echo $idFila; ?>" class="<?php echo $tieneError ? 'fila-error' : ''; ?>">
                                <!-- CLIENTE POR LÍNEA -->
                                <td>
                                    <div class="input-con-boton">
                                        <input type="hidden" name="lineas[<?php echo $idFila; ?>][idCliente]" id="cliente_id_<?php echo $idFila; ?>" value="<?php echo htmlspecialchars($l['idCliente'] ?? ''); ?>" required>
                                        <input type="text" name="lineas[<?php echo $idFila; ?>][nombreCliente]" id="cliente_nombre_<?php echo $idFila; ?>" value="<?php echo htmlspecialchars($l['nombreCliente'] ?? ''); ?>" readonly required placeholder="Cliente...">
                                        <button type="button" class="btn-secundario btn-icono" onclick="abrirModalCliente(<?php echo $idFila; ?>)" style="padding: 10px;">
                                            <i class="fa-solid fa-building"></i>
                                        </button>
                                    </div>
                                </td>

                                <!-- CENTRO POR LÍNEA -->
                                <td>
                                    <div class="input-con-boton">
                                        <input type="hidden" name="lineas[<?php echo $idFila; ?>][idCentro]" id="centro_id_<?php echo $idFila; ?>" value="<?php echo htmlspecialchars($l['idCentro'] ?? ''); ?>" required>
                                        <input type="text" name="lineas[<?php echo $idFila; ?>][nombreCentro]" id="centro_nombre_<?php echo $idFila; ?>" value="<?php echo htmlspecialchars($l['nombreCentro'] ?? ''); ?>" readonly required placeholder="Centro...">
                                        <button type="button" class="btn-secundario btn-icono" onclick="abrirModalCentro(<?php echo $idFila; ?>)" style="padding: 10px;">
                                            <i class="fa-solid fa-location-dot"></i>
                                        </button>
                                    </div>
                                </td>

                                <td><input type="time" name="lineas[<?php echo $idFila; ?>][horaDesde]" value="<?php echo substr($l['horaDesde'], 0, 5); ?>" required></td>
                                <td><input type="time" name="lineas[<?php echo $idFila; ?>][horaHasta]" value="<?php echo substr($l['horaHasta'], 0, 5); ?>" required></td>

                                <!-- CATEGORÍA -->
                                <td>
                                    <div class="input-con-boton">
                                        <input type="hidden" name="lineas[<?php echo $idFila; ?>][idPuesto]" id="categoria_id_<?php echo $idFila; ?>" value="<?php echo htmlspecialchars($l['idPuesto'] ?? ''); ?>">
                                        <input type="text" name="lineas[<?php echo $idFila; ?>][categoriaProfesional]" id="categoria_nombre_<?php echo $idFila; ?>" value="<?php echo htmlspecialchars($l['categoriaProfesional'] ?? ''); ?>" readonly required placeholder="Categoría...">
                                        <button type="button" class="btn-secundario btn-icono" onclick="abrirModalCategoria(<?php echo $idFila; ?>)" style="padding: 10px;">
                                            <i class="fa-solid fa-list"></i>
                                        </button>
                                    </div>
                                </td>

                                <!-- VEHÍCULO -->
                                <td>
                                    <div class="input-con-boton" id="vehiculo_container_<?php echo $idFila; ?>" style="<?php echo $esMaq ? 'display:flex;' : 'display:none;'; ?>">
                                        <input type="hidden" name="lineas[<?php echo $idFila; ?>][vehiculoUtilizado]" id="vehiculo_id_<?php echo $idFila; ?>" value="<?php echo htmlspecialchars($l['vehiculoUtilizado'] ?? ''); ?>">
                                        <input type="text" id="vehiculo_nombre_<?php echo $idFila; ?>" value="<?php echo htmlspecialchars($l['vehiculoUtilizado'] ?? ''); ?>" readonly placeholder="Vehículo..." <?php echo $esMaq ? 'required' : ''; ?>>
                                        <button type="button" class="btn-secundario btn-icono" onclick="abrirModalVehiculo(<?php echo $idFila; ?>)" style="padding: 10px;">
                                            <i class="fa-solid fa-truck"></i>
                                        </button>
                                    </div>
                                </td>

                                <td><input type="text" name="lineas[<?php echo $idFila; ?>][observaciones]" value="<?php echo htmlspecialchars($l['observaciones'] ?? ''); ?>" placeholder="Obs..."></td>

                                <td style="text-align:center;">
                                    <button type="button" class="btn-sm btn-eliminar" onclick="eliminarLinea(<?php echo $idFila; ?>)"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php if ($tieneError): ?>
                                <tr class="fila-error-mensaje" id="error_linea_<?php echo $idFila; ?>">
                                    <td colspan="8"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $erroresLineas[$idFila]; ?></td>
                                </tr>
                            <?php endif; ?>
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

<!-- Modal Empleados (Cabecera) -->
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
                                    <button type="button" class="btn-sm btn-editar" onclick="seleccionarEmpleado(<?php echo $emp['id']; ?>, '<?php echo htmlspecialchars(addslashes($emp['nombre'] . ' ' . $emp['apellido1'])); ?>')">Seleccionar</button>
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

<!-- Modal Clientes (Línea) -->
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

<!-- Modal Centros (Línea - Dinámico) -->
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
                    <!-- Rellenado por AJAX -->
                </tbody>
            </table>
        </div>
        <button type="button" class="btn-eliminar mt-15" onclick="cerrarModal('modalCentros')">Cancelar</button>
    </div>
</div>

<!-- Modal Categorías -->
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
                    <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <td><strong>Maquinista</strong> <span style="font-size: 0.8rem; color: #b91c1c; margin-left: 8px;"><i class="fa-solid fa-circle-exclamation"></i> Requiere Vehículo</span></td>
                        <td style="text-align: center;">
                            <button type="button" class="btn-principal" style="padding: 5px 10px;" onclick="seleccionarCategoria(0, 'Maquinista', true)">Seleccionar</button>
                        </td>
                    </tr>
                    <?php if (!empty($puestos)): foreach ($puestos as $puesto):
                            if ($puesto['descripcion'] == 'Maquinista') continue; ?>
                            <tr>
                                <td><?php echo htmlspecialchars($puesto['descripcion']); ?></td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-sm btn-editar" onclick="seleccionarCategoria(<?php echo $puesto['id']; ?>, '<?php echo htmlspecialchars(addslashes($puesto['descripcion'])); ?>', false)">Seleccionar</button>
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

<!-- Modal Vehículos -->
<div id="modalVehiculos" class="modal" style="display: none;">
    <div class="modal-contenido" style="max-width: 900px;">
        <h3>Seleccionar Vehículo</h3>
        <div class="tabla-contenedor-scroll">
            <table class="tabla-datos" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="text-align: left;">Denominación</th>
                        <th style="text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($vehiculos)): foreach ($vehiculos as $veh):
                            $denominacion = htmlspecialchars($veh['denominacion']);
                            $denominacionJs = htmlspecialchars(addslashes($veh['denominacion']));
                    ?>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td><strong><?php echo $denominacion; ?></strong></td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-sm btn-editar" onclick="seleccionarVehiculo('<?php echo $denominacionJs; ?>', '<?php echo $denominacionJs; ?>')">Seleccionar</button>
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
    const tbodyLineas = document.getElementById('cuerpo-lineas-parte');
    let contadorLineas = <?php echo isset($maxIdFila) && $maxIdFila > 0 ? $maxIdFila : 0; ?>;

    // Variables para saber qué fila ha invocado cada modal
    let filaClienteActiva = null;
    let filaCentroActiva = null;
    let filaVehiculoActiva = null;
    let filaCategoriaActiva = null;

    function abrirModal(idModal) {
        document.getElementById(idModal).style.display = 'flex';
    }

    function cerrarModal(idModal) {
        document.getElementById(idModal).style.display = 'none';
    }

    // --- NUEVO: Intercepción del envío para mostrar errores en UI ---
    document.getElementById('formParte').addEventListener('submit', function(event) {
        const idEmpleado = document.getElementById('idEmpleadoInput').value;
        const divErrores = document.getElementById('contenedor-errores-js');
        const textoErrores = document.getElementById('texto-errores-js');
        let errores = [];

        // Resetear estilos y ocultar el div
        document.getElementById('nombreEmpleadoInput').style.border = '1px solid #94a3b8';
        divErrores.style.display = 'none';

        // Comprobar Empleado
        if (!idEmpleado || idEmpleado === "0" || idEmpleado === "") {
            errores.push("Falta rellenar el Empleado. Utilice el botón 'Buscar'.");
            document.getElementById('nombreEmpleadoInput').style.border = '2px solid #ef4444';
        }

        // Si hay errores, bloqueamos el envío y mostramos en pantalla
        if (errores.length > 0) {
            event.preventDefault(); 
            textoErrores.innerHTML = "<strong>POR FAVOR REVISE LOS SIGUIENTES ERRORES:</strong><br>" + errores.join("<br>");
            divErrores.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' }); // Llevar al usuario arriba del todo
        }
    });

    // --- Empleado (Cabecera) ---
    function seleccionarEmpleado(id, nombre) {
        document.getElementById('idEmpleadoInput').value = id;
        const inputNombre = document.getElementById('nombreEmpleadoInput');
        inputNombre.value = nombre;
        inputNombre.style.border = '1px solid #94a3b8'; // Restaurar color normal
        cerrarModal('modalEmpleados');
    }

    // --- Añadir Línea en Blanco ---
    function agregarLineaParte() {
        contadorLineas++;
        const fila = document.createElement('tr');
        fila.id = 'linea_' + contadorLineas;

        fila.innerHTML = `
            <td>
                <div class="input-con-boton">
                    <input type="hidden" name="lineas[${contadorLineas}][idCliente]" id="cliente_id_${contadorLineas}" required>
                    <input type="text" name="lineas[${contadorLineas}][nombreCliente]" id="cliente_nombre_${contadorLineas}" readonly required placeholder="Cliente...">
                    <button type="button" class="btn-secundario btn-icono" onclick="abrirModalCliente(${contadorLineas})" style="padding: 10px;"><i class="fa-solid fa-building"></i></button>
                </div>
            </td>
            <td>
                <div class="input-con-boton">
                    <input type="hidden" name="lineas[${contadorLineas}][idCentro]" id="centro_id_${contadorLineas}" required>
                    <input type="text" name="lineas[${contadorLineas}][nombreCentro]" id="centro_nombre_${contadorLineas}" readonly required placeholder="Centro...">
                    <button type="button" class="btn-secundario btn-icono" onclick="abrirModalCentro(${contadorLineas})" style="padding: 10px;"><i class="fa-solid fa-location-dot"></i></button>
                </div>
            </td>
            <td><input type="time" name="lineas[${contadorLineas}][horaDesde]" required></td>
            <td><input type="time" name="lineas[${contadorLineas}][horaHasta]" required></td>
            <td>
                <div class="input-con-boton">
                    <input type="hidden" name="lineas[${contadorLineas}][idPuesto]" id="categoria_id_${contadorLineas}">
                    <input type="text" name="lineas[${contadorLineas}][categoriaProfesional]" id="categoria_nombre_${contadorLineas}" readonly required placeholder="Categoría...">
                    <button type="button" class="btn-secundario btn-icono" onclick="abrirModalCategoria(${contadorLineas})" style="padding: 10px;"><i class="fa-solid fa-list"></i></button>
                </div>
            </td>
            <td>
                <div class="input-con-boton" id="vehiculo_container_${contadorLineas}" style="display:none;">
                    <input type="hidden" name="lineas[${contadorLineas}][vehiculoUtilizado]" id="vehiculo_id_${contadorLineas}">
                    <input type="text" id="vehiculo_nombre_${contadorLineas}" readonly placeholder="Vehículo...">
                    <button type="button" class="btn-secundario btn-icono" onclick="abrirModalVehiculo(${contadorLineas})" style="padding: 10px;"><i class="fa-solid fa-truck"></i></button>
                </div>
            </td>
            <td><input type="text" name="lineas[${contadorLineas}][observaciones]" placeholder="Obs..."></td>
            <td style="text-align:center;"><button type="button" class="btn-sm btn-eliminar" onclick="eliminarLinea(${contadorLineas})"><i class="fa-solid fa-trash"></i></button></td>
        `;
        tbodyLineas.appendChild(fila);
    }

    function eliminarLinea(idFila) {
        const fila = document.getElementById('linea_' + idFila);
        const filaError = document.getElementById('error_linea_' + idFila);
        if (fila) fila.remove();
        if (filaError) filaError.remove();
    }

    // --- Lógica de Clientes y Centros (POR FILA) ---
    function abrirModalCliente(idFila) {
        filaClienteActiva = idFila;
        abrirModal('modalClientes');
    }

    function seleccionarCliente(id, nombre) {
        if (filaClienteActiva !== null) {
            document.getElementById('cliente_id_' + filaClienteActiva).value = id;
            document.getElementById('cliente_nombre_' + filaClienteActiva).value = nombre;
            // Si cambiamos el cliente, borramos el centro anterior de esa línea
            document.getElementById('centro_id_' + filaClienteActiva).value = '';
            document.getElementById('centro_nombre_' + filaClienteActiva).value = '';

            cerrarModal('modalClientes');
            filaClienteActiva = null;
        }
    }

    function abrirModalCentro(idFila) {
        const idClienteFila = document.getElementById('cliente_id_' + idFila).value;
        if (!idClienteFila) {
            alert("Por favor, seleccione primero un Cliente para esta línea.");
            return;
        }
        filaCentroActiva = idFila;
        cargarCentros(idClienteFila);
        abrirModal('modalCentros');
    }

    function cargarCentros(idCliente) {
        const tbody = document.getElementById('cuerpo-tabla-centros');
        tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;">Cargando centros...</td></tr>';

        // Reutilizamos el endpoint del controlador que ya tenías
        fetch(`/index.php?controller=albaran&action=obtenerCentrosPorCliente&idCliente=${idCliente}`)
            .then(async response => {
                if (!response.ok) throw new Error('Error de red');
                return response.json();
            })
            .then(data => {
                tbody.innerHTML = '';
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;">No hay centros asignados a este cliente.</td></tr>';
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
            }).catch(error => {
                tbody.innerHTML = `<tr><td colspan="2" style="text-align:center; color: #b91c1c;">Error al cargar centros.</td></tr>`;
            });
    }

    function seleccionarCentro(id, direccion) {
        if (filaCentroActiva !== null) {
            document.getElementById('centro_id_' + filaCentroActiva).value = id;
            document.getElementById('centro_nombre_' + filaCentroActiva).value = direccion;
            cerrarModal('modalCentros');
            filaCentroActiva = null;
        }
    }

    // --- Lógica Categoría ---
    function abrirModalCategoria(idFila) {
        filaCategoriaActiva = idFila;
        abrirModal('modalCategorias');
    }

    function seleccionarCategoria(id, descripcion, esMaquinista) {
        if (filaCategoriaActiva !== null) {
            document.getElementById('categoria_id_' + filaCategoriaActiva).value = id;
            document.getElementById('categoria_nombre_' + filaCategoriaActiva).value = descripcion;

            const contenedorVehiculo = document.getElementById('vehiculo_container_' + filaCategoriaActiva);
            const inputVehiculoId = document.getElementById('vehiculo_id_' + filaCategoriaActiva);
            const inputVehiculoNombre = document.getElementById('vehiculo_nombre_' + filaCategoriaActiva);

            if (esMaquinista || descripcion.toLowerCase() === 'maquinista') {
                contenedorVehiculo.style.display = 'flex';
                inputVehiculoNombre.required = true;
            } else {
                contenedorVehiculo.style.display = 'none';
                inputVehiculoNombre.required = false;
                inputVehiculoId.value = '';
                inputVehiculoNombre.value = '';
            }
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
            cerrarModal('modalVehiculos');
            filaVehiculoActiva = null;
        }
    }
</script>

<style>
    /* Se mantienen los mismos estilos CSS base que utilizaste en Albaranes */
    .fila-error td {
        background-color: #fef2f2 !important;
        border-top: 2px solid #ef4444;
    }

    .fila-error-mensaje td {
        background-color: #fef2f2;
        color: #b91c1c;
        font-weight: bold;
        padding: 5px 15px;
        border-bottom: 2px solid #ef4444;
    }

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
        padding: 8px;
        border: 1px solid #94a3b8;
        border-radius: 6px;
        font-size: 0.9rem;
        font-family: inherit;
        background: #ffffff;
        width: 100%;
        box-sizing: border-box;
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
        gap: 5px;
    }

    .input-con-boton input {
        flex-grow: 1;
        min-width: 0;
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

    .btn-principal {
        background: #0f4c81;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
    }

    .btn-secundario {
        background: #475569;
        color: white;
        padding: 8px 12px;
        border: none;
        border-radius: 6px;
    }

    .btn-eliminar {
        background: #ef4444;
        color: white;
        padding: 8px 12px;
        border: none;
        border-radius: 6px;
    }

    .btn-icono {
        display: flex;
        align-items: center;
        gap: 5px;
    }
</style>