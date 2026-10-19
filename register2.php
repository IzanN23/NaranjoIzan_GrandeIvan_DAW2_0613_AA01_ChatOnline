<?php
// Inicia la sesión antes de generar HTML; podrá utilizarse para guardar el usuario tras registrarse.
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatOnline | Crear cuenta</title>
    <link rel="stylesheet" href="css/styles.css?v=2">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="Chat Online, inicio">
            <span class="brand-icon" aria-hidden="true">C</span>
            <span>ChatOnline</span>
        </a>
    </header>

    <main class="page-content">
        <section class="intro">
            <p class="eyebrow">Únete a ChatOnline</p>
            <h1>Crea tu cuenta.</h1>
            <p>Completa tus datos para empezar a conversar con tus amigos.</p>
        </section>

        <div class="forms">
            <section class="form-card register-card" aria-labelledby="register-title">
                <div class="card-heading">
                    <h2 id="register-title">Registrarse</h2>
                </div>
                <p class="card-description">Introduce tus datos para crear una cuenta.</p>

                <!-- POST envía los datos al archivo que procesará y validará el registro. -->
                <form action="processes/register.php" method="post">
                    <div class="form-field">
                        <label for="register-real-name">Nombre real</label>
                        <!-- nombre_real es la clave que recibirá el código PHP del registro. -->
                        <input
                            type="text"
                            id="register-real-name"
                            name="nombre_real"
                            maxlength="100"
                            autocomplete="name"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label for="register-username">Username</label>
                        <!-- pattern limita el username a letras, números y guion bajo en el navegador. -->
                        <input
                            type="text"
                            id="register-username"
                            name="username"
                            maxlength="30"
                            pattern="[A-Za-z0-9_]+"
                            title="Utiliza solo letras, números y guion bajo."
                            autocomplete="username"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label for="register-email">Correo electrónico</label>
                        <input
                            type="email"
                            id="register-email"
                            name="email"
                            maxlength="254"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label for="register-password">Contraseña</label>
                        <input
                            type="password"
                            id="register-password"
                            name="password"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label for="register-password-confirmation">Repite la contraseña</label>
                        <input
                            type="password"
                            id="register-password-confirmation"
                            name="confirmar_password"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <button type="submit">Crear cuenta</button>
                </form>
                <p class="form-switch">¿Ya tienes cuenta? <a href="index.php">Inicia sesión</a>.</p>
            </section>
        </div>
    </main>

    <footer class="site-footer">
        <p>ChatOnline · Mensajes para estar más cerca</p>
    </footer>
    
</body>
</html>
