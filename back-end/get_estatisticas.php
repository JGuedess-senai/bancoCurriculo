<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php';
session_start();

$stats = [];

// Vagas disponíveis - informação geral (disponível para qualquer usuário)
$resVagas = $conexao->query("SELECT COUNT(*) as total FROM vagas WHERE ativo = 1");
$stats['vagas'] = $resVagas ? (int)$resVagas->fetch_assoc()['total'] : 0;
if ($resVagas) $resVagas->free();

// Painel do aluno (usa chave de sessão 'aluno')
if (isset($_SESSION['aluno'])) {
    $aluno_id = (int)$_SESSION['aluno'];
    // Candidaturas enviadas (pela tabela candidaturas)
    $resCandEnv = $conexao->query("SELECT COUNT(*) as total FROM candidaturas WHERE aluno_id = $aluno_id");
    $stats['candidaturas_enviadas'] = $resCandEnv ? (int)$resCandEnv->fetch_assoc()['total'] : 0;
    if ($resCandEnv) $resCandEnv->free();
    // Candidaturas pendentes (status = 'em_analise')
    $resCandPend = $conexao->query("SELECT COUNT(*) as total FROM candidaturas WHERE aluno_id = $aluno_id AND status = 'em_analise'");
    $stats['candidaturas_pendentes'] = $resCandPend ? (int)$resCandPend->fetch_assoc()['total'] : 0;
    if ($resCandPend) $resCandPend->free();
}

// Painel da empresa (usa chave de sessão 'empresa')
if (isset($_SESSION['empresa'])) {
    $empresa_id = (int)$_SESSION['empresa'];
    // Vagas ativas da empresa
    $resVagasEmp = $conexao->query("SELECT COUNT(*) as total FROM vagas WHERE empresa_id = $empresa_id AND ativo = 1");
    $stats['vagas_ativas'] = $resVagasEmp ? (int)$resVagasEmp->fetch_assoc()['total'] : 0;
    if ($resVagasEmp) $resVagasEmp->free();
    // Candidatos (contagem de candidaturas às vagas da empresa)
    $resCandidatos = $conexao->query("SELECT COUNT(*) as total FROM candidaturas WHERE vaga_id IN (SELECT id FROM vagas WHERE empresa_id = $empresa_id)");
    $stats['candidatos'] = $resCandidatos ? (int)$resCandidatos->fetch_assoc()['total'] : 0;
    if ($resCandidatos) $resCandidatos->free();
    // Candidaturas pendentes para a empresa
    $resCandPendEmp = $conexao->query("SELECT COUNT(*) as total FROM candidaturas WHERE vaga_id IN (SELECT id FROM vagas WHERE empresa_id = $empresa_id) AND status = 'em_analise'");
    $stats['candidaturas_pendentes_empresa'] = $resCandPendEmp ? (int)$resCandPendEmp->fetch_assoc()['total'] : 0;
    if ($resCandPendEmp) $resCandPendEmp->free();
}

echo json_encode($stats, JSON_UNESCAPED_UNICODE);
$conexao->close();
?>