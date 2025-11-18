<?php
header('Content-Type: application/json');
include 'conexao.php';
session_start();

// Retorna dados da empresa autenticada ou dados fictícios para testes
$empresaId = isset($_SESSION['empresa']) ? intval($_SESSION['empresa']) : (isset($_GET['id']) ? intval($_GET['id']) : 1);

if (!$empresaId) {
    http_response_code(401);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Empresa não autenticada.']);
    exit;
}

// Buscar empresa no banco
$stmt = $conexao->prepare("SELECT id, nome, email FROM empresas WHERE id = ?");
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao preparar statement.']);
    exit;
}

$stmt->bind_param('i', $empresaId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $empresa = $result->fetch_assoc();
    echo json_encode([
        'status' => 'sucesso',
        'id' => $empresa['id'],
        'nome' => $empresa['nome'],
        'email' => $empresa['email']
    ]);
} else {
    http_response_code(404);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Empresa não encontrada.']);
}

$stmt->close();
$conexao->close();
?>
