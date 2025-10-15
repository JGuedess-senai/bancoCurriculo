<?php
include "conexao.php";
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['aluno'])) {
    http_response_code(401);
    echo json_encode(["erro" => "Não autenticado"]);
    exit;
}

$alunoId = intval($_SESSION['aluno']);

$stmt = $conexao->prepare("SELECT nome, email FROM alunos WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $alunoId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(["nome" => $row['nome'], "email" => $row['email']]);
} else {
    http_response_code(404);
    echo json_encode(["erro" => "Aluno não encontrado"]);
}

$stmt->close();
$conexao->close();

?>