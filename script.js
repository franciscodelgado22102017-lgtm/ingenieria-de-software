// script.js - Con manejo de cookies
function mostrarNotificacion(mensaje, tipo = 'success') {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${tipo} alert-dismissible fade show shadow`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        <i class="bi bi-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'}-fill me-2"></i>
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv && alertDiv.remove) {
            alertDiv.remove();
        }
    }, 5000);
}

function registroUsuarios() {
    var nombre = document.getElementById('nombre').value;
    var email = document.getElementById('email').value;
    var pwd = document.getElementById('pwd').value;
    
    if (!nombre || !email || !pwd) {
        mostrarNotificacion('Por favor complete todos los campos', 'danger');
        return;
    }
    
    if (pwd.length < 6) {
        mostrarNotificacion('La contraseña debe tener al menos 6 caracteres', 'warning');
        return;
    }
    
    $.ajax({
        url: 'usuarios.php',
        type: 'POST',
        data: {
            nombre: nombre,
            email: email,
            pwd: pwd
        },
        timeout: 30000,
        success: function(response) {
            console.log('Respuesta del servidor (registro):', response);
            if (response.trim() === 'success') {
                mostrarNotificacion('¡Registro exitoso! Redirigiendo...', 'success');
                setTimeout(() => {
                    window.location.href = 'home.php';
                }, 1000);
            } else {
                mostrarNotificacion(response, 'danger');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            mostrarNotificacion('Error al registrar usuario: ' + error, 'danger');
        }
    });
}

// Función para verificar si hay una cookie activa (para redirección automática)
function verificarCookieActiva() {
    // Esta función verifica si existe cookie y redirige
    // Se puede llamar desde el login si es necesario
}
