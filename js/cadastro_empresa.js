
  //validações da parte de Empresas
  document.addEventListener('DOMContentLoaded', function () {
    console.log("JS de cadastro de empresa carregado com sucesso!");

    const formEmpresa = document.getElementById('formEmpresa');
    if (formEmpresa) {
      formEmpresa.addEventListener('submit', function (e) {
        e.preventDefault();

        const nome = document.getElementById('nome').value.trim();
        const cnpj = document.getElementById('cnpj').value.trim();
        const email = document.getElementById('email').value.trim();
        const senha = document.getElementById('senha').value;
        const confirmarSenha = document.getElementById('confirmSenha').value;

        function validarSenha(senha) {
          return senha.length >= 8 && /[a-zA-Z]/.test(senha) && /\d/.test(senha);
        }

        if (!/^\d{14}$/.test(cnpj)) {
          alert('CNPJ inválido. Digite os 14 números sem pontos ou traços.');
          return;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          alert('E-mail inválido.');
          return;
        }

        if (!validarSenha(senha)) {
          alert('A senha deve ter no mínimo 8 caracteres, com letras e números.');
          return;
        }

        if (senha !== confirmarSenha) {
          alert('As senhas não coincidem.');
          return;
        }

        // Envio dos dados
        const formData = new URLSearchParams();
        formData.append('nome', nome);
        formData.append('cnpj', cnpj);
        formData.append('email', email);
        formData.append('senha', senha);

        fetch('../back-end/cadastro_empresa.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: formData.toString()
        })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'sucesso') {
              alert(data.mensagem);
              window.location.href = '../front-end/login_empresa.html';
            } else {
              alert(data.mensagem);
            }
          })
          .catch(err => {
            console.error('Erro:', err);
            alert('Erro ao enviar os dados.');
          });
      });
    }
  });