<?php
include "conexao.php";
header('Content-Type: application/json; charset=utf-8');

$result = $conexao->query("SELECT * FROM curriculos ORDER BY data_envio DESC");
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
echo json_encode($rows, JSON_UNESCAPED_UNICODE);
$result->free();
$conexao->close();
?>
