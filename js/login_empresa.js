// Verificação da Empresa
// Suporta tanto form com id "formLoginEmpresa" quanto "formLogin" (HTML usa "formLogin").
const formEmpresa = document.getElementById("formLoginEmpresa") || document.getElementById("formLogin");
if (formEmpresa) {
  formEmpresa.addEventListener("submit", function (event) {
    event.preventDefault();
    const cnpj = document.getElementById("cnpj").value;
    const senha = document.getElementById("senha").value;

    // Função para mostrar overlay de aviso quando o cadastro está pendente
    function showPendingNotice(message) {
      const existing = document.getElementById('pendingNoticeOverlay');
      if (existing) existing.remove();

      const overlay = document.createElement('div');
      overlay.id = 'pendingNoticeOverlay';
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
      title.textContent = 'Aguardando aprovação';
      title.style.marginTop = '0';

      const p = document.createElement('p');
      p.textContent = message + ' Sua conta está em análise e será aprovada em breve.';

      const actions = document.createElement('div');
      actions.style.marginTop = '12px';

      const btnOk = document.createElement('button');
      btnOk.textContent = 'OK';
      Object.assign(btnOk.style, { padding: '8px 12px', cursor: 'pointer' });
      btnOk.addEventListener('click', () => overlay.remove());

      actions.appendChild(btnOk);

      box.appendChild(title);
      box.appendChild(p);
      box.appendChild(actions);
      overlay.appendChild(box);
      document.body.appendChild(overlay);
    }

    fetch("../back-end/login_empresa.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `cnpj=${encodeURIComponent(cnpj)}&senha=${encodeURIComponent(senha)}`
    })
      .then(response => response.text())
      .then(raw => {
        // Normaliza: remove tags HTML (caso o servidor retorne HTML) e trim
        const stripped = raw.replace(/<[^>]*>/g, '').trim();
        console.log('Resposta login (raw):', raw);
        console.log('Resposta login (stripped):', stripped);

        const data = stripped;

        // Se servidor respondeu OK, redireciona
        if (data === "OK") {
          window.location.href = "../front-end/painel_empresa.html";
          return;
        }

        // Detecta mensagens de 'pendente' de forma robusta (insensitive, sem depender de acentos)
        const lower = data.toLowerCase();
        if (lower.includes('pendente') || lower.includes('em analise') || lower.includes('em análise')) {
          showPendingNotice(data);
          return;
        }

        // Caso padrão: mostra mensagem de erro (ou mensagem do servidor)
        alert(data);
      })
      .catch(error => {
        console.error('Erro na requisição:', error);
        alert("Erro na requisição: " + error);
      });
  });
}