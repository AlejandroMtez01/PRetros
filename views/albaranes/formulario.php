<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$mensaje_error = $_SESSION['error_guardado'] ?? null;
unset($_SESSION['error_guardado']);

$erroresLineas = isset($erroresLineas) ? $erroresLineas : [];

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
            <i class="fa-solid fa-xmark"></i>&nbsp; Cancelar
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

    <form action="/index.php?controller=albaran&action=<?php echo $accionFormulario; ?>" method="POST" id="formAlbaran" class="formulario-estandar">

        <?php if ($esEdicion): ?>
            <input type="hidden" name="idAlbaran" value="<?php echo $albaran['id']; ?>">
        <?php endif; ?>

        <!-- CABECERA -->
        <fieldset>
            <legend>Datos del Albarán</legend>
            <div class="grid-3">
                <div class="form-group">
                    <label>Número Albarán</label>
                    <input type="number" name="numAlbaran" value="<?php echo htmlspecialchars($albaran['numAlbaran'] ?? ''); ?>" required <?php echo $esEdicion ? 'readonly' : ''; ?>>
                </div>
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" name="fecha" value="<?php echo htmlspecialchars(substr($albaran['fecha'] ?? '', 0, 10)); ?>" required>
                </div>
                <div class="form-group">
                    <label>Cliente</label>
                    <div class="input-con-boton">
                        <input type="hidden" name="idCliente" id="idClienteInput" value="<?php echo $albaran['idCliente'] ?? ''; ?>">
                        <input type="text" name="nombreCliente" id="nombreClienteInput" value="<?php echo htmlspecialchars($albaran['nombreCliente'] ?? ''); ?>" placeholder="Seleccione cliente..." readonly>
                        <button type="button" class="btn-secundario btn-icono" onclick="abrirModal('modalClientes')">
                            <i class="fa-solid fa-building"></i> Buscar
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Centro de Trabajo</label>
                    <div class="input-con-boton">
                        <input type="hidden" name="idCentro" id="idCentroInput" value="<?php echo $albaran['idCentro'] ?? ''; ?>">
                        <input type="text" name="nombreCentro" id="nombreCentroInput" value="<?php echo htmlspecialchars($albaran['nombreCentro'] ?? ''); ?>" placeholder="Seleccione primero un cliente..." readonly>
                        <button type="button" id="btnBuscarCentro" class="btn-secundario btn-icono" onclick="abrirModal('modalCentros')" <?php echo empty($albaran['idCliente']) ? 'disabled' : ''; ?>>
                            <i class="fa-solid fa-location-dot"></i> Buscar
                        </button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="observaciones" rows="2" placeholder="Añade notas o indicaciones aquí..."><?php echo htmlspecialchars($albaran['observaciones'] ?? ''); ?></textarea>
            </div>
        </fieldset>

        <br>

        <!-- LÍNEAS DE EMPLEADOS -->
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
                    $maxIdFila = 0;
                    if (!empty($lineas)):
                        foreach ($lineas as $idFila => $l):
                            $idFila = is_numeric($idFila) ? (int)$idFila : ++$maxIdFila;
                            if ($idFila > $maxIdFila) { $maxIdFila = $idFila; }

                            $nombreEmpleadoMostrado = $l['empNombreCompleto'] ?? trim(($l['empNombre'] ?? '') . ' ' . ($l['empApellido'] ?? ''));

                            $nombreVehiculoCargado = '';
                            if (!empty($l['vehiculoUtilizado'])) {
                                foreach ($vehiculos_precio_hora as $veh) {
                                    if (trim($veh['denominacion']) == trim($l['vehiculoUtilizado'])) {
                                        $nombreVehiculoCargado = htmlspecialchars($veh['denominacion']); break;
                                    }
                                }
                            }

                            $esMaq = (strtolower($l['categoriaProfesional'] ?? '') === 'maquinista');
                            $tieneError = isset($erroresLineas[$idFila]);
                    ?>
                            <tr id="linea_<?php echo $idFila; ?>" class="<?php echo $tieneError ? 'fila-error' : ''; ?>">
                                <td>
                                    <input type="hidden" name="lineas[<?php echo $idFila; ?>][idEmpleado]" value="<?php echo $l['idEmpleado']; ?>">
                                    <input type="hidden" name="lineas[<?php echo $idFila; ?>][empNombreCompleto]" value="<?php echo htmlspecialchars($nombreEmpleadoMostrado); ?>">
                                    <strong><?php echo htmlspecialchars($nombreEmpleadoMostrado); ?></strong>
                                </td>
                                <td><input type="time" name="lineas[<?php echo $idFila; ?>][horaDesde]" value="<?php echo substr($l['horaDesde'], 0, 5); ?>" required></td>
                                <td><input type="time" name="lineas[<?php echo $idFila; ?>][horaHasta]" value="<?php echo substr($l['horaHasta'], 0, 5); ?>" required></td>

                                <td>
                                    <div class="input-con-boton">
                                        <input type="text" name="lineas[<?php echo $idFila; ?>][categoriaProfesional]" id="categoria_nombre_<?php echo $idFila; ?>" value="<?php echo htmlspecialchars($l['categoriaProfesional']); ?>" readonly required placeholder="Categoría..." style="min-width: 120px;">
                                        <button type="button" class="btn-secundario btn-icono" onclick="abrirModalCategoria(<?php echo $idFila; ?>)" style="padding: 10px;">
                                            <i class="fa-solid fa-list"></i>
                                        </button>
                                    </div>
                                </td>

                                <td>
                                    <div class="input-con-boton" id="vehiculo_container_<?php echo $idFila; ?>" style="<?php echo $esMaq ? 'display:flex;' : 'display:none;'; ?>">
                                        <input type="hidden" name="lineas[<?php echo $idFila; ?>][vehiculoUtilizado]" id="vehiculo_id_<?php echo $idFila; ?>" value="<?php echo htmlspecialchars($l['vehiculoUtilizado'] ?? ''); ?>">
                                        <input type="text" id="vehiculo_nombre_<?php echo $idFila; ?>" value="<?php echo $nombreVehiculoCargado; ?>" readonly placeholder="Vehículo..." style="min-width: 100px;" <?php echo $esMaq ? 'required' : ''; ?>>
                                        <button type="button" class="btn-secundario btn-icono" onclick="abrirModalVehiculo(<?php echo $idFila; ?>)" style="padding: 10px;">
                                            <i class="fa-solid fa-truck"></i>
                                        </button>
                                    </div>
                                </td>
                                <td><input type="number" step="0.01" name="lineas[<?php echo $idFila; ?>][importe]" value="<?php echo htmlspecialchars($l['importe'] ?? ''); ?>" placeholder="0.00" style="width: 100px;"></td>
                                <td style="text-align:center;">
                                    <button type="button" class="btn-sm btn-eliminar" onclick="eliminarLinea(<?php echo $idFila; ?>)"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>

                            <?php if ($tieneError): ?>
                                <tr class="fila-error-mensaje" id="error_linea_<?php echo $idFila; ?>">
                                    <td colspan="7">
                                        <i class="fa-solid fa-circle-exclamation"></i> <?php echo $erroresLineas[$idFila]; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>

                    <?php
                        endforeach;
                    endif;
                    ?>
                </tbody>
            </table>
        </fieldset>

        <!-- LÍNEAS DE MATERIALES -->
        <fieldset style="margin-top: 20px;">
            <legend>Materiales Utilizados</legend>
            <button type="button" class="btn-secundario" onclick="abrirModal('modalMateriales')" style="margin-bottom: 15px;">
                <i class="fa-solid fa-box-open"></i> Añadir Material
            </button>

            <table class="tabla-datos" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Precio Unidad (€)</th>
                        <th>Unidades</th>
                        <th>Total (€)</th>
                        <th style="text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody id="cuerpo-lineas-materiales">
                    <?php
                    $contMat = 0;
                    if (!empty($materiales)):
                        foreach ($materiales as $idFila => $m):
                            $idFilaM = is_numeric($idFila) ? (int)$idFila : ++$contMat;
                            if ($idFilaM > $contMat) $contMat = $idFilaM;
                    ?>
                            <tr id="linea_mat_<?php echo $idFilaM; ?>">
                                <td>
                                    <input type="hidden" name="materiales[<?php echo $idFilaM; ?>][denominacionArticulo]" value="<?php echo htmlspecialchars($m['denominacionArticulo']); ?>">
                                    <strong><?php echo htmlspecialchars($m['denominacionArticulo']); ?></strong>
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="precio-mat" id="precio_mat_<?php echo $idFilaM; ?>" name="materiales[<?php echo $idFilaM; ?>][precioUnitario]" value="<?php echo $m['precioUnitario']; ?>" readonly style="width: 100px; background: #e2e8f0;">
                                </td>
                                <td>
                                    <input type="number" step="0.01" id="unidades_mat_<?php echo $idFilaM; ?>" name="materiales[<?php echo $idFilaM; ?>][unidades]" value="<?php echo $m['unidades']; ?>" required oninput="calcularTotalMaterial(<?php echo $idFilaM; ?>)" style="width: 100px;">
                                </td>
                                <td>
                                    <input type="number" step="0.01" id="total_mat_<?php echo $idFilaM; ?>" name="materiales[<?php echo $idFilaM; ?>][importeTotal]" value="<?php echo $m['importeTotal']; ?>" readonly style="width: 100px; background: #e2e8f0; font-weight: bold; color: #0f4c81;">
                                </td>
                                <td style="text-align:center;">
                                    <button type="button" class="btn-sm btn-eliminar" onclick="eliminarLineaMaterial(<?php echo $idFilaM; ?>)"><i class="fa-solid fa-trash"></i></button>
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
                        <td>
                            <strong>Maquinista</strong>
                            <span style="font-size: 0.8rem; color: #b91c1c; margin-left: 8px;"><i class="fa-solid fa-circle-exclamation"></i> Requiere Vehículo</span>
                        </td>
                        <td style="text-align: center;">
                            <button type="button" class="btn-principal" style="padding: 5px 10px;" onclick="seleccionarCategoria('Maquinista', true)">Seleccionar</button>
                        </td>
                    </tr>
                    <?php if (!empty($puestos)): foreach ($puestos as $puesto): ?>
                        <?php if ($puesto['descripcion'] == 'Maquinista') continue; ?>
                            <tr>
                                <td><?php echo htmlspecialchars($puesto['descripcion']); ?></td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-sm btn-editar" onclick="seleccionarCategoria('<?php echo htmlspecialchars(addslashes($puesto['descripcion'])); ?>', false)">Seleccionar</button>
                                </td>
                            </tr>
                    <?php endforeach; endif; ?>
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
                    <?php endforeach; endif; ?>
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
                    <?php if (!empty($centros_actuales)): foreach ($centros_actuales as $cen): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cen['denominacion']); ?></td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-sm btn-editar" onclick="seleccionarCentro(<?php echo $cen['id']; ?>, '<?php echo htmlspecialchars(addslashes($cen['denominacion'])); ?>')">Seleccionar</button>
                                </td>
                            </tr>
                    <?php endforeach; endif; ?>
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
                    <?php endforeach; endif; ?>
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
                            $denominacion = htmlspecialchars($veh['denominacion']);
                            $denominacionJs = htmlspecialchars(addslashes($veh['denominacion']));
                            $prefijo = isset($veh['prefijo_tipo']) ? htmlspecialchars($veh['prefijo_tipo']) : 'VEHÍCULO';
                            $precioH = isset($veh['precio_hora_extraido']) ? number_format((float)$veh['precio_hora_extraido'], 2, ',', '.') . ' €' : 'N/A';
                    ?>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td><strong><?php echo $denominacion; ?></strong></td>
                                <td style="text-align: center;"><span style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; color: #475569; font-weight: bold;"><?php echo $prefijo; ?></span></td>
                                <td style="text-align: center; color: #0f4c81; font-weight: bold;"><?php echo $precioH; ?></td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-sm btn-editar" onclick="seleccionarVehiculo('<?php echo $denominacionJs; ?>', '<?php echo $denominacionJs; ?>')">Seleccionar</button>
                                </td>
                            </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn-eliminar mt-15" onclick="cerrarModal('modalVehiculos')">Cancelar</button>
    </div>
