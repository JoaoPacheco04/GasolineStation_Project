document.getElementById('agendamentoForm').addEventListener('submit', function(event) {
    event.preventDefault();
  
    const servico = document.getElementById('servico').value;
    const data = document.getElementById('data').value;
    const hora = document.getElementById('hora').value;
    const mensagemDiv = document.getElementById('mensagem');
  
    if (servico && data && hora) {
      mensagemDiv.innerHTML = ` Serviço <strong>${servico}</strong> marcado para o dia <strong>${data}</strong> às <strong>${hora}</strong>.`;
      mensagemDiv.style.display = 'block';
    } else {
      mensagemDiv.innerHTML = ' Por favor, preencha todos os campos.';
      mensagemDiv.style.display = 'block';
    }
});

document.getElementById('voltarBtn').addEventListener('click', function () {
    window.history.back();
  });