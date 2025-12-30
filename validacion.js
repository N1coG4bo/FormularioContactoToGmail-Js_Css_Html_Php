// Agrega un escuchador de eventos al formulario con el ID 'miFormulario' que se activa al intentar enviarlo.
document.getElementById('miFormulario').addEventListener('submit', function(event) {
    
    // --- OBTENCIÓN DE DATOS DEL FORMULARIO ---
    var nombre = document.getElementById('nombre').value;
    var email = document.getElementById('email').value;
    var asunto = document.getElementById('asunto').value;
    var mensaje = document.getElementById('mensaje').value;

    // --- VALIDACIÓN DE CAMPOS VACÍOS ---
    // Comprueba si alguno de los campos (nombre, email, asunto o mensaje) está vacío o solo contiene espacios en blanco.
    if(nombre.trim() === '' || email.trim() === '' || asunto.trim() === '' || mensaje.trim() === '') {
        alert('Por favor, completa todos los campos (incluyendo el asunto).');
        event.preventDefault();
        return;
    }

    // --- VALIDACIÓN DEL FORMATO DE EMAIL ---
    if(!email.includes('@') || !email.includes('.')) {
        alert('Por favor, ingresa un correo válido.');
        event.preventDefault();
        return;
    }
    
    // --- ENVÍO DEL FORMULARIO ---
    // Si todas las validaciones anteriores son exitosas, el formulario se enviará al archivo 'enviar.php' especificado en el atributo 'action' del formulario HTML.
});