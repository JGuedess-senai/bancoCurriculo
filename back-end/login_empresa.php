<?php
include "conexao.php";
session_start();

$cnpj = $_POST['cnpj'] ?? '';
$senha = $_POST['senha'] ?? '';

// Verifica se a empresa existe no banco
$stmt = $conexao->prepare("SELECT * FROM empresas WHERE cnpj = ?");
$stmt->bind_param("s", $cnpj);
$stmt->execute();
$result = $stmt->get_result();

if ($empresa = $result->fetch_assoc()) {
    if (password_verify($senha, $empresa['senha'])) {
        $_SESSION['empresa'] = $empresa['id'];
        echo "OK";
    } else {
        echo "Senha incorreta.";
    }
} else {
    echo "CNPJ não encontrado.";
}

$stmt->close();
$conexao->close();
?>