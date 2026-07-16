<div class="encabezado-modulo">
    <h2>Seleccionar Tipo de Registro</h2>
    <a href="/index.php?controller=articulo&action=index" class="btn-secundario">Volver</a>
</div>

<div class="tarjeta-formulario" style="max-width: 500px; margin: 0 auto; text-align: center; padding: 40px 20px;">
    <h3 style="margin-top: 0; color: #1e293b; margin-bottom: 20px;">¿Qué tipo de elemento vas a registrar?</h3>
    
    <form action="/index.php" method="GET">
        <input type="hidden" name="controller" value="articulo">
        <input type="hidden" name="action" value="crear">
        
        <select name="prefijo" required style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem;">
            <option value="">-- Seleccionar Catálogo --</option>
            <?php foreach ($catalogos as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat['prefijo']); ?>">
                    <?php echo htmlspecialchars($cat['nombre_tipo']); ?> (<?php echo htmlspecialchars($cat['prefijo']); ?>)
                </option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit" class="btn-primario" style="width: 100%; padding: 12px; font-size: 1.05rem;">Continuar <i class="fa-solid fa-arrow-right"></i></button>
    </form>
</div>