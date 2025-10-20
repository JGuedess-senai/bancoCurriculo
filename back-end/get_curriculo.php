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

$stmt = $conexao->prepare("SELECT email FROM alunos WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $alunoId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $email = $row['email'];
} else {
    http_response_code(404);
    echo json_encode(["erro" => "Aluno não encontrado"]);
    exit;
}

$stmt = $conexao->prepare("SELECT nome, idade, telefone, email, curso, periodo, ano_ingresso, turno, objetivo, habilidades, experiencia, cursos, curriculo FROM curriculos WHERE email = ? ORDER BY data_envio DESC LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(["erro" => "Currículo não encontrado"]);
}

$stmt->close();
$conexao->close();

?>
