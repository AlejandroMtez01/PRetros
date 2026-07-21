<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$mensaje_error = $_SESSION['error_guardado'] ?? null;
unset($_SESSION['error_guardado']);

// ==========================================
// DETECCIÓN DE MODO: CREAR O EDITAR
// ==========================================
$esEdicion = isset($empleado) && !empty($empleado['id']);
$titulo_formulario = $esEdicion ? 'Editar Empleado: ' . htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido1']) : 'Crear Nuevo Empleado';
$accion_url = $esEdicion ? '/index.php?controller=empleado&action=actualizar' : '/index.php?controller=empleado&action=guardar';
$iconoBoton = $esEdicion ? 'fa-arrows-rotate' : 'fa-floppy-disk';
$textoBoton = $esEdicion ? 'Actualizar Empleado' : 'Guardar Empleado';
?>

<div class="contenedor-albaran">
    <div class="encabezado-seccion" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><?php echo $titulo_formulario; ?></h2>
        <a href="/index.php?controller=empleado" class="btn-secundario" style="text-decoration: none;">
            <i class="fa-solid fa-xmark"></i> Cancelar
        </a>
    </div>

    <!-- Bloque de Error -->
    <?php if ($mensaje_error): ?>
        <div class="alerta-error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <?php echo is_array($mensaje_error) ? implode(" ", $mensaje_error) : htmlspecialchars($mensaje_error); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($accion_url); ?>" method="POST" class="formulario-estandar">
        
        <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?php echo $empleado['id']; ?>">
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- BLOQUE 1: DATOS PERSONALES                 -->
        <!-- ========================================== -->
        <fieldset>
            <legend>Datos Personales</legend>
            <div class="grid-3">
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($empleado['nombre'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="apellido1">Primer Apellido *</label>
                    <input type="text" id="apellido1" name="apellido1" value="<?php echo htmlspecialchars($empleado['apellido1'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="apellido2">Segundo Apellido</label>
                    <input type="text" id="apellido2" name="apellido2" value="<?php echo htmlspecialchars($empleado['apellido2'] ?? ''); ?>">
                </div>
            </div>
        </fieldset>

        <br>

        <!-- ========================================== -->
        <!-- BLOQUE 2: DATOS LABORALES E IDENTIDAD      -->
        <!-- ========================================== -->
        <fieldset>
            <legend>Identidad y Datos Laborales</legend>
            
            <div class="grid-2">
                <div class="form-group">
                    <label for="DNI">DNI / NIE *</label>
                    <input type="text" id="DNI" name="DNI" value="<?php echo htmlspecialchars($empleado['DNI'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="numSS">Nº Seguridad Social *</label>
                    <input type="text" id="numSS" name="numSS" value="<?php echo htmlspecialchars($empleado['numSS'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="fechaAlta">Fecha de Alta *</label>
                    <input type="date" id="fechaAlta" name="fechaAlta" value="<?php echo htmlspecialchars($empleado['fechaAlta'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="fechaBaja">Fecha de Baja</label>
                    <input type="date" id="fechaBaja" name="fechaBaja" value="<?php echo htmlspecialchars($empleado['fechaBaja'] ?? ''); ?>">
                </div>
            </div>
        </fieldset>

        <br>
        <button type="submit" class="btn-principal">
            <i class="fa-solid <?php echo $iconoBoton; ?>"></i> <?php echo $textoBoton; ?>
        </button>
    </form>
</div>

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

    .formulario-estandar input:not([type="hidden"]) {
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

    .formulario-estandar input:focus {
        outline: none;
        border-color: #0f4c81;
        box-shadow: 0 0 0 3px rgba(15, 76, 129, 0.15);
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

    .alerta-error {
        background-color: #fee2e2;
        color: #b91c1c;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #f87171;
        margin-bottom: 20px;
        font-size: 0.95rem;
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