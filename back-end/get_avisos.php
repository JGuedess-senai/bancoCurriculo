<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php';

$result = $conexao->query("SELECT id, titulo, mensagem, criado_em FROM avisos ORDER BY criado_em DESC");
$avisos = [];
while ($row = $result->fetch_assoc()) {
    $avisos[] = $row;
}
echo json_encode($avisos, JSON_UNESCAPED_UNICODE);
$result->free();
$conexao->close();
?>