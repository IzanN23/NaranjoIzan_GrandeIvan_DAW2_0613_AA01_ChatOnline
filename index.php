<?php
// Inicia o recupera la sesion. Debe ocurrir antes de enviar HTML al navegador.
session_start();

// Si el login fue correcto, el procesador guarda username en la sesion.
$username = '';
// El registro redirige con registered=1 para mostrar un aviso de exito.
$registered = isset($_GET['registered']) && $_GET['registered'] === '1';
// El login redirige con error=login si las credenciales no son correctas.
$loginError = isset($_GET['error']) && $_GET['error'] === 'login';

if (isset($_SESSION['username']) && is_string($_SESSION['username'])) {
    $username = $_SESSION['username'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatOnline | Iniciar sesión</title>
    <link rel="stylesheet" href="css/styles.css?v=2">
</head>
<body>
    <!-- Cabecera común de la aplicación. -->
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="Chat Online, inicio">
            <span class="brand-icon" aria-hidden="true">C</span>
            <span>ChatOnline</span>
        </a>

        <!-- Solo se muestra el usuario y el enlace de salida si hay sesión iniciada. -->
        <?php if ($username !== '') { ?>
            <div class="session-info">
                <!-- htmlspecialchars evita interpretar el username como codigo HTML. -->
                <span>Sesión iniciada como <strong><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></strong></span>
                <a href="logout.php">Cerrar sesión</a>
            </div>
        <?php } ?>
    </header>

    <main class="page-content">
        <section class="intro">
            <p class="eyebrow">Tu espacio para conversar</p>
            <h1>Bienvenido a ChatOnline.</h1>
            <p>Inicia sesión para continuar y conversar con tus amigos.</p>
        </section>

        <div class="forms">
            <section class="form-card" aria-labelledby="login-title">
                <div class="card-heading">
                    <h2 id="login-title">Iniciar sesión</h2>
                </div>
                <p class="card-description">¿Ya tienes cuenta? Inicia sesión con tu username.</p>

                <?php if ($registered) { ?>
                    <p class="form-message" role="status">Tu cuenta se ha creado. Ya puedes iniciar sesión.</p>
                <?php } elseif ($loginError) { ?>
                    <p class="form-message" role="alert">Username o contraseña incorrectos.</p>
                <?php } ?>

                <!-- POST envia username y password a login.proc.php, que los compara con la BBDD. -->
                <form action="processes/login.proc.php" method="post">
                    <div class="form-field">
                        <label for="login-username">Username</label>
                        <!-- name define la clave que login.proc.php buscara en $_POST. -->
                        <input
                            type="text"
                            id="login-username"
                            name="username"
                            maxlength="30"
                            autocomplete="username"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label for="login-password">Contraseña</label>
                        <!-- El navegador oculta visualmente el valor; no sustituye el hash en PHP. -->
                        <input
                            type="password"
                            id="login-password"
                            name="password"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <button type="submit">Entrar</button>
                </form>
                <p class="form-switch">¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>.</p>
            </section>
        </div>
    </main>

    <footer class="site-footer">
        <p>ChatOnline · Mensajes para estar más cerca</p>
    </footer>
    
</body>
</html>
