document.addEventListener("DOMContentLoaded", function () {
  const usuario = localStorage.getItem("usuario");
  const emailElement = document.getElementById("usuario-email");

  if (usuario) {
    emailElement.textContent = usuario;
  } else {
    // si no hay sesión activa, se redirige al login
    window.location.href = "login.html";
  }

  // botón cerrar sesión
  document.getElementById("logout-button").addEventListener("click", function () {
    localStorage.removeItem("usuario");
    window.location.href = "login.html";
  });
});
