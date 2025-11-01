// Carregar estatísticas ao iniciar a página
document.addEventListener('DOMContentLoaded', function () {
    carregarEstatisticas();
    carregarAprovacoesEmpresasPendentes();
});

// Função para carregar estatísticas do dashboard
function carregarEstatisticas() {
    fetch('../back-end/get_estatisticas.php')
        .then(response => response.json())
        .then(data => {
            document.querySelector('[data-stat="empresas"]').textContent = data.totalEmpresas;
            document.querySelector('[data-stat="alunos"]').textContent = data.totalAlunos;
            document.querySelector('[data-stat="vagas"]').textContent = data.vagasAbertas;
            document.querySelector('[data-stat="contratacoes"]').textContent = data.totalContratacoes;
        })
        .catch(error => console.error('Erro ao carregar estatísticas:', error));
}

// Função para carregar aprovações pendentes de empresas
function carregarAprovacoesEmpresasPendentes() {
    fetch('../back-end/get_empresas_pendentes.php')
        .then(response => response.json())
        .then(data => {
            const totalPendentes = data.length;
            document.querySelector('.action-card p').textContent = 
                `${totalPendentes} ${totalPendentes === 1 ? 'empresa aguardando' : 'empresas aguardando'} aprovação de cadastro`;
        })
        .catch(error => console.error('Erro ao carregar aprovações pendentes:', error));
}

// Validação de senha no modal de configurações
document.getElementById('configuracoesModal').addEventListener('show.bs.modal', function () {
    const form = this.querySelector('form');
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        
        const senhaAtual = document.getElementById('inputSenhaAtual').value;
        const novaSenha = document.getElementById('inputNovaSenha').value;
        const confirmaSenha = document.getElementById('inputConfirmaSenha').value;

        if (novaSenha !== confirmaSenha) {
            alert('As senhas não coincidem!');
            return;
        }

        const formData = new FormData();
        formData.append('senha_atual', senhaAtual);
        formData.append('nova_senha', novaSenha);

        fetch('../back-end/atualizar_senha_admin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Senha atualizada com sucesso!');
                bootstrap.Modal.getInstance(document.getElementById('configuracoesModal')).hide();
                form.reset();
            } else {
                alert(data.message || 'Erro ao atualizar senha!');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao processar a solicitação!');
        });
    });
});

// Gerenciamento do relatório mensal
document.querySelector('.action-card .btn-modern[href="#"]').addEventListener('click', function(e) {
    e.preventDefault();
    gerarRelatorioMensal();
});

function gerarRelatorioMensal() {
    fetch('../back-end/gerar_relatorio_mensal.php')
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'relatorio_mensal.pdf';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        })
        .catch(error => {
            console.error('Erro ao gerar relatório:', error);
            alert('Erro ao gerar relatório mensal!');
        });
}

// Gerenciamento de sessão
document.querySelector('.btn-sair').addEventListener('click', function(e) {
    e.preventDefault();
    
    if (confirm('Deseja realmente sair?')) {
        fetch('../back-end/logout.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'index.html';
                }
            })
            .catch(error => {
                console.error('Erro ao fazer logout:', error);
                window.location.href = 'index.html';
            });
    }
});