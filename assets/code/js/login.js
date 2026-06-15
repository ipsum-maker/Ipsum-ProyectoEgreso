document.addEventListener("DOMContentLoaded", function() {
  const form = document.getElementById("login-form");

  form.addEventListener("submit", function(event) {
    event.preventDefault();

    const email = document.getElementById("correo").value;
    const password = document.getElementById("contrasena").value;

    if (email && password) {
      // se guarda el correo en localStorage
      localStorage.setItem("usuario", email);

      // redirige al panel
      window.location.href = "panel-profesores.html";
    } else {
      alert("❌ Debe ingresar correo y contraseña");
    }
  });
});
