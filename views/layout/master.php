<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Remediación de Aguas y Suelos</title>
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- ESTILOS DEL SELECTOR DE EMPRESA -->
    <style>
        .selector-contexto-empresa {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 10px;
            background-color: rgba(0, 0, 0, 0.1);
        }

        .selector-contexto-empresa label {
            display: block;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .select-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .icon-empresa {
            position: absolute;
            left: 10px;
            color: #cbd5e1;
            font-size: 0.9rem;
            pointer-events: none;
        }

        #selectorEmpresaLateral {
            width: 100%;
            padding: 8px 10px 8px 32px;
            background-color: #334155;
            color: #f8fafc;
            border: 1px solid #475569;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            outline: none;
            transition: border-color 0.2s;
        }

        #selectorEmpresaLateral:hover, #selectorEmpresaLateral:focus {
            border-color: #64748b;
        }

        .empresa-unica-label {
            display: flex;
            align-items: center;
            padding: 8px 10px 8px 32px;
            background-color: rgba(51, 65, 85, 0.3);
            border-radius: 6px;
            color: #e2e8f0;
            font-size: 0.85rem;
            font-weight: 500;
            position: relative;
        }

        .empresa-alerta {
            color: #fca5a5;
            font-size: 0.85rem;
            padding: 10px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 6px;
        }
    </style>
</head>
<body>
    
    <aside class="sidebar">
        <h2>Panel Operativo</h2>       
        
        <!-- ========================================== -->
        <!-- BLOQUE: SELECTOR DE EMPRESA ACTIVA         -->
        <!-- ========================================== -->
        <?php
        $empresas_usuario = isset($_SESSION['empresas_usuario']) ? $_SESSION['empresas_usuario'] : [];
        $id_empresa_activa = isset($_SESSION['idEmpresa']) ? $_SESSION['idEmpresa'] : null;
        ?>
        <div class="selector-contexto-empresa">
            <?php if (count($empresas_usuario) > 1): ?>
                
                <!-- TIENE VARIAS EMPRESAS: Mostramos el desplegable -->
                <!-- Asegúrate de crear la acción 'cambiar_empresa' en tu LoginController -->
                <form action="/index.php?controller=login&action=cambiar_empresa" method="POST" id="formCambioEmpresa">
                    <label for="selectorEmpresaLateral">Empresa Activa</label>
                    <div class="select-wrapper">
                        <i class="fa-solid fa-building icon-empresa"></i>
                        <select name="nueva_empresa_id" id="selectorEmpresaLateral" onchange="this.form.submit()">
                            <?php foreach ($empresas_usuario as $emp): ?>
                                <option value="<?php echo htmlspecialchars($emp['id']); ?>" <?php echo ($id_empresa_activa == $emp['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($emp['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>

            <?php elseif (count($empresas_usuario) === 1): ?>
                
                <!-- TIENE SOLO UNA EMPRESA: Mostramos texto fijo -->
                <label>Empresa Activa</label>
                <div class="empresa-unica-label">
                    <i class="fa-solid fa-building icon-empresa"></i>
                    <span><?php echo htmlspecialchars($empresas_usuario[0]['nombre']); ?></span>
                </div>

            <?php else: ?>
                
                <!-- SIN EMPRESA -->
                <div class="empresa-alerta">
                    <i class="fa-solid fa-triangle-exclamation"></i> Sin empresa asignada
                </div>
                
            <?php endif; ?>
        </div>
        <!-- ========================================== -->

        <nav>
            <ul>
                <li><a href="/index.php?controller=cliente">👥 Clientes</a></li>
                <li><a href="/index.php?controller=empleado">👷 Empleados</a></li>
                <li><a href="/index.php?controller=catalogo_inventario">⚙️ Catálogo Inventario</a></li>
                <li><a href="/index.php?controller=articulo">🚜 Inventario</a></li>
                <!-- <li><a href="/index.php?controller=catalogo_operacion">📋 Catálogo Operaciones</a></li> -->
                <!-- <li><a href="/index.php?controller=proyecto">🏗️ Proyectos</a></li> -->
                 <li><a href="/index.php?controller=albaran">📝 Albaranes</a></li>
                <li><a href="/index.php?controller=tabla&action=index">🔧 Tablas Auxiliares</a></li>
            </ul>
            <br><br><br>
            
            <!-- ========================================== -->
            <!-- BLOQUE: PERFIL DE USUARIO Y SALIR          -->
            <!-- ========================================== -->
            <div class="perfil-usuario" style="text-align: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                <p style="margin: 0; font-weight: 600; font-size: 1.05rem; color: #ffffff;">
                    Hola, <?= isset($_SESSION['usuario_nombre']) ? htmlspecialchars($_SESSION['usuario_nombre']) : 'N#D' ?>
                </p>
                <a href="/index.php?controller=login&action=salir" style="display: inline-block; margin-top: 10px; font-size: 0.85rem; color: #ef4444; text-decoration: none; background: rgba(239, 68, 68, 0.1); padding: 5px 12px; border-radius: 20px; transition: 0.2s;">
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