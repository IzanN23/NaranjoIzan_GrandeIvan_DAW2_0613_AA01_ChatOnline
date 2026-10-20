<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Onlline</title>
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

    <!-- menu sidebar donde aparezcan las personas agregadas -->



    <!-- un div donde esten los componentes del chat -->


    <!-- espacio para mostrar los mensajes con los nombres de los usuarios que los mandan -->


    <!-- un espacio de input para poder escribir los mensajes -->


    <!-- un boton para enviar los mensajes -->

    
</body>
</html>