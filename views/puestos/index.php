<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$mensaje_error = $_SESSION['error_guardado'] ?? null;
unset($_SESSION['error_guardado']); 
?>

<div class="encabezado-seccion" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Gestión de Puestos de Trabajo</h2>
    <button type="button" class="btn-principal" onclick="abrirModalPuestoNuevo()">
        <i class="fa-solid fa-plus"></i> Nuevo Puesto
    </button>
</div>

<?php if ($mensaje_error): ?>
    <div class="alerta-error">
        <i class="fa-solid fa-triangle-exclamation"></i> <strong>Error:</strong> <?= $mensaje_error ?>
    </div>
<?php endif; ?>

<div class="contenedor-tabla">
    <table class="tabla-datos" style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <thead style="background: #f1f5f9; border-bottom: 2px solid #e2e8f0;">
            <tr>
                <th style="padding: 12px 15px; text-align: left; color: #475569;">DESCRIPCIÓN</th>
                <th style="padding: 12px 15px; text-align: right; color: #475569;">PRECIO / HORA</th>
                <th style="padding: 12px 15px; text-align: center; color: #475569; width: 150px;">ACCIONES</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($puestos)): ?>
                <?php foreach ($puestos as $puesto): ?>
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 12px 15px;"><strong><?php echo htmlspecialchars($puesto['descripcion']); ?></strong></td>
                        <td style="padding: 12px 15px; text-align: right; font-weight: bold; color: #0f4c81;">
                            <?php echo number_format($puesto['precioHora'], 2, ',', '.'); ?> €
                        </td>
                        <td style="padding: 12px 15px; display: flex; gap: 8px; justify-content: center;">
                            <!-- Pasamos los datos al JS para rellenar el modal -->
                            <button type="button" class="btn-principal" style="padding: 6px 12px;" 
                                onclick="abrirModalPuestoEditar(<?php echo $puesto['id']; ?>, '<?php echo htmlspecialchars(addslashes($puesto['descripcion'])); ?>', <?php echo $puesto['precioHora']; ?>)">
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <a href="/index.php?controller=puesto&action=eliminar&id=<?php echo $puesto['id']; ?>" class="btn-eliminar" style="padding: 6px 12px; text-decoration: none;" onclick="return confirm('¿Estás seguro de eliminar este puesto?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align: center; padding: 30px; color: #64748b;">No hay puestos registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ========================================== -->
<!-- MODAL DE CREACIÓN / EDICIÓN PUESTOS        -->
<!-- ========================================== -->
<div id="modalEdicionPuesto" class="modal" style="display: none;">
    <div class="modal-contenido" style="max-width: 500px;">
        <h3 id="tituloModalPuesto">Nuevo Puesto</h3>
        
        <form action="/index.php?controller=puesto&action=guardar" method="POST" class="formulario-estandar">
            <input type="hidden" name="id" id="inputIdPuesto" value="">

            <div class="form-group">
                <label>Descripción del Puesto</label>
                <input type="text" name="descripcion" id="inputDescripcion" required placeholder="Ej: Peón Especialista...">
            </div>

            <div class="form-group">
                <label>Precio por Hora (€)</label>
                <!-- step="0.01" permite decimales -->
                <input type="number" step="0.01" name="precioHora" id="inputPrecioHora" required placeholder="0.00" style="font-weight: bold; color: #0f4c81;">
            </div>

            <div style="display: flex; gap: 10px; margin-top: 25px;">
                <button type="submit" class="btn-principal" style="flex: 1;"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                <button type="button" class="btn-secundario" style="flex: 1;" onclick="cerrarModal('modalEdicionPuesto')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModal(idModal) { document.getElementById(idModal).style.display = 'flex'; }
    function cerrarModal(idModal) { document.getElementById(idModal).style.display = 'none'; }

    function abrirModalPuestoNuevo() {
        document.getElementById('inputIdPuesto').value = '';
        document.getElementById('inputDescripcion').value = '';
        document.getElementById('inputPrecioHora').value = '';
        document.getElementById('tituloModalPuesto').innerText = 'Crear Nuevo Puesto';
        abrirModal('modalEdicionPuesto');
    }

    function abrirModalPuestoEditar(id, descripcion, precioHora) {
        document.getElementById('inputIdPuesto').value = id;
        document.getElementById('inputDescripcion').value = descripcion;
        document.getElementById('inputPrecioHora').value = precioHora;
        document.getElementById('tituloModalPuesto').innerText = 'Editar Puesto';
        abrirModal('modalEdicionPuesto');
    }
</script>

<style>
    /* Traemos los estilos esenciales por si se abre fuera de un contexto con CSS cargado */
    .modal { position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(15, 23, 42, 0.6); display: flex; align-items: center; justify-content: center; }
    .modal-contenido { background-color: #ffffff; padding: 25px; width: 90%; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    .modal-contenido h3 { margin-top: 0; color: #0f4c81; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 20px; }
    .form-group { margin-bottom: 15px; display: flex; flex-direction: column; }
    .form-group label { font-weight: 600; color: #334155; margin-bottom: 6px; font-size: 0.85rem; }
    .formulario-estandar input { padding: 10px; border: 1px solid #94a3b8; border-radius: 6px; font-size: 1rem; width: 100%; box-sizing: border-box; }
    button { cursor: pointer; transition: opacity 0.2s; border: none; font-family: inherit; }
    button:hover { opacity: 0.9; }
    .btn-principal { background: #0f4c81; color: white; padding: 10px 15px; border-radius: 6px; font-weight: 600; }
    .btn-secundario { background: #475569; color: white; padding: 10px 15px; border-radius: 6px; font-weight: 600; }
    .btn-eliminar { background: #ef4444; color: white; border-radius: 6px; }
    .alerta-error { background-color: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px; border: 1px solid #f87171; margin-bottom: 20px; }
</style>