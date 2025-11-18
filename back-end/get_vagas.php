<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

include 'conexao.php';

$result = $conexao->query("SELECT v.*, e.nome AS empresa_nome FROM vagas v LEFT JOIN empresas e ON v.empresa_id = e.id WHERE v.ativo = 1 ORDER BY v.data_criacao DESC");

if ($result === false) {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro na consulta: ' . $conexao->error]);
    exit;
}

$rows = [];
while ($row = $result->fetch_assoc()) {
    // manter todas as colunas retornadas (v.*) e enviar ao frontend
    $rows[] = $row;
}

echo json_encode($rows, JSON_UNESCAPED_UNICODE);

$result->free();
$conexao->close();
?>
