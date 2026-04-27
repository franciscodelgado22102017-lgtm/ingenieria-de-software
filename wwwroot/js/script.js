function resetImage(){
	alert("La image se va a reiniciar");
  $.ajax ({url: '/ajax-jquery/img-woman.html',
	     cache: false,
	     success: function (result) {
		 $('#contenido-imagen').html(result);
	     }
	    });
}

function changeImage(){
  alert("Hola imagen se va a cambiar");
  $.ajax ({url: '/ajax-jquery/img-man.html',
	     cache: false,
	     success: function (result) {
		 $('#contenido-imagen').html(result);
	     }
	    });
}

function registroUsuarios(){
	let name = $("#nombre");
	let email = $("#email");
	let password = $("#pwd");
	 // Recopilar datos de un formulario para enviarlos a un servidor.
	let formData = new FormData();
	
	formData.append("nombre", name.val());
	formData.append("email", email.val());
	formData.append("pwd", password.val());
	$.ajax({url: "/ajax-jquery/usuarios.php",
		data: formData,
		processData: false,
		contentType: false,
		type: "POST",
		cache: false,		
		success: function(result){
			$('#form-registro-usuarios')[0].reset(); 
			$('#main').html(result);
		    //alert("El registro de ha completado correctamente!");
		},
		error: function (xhr, status) {

		}
	       });

}

function login(){
	
	let email = $("#email");
	let password = $("#pwd");
	 // Recopilar datos de un formulario para enviarlos a un servidor.
	let formData = new FormData();
	
	
	formData.append("email", email.val());
	formData.append("pwd", password.val());
	$.ajax({url: "/ajax-jquery/login.php",
		data: formData,
		processData: false,
		contentType: false,
		type: "POST",
		cache: false,		
		success: function(result){
			
			$('#main').html(result);
		    //alert("El registro de ha completado correctamente!");
		},
		error: function (xhr, status) {

		}
	       });

}

