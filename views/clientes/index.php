<div class="encabezado-modulo">
    <h2>Listado de Clientes</h2>
    <a href="/index.php?controller=cliente&action=crear" class="btn-primario">
        <i class="fa-solid fa-plus"></i> Nuevo Cliente
    </a>
</div>

<div class="contenedor-tabla">
    <table class="tabla-datos">
        <thead>
            <tr>
                <th>Razón Social</th>
                <th>CIF</th>
                <th>Sede Fiscal</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($clientes)): ?>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cliente['razonSocial']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['CIF']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['sedeFiscal']); ?></td>
                        
                        <td class="celda-acciones">
                            <a href="/index.php?controller=centro&action=index&idCliente=<?php echo $cliente['id']; ?>" class="btn-sm btn-centros">
                                <i class="fa-solid fa-building"></i> Centros
                            </a>

                            <a href="/index.php?controller=cliente&action=editar&id=<?php echo $cliente['id']; ?>" class="btn-sm btn-editar">
                                <i class="fa-solid fa-pen"></i> Editar
                            </a>
                            
                            <a href="/index.php?controller=cliente&action=eliminar&id=<?php echo $cliente['id']; ?>" onclick="return confirm('¿Estás seguro de que deseas eliminar este cliente?');" class="btn-sm btn-eliminar">
                                <i class="fa-solid fa-xmark"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="tabla-vacia">
                        No hay clientes registrados en este momento.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>