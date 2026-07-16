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
                    <label for="direccion">Dirección *</label>
                    <input type="text" id="direccion" name="direccion" required 
                           value="<?php echo isset($centro) ? htmlspecialchars($centro['direccion']) : ''; ?>"
                           class="<?php echo isset($errores['direccion']) ? 'input-error' : ''; ?>"
                           maxlength="45"
                           placeholder="Ej: Calle de las Flores, 12">
                </div>

                <div class="form-group">
                    <label for="poblado">Poblado (Localidad)</label>
                    <input type="text" id="poblado" name="poblado" 
                           value="<?php echo isset($centro) ? htmlspecialchars($centro['poblado']) : ''; ?>"
                           maxlength="45"
                           placeholder="Ej: Madrid">
                </div>

            </div>

            <div class="acciones-formulario">
                <a href="/index.php?controller=centro&action=index&idCliente=<?php echo $_GET['idCliente']; ?>" class="btn-secundario">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </a>
                <button type="submit" class="btn-primario">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Centro
                </button>
            </div>
            
        </form>
    </div>
</div>