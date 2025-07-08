document.getElementById("registoForm").addEventListener("submit", function(event) {
    event.preventDefault();
  
    const nome = document.getElementById("nome").value;
    const morada = document.getElementById("morada").value;
    const nif = document.getElementById("nif").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
  
    console.log("Utilizador registado:");
    console.log("Nome:", nome);
    console.log("Morada:", morada);
    console.log("NIF:", nif);
    console.log("Email:", email);
    console.log("Password:", password);
  
    alert("Utilizador registado com sucesso!");
    this.reset();
  });
  