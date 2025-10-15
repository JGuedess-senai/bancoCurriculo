//validações da parte de Alunos
document.addEventListener('DOMContentLoaded', function () {
  function validarSenha(senha) {
    return senha.length >= 8 && /[a-zA-Z]/.test(senha) && /\d/.test(senha);
  }

  const formAluno = document.getElementById('formAluno');
  if (formAluno) {
    formAluno.addEventListener('submit', function (e) {
      e.preventDefault();

      const nome = document.getElementById('nome').value.trim();
      const email = document.getElementById('email').value.trim();
      const data_nascimento = document.getElementById('data_nascimento').value;
      const senha = document.getElementById('senha').value;
      const confirmarSenha = document.getElementById('confirmSenha').value;

      if (!email.endsWith('@etec.sp.gov.br')) {
        alert('Use um e-mail institucional que termine com @etec.sp.gov.br.');
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

      const formData = new URLSearchParams();
      formData.append('nome', nome); 2
      formData.append('email', email);
      formData.append('data_nascimento', data_nascimento);
      formData.append('senha', senha);

      fetch('../back-end/cadastro_aluno.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
      })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'sucesso') {
            alert(data.mensagem);
            window.location.href = '../front-end/login_aluno.html'; // redireciona após cadastro
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