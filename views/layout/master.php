../<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Remediación de Aguas y Suelos</title>
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    
    <aside class="sidebar">
        <h2>Panel Operativo</h2>       
        <nav>
            <ul>
                <li><a href="/index.php?controller=cliente">👥 Clientes</a></li>
                <li><a href="/index.php?controller=empleado">👷 Empleados</a></li>
                <li><a href="/index.php?controller=catalogo_inventario">⚙️ Catálogo Inventario</a></li>
                <li><a href="/index.php?controller=catalogo_operacion">📋 Catálogo Operaciones</a></li>
                <li><a href="/index.php?controller=articulo">🚜 Inventario</a></li>
                <li><a href="/index.php?controller=proyecto">🏗️ Proyectos</a></li>
                <li><a href="/index.php?controller=tabla&action=index">🏗️ GTablas</a></li>
            </ul>
            <br><br><br>
             <!-- ========================================== -->
        <!-- BLOQUE: PERFIL DE USUARIO Y SALIR          -->
        <!-- ========================================== -->
        <div class="perfil-usuario" style="text-align: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
            <p style="margin: 0; font-weight: 600; font-size: 1.05rem; color: #ffffff;">
                Hola, <?= isset($_SESSION['usuario_nombre']) ? htmlspecialchars($_SESSION['usuario_nombre']) : 'N#D' ?>
            </p>
            <a href="/PRetros/public/index.php?controller=login&action=salir" style="display: inline-block; margin-top: 10px; font-size: 0.85rem; color: #ef4444; text-decoration: none; background: rgba(239, 68, 68, 0.1); padding: 5px 12px; border-radius: 20px; transition: 0.2s;">
                ❌ Cerrar Sesión
            </a>
        </div>
        <!-- ========================================== -->
        </nav>
    </aside>

    <main class="contenido-principal">
        <?php 
        // Aquí se inyecta la tabla de empleados, el formulario de un proyecto, etc.
        require_once $contenido_vista; 
        ?>
    </main>

</body>
</html>