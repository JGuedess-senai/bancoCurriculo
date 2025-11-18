<?php
include "conexao.php";
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "erro", "mensagem" => "Requisição inválida."]);
    exit;
}

// Receber dados do POST
$nome = $_POST['nome'] ?? '';
$cnpj = $_POST['cnpj'] ?? '';
$email = $_POST['email'] ?? '';
$senha_plain = $_POST['senha'] ?? '';

// Validações
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "erro", "mensagem" => "E-mail inválido."]);
    exit;
}

if (strlen($senha_plain) < 8 || !preg_match("/[A-Za-z]/", $senha_plain) || !preg_match("/[0-9]/", $senha_plain)) {
    echo json_encode(["status" => "erro", "mensagem" => "Senha inválida. Mínimo 8 caracteres, com letras e números."]);
    exit;
}

if (!preg_match("/^\d{14}$/", $cnpj)) {
    echo json_encode(["status" => "erro", "mensagem" => "CNPJ inválido. Use apenas números."]);
    exit;
}

// Hash da senha
$senha = password_hash($senha_plain, PASSWORD_DEFAULT);

// Preparar o SQL
$sql = "INSERT INTO empresas (nome, cnpj, email, senha, status) VALUES (?, ?, ?, ?, 'pendente')";

$stmt = $conexao->prepare($sql);

if (!$stmt) {
    echo json_encode(["status" => "erro", "mensagem" => "Erro no prepare: " . $conexao->error]);
    $conexao->close();
    exit;
}

// Vincular parâmetros
$bindOk = $stmt->bind_param("ssss", $nome, $cnpj, $email, $senha);

if (!$bindOk) {
    echo json_encode(["status" => "erro", "mensagem" => "Erro no bind_param: " . $stmt->error]);
    $stmt->close();
    $conexao->close();
    exit;
}

// Executar
if ($stmt->execute()) {
    echo json_encode(["status" => "sucesso", "mensagem" => "Cadastro da empresa realizado com sucesso."]);
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Erro ao cadastrar: " . $stmt->error]);
}

$stmt->close();
$conexao->close();
