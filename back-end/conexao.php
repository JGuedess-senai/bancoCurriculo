<?php
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "banco_curriculo";

$conexao = new mysqli($host, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    // Sempre retornar JSON em caso de erro de conexão (API-friendly)
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    $msg = 'Erro na conexão com o banco de dados: ' . $conexao->connect_error;
    // Log do erro em arquivo para diagnóstico (não expor demais ao cliente)
    @file_put_contents(__DIR__ . '/error.log', date('c') . ' - DB CONNECT ERROR: ' . $msg . PHP_EOL, FILE_APPEND);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro na conexão com o banco de dados.']);
    exit;
}
?>
