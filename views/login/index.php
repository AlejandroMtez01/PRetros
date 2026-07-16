<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - ERP Remediación</title>
    <!-- Cargamos los iconos de FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Estilos específicos para que el login quede centrado y elegante */
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            color: #1e293b;
        }
        .tarjeta-login {
            background-color: #ffffff;
            width: 100%;
            max-width: 420px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            text-align: center;
            border-top: 5px solid #0f4c81;
        }
        .tarjeta-login h1 {
            color: #0f4c81;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        .tarjeta-login p {
            color: #64748b;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }
        .grupo-form {
            text-align: left;
            margin-bottom: 20px;
        }
        .grupo-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #475569;
            font-size: 0.9rem;
        }
        .grupo-form input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
            background-color: #f8fafc;
        }
        .grupo-form input:focus {
            outline: none;
            border-color: #0f4c81;
            box-shadow: 0 0 0 3px rgba(15, 76, 129, 0.1);
            background-color: #ffffff;
        }
        .btn-entrar {
            width: 100%;
            background-color: #0f4c81;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 10px;
        }
        .btn-entrar:hover {
            background-color: #0a365c;
        }
        .alerta-error {
            background-color: #fee2e2;
            color: #ef4444;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border: 1px solid #f87171;
            text-align: left;
        }
        .alerta-exito {
            background-color: #d1fae5;
            color: #10b981;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border: 1px solid #34d399;
            text-align: left;
        }
        .enlace-registro {
            display: block;
            margin-top: 25px;
            font-size: 0.9rem;
            color: #64748b;
        }
        .enlace-registro a {
            color: #0f4c81;
            text-decoration: none;
            font-weight: 600;
        }
        .enlace-registro a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="tarjeta-login">
        <!-- Puedes cambiar el icono por el logo de tu empresa -->
        <i class="fa-solid fa-leaf" style="font-size: 3rem; color: #0f4c81; margin-bottom: 15px;"></i>
        
        <h1>ERP Remediación</h1>
        <p>Introduce tus credenciales para acceder</p>

        <!-- Bloque para mostrar el mensaje de error si las credenciales fallan -->
        <?php if (isset($error)): ?>
            <div class="alerta-error">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Bloque para mostrar mensaje de éxito cuando alguien se acaba de registrar -->
        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'registrado'): ?>
            <div class="alerta-exito">
                <i class="fa-solid fa-circle-check"></i> Registro completado con éxito. Ya puedes iniciar sesión.
            </div>
        <?php endif; ?>

        <!-- Formulario apuntando a la acción 'autenticar' de tu Front Controller -->
        <form action="/index.php?controller=login&action=autenticar" method="POST">
            
            <div class="grupo-form">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" placeholder="usuario@empresa.com" required>
            </div>

            <div class="grupo-form">
                <label for="contraseña">Contraseña</label>
                <!-- OJO: El name="contraseña" coincide con lo que pide tu controlador -->
                <input type="password" id="contraseña" name="contraseña" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-entrar">
                Acceder <i class="fa-solid fa-arrow-right-to-bracket" style="margin-left: 5px;"></i>
            </button>

        </form>

        <!-- Enlace para ir al formulario de registro si el usuario no existe -->
        <div class="enlace-registro">
            ¿No tienes una cuenta? <a href="/PRetros/public/index.php?controller=login&action=registrar">Regístrate aquí</a>
        </div>
    </div>

</body>
</html>