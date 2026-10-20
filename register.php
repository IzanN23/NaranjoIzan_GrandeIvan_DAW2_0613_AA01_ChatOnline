<?php
// Inicia la sesion antes de mostrar HTML. PHP la puede continuar en otras paginas.
session_start();

// El procesador vuelve a esta pagina con ?error=... cuando algun dato no es valido.
// El operador ?? usa una cadena vacia si la variable error no viene en la URL.
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- Hace que el diseño se adapte al ancho del dispositivo. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatOnline | Crear cuenta</title>
    <!-- El valor v=2 permite al navegador distinguir esta version del CSS en su cache. -->
    <link rel="stylesheet" href="css/styles.css?v=2">
</head>
<body>
    <!-- Cabecera con un enlace para volver al inicio de sesion. -->
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="Chat Online, inicio">
            <span class="brand-icon" aria-hidden="true">C</span>
            <span>ChatOnline</span>
        </a>
    </header>

    <!-- Contenido principal de la pagina de registro. -->
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

                <!-- Estos mensajes dependen del codigo de error enviado por register.proc.php. -->
                <?php if ($error === 'invalid') { ?>
                    <p class="form-message" role="alert">Revisa los datos: comprueba el formato del email, que las contraseñas coincidan y que la contraseña tenga entre 8 y 72 caracteres.</p>
                <?php } elseif ($error === 'exists') { ?>
                    <p class="form-message" role="alert">Ese username o correo ya está registrado.</p>
                <?php } ?>

                <!-- POST envia los campos en el cuerpo de la peticion a register.proc.php. -->
                <form action="processes/register.proc.php" method="post">
                    <div class="form-field">
                        <label for="register-real-name">Nombre real</label>
                        <!-- El atributo name sera la clave usada para leer el valor desde $_POST. -->
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
                        <!-- pattern valida en el navegador; register.proc.php tambien lo valida en el servidor. -->
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

                    <!-- El tipo email comprueba el formato basico antes de enviar el formulario. -->
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

                    <!-- type=password oculta lo escrito en pantalla, pero no cifra el dato enviado. -->
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

                    <!-- Esta segunda entrada permite comparar ambas contrasenas en PHP. -->
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
