<?php
// Endpoint para remover um aviso pelo id (retorna JSON)
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido.']);
    exit;
}

try {
    $stmt = $conexao->prepare("DELETE FROM avisos WHERE id = ?");
    if (!$stmt) throw new Exception($conexao->error);
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Aviso removido com sucesso.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Aviso não encontrado.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao remover aviso: ' . $stmt->error]);
    }
    $stmt->close();
    $conexao->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}

?>
