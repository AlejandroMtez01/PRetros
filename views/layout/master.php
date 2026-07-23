<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Remediación de Aguas y Suelos</title>
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- ESTILOS DEL SELECTOR DE EMPRESA Y MENÚ COLAPSABLE -->
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
            transition: border-color 0.5s;
        }

        #selectorEmpresaLateral:hover,
        #selectorEmpresaLateral:focus {
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

        .sidebar {
            transition: transform 0.6s ease-in-out;
            z-index: 1000;
        }

        .contenido-principal {
            /* Añadimos transición también para el ancho y ancho máximo */
            transition: margin-left 0.6s ease-in-out, width 0.6s ease-in-out, max-width 0.6s ease-in-out;
        }

        .btn-toggle-menu {
            position: fixed;
            top: 20px;
            left: 260px;
            background-color: #0f4c81;
            color: white;
            border: none;
            border-radius: 0 8px 8px 0;
            width: 35px;
            height: 45px;
            cursor: pointer;
            z-index: 1001;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: left 0.6s ease-in-out, background-color 0.4s;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
        }

        .btn-toggle-menu:hover {
            background-color: #334155;
        }

        /* ========================================== */
        /* ESTILOS PARA OCULTAR/MOSTRAR EL MENÚ       */
        /* ========================================== */

        .sidebar {
            transition: all 0.3s ease-in-out;
            z-index: 1000;
        }

        .contenido-principal {
            transition: all 0.3s ease-in-out;
        }

        .contenedor-albaran,
        .contenedor-tabla,
        .panel-filtros,
        .tarjeta-formulario {
            transition: max-width 0.3s ease-in-out, width 0.3s ease-in-out;
        }

        /* Botón flotante */
        .btn-toggle-menu {
            position: fixed;
            top: 20px;
            left: 260px;
            /* Ajusta este valor si tu sidebar mide distinto (ej. 250px o 300px) */
            background-color: #0f4c81;
            color: white;
            border: none;
            border-radius: 0 8px 8px 0;
            width: 35px;
            height: 45px;
            cursor: pointer;
            z-index: 1001;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: left 0.3s ease-in-out, background-color 0.2s;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
        }

        .btn-toggle-menu:hover {
            background-color: #334155;
        }

        /* --- ESTADOS CUANDO EL MENÚ ESTÁ OCULTO --- */

        body.menu-oculto .sidebar {
            transform: translateX(-100%);
            /* CLAVE: Esto elimina el "hueco" fantasma que dejaba el menú al ocultarse */
            margin-left: -260px;
            /* Debe coincidir con el ancho de tu sidebar */
        }

        body.menu-oculto .btn-toggle-menu {
            left: 0;
        }

        body.menu-oculto .contenido-principal {
            margin-left: 0 !important;
            width: 100% !important;
            flex-grow: 1 !important;
            /* Si usas Flexbox, esto fuerza a ocupar el 100% */
        }

        /* CLAVE: Destruimos el límite de ancho para que se estire al máximo */
        body.menu-oculto .contenedor-albaran,
        body.menu-oculto .contenedor-tabla,
        body.menu-oculto .panel-filtros,
        body.menu-oculto .tarjeta-formulario,
        body.menu-oculto .formulario-estandar {
            max-width: none !important;
            width: 100% !important;
        }
    </style>
</head>

<body>

    <?php
    // Comprobamos si en sesión el menú está marcado como oculto
    $clase_menu = (isset($_SESSION['menu_oculto']) && $_SESSION['menu_oculto'] === true) ? 'menu-oculto' : '';
    ?>

    <body class="<?php echo $clase_menu; ?>"></body>

    <!-- Botón Flotante para ocultar/mostrar menú -->
    <button id="toggleMenuBtn" class="btn-toggle-menu" aria-label="Ocultar/Mostrar menú">
        <i class="fa-solid fa-chevron-left"></i>
    </button>

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
                <label>Empresa Activa</label>
                <div class="empresa-unica-label">
                    <i class="fa-solid fa-building icon-empresa"></i>
                    <span><?php echo htmlspecialchars($empresas_usuario[0]['nombre']); ?></span>
                </div>
            <?php else: ?>
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
                <li><a href="/index.php?controller=albaran">📝 Albaranes</a></li>
                <li><a href="/index.php?controller=partes">🎯 Partes</a></li>
                <li><a href="/index.php?controller=auditoria">📋 Auditoria</a></li>
                <li><a href="/index.php?controller=tabla&action=index">🔧 Tablas Auxiliares</a></li>
                <li><a href="/index.php?controller=puesto&action=index">👩‍💼 Puestos</a></li>
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
        require_once $contenido_vista;
        ?>
    </main>

    <!-- SCRIPT PARA GESTIONAR EL BOTÓN DEL MENÚ -->
   

    <!-- SCRIPT PARA GESTIONAR EL BOTÓN DEL MENÚ Y GUARDAR SESIÓN -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnToggle = document.getElementById('toggleMenuBtn');
            const iconToggle = btnToggle.querySelector('i');
            
            // 1. Ajustar el icono inicial por si la página carga con el menú ya oculto
            if (document.body.classList.contains('menu-oculto')) {
                iconToggle.classList.remove('fa-chevron-left');
                iconToggle.classList.add('fa-chevron-right');
            }

            // 2. Evento de clic
            btnToggle.addEventListener('click', function() {
                document.body.classList.toggle('menu-oculto');
                const estaOculto = document.body.classList.contains('menu-oculto');
                
                // Cambiamos la dirección de la flecha
                if (estaOculto) {
                    iconToggle.classList.remove('fa-chevron-left');
                    iconToggle.classList.add('fa-chevron-right');
                } else {
                    iconToggle.classList.remove('fa-chevron-right');
                    iconToggle.classList.add('fa-chevron-left');
                }

                // 3. Llamada silenciosa al servidor para guardar la preferencia en la sesión
                fetch('/index.php?controller=login&action=guardar_estado_menu', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'oculto=' + (estaOculto ? '1' : '0')
                });
            });
        });
    </script>
</body>

</html>