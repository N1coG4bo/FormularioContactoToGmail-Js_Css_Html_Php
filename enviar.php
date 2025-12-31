<?php
// enviar.php - Optimizado para Infinity Free
// Autor: Nicolás & Kirli AI
header('Content-Type: text/html; charset=utf-8');

// 1. CARGA DE CREDENCIALES SEGURA
// Carga de credenciales segura
if (file_exists('config.php')) {
    require_once 'config.php';
} else {
    die("Error crítico: Falta config.php. Sube este archivo a tu carpeta en Infinity Free.");
}

// 2. VERIFICACIÓN DEL MÉTODO DE ENVÍO
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3. RECOLECCIÓN Y LIMPIEZA DE DATOS
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $email_usuario = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $asunto_usuario = isset($_POST['asunto']) ? htmlspecialchars(trim($_POST['asunto'])) : "Consulta Web";
    $mensaje = htmlspecialchars(trim($_POST['mensaje']));

    // Uso de variables desde config.php
    $mi_usuario = SMTP_USUARIO; 
    $mi_password = SMTP_PASSWORD; 
    $correo_destino = SMTP_DESTINO; 
    $servidor = SMTP_SERVIDOR;
    $puerto = SMTP_PUERTO; 

    // 4. CONSTRUCCIÓN DEL CORREO
    $asunto_final = $asunto_usuario . " (" . $nombre . ")";
    
    // Cabeceras vitales para evitar Spam
    $cabeceras  = "Date: " . date("r") . "\r\n";
    $cabeceras .= "From: Nicolas Web <" . $mi_usuario . ">\r\n";
    $cabeceras .= "To: " . $correo_destino . "\r\n";
    $cabeceras .= "Reply-To: " . $email_usuario . "\r\n";
    $cabeceras .= "Subject: " . $asunto_final . "\r\n";
    $cabeceras .= "Message-ID: <" . time() . "-" . $mi_usuario . ">\r\n";
    $cabeceras .= "X-Mailer: PHP/InfinityFree-Socket\r\n";
    $cabeceras .= "MIME-Version: 1.0\r\n";
    $cabeceras .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";

    // Cuerpo del mensaje
    $cuerpo  = "Nuevo mensaje desde Infinity Free:\r\n";
    $cuerpo .= "-----------------------------------\r\n";
    $cuerpo .= "De: $nombre ($email_usuario)\r\n";
    $cuerpo .= "Asunto: $asunto_usuario\r\n";
    $cuerpo .= "-----------------------------------\r\n";
    $cuerpo .= $mensaje . "\r\n";
    $cuerpo .= "-----------------------------------\r\n.";

    // 5. ENVÍO DEL CORREO (MOTOR DE ENVÍO)
    try {
        // TRUCO PARA INFINITY FREE:
        // Creamos un contexto que permite certificados SSL "relajados".
        // Esto evita el error "SSL operation failed" común en hostings compartidos.
        $opciones_ssl = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        $contexto = stream_context_create($opciones_ssl);

        // Usamos stream_socket_client en lugar de fsockopen (es más moderno y acepta opciones)
        $socket = stream_socket_client(
            "ssl://" . $servidor . ":" . $puerto, 
            $errno, 
            $errstr, 
            15, 
            STREAM_CLIENT_CONNECT, 
            $contexto
        );

        if (!$socket) throw new Exception("Error de conexión ($errno): $errstr");

        // Función auxiliar para leer respuesta del servidor
        function chat_silencioso($socket, $msg) {
            if ($msg !== null) fputs($socket, $msg . "\r\n");
            $response = "";
            while($str = fgets($socket, 512)) {
                $response .= $str;
                // Si la linea 4 es un espacio, terminó la respuesta (Estándar SMTP)
                if(substr($str, 3, 1) == ' ') break; 
            }
            return $response;
        }

        // Diálogo SMTP
        chat_silencioso($socket, null); // Leer bienvenida del servidor
        chat_silencioso($socket, "EHLO " . $_SERVER['HTTP_HOST']);
        
        // Autenticación
        chat_silencioso($socket, "AUTH LOGIN");
        chat_silencioso($socket, base64_encode($mi_usuario));
        chat_silencioso($socket, base64_encode($mi_password));
        
        // Datos del sobre (Envelope)
        chat_silencioso($socket, "MAIL FROM: <$mi_usuario>");
        chat_silencioso($socket, "RCPT TO: <$correo_destino>");
        chat_silencioso($socket, "DATA");
        
        // Enviar contenido y finalizar con punto
        fputs($socket, $cabeceras . $cuerpo . "\r\n");
        $resultado_final = chat_silencioso($socket, "."); // El punto indica fin del mensaje
        
        chat_silencioso($socket, "QUIT");
        fclose($socket);

        // 6. RESPUESTA AL USUARIO
        // El código 250 significa "OK" en idioma SMTP
        if (strpos($resultado_final, "250") !== false) {
            echo "<script>
                alert('¡Mensaje enviado con éxito desde Infinity Free!');
                window.location.href='index.html';
            </script>";
        } else {
            // Si falla, mostramos qué respondió Google (útil para depurar)
            // En producción real, no muestres $resultado_final al usuario.
            throw new Exception("Google rechazó el mensaje: " . $resultado_final);
        }

    } catch (Exception $e) {
        echo "<script>
            console.error('Error: " . addslashes($e->getMessage()) . "');
            alert('Error al enviar. Revisa la consola (F12) o verifica tu contraseña de aplicación.');
            window.location.href='index.html';
        </script>";
    }

} else {
    header("Location: index.html");
}
?>