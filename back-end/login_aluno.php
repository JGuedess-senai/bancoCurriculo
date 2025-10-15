<?php
include "conexao.php";
session_start();

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

// Verifica se o Aluno existe no banco
$stmt = $conexao->prepare("SELECT * FROM alunos WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if (password_verify($senha, $user['senha'])) {
        $_SESSION['aluno'] = $user['id'];
        echo "OK";
    } else {
        echo "Senha incorreta.";
    }
} else {
    echo "E-mail não encontrado.";
}

$stmt->close();
$conexao->close();
?>