<?php
session_start();

if (isset($_SESSION['id_usuario'])) {
    header("Location: home.php");
    exit();
}

if (isset($_COOKIE["id_usuario"]) && !empty($_COOKIE["id_usuario"])) {
    try {
        require_once 'db.php';
        $db = conectarDB();
        $stmt = $db->prepare("SELECT id_usuario, nombre, email FROM usuarios WHERE id_usuario = :id");
        $stmt->execute(['id' => $_COOKIE["id_usuario"]]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['username'] = $usuario['nombre'];
            $_SESSION['email'] = $usuario['email'];
            header("Location: home.php");
            exit();
        }
    } catch (Exception $e) {
        // Error al verificar cookie
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Biblioteca Digital</title>
    <link href="./wwwroot/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./wwwroot/css/bootstrap-icons.min.css">
    <script src="./wwwroot/js/jquery-4.0.0.min.js"></script>
    <style>
        .bg-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            min-width: 300px;
        }
    </style>
</head>
<body class="bg-gradient">
    <div class="alert-container" id="alert-container"></div>

    <div class="container">
        <div class="row min-vh-100 align-items-center justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-success text-white text-center py-4 border-0">
                        <i class="bi bi-book-half fs-1"></i>
                        <h2 class="mt-2 mb-0">Biblioteca Digital</h2>
                        <p class="text-white-50 mb-0">Inicia sesión para continuar</p>
                    </div>

                    <div class="card-body p-5">
                        <form id="loginForm">
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-envelope-fill me-2"></i>Email
                                </label>
                                <input type="email" class="form-control form-control-lg"
                                       id="email" name="email" placeholder="correo@ejemplo.com" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-lock-fill me-2"></i>Contraseña
                                </label>
                                <input type="password" class="form-control form-control-lg"
                                       id="pwd" name="pwd" placeholder="Tu contraseña" required>
                            </div>

                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                                <label class="form-check-label" for="remember">
                                    <i class="bi bi-check-circle me-1"></i>Recordarme por 30 días
                                </label>
                            </div>

                            <button class="btn btn-success btn-lg w-100 rounded-pill" type="submit">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                            </button>

                            <div class="text-center mt-4">
                                <p class="mb-0">¿No tienes cuenta?
                                    <a href="registro.html" class="text-decoration-none fw-bold">
                                        Regístrate aquí
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="./wwwroot/js/bootstrap.bundle.min.js"></script>
    <script>
        function mostrarNotificacion(mensaje, tipo) {
            const alertContainer = document.getElementById('alert-container');
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

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const pwd = document.getElementById('pwd').value;
            const remember = document.getElementById('remember').checked ? 1 : 0;

            console.log("Enviando login - Remember:", remember); // Para debug

            if (!email || !pwd) {
                mostrarNotificacion('Por favor completa todos los campos', 'danger');
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
                dataType: 'text',
                success: function(response) {
                    response = response.trim();
                    console.log("Respuesta:", response);
                    
                    if (response === 'success') {
                        mostrarNotificacion('¡Bienvenido! Redirigiendo...', 'success');
                        setTimeout(() => {
                            window.location.href = 'home.php';
                        }, 1000);
                    } else if (response === 'error_password') {
                        mostrarNotificacion('Contraseña incorrecta', 'danger');
                        document.getElementById('pwd').value = '';
                    } else if (response === 'error_campos_vacios') {
                        mostrarNotificacion('Por favor completa todos los campos', 'danger');
                    } else {
                        mostrarNotificacion('Error al iniciar sesión: ' + response, 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    mostrarNotificacion('Error al conectar con el servidor', 'danger');
                }
            });
        });
    </script>
</body>
</html>
