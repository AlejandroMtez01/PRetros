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
$accion_url = $esEdicion ? '/index.php?controller=empleado&action=actualizar&id=' . $empleado['id'] : '/index.php?controller=empleado&action=guardar';
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

    <!-- Bloque de Error PHP -->
    <?php if ($mensaje_error): ?>
        <div class="alerta-error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <?php echo is_array($mensaje_error) ? implode(" ", $mensaje_error) : htmlspecialchars($mensaje_error); ?>
        </div>
    <?php endif; ?>

    <!-- Contenedor dinámico para errores de JavaScript -->
    <div id="contenedor-errores-js" style="display: none;" class="alerta-error"></div>

    <form action="<?php echo htmlspecialchars($accion_url); ?>" method="POST" class="formulario-estandar" id="formEmpleado">

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
                    <input type="text" id="numSS" name="numSS" value="<?php echo htmlspecialchars($empleado['numSS'] ?? ''); ?>" required placeholder="12 dígitos">
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
<!-- SCRIPT DE VALIDACIÓN DINÁMICA              -->
<!-- ========================================== -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formEmpleado');
        const contenedorErrores = document.getElementById('contenedor-errores-js');

        // Función para validar DNI o NIE español
        function validarDNI(dni) {
            dni = dni.toUpperCase().replace(/[-\s]/g, '');
            if (!/^[YXZ]?\d{7,8}[A-Z]$/.test(dni)) return false;

            const letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
            let numero = dni.substring(0, dni.length - 1);
            const letra = dni.substring(dni.length - 1);

            // Sustituir letras de NIE por números para el cálculo
            numero = numero.replace('X', 0).replace('Y', 1).replace('Z', 2);

            return letras.charAt(numero % 23) === letra;
        }

        // Función para validar Número de la Seguridad Social (NAF) según la regla oficial
        function validarNSS(nss) {
            // Limpiamos posibles espacios, guiones o puntos
            nss = nss.replace(/[-\s\./]/g, '');

            // Debe ser un número de exactamente 12 dígitos
            if (nss.length !== 12 || !/^\d+$/.test(nss)) return false;

            // Desglosamos las partes del NAF
            const provincia = nss.substring(0, 2);
            const numeroSecuencia = nss.substring(2, 10);
            const controlOriginal = parseInt(nss.substring(10, 12), 10);

            const numeroInt = parseInt(numeroSecuencia, 10);
            let cadenaCalculo = '';

            // REGLA OFICIAL DE LA TGSS:
            // Si el número central es menor de 10.000.000 (empieza por cero),
            // se suprime ese primer dígito al concatenar con la provincia.
            if (numeroInt < 10000000) {
                cadenaCalculo = provincia + numeroSecuencia.substring(1);
            } else {
                cadenaCalculo = provincia + numeroSecuencia;
            }

            // Realizamos el cálculo del módulo 97 sobre la cadena correcta
            const dividendo = parseInt(cadenaCalculo, 10);
            const controlCalculado = dividendo % 97;

            return controlCalculado === controlOriginal;
        }



        form.addEventListener('submit', function(e) {
            let errores = [];

            // 1. Validar DNI
            const inputDni = document.getElementById('DNI');
            if (!validarDNI(inputDni.value)) {
                errores.push({
                    mensaje: "El DNI/NIE introducido no es válido matemáticamente.",
                    id: "DNI"
                });
            }

            // 2. Validar Seguridad Social
            const inputSS = document.getElementById('numSS');
            if (!validarNSS(inputSS.value)) {
                errores.push({
                    mensaje: "El Número de la Seguridad Social introducido no es válido (deben ser 12 dígitos concordantes).",
                    id: "numSS"
                });
            }

            // 3. Validar Fechas Congruentes
            const inputAlta = document.getElementById('fechaAlta');
            const inputBaja = document.getElementById('fechaBaja');
            if (inputAlta.value && inputBaja.value) {
                const fechaAlta = new Date(inputAlta.value);
                const fechaBaja = new Date(inputBaja.value);
                const fechaHoy = new Date();

                if (fechaBaja < fechaAlta) {
                    errores.push({
                        mensaje: "Error de congruencia: La Fecha de Baja no puede ser anterior a la Fecha de Alta.",
                        id: "fechaBaja"
                    });
                }

            }
            // Validación independiente para la fecha de alta
            if (inputAlta.value) {
                const fechaAlta = new Date(inputAlta.value);
                fechaAlta.setHours(0, 0, 0, 0);


                // Creamos un objeto para hoy pero reseteando las horas a 00:00:00 para comparar solo el día
                const fechaHoy = new Date();
                fechaHoy.setHours(0, 0, 0, 0);

                // CORRECCIÓN: Si la fecha de alta es estrictamente mayor que hoy
                if (fechaAlta > fechaHoy) {
                    errores.push({
                        mensaje: "Error de congruencia: La Fecha de Alta no puede ser superior a la Fecha de Hoy.",
                        id: "fechaAlta"
                    });
                }
            }

            // Si hay errores, detener el envío y mostrarlos
            if (errores.length > 0) {
                e.preventDefault(); // Evitamos que el formulario haga POST

                // Construimos la lista de errores en HTML
                let htmlErrores = '<i class="fa-solid fa-triangle-exclamation"></i> <strong>Operación denegada. Corrige los siguientes errores:</strong><ul style="margin-top: 10px; padding-left: 20px;">';

                errores.forEach(err => {
                    // Creamos un enlace clickeable que pasa el ID del campo a una función global
                    htmlErrores += `<li style="margin-bottom: 5px;">
                    ${err.mensaje} <a href="javascript:void(0)" onclick="enfocarCampo('${err.id}')" style="color: #b91c1c; text-decoration: underline; font-weight: 500;">(Ir al error)
                    </a>
                </li>`;
                });

                htmlErrores += '</ul>';
                contenedorErrores.innerHTML = htmlErrores;
                contenedorErrores.style.display = 'block';

                // Hacer scroll suave hacia la caja de errores
                contenedorErrores.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            } else {
                contenedorErrores.style.display = 'none';
            }
        });
    });

    // Función global para que al hacer clic en el error haga focus y un pequeño parpadeo rojo
    function enfocarCampo(idCampo) {
        const elemento = document.getElementById(idCampo);
        if (elemento) {
            elemento.focus();
            elemento.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            // Efecto visual temporal para destacar el campo
            const bordeOriginal = elemento.style.borderColor;
            elemento.style.borderColor = '#ef4444';
            elemento.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.2)';

            setTimeout(() => {
                elemento.style.borderColor = bordeOriginal;
                elemento.style.boxShadow = '';
            }, 1500);
        }
    }
</script>

<!-- ========================================== -->
<!-- ESTILOS UNIFICADOS DE FORMULARIO           -->
<!-- ========================================== -->
<style>
    /* ... Tus estilos CSS se mantienen exactamente igual ... */
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

    .btn-principal:hover,
    .btn-secundario:hover {
        opacity: 0.9;
    }
</style>