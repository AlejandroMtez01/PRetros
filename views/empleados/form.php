<div class="cabecera-modulo">
    <h1><?= htmlspecialchars($titulo_formulario) ?></h1>
    <a href="/PRetros/public/index.php?controller=empleado&action=index" class="btn-secundario">
        <i class="fa-solid fa-arrow-left"></i> Volver al listado
    </a>
</div>

<div class="tarjeta-formulario">
    <form action="<?= htmlspecialchars($accion_url) ?>" method="POST">
        
        <!-- Bloque 1: Datos Personales -->
        <h3 class="seccion-titulo">Datos Personales</h3>
        
        <div class="grid-2">
            <div class="form-group">
                <label for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($empleado['nombre'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="apellido1">Primer Apellido *</label>
                <input type="text" id="apellido1" name="apellido1" value="<?= htmlspecialchars($empleado['apellido1'] ?? '') ?>" required>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="apellido2">Segundo Apellido</label>
                <input type="text" id="apellido2" name="apellido2" value="<?= htmlspecialchars($empleado['apellido2'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="DNI">DNI / NIE *</label>
                <input type="text" id="DNI" name="DNI" value="<?= htmlspecialchars($empleado['DNI'] ?? '') ?>" required>
            </div>
        </div>
        <br>
        <!-- Bloque 2: Datos Laborales -->
        <h3 class="seccion-titulo">Datos Laborales</h3>
        
        <div class="grid-2">
            <div class="form-group">
                <label for="numSS">Nº Seguridad Social *</label>
                <input type="text" id="numSS" name="numSS" value="<?= htmlspecialchars($empleado['numSS'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="fechaAlta">Fecha de Alta *</label>
                <!-- Al haber configurado input[type="date"] en el CSS, se verá perfecto -->
                <input type="date" id="fechaAlta" name="fechaAlta" value="<?= htmlspecialchars($empleado['fechaAlta'] ?? '') ?>" required>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="fechaBaja">Fecha de Baja</label>
                <input type="date" id="fechaBaja" name="fechaBaja" value="<?= htmlspecialchars($empleado['fechaBaja'] ?? '') ?>">
            </div>
            <!-- Elemento vacío para mantener la estructura de la cuadrícula de 2 columnas -->
            <div></div> 
        </div>

        <!-- Botón de Envío -->
        <div class="acciones-formulario">
            <button type="submit" class="btn-primario">
                <i class="fa-solid fa-floppy-disk"></i> Guardar Empleado
            </button>
        </div>
        
    </form>
</div>