</div>

<!-- Modal Materiales -->
<div id="modalMateriales" class="modal" style="display: none;">
    <div class="modal-contenido">
        <h3>Catálogo de Materiales</h3>
        <div class="tabla-contenedor-scroll">
            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th style="text-align: center;">Stock/Uds</th>
                        <th style="text-align: center;">Precio Ud.</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($catalogoMateriales)): foreach ($catalogoMateriales as $catMat): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($catMat['nombre_extraido']); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($catMat['unidades_extraidas']); ?></td>
                                <td style="text-align: center; font-weight: bold; color: #0f4c81;"><?php echo number_format($catMat['precio_extraido'], 2, ',', '.'); ?> €</td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-sm btn-editar" onclick="agregarLineaMaterial('<?php echo htmlspecialchars(addslashes($catMat['nombre_extraido'])); ?>', <?php echo $catMat['precio_extraido']; ?>)">Añadir</button>
                                </td>
                            </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="4" style="text-align: center; color: #64748b;">No hay materiales registrados en el inventario con este formato.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn-eliminar mt-15" onclick="cerrarModal('modalMateriales')">Cancelar</button>
    </div>
</div>

<!-- ========================================== -->
<!-- SCRIPTS Y LÓGICA DINÁMICA                  -->
<!-- ========================================== -->
<script>
    const tbodyLineas = document.getElementById('cuerpo-lineas-albaran');
    let contadorLineas = <?php echo isset($maxIdFila) && $maxIdFila > 0 ? $maxIdFila : 0; ?>;

    let filaVehiculoActiva = null;
    let filaCategoriaActiva = null;

    function abrirModal(idModal) { document.getElementById(idModal).style.display = 'flex'; }
    function cerrarModal(idModal) { document.getElementById(idModal).style.display = 'none'; }

    // --- NUEVO: Intercepción del envío para mostrar errores en UI ---
    document.getElementById('formAlbaran').addEventListener('submit', function(event) {
        const idCliente = document.getElementById('idClienteInput').value;
        const idCentro = document.getElementById('idCentroInput').value;
        const divErrores = document.getElementById('contenedor-errores-js');
        const textoErrores = document.getElementById('texto-errores-js');
        let errores = [];

        // Resetear estilos y ocultar el div
        document.getElementById('nombreClienteInput').style.border = '1px solid #94a3b8';
        document.getElementById('nombreCentroInput').style.border = '1px solid #94a3b8';
        divErrores.style.display = 'none';

        // Comprobar Cliente
        if (!idCliente || idCliente === "0" || idCliente === "") {
            errores.push("Falta rellenar el Cliente. Utilice el botón 'Buscar'.");
            document.getElementById('nombreClienteInput').style.border = '2px solid #ef4444';
        }
        
        // Comprobar Centro
        if (!idCentro || idCentro === "0" || idCentro === "") {
            errores.push("Falta rellenar el Centro de Trabajo. Utilice el botón 'Buscar'.");
            document.getElementById('nombreCentroInput').style.border = '2px solid #ef4444';
        }

        // Si hay errores, bloqueamos el envío y mostramos en pantalla
        if (errores.length > 0) {
            event.preventDefault(); 
            textoErrores.innerHTML = "<strong>POR FAVOR REVISE LOS SIGUIENTES ERRORES:</strong><br>" + errores.join("<br>");
            divErrores.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' }); // Llevar al usuario arriba del todo
        }
    });

    // --- Lógica Clientes/Centros ---
    function seleccionarCliente(id, nombre) {
        document.getElementById('idClienteInput').value = id;
        const inputNombre = document.getElementById('nombreClienteInput');
        inputNombre.value = nombre;
        inputNombre.style.border = '1px solid #94a3b8'; // Restaurar color normal
        
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
                if (!response.ok) throw new Error('Error del servidor');
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
            }).catch(error => { tbody.innerHTML = `<tr><td colspan="2" style="text-align:center;">Error: ${error.message}</td></tr>`; });
    }

    function seleccionarCentro(id, direccion) {
        document.getElementById('idCentroInput').value = id;
        const inputNombre = document.getElementById('nombreCentroInput');
        inputNombre.value = direccion;
        inputNombre.style.border = '1px solid #94a3b8'; // Restaurar color normal
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
            const inputVehiculoId = document.getElementById('vehiculo_id_' + filaCategoriaActiva);
            const inputVehiculoNombre = document.getElementById('vehiculo_nombre_' + filaCategoriaActiva);

            if (esMaquinista || nombreCategoria.toLowerCase() === 'maquinista') {
                contenedorVehiculo.style.display = 'flex';
                inputVehiculoNombre.required = true;
            } else {
                contenedorVehiculo.style.display = 'none';
                inputVehiculoNombre.required = false;
                inputVehiculoId.value = '';
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

    // --- Lógica Líneas Empleado ---
    function agregarLineaEmpleado(idEmpleado, nombreEmpleado) {
        contadorLineas++;
        const fila = document.createElement('tr');
        fila.id = 'linea_' + contadorLineas;

        fila.innerHTML = `
            <td>
                <input type="hidden" name="lineas[${contadorLineas}][idEmpleado]" value="${idEmpleado}">
                <input type="hidden" name="lineas[${contadorLineas}][empNombreCompleto]" value="${nombreEmpleado}">
                <strong>${nombreEmpleado}</strong>
            </td>
            <td><input type="time" name="lineas[${contadorLineas}][horaDesde]" required></td>
            <td><input type="time" name="lineas[${contadorLineas}][horaHasta]" required></td>
            
            <td>
                <div class="input-con-boton">
                    <input type="text" name="lineas[${contadorLineas}][categoriaProfesional]" id="categoria_nombre_${contadorLineas}" readonly required placeholder="Categoría..." style="min-width: 120px;">
                    <button type="button" class="btn-secundario btn-icono" onclick="abrirModalCategoria(${contadorLineas})" style="padding: 10px;">
                        <i class="fa-solid fa-list"></i>
                    </button>
                </div>
            </td>
            
            <td>
                <div class="input-con-boton" id="vehiculo_container_${contadorLineas}" style="display:none;">
                    <input type="hidden" name="lineas[${contadorLineas}][vehiculoUtilizado]" id="vehiculo_id_${contadorLineas}">
                    <input type="text" id="vehiculo_nombre_${contadorLineas}" readonly placeholder="Vehículo..." style="min-width: 100px;">
                    <button type="button" class="btn-secundario btn-icono" onclick="abrirModalVehiculo(${contadorLineas})" style="padding: 10px;">
                        <i class="fa-solid fa-truck"></i>
                    </button>
                </div>
            </td>
            <td><input type="number" step="0.01" name="lineas[${contadorLineas}][importe]" placeholder="0.00" style="width: 100px;"></td>
            <td style="text-align:center;">
                <button type="button" class="btn-sm btn-eliminar" onclick="eliminarLinea(${contadorLineas})"><i class="fa-solid fa-trash"></i></button>
            </td>
        `;
        tbodyLineas.appendChild(fila);
        cerrarModal('modalEmpleados');
    }

    function eliminarLinea(idFila) {
        const fila = document.getElementById('linea_' + idFila);
        const filaError = document.getElementById('error_linea_' + idFila);
        if (fila) fila.remove();
        if (filaError) filaError.remove();
    }

    // --- Cálculo Empleados ---
    const preciosPuestos = {
        <?php foreach ($puestos as $p): ?> "<?php echo addslashes($p['descripcion']); ?>": <?php echo (float)$p['precioHora']; ?>,
        <?php endforeach; ?>
    };

    const preciosVehiculos = {
        <?php foreach ($vehiculos_precio_hora as $v): ?> "<?php echo addslashes($v['denominacion']); ?>": <?php echo (float)($v['precio_hora_extraido'] ?? 0); ?>,
        <?php endforeach; ?>
    };

    function calcularImporte(idFila) {
        const inputPuesto = document.getElementById('categoria_nombre_' + idFila).value;
        const inputVehiculoId = document.getElementById('vehiculo_id_' + idFila).value;
        const inputImporte = document.querySelector(`input[name="lineas[${idFila}][importe]"]`);
        let importeCalculado = 0;

        if (inputPuesto.toLowerCase() === 'maquinista' && inputVehiculoId) {
            importeCalculado = preciosVehiculos[inputVehiculoId] || 0;
        } else if (preciosPuestos[inputPuesto]) {
            importeCalculado = preciosPuestos[inputPuesto];
        }
        inputImporte.value = importeCalculado.toFixed(2);
    }

    // --- Lógica Materiales ---
    let contadorMateriales = <?php echo isset($contMat) ? $contMat : 0; ?>;
    const tbodyMateriales = document.getElementById('cuerpo-lineas-materiales');

    function agregarLineaMaterial(nombre, precio) {
        contadorMateriales++;
        const fila = document.createElement('tr');
        fila.id = 'linea_mat_' + contadorMateriales;

        fila.innerHTML = `
            <td>
                <input type="hidden" name="materiales[${contadorMateriales}][denominacionArticulo]" value="${nombre}">
                <strong>${nombre}</strong>
            </td>
            <td>
                <input type="number" step="0.01" id="precio_mat_${contadorMateriales}" name="materiales[${contadorMateriales}][precioUnitario]" value="${precio}" readonly style="width: 100px; background: #e2e8f0;">
            </td>
            <td>
                <input type="number" step="0.01" id="unidades_mat_${contadorMateriales}" name="materiales[${contadorMateriales}][unidades]" value="1" required oninput="calcularTotalMaterial(${contadorMateriales})" style="width: 100px;">
            </td>
            <td>
                <input type="number" step="0.01" id="total_mat_${contadorMateriales}" name="materiales[${contadorMateriales}][importeTotal]" value="${precio}" readonly style="width: 100px; background: #e2e8f0; font-weight: bold; color: #0f4c81;">
            </td>
            <td style="text-align:center;">
                <button type="button" class="btn-sm btn-eliminar" onclick="eliminarLineaMaterial(${contadorMateriales})"><i class="fa-solid fa-trash"></i></button>
            </td>
        `;

        tbodyMateriales.appendChild(fila);
        cerrarModal('modalMateriales');
    }

    function eliminarLineaMaterial(idFila) {
        const fila = document.getElementById('linea_mat_' + idFila);
        if (fila) fila.remove();
    }

    function calcularTotalMaterial(idFila) {
        const unidades = parseFloat(document.getElementById('unidades_mat_' + idFila).value) || 0;
        const precio = parseFloat(document.getElementById('precio_mat_' + idFila).value) || 0;
        const totalInput = document.getElementById('total_mat_' + idFila);
        totalInput.value = (unidades * precio).toFixed(2);
    }
</script>

<style>
    .fila-error td { background-color: #fef2f2 !important; border-top: 2px solid #ef4444; }
    .fila-error-mensaje td { background-color: #fef2f2; color: #b91c1c; font-weight: bold; padding: 5px 15px; border-bottom: 2px solid #ef4444; }
    .formulario-estandar fieldset { border: 1px solid #cbd5e1; border-radius: 8px; padding: 20px; background: #f8fafc; margin-bottom: 20px; }
    .formulario-estandar legend { background: #0f4c81; color: #ffffff; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem; font-weight: bold; }
    .form-group { margin-bottom: 15px; display: flex; flex-direction: column; }
    .form-group label { font-weight: 600; color: #334155; margin-bottom: 6px; font-size: 0.85rem; }
    .formulario-estandar input:not([type="hidden"]), .formulario-estandar select, .formulario-estandar textarea { padding: 10px; border: 1px solid #94a3b8; border-radius: 6px; font-size: 0.95rem; font-family: inherit; background: #ffffff; transition: border-color 0.2s, box-shadow 0.2s; width: 100%; box-sizing: border-box; }
    .formulario-estandar input:focus, .formulario-estandar select:focus, .formulario-estandar textarea:focus { outline: none; border-color: #0f4c81; box-shadow: 0 0 0 3px rgba(15, 76, 129, 0.15); }
    .formulario-estandar input[readonly] { background-color: #e2e8f0; cursor: not-allowed; }
    .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
    .input-con-boton { display: flex; gap: 10px; }
    .input-con-boton input { flex-grow: 1; }
    .mt-15 { margin-top: 15px; }
    .alerta-error { background-color: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px; border: 1px solid #f87171; margin-bottom: 20px; font-size: 0.95rem; }
    .modal { position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(15, 23, 42, 0.6); display: flex; align-items: center; justify-content: center; }
    .modal-contenido { background-color: #ffffff; padding: 25px; width: 90%; max-width: 800px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); }
    .modal-contenido h3 { margin-top: 0; color: #0f4c81; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 15px; }
    .tabla-contenedor-scroll { max-height: 400px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 6px; }
    button, .btn-secundario, .btn-principal { font-family: inherit; cursor: pointer; transition: opacity 0.2s; border: none; }
    button:disabled { opacity: 0.5; cursor: not-allowed; }
    button:hover:not(:disabled), .btn-secundario:hover, .btn-principal:hover { opacity: 0.9; }
    .btn-principal { background: #0f4c81; color: white; padding: 12px 24px; border-radius: 6px; font-weight: 600; font-size: 1rem; }
    .btn-secundario { background: #475569; color: white; padding: 10px 15px; border-radius: 6px; font-weight: 500; }
    .btn-eliminar { background: #ef4444; color: white; padding: 10px 15px; border-radius: 6px; font-weight: 500; }
    .btn-icono { display: flex; align-items: center; gap: 8px; white-space: nowrap; }
</style>