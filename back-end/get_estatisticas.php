<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php';
session_start();

$stats = [];

// Painel do aluno
if (isset($_SESSION['aluno_id'])) {
    $aluno_id = $_SESSION['aluno_id'];
    // Vagas disponíveis
    $resVagas = $conexao->query("SELECT COUNT(*) as total FROM vagas WHERE ativo = 1");
    $stats['vagas'] = $resVagas ? (int)$resVagas->fetch_assoc()['total'] : 0;
    if ($resVagas) $resVagas->free();
    // Candidaturas enviadas
    $resCandEnv = $conexao->query("SELECT COUNT(*) as total FROM curriculos WHERE email = (SELECT email FROM alunos WHERE id = $aluno_id)");
    $stats['candidaturas_enviadas'] = $resCandEnv ? (int)$resCandEnv->fetch_assoc()['total'] : 0;
    if ($resCandEnv) $resCandEnv->free();
    // Candidaturas pendentes (exemplo: status = 'pendente' se existir)
    $resCandPend = $conexao->query("SELECT COUNT(*) as total FROM curriculos WHERE email = (SELECT email FROM alunos WHERE id = $aluno_id) AND (status IS NULL OR status = 'pendente')");
    $stats['candidaturas_pendentes'] = $resCandPend ? (int)$resCandPend->fetch_assoc()['total'] : 0;
    if ($resCandPend) $resCandPend->free();
}

// Painel da empresa
if (isset($_SESSION['empresa_id'])) {
    $empresa_id = $_SESSION['empresa_id'];
    // Vagas ativas
    $resVagasEmp = $conexao->query("SELECT COUNT(*) as total FROM vagas WHERE empresa_id = $empresa_id AND ativo = 1");
    $stats['vagas_ativas'] = $resVagasEmp ? (int)$resVagasEmp->fetch_assoc()['total'] : 0;
    if ($resVagasEmp) $resVagasEmp->free();
    // Candidatos (currículos recebidos nas vagas da empresa)
    $resCandidatos = $conexao->query("SELECT COUNT(*) as total FROM curriculos WHERE id IN (SELECT curriculo_id FROM candidaturas WHERE vaga_id IN (SELECT id FROM vagas WHERE empresa_id = $empresa_id))");
    $stats['candidatos'] = $resCandidatos ? (int)$resCandidatos->fetch_assoc()['total'] : 0;
    if ($resCandidatos) $resCandidatos->free();
    // Candidaturas pendentes
    $resCandPendEmp = $conexao->query("SELECT COUNT(*) as total FROM candidaturas WHERE vaga_id IN (SELECT id FROM vagas WHERE empresa_id = $empresa_id) AND (status IS NULL OR status = 'pendente')");
    $stats['candidaturas_pendentes'] = $resCandPendEmp ? (int)$resCandPendEmp->fetch_assoc()['total'] : 0;
    if ($resCandPendEmp) $resCandPendEmp->free();
}

echo json_encode($stats, JSON_UNESCAPED_UNICODE);
$conexao->close();
?>