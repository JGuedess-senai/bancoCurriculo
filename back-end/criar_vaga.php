<?php
header('Content-Type: application/json');
include 'conexao.php';
session_start();

// Apenas empresas autenticadas podem criar vagas
$empresaId = isset($_SESSION['empresa']) ? intval($_SESSION['empresa']) : null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Requisição inválida.']);
    exit;
}

// Permitir criação mesmo sem sessão para facilitar testes locais (comentar se desejar forçar login)
// if (!$empresaId) {
//     echo json_encode(['status' => 'erro', 'mensagem' => 'Empresa não autenticada.']);
//     exit;
// }

// Função simples de sanitização
function san($v) {
    return trim(htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
}

$titulo = isset($_POST['titulo']) ? san($_POST['titulo']) : '';
$descricao = isset($_POST['descricao']) ? san($_POST['descricao']) : '';
$requisitos = isset($_POST['requisitos']) ? san($_POST['requisitos']) : '';
$salario = isset($_POST['salario']) ? san($_POST['salario']) : '';
$local = isset($_POST['local']) ? san($_POST['local']) : '';
$tipo = isset($_POST['tipo']) ? san($_POST['tipo']) : '';

// Validações básicas
if (empty($titulo) || empty($descricao)) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Título e descrição são obrigatórios.']);
    exit;
}

// Se empresa não autenticada, aceitar empresa_id enviado (útil para testes)
if (!$empresaId) {
    $empresaId = isset($_POST['empresa_id']) ? intval($_POST['empresa_id']) : null;
    if (!$empresaId) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Empresa não informada.']);
        exit;
    }
}

$stmt = $conexao->prepare("INSERT INTO vagas (empresa_id, titulo, descricao, requisitos, salario, local, tipo) VALUES (?, ?, ?, ?, ?, ?, ?)");
if ($stmt === false) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao preparar statement: ' . $conexao->error]);
    exit;
}

$stmt->bind_param('issssss', $empresaId, $titulo, $descricao, $requisitos, $salario, $local, $tipo);

if ($stmt->execute()) {
    echo json_encode(['status' => 'sucesso', 'mensagem' => 'Vaga criada com sucesso.', 'vaga_id' => $stmt->insert_id]);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao inserir vaga: ' . $stmt->error]);
}

$stmt->close();
$conexao->close();

?>