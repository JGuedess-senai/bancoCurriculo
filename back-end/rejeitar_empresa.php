<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$stmt = $conexao->prepare("DELETE FROM empresas WHERE id = ?");
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Empresa rejeitada e excluída!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao rejeitar empresa: ' . $stmt->error]);
}
$stmt->close();
$conexao->close();
?>