<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

session_start();
include 'conexao.php';

// Verificar se o aluno está logado
if (!isset($_SESSION['aluno'])) {
    http_response_code(401);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário não autenticado']);
    exit;
}

$aluno_id = $_SESSION['aluno'];

// Consultar candidaturas do aluno com informações da vaga e empresa
$query = "SELECT 
    c.id,
    c.status,
    c.data_candidatura,
    v.id as vaga_id,
    v.titulo,
    v.descricao,
    v.local,
    v.salario,
    e.nome AS empresa_nome
FROM candidaturas c
INNER JOIN vagas v ON c.vaga_id = v.id
INNER JOIN empresas e ON c.empresa_id = e.id
WHERE c.aluno_id = ?
ORDER BY c.data_candidatura DESC";

$stmt = $conexao->prepare($query);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao preparar consulta: ' . $conexao->error]);
    exit;
}

$stmt->bind_param('i', $aluno_id);
$stmt->execute();

if ($stmt->error) {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro na execução: ' . $stmt->error]);
    exit;
}

$result = $stmt->get_result();
$candidaturas = [];

while ($row = $result->fetch_assoc()) {
    $candidaturas[] = $row;
}

echo json_encode($candidaturas, JSON_UNESCAPED_UNICODE);

$stmt->close();
$conexao->close();
?>
