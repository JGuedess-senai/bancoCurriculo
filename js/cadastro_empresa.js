
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

        // Função auxiliar: exibe uma sobreposição informando que o cadastro
        // está aguardando aprovação e oferece botão para ir ao login.
        function showApprovalNotice(message) {
          const existing = document.getElementById('approvalNoticeOverlay');
          if (existing) existing.remove();

          const overlay = document.createElement('div');
          overlay.id = 'approvalNoticeOverlay';
          Object.assign(overlay.style, {
            position: 'fixed',
            top: '0',
            left: '0',
            width: '100%',
            height: '100%',
            backgroundColor: 'rgba(0,0,0,0.5)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            zIndex: '10000'
          });

          const box = document.createElement('div');
          Object.assign(box.style, {
            background: '#fff',
            padding: '20px',
            borderRadius: '8px',
            maxWidth: '420px',
            width: '90%',
            boxShadow: '0 2px 12px rgba(0,0,0,0.4)',
            textAlign: 'center'
          });

          const title = document.createElement('h2');
          title.textContent = 'Cadastro recebido';
          title.style.marginTop = '0';

          const p = document.createElement('p');
          p.textContent = message + ' Você será notificado quando sua conta for aprovada.';

          const actions = document.createElement('div');
          actions.style.marginTop = '12px';

          const btnLogin = document.createElement('button');
          btnLogin.textContent = 'Ir para login';
          Object.assign(btnLogin.style, { marginRight: '8px', padding: '8px 12px', cursor: 'pointer' });
          btnLogin.addEventListener('click', () => {
            window.location.href = '../front-end/login_empresa.html';
          });

          const btnClose = document.createElement('button');
          btnClose.textContent = 'Fechar';
          Object.assign(btnClose.style, { padding: '8px 12px', cursor: 'pointer' });
          btnClose.addEventListener('click', () => overlay.remove());

          actions.appendChild(btnLogin);
          actions.appendChild(btnClose);

          box.appendChild(title);
          box.appendChild(p);
          box.appendChild(actions);
          overlay.appendChild(box);
          document.body.appendChild(overlay);
        }

        // Envia os dados ao backend e trata respostas não-JSON para diagnóstico
        fetch('../back-end/cadastro_empresa.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: formData.toString()
        })
          .then(async (response) => {
            const text = await response.text();
            // tenta parsear JSON, caso contrário mostra o texto para diagnóstico
            try {
              const data = JSON.parse(text);
              return data;
            } catch (e) {
              console.error('Resposta do servidor (não-JSON):', text);
              // mostra mensagem mais útil ao usuário
              alert('Resposta inesperada do servidor:\n' + text);
              throw new Error('invalid-json');
            }
          })
          .then(data => {
            if (data.status === 'sucesso') {
              // Mostra aviso de aprovação pendente em vez de redirecionar de imediato
              showApprovalNotice(data.mensagem);
            } else {
              alert(data.mensagem || 'Erro no servidor');
            }
          })
          .catch(err => {
            if (err.message === 'invalid-json') return; // já tratamos e mostramos ao usuário
            console.error('Erro de rede ou runtime:', err);
            alert('Erro ao enviar os dados. Verifique o console e o servidor.');
          });
      });
    }
  });