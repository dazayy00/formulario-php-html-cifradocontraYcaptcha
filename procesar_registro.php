<?php
// Conexión a la base de datos
$host = 'localhost';
$dbname = 'nahuatl';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Verificar que los datos fueron enviados
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];


    //catcha
    $ip = $_SERVER['REMOTE_ADDR'];
    $captcha = $_POST['g-recaptcha-response'];
    $secretkey = "quita el secretkey";

    $respuesta = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretkey&response=$captcha&remoteip=$ip");

    $atributos = json_decode($respuesta, TRUE);

    // Si el CAPTCHA no es válido
    if (!$atributos['success']) {
        echo "<p class='error'>Por favor, completa el CAPTCHA correctamente.</p>";
        exit;
    }
    //finaliza captcha

    //acepta ñ
    if (!preg_match('/^[\wñÑáéíóúü]+(\.[\wñÑáéíóúü]+)*@[a-zA-ZñÑáéíóúü]+(\.[a-zA-ZñÑáéíóúü]+)+$/', $email)) {
        echo "<p class='error'>Por favor ingresa un correo electrónico válido.</p>";
        exit;
    }

    // Cifrar la contraseña
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Insertar datos en la base de datos
    try {
        $sql = "INSERT INTO usuarios (nombre, email, password) VALUES (:nombre, :email, :password)";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $passwordHash, PDO::PARAM_STR);

        $stmt->execute();
        echo "Registro exitoso. ¡Bienvenido, $nombre!";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Código de error para duplicados
            echo "Error: El email ya está registrado.";
        } else {
            echo "Error al registrar: " . $e->getMessage();
        }
    }
}
?>
