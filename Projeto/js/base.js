document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("login-form");
  
    form.addEventListener("submit", function (event) {
      event.preventDefault();
  
      const username = document.getElementById("username").value;
      const password = document.getElementById("password").value;
  
      if (username === "admin" && password === "password1") {
        window.location.href = "adminbase.html";
      }
      else if (username === "cliente" && password === "password2") {
        window.location.href = "clientebase.html";
      }
      else if (username === "funser" && password === "password3") {
        window.location.href = "funserviço.html";
      }
      else if (username === "operador" && password === "password4") {
        window.location.href = "operadorbase.html";
      }
      else if (username === "gerente" && password === "password5") {
        window.location.href = "gerentebase.html";
      }
      else if (username === "funadm" && password === "password6") {
        window.location.href = "funadmbase.html";
      }
       else {
        alert("Invalid username or password!");
      }
    });
  });