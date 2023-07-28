<?php
// Conexión a la base de datos (reemplaza los valores con los de tu propia configuración)
$servername = "localhost";
$username = "root";
$dbname = "pokeapi";

$conn = new mysqli($servername, $username, '', $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Función para escapar caracteres especiales y evitar inyección SQL
function escape($conn, $value) {
    return $conn->real_escape_string($value);
}

// Función para bloquear la cuenta temporalmente después de múltiples intentos fallidos de inicio de sesión
function bloquearCuenta($conn, $usuario) {
    $tiempoBloqueo = time() + 300; // Bloquear durante 5 minutos (300 segundos)

    $sql = "UPDATE sesion SET tiempo_bloqueo = '$tiempoBloqueo' WHERE usuario = '$usuario'";
    $conn->query($sql);
}

// Función para verificar si la cuenta está bloqueada
function cuentaBloqueada($conn, $usuario) {
    $tiempoActual = time();

    $sql = "SELECT tiempo_bloqueo FROM sesion WHERE usuario = '$usuario'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $tiempoBloqueo = $row["tiempo_bloqueo"];

        if ($tiempoActual < $tiempoBloqueo) {
            return true; // Cuenta bloqueada
        }
    }

    return false; // Cuenta no bloqueada
}

// Obtener los valores enviados desde el formulario de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = escape($conn, $_POST["usuario"]);
    $pass = $_POST["pass"];

    // Verificar si la cuenta está bloqueada
    if (cuentaBloqueada($conn, $usuario)) {
        echo "Tu cuenta está bloqueada temporalmente. Inténtalo de nuevo más tarde.";
        exit();
    }

    // Verificar las credenciales en la base de datos
    $sql = "SELECT pass FROM sesion WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->bind_result($passHash);

    if ($stmt->fetch()) {
        // Verificar la pass
        if ($pass == $passHash) {
            // Inicio de sesión exitoso
            header("Location: pokemon.html");
        } else {
            // pass incorrecta
            echo "pass incorrecta.";
$stmt->close();

        }
    } else {
        // Usuario no encontrado
        echo "Usuario no encontrado.";
    }

    $stmt->close();
}
$conn->close();
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Iniciar sesión</title>
    <!-- Archivos CSS -->
    <link href="./dist/css/tabler.min.css?1684106062" rel="stylesheet" />
    <link href="./dist/css/tabler-flags.min.css?1684106062" rel="stylesheet" />
    <link href="./dist/css/tabler-payments.min.css?1684106062" rel="stylesheet" />
    <link href="./dist/css/tabler-vendors.min.css?1684106062" rel="stylesheet" />
    <link href="./dist/css/demo.min.css?1684106062" rel="stylesheet" />
    <style>
        @import url('https://rsms.me/inter/inter.css');

        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }

        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }
    </style>
</head>

<body class=" d-flex flex-column">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <a href="." class="navbar-brand navbar-brand-autodark">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/98/International_Pok%C3%A9mon_logo.svg/800px-International_Pok%C3%A9mon_logo.svg.png"
                        height="70" alt="">
                </a>
            </div>
            <div class="card card-md">
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Inicia sesión</h2>
                    <form action="" method="post" autocomplete="off" novalidate>
                        <div class="mb-3">
                            <label class="mb-3 form-label">Correo electrónico</label>
                            <input type="email" class="mb-5 form-control" name="usuario"
                                placeholder="tucorreo@ejemplo.com" autocomplete="off">
                        </div>
                        <div class="mb-2">
                            <label class="mb-3 form-label">
                                Contraseña
                            </label>
                            <div class="mb-5 input-group input-group-flat">
                                <input type="password" class="form-control" name="pass" placeholder="Tu contraseña"
                                    autocomplete="off">
                                <span class="input-group-text">
                                    <a href="#" class="link-secondary" title="Mostrar contraseña"
                                        data-bs-toggle="tooltip"><!-- Descargar el icono SVG de http://tabler-icons.io/i/eye -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                            <path
                                                d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                                        </svg>
                                    </a>
                                </span>
                            </div>
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
                        </div>
                    </form>
                </div>
                <div class="text-center text-muted mt-3">
                    ¿No tienes una cuenta todavía? <a href="./sign-up.html" tabindex="-1">Regístrate</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Librerías JS -->
    <!-- Tabler Core -->
    <script src="./dist/js/tabler.min.js?1684106062" defer></script>
    <script src="./dist/js/demo.min.js?1684106062" defer></script>
</body>

</html>