<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - ERP Remediación</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            color: #1e293b;
            padding: 20px; 
        }
        .tarjeta-login {
            background-color: #ffffff;
            width: 100%;
            max-width: 500px; 
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
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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
        /* Añadimos 'select' a la regla CSS de los inputs */
        .grupo-form input, .grupo-form select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .grupo-form input:focus, .grupo-form select:focus {
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
        <i class="fa-solid fa-user-plus" style="font-size: 3rem; color: #0f4c81; margin-bottom: 15px;"></i>
        
        <h1>Crear Cuenta</h1>
        <p>Rellena tus datos para darte de alta en el ERP</p>

        <?php if (isset($error)): ?>
            <div class="alerta-error">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="/index.php?controller=login&action=registrar" method="POST">
            
            <div class="grid-2">
                <div class="grupo-form">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="grupo-form">
                    <label for="apellido1">Primer Apellido *</label>
                    <input type="text" id="apellido1" name="apellido1" required>
                </div>
            </div>

            <div class="grid-2">
                <div class="grupo-form">
                    <label for="apellido2">Segundo Apellido</label>
                    <input type="text" id="apellido2" name="apellido2" placeholder="(Opcional)">
                </div>
                <div class="grupo-form">
                    <label for="idEmpresa">Empresa *</label>
                    <!-- EL INPUT NUMÉRICO FUE SUSTITUIDO POR ESTE SELECT -->
                    <select id="idEmpresa" name="idEmpresa" required>
                        <option value="">Selecciona una empresa...</option>
                        <?php if (isset($empresas) && !empty($empresas)): ?>
                            <?php foreach ($empresas as $emp): ?>
                                <option value="<?= htmlspecialchars($emp['id']) ?>">
                                    <?= htmlspecialchars($emp['denominacion']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="grupo-form">
                <label for="email">Correo Electrónico *</label>
                <input type="email" id="email" name="email" placeholder="usuario@empresa.com" required>
            </div>

            <div class="grupo-form">
                <label for="contraseña">Contraseña *</label>
                <input type="password" id="contraseña" name="contraseña" placeholder="Mínimo 6 caracteres" required>
            </div>

            <button type="submit" class="btn-entrar">
                Completar Registro <i class="fa-solid fa-check" style="margin-left: 5px;"></i>
            </button>

        </form>

        <div class="enlace-registro">
            ¿Ya tienes una cuenta? <a href="/index.php?controller=login">Inicia sesión aquí</a>
        </div>
    </div>

</body>
</html>