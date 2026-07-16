<div class="encabezado-modulo">
    <h2><?php echo $titulo_formulario; ?></h2>
</div>

<div class="contenedor-formulario">
    <div class="tarjeta-formulario">


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

        <form action="<?php echo $accion_url; ?>" method="POST">
            <div class="form-grid">

                <div class="form-group">
                    <label for="razonSocial">Razón Social *</label>
                    <input type="text" id="razonSocial" name="razonSocial" required
                        value="<?php echo isset($cliente) ? htmlspecialchars($cliente['razonSocial']) : ''; ?>"
                        class="<?php echo isset($errores['razonSocial']) ? 'input-error' : ''; ?>"
                        placeholder="Ej: Constructora López S.L.">
                </div>

                <div class="form-group">
                    <label for="CIF">CIF *</label>
                    <input type="text" id="CIF" name="CIF" required
                        value="<?php echo isset($cliente) ? htmlspecialchars($cliente['CIF']) : ''; ?>"
                        class="<?php echo isset($errores['CIF']) ? 'input-error' : ''; ?>"
                        placeholder="Ej: B12345678">
                </div>

                <div class="form-group col-completa">
                    <label for="sedeFiscal">Sede Fiscal *</label>
                    <input type="text" id="sedeFiscal" name="sedeFiscal" required
                        value="<?php echo isset($cliente) ? htmlspecialchars($cliente['sedeFiscal']) : ''; ?>"
                        class="<?php echo isset($errores['sedeFiscal']) ? 'input-error' : ''; ?>"
                        placeholder="Ej: Av. de la Innovación 45">
                </div>

            </div>

            <div class="acciones-formulario">
                <a href="/index.php?controller=cliente&action=index" class="btn-secundario">
                    <i class="fa-solid fa-xmark"></i> &nbsp;  Cancelar
                </a>
                <button type="submit" class="btn-primario">
                    <i class="fa-solid fa-floppy-disk"></i> &nbsp; Guardar Cliente
                </button>
            </div>
        </form>
    </div>
</div>