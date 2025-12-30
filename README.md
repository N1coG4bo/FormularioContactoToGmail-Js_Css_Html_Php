Readme#

Crear un archivo 'config.php' con el siguiente contenido:

<?php
// config.php
// Credenciales para Infinity Free + Gmail
  
define('SMTP_USUARIO', 'correo_con_clave_aplicacion@gmail.com'); // <--- Correo de Gmail al que le sacan la clave de aplicación
define('SMTP_PASSWORD', 'oute anud euñl asif'); // <--- AQUI va tu Contraseña de Aplicación
define('SMTP_DESTINO', 'correo_destino@gmail.com');

// Configuración Fija
define('SMTP_SERVIDOR', 'smtp.gmail.com');
define('SMTP_PUERTO', 465);
?>