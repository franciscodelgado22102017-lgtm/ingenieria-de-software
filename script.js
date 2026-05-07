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

// Función para registro de usuarios
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
    
    // Validar email
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        mostrarNotificacion('Ingrese un email válido', 'danger');
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
                }, 1500);
            } else {
                mostrarNotificacion(response, 'danger');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX completo:', {
                status: status,
                error: error,
                responseText: xhr.responseText
            });
            mostrarNotificacion('Error al registrar usuario: ' + error, 'danger');
        }
    });
}

// Función para login
function login() {
    var email = document.getElementById('email').value;
    var pwd = document.getElementById('pwd').value;
    var remember = document.getElementById('remember') ? (document.getElementById('remember').checked ? 1 : 0) : 0;
    
    if (!email || !pwd) {
        mostrarNotificacion('Por favor complete todos los campos', 'danger');
        return;
    }
    
    $.ajax({
        url: 'login.php',
        type: 'POST',
        data: {
            email: email,
            pwd: pwd,
            remember: remember
        },
        timeout: 30000,
        success: function(response) {
            console.log('Respuesta del servidor (login):', response);
            let resp = response.trim();
            
            if (resp === 'success') {
                mostrarNotificacion('¡Bienvenido! Redirigiendo...', 'success');
                setTimeout(() => {
                    window.location.href = 'home.php';
                }, 1000);
            } else if (resp === 'error_password') {
                mostrarNotificacion('Contraseña incorrecta', 'danger');
                document.getElementById('pwd').value = '';
            } else if (resp === 'error_usuario') {
                mostrarNotificacion('Usuario no encontrado', 'danger');
            } else if (resp === 'error_campos_vacios') {
                mostrarNotificacion('Por favor complete todos los campos', 'danger');
            } else {
                mostrarNotificacion('Email o contraseña incorrectos', 'danger');
                document.getElementById('pwd').value = '';
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX completo:', {
                status: status,
                error: error,
                responseText: xhr.responseText
            });
            mostrarNotificacion('Error al iniciar sesión: ' + error, 'danger');
        }
    });
}

// Función para cambiar imagen (para Ajax.html)
function changeImage() {
    $.ajax({
        url: 'img-man.html',
        type: 'GET',
        success: function(response) {
            $('#contenido-imagen').html(response);
        },
        error: function() {
            console.log('Error al cargar imagen de hombre');
        }
    });
}

function resetImage() {
    $.ajax({
        url: 'img-woman.html',
        type: 'GET',
        success: function(response) {
            $('#contenido-imagen').html(response);
        },
        error: function() {
            console.log('Error al cargar imagen de mujer');
        }
    });
}

// Función para verificar si hay cookie activa y redirigir
function verificarCookieActiva() {
    // Esta función puede ser usada para verificar cookies desde el cliente
    // Útil para redirecciones automáticas
    console.log('Verificando cookies...');
    let cookies = document.cookie.split(';');
    for (let i = 0; i < cookies.length; i++) {
        let cookie = cookies[i].trim();
        if (cookie.startsWith('id_usuario=')) {
            console.log('Cookie de sesión encontrada');
            return true;
        }
    }
    return false;
}

// Inicializar cuando el documento esté listo
$(document).ready(function() {
    console.log('Script cargado correctamente');
    
    // Si estamos en la página de login y hay cookie, redirigir
    if (window.location.pathname.includes('index.php') || window.location.pathname.includes('index.html')) {
        if (verificarCookieActiva()) {
            // Redirigir a home si hay cookie
            window.location.href = 'home.php';
        }
    }
});
