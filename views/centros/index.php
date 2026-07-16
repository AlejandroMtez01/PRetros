<div class="encabezado-modulo">
    <h2>Centros de: <?php echo htmlspecialchars($cliente['razonSocial'] ?? 'Cliente'); ?></h2>
    
    <div style="display: flex; gap: 10px;">
        <a href="/index.php?controller=cliente&action=index" class="btn-secundario" style="background: white; border: 1px solid #cbd5e1; padding: 10px 20px; border-radius: 6px; text-decoration: none; color: #475569; font-weight: 600;">
            <i class="fa-solid fa-arrow-left"></i> Volver a Clientes
        </a>
        <a href="/index.php?controller=centro&action=crear&idCliente=<?php echo $idCliente; ?>" class="btn-primario">
            <i class="fa-solid fa-plus"></i> Nuevo Centro
        </a>
    </div>
</div>

<div class="contenedor-tabla">
    <table class="tabla-datos">
        <thead>
            <tr>
                <th>Dirección</th>
                <th>Poblado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($centros)): ?>
                <?php foreach ($centros as $centro): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($centro['direccion']); ?></td>
                        <td><?php echo htmlspecialchars($centro['poblado']); ?></td>
                        
                        <td class="celda-acciones">
                            <a href="/index.php?controller=centro&action=editar&id=<?php echo $centro['id']; ?>" class="btn-sm btn-editar">
                                <i class="fa-solid fa-pen"></i> Editar
                            </a>
                            <a href="/index.php?controller=centro&action=eliminar&id=<?php echo $centro['id']; ?>&idCliente=<?php echo $centro['idCliente']; ?>" onclick="return confirm('¿Eliminar este centro?');" class="btn-sm btn-eliminar">
                                <i class="fa-solid fa-xmark"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="tabla-vacia">
                        Este cliente no tiene centros registrados.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>