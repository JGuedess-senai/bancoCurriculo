<?php
include "conexao.php";
header('Content-Type: application/json');

// Verifica se veio tudo via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $data = $_POST['data_nascimento'] ?? '';
    $senha_plain = $_POST['senha'] ?? '';

    // Validação de senha
    if (strlen($senha_plain) < 8 || !preg_match("/[A-Za-z]/", $senha_plain) || !preg_match("/[0-9]/", $senha_plain)) {
        echo json_encode(["status" => "erro", "mensagem" => "Senha inválida. Mínimo 8 caracteres, com letras e números."]);
        exit;
    }

    // Validação de e-mail institucional
    if (strpos($email, '@etec.sp.gov.br') === false) {
        echo json_encode(["status" => "erro", "mensagem" => "E-mail inválido. Use e-mail institucional."]);
        exit;
    }

    // Hash da senha
    $senha = password_hash($senha_plain, PASSWORD_DEFAULT);

    // Preparar statement
    $stmt = $conexao->prepare("INSERT INTO alunos (nome, email, data_nascimento, senha) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $email, $data, $senha);

    if ($stmt->execute()) {
        echo json_encode(["status" => "sucesso", "mensagem" => "Cadastro realizado com sucesso."]);
    } else {
        echo json_encode(["status" => "erro", "mensagem" => "Erro ao cadastrar: " . $stmt->error]);
    }

    $stmt->close();
    $conexao->close();
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Requisição inválida."]);
}
?>