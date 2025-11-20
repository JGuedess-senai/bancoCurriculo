<?php
// Garante que apenas JSON será retornado, mesmo em caso de erro
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php';

$titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$mensagem = isset($_POST['mensagem']) ? trim($_POST['mensagem']) : '';
if (empty($titulo) && empty($mensagem)) {
    echo json_encode(['success' => false, 'message' => 'Preencha título ou mensagem.']);
    exit;
}

try {
    $stmt = $conexao->prepare("INSERT INTO avisos (titulo, mensagem) VALUES (?, ?)");
    if (!$stmt) throw new Exception($conexao->error);
    $stmt->bind_param('ss', $titulo, $mensagem);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Aviso publicado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao publicar aviso: ' . $stmt->error]);
    }
    $stmt->close();
    $conexao->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}
?>