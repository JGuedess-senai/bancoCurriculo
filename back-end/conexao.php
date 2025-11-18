<?php
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "banco_curriculo";

$conexao = new mysqli($host, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    // Se for uma requisição JSON, retornar JSON
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro na conexão com o banco de dados: ' . $conexao->connect_error]);
        exit;
    }
    // Caso contrário, die normal
    die("Erro na conexão: " . $conexao->connect_error);
}
?>
