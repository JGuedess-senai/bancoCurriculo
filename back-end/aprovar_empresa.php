<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$stmt = $conexao->prepare("UPDATE empresas SET status = 'aprovado' WHERE id = ?");
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Empresa aprovada com sucesso!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao aprovar empresa: ' . $stmt->error]);
}
$stmt->close();
$conexao->close();
?>