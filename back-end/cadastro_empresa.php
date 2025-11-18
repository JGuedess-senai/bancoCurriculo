<?php
include "conexao.php";
header('Content-Type: application/json');
// Evita que warnings/fatals sejam enviados como HTML (quebram o JSON de resposta)
ini_set('display_errors', '0');
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'] ?? '';
    $cnpj = $_POST['cnpj'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha_plain = $_POST['senha'] ?? '';

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

    $senha = password_hash($senha_plain, PASSWORD_DEFAULT);

    // Tenta preparar o INSERT usando primeiro a coluna 'estado', se falhar tenta 'status'
    $lastError = '';
    $stmt = false;
    foreach (['estado', 'status'] as $col) {
        $sql = "INSERT INTO empresas (nome, cnpj, email, senha, $col) VALUES (?, ?, ?, ?, 'pendente')";
        $stmt = $conexao->prepare($sql);
        if ($stmt !== false) {
            // encontrou um SQL compatível
            break;
        }
        $lastError = $conexao->error;
    }

    if ($stmt === false) {
        // Retornar erro em JSON para o cliente (evita chamadas em métodos de boolean)
        echo json_encode(["status" => "erro", "mensagem" => "Erro no prepare: " . $lastError]);
        $conexao->close();
        exit;
    }

    $bindOk = $stmt->bind_param("ssss", $nome, $cnpj, $email, $senha);
    if ($bindOk === false) {
        echo json_encode(["status" => "erro", "mensagem" => "Erro no bind_param: " . $stmt->error]);
        $stmt->close();
        $conexao->close();
        exit;
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "sucesso", "mensagem" => "Cadastro da empresa realizado com sucesso."]);
    } else {
        echo json_encode(["status" => "erro", "mensagem" => "Erro ao cadastrar: " . $stmt->error]);
    }

    $stmt->close();
    $conexao->close();
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Requisição inválida."]);
}
?>