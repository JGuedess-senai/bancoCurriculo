<?php
include "conexao.php";
session_start();

// Evita que notices/warnings sejam impressos na resposta e poluam a saída do login
ini_set('display_errors', '0');
error_reporting(E_ALL);

$cnpj = $_POST['cnpj'] ?? '';
$senha = $_POST['senha'] ?? '';

// Verifica se a empresa existe no banco
$stmt = $conexao->prepare("SELECT * FROM empresas WHERE cnpj = ?");
$stmt->bind_param("s", $cnpj);
$stmt->execute();
$result = $stmt->get_result();

if ($empresa = $result->fetch_assoc()) {

    // 1️⃣ Verifica se está aprovado
    // Usa operador null-coalescing para evitar Notice quando coluna não existe
    $estado = $empresa['estado'] ?? ($empresa['status'] ?? null);
    if ($estado !== 'aprovado') {
        echo "Seu cadastro ainda está pendente de aprovação.";
        exit;
    }

    // 2️⃣ Verifica senha
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