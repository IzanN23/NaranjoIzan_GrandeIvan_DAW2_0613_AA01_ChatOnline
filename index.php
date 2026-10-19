<?php
session_start();

$username = '';

if (isset($_SESSION['username']) && is_string($_SESSION['username'])) {
    $username = $_SESSION['username'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Online | Acceso</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="Chat Online, inicio">
            <span class="brand-icon" aria-hidden="true">C</span>
            <span>ChatOnline</span>
        </a>

        <?php if ($username !== '') { ?>
            <div class="session-info">
                <span>Sesión iniciada como <strong><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></strong></span>
                <a href="logout.php">Cerrar sesión</a>
            </div>
        <?php } ?>
    </header>

    <main class="page-content">
        <section class="intro">
            <p class="eyebrow">Tu espacio para conversar</p>
            <h1>Conecta con tus amigos.</h1>
            <p>Inicia sesión o crea una cuenta para empezar a chatear.</p>
        </section>

        <div class="forms">
            <section class="form-card" aria-labelledby="login-title">
                <div class="card-heading">
                    <span class="card-number">01</span>
                    <h2 id="login-title">Iniciar sesión</h2>
                </div>
                <p class="card-description">¿Ya tienes una cuenta? Entra con tu username.</p>

                <form action="processes/login.php" method="post">
                    <div class="form-field">
                        <label for="login-username">Username</label>
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
            </section>

            <section class="form-card register-card" aria-labelledby="register-title">
                <div class="card-heading">
                    <span class="card-number">02</span>
                    <h2 id="register-title">Crear cuenta</h2>
                </div>
                <p class="card-description">Regístrate para encontrar y agregar amigos.</p>

                <form action="processes/register.php" method="post">
                    <div class="form-field">
                        <label for="register-real-name">Nombre real</label>
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
            </section>
        </div>
    </main>

    <footer class="site-footer">
        <p>ChatOnline · Mensajes para estar más cerca</p>
    </footer>
    
</body>
</html>
