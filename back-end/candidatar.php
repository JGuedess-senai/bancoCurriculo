<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php';
session_start();

// Deve estar logado como aluno
if (!isset($_SESSION['aluno'])) {
    http_response_code(401);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário não autenticado']);
    exit;
}

$aluno_id = (int) $_SESSION['aluno'];

$input = json_decode(file_get_contents('php://input'), true);
$vaga_id = isset($input['vaga_id']) ? (int)$input['vaga_id'] : 0;
$empresa_id = isset($input['empresa_id']) ? (int)$input['empresa_id'] : 0;

if ($vaga_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Vaga inválida']);
    exit;
}

// Verifica se a vaga existe e está ativa
$stmt = $conexao->prepare("SELECT id, empresa_id, ativo FROM vagas WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $vaga_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$row = $res->fetch_assoc()) {
    http_response_code(404);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Vaga não encontrada']);
    exit;
}
if ((int)$row['ativo'] !== 1) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Vaga não está ativa']);
    exit;
}

// Se empresa_id não foi enviado, usar o da vaga
if (empty($empresa_id)) {
    $empresa_id = (int)$row['empresa_id'];
}

// Verificar se já existe candidatura
$stmt2 = $conexao->prepare("SELECT id FROM candidaturas WHERE aluno_id = ? AND vaga_id = ? LIMIT 1");
$stmt2->bind_param('ii', $aluno_id, $vaga_id);
$stmt2->execute();
$res2 = $stmt2->get_result();
if ($res2->fetch_assoc()) {
    echo json_encode(['status' => 'ok', 'mensagem' => 'Você já se candidatou a esta vaga']);
    exit;
}

// Inserir candidatura
$stmt3 = $conexao->prepare("INSERT INTO candidaturas (aluno_id, vaga_id, empresa_id) VALUES (?, ?, ?)");
$stmt3->bind_param('iii', $aluno_id, $vaga_id, $empresa_id);
if ($stmt3->execute()) {
    echo json_encode(['status' => 'ok', 'mensagem' => 'Candidatura enviada com sucesso']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao salvar candidatura: ' . $conexao->error]);
}

$stmt->close();
$stmt2->close();
$stmt3->close();
$conexao->close();
?>