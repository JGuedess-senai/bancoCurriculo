<?php
header('Content-Type: application/json');
session_start();
require_once 'conexao.php';

// Verificar se o administrador está logado
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Não autorizado'
    ]);
    exit;
}

// Verificar se os campos necessários foram enviados
if (!isset($_POST['senha_atual']) || !isset($_POST['nova_senha'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Dados incompletos'
    ]);
    exit;
}

try {
    // Buscar senha atual do administrador
    $sql = "SELECT senha FROM administradores WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar se a senha atual está correta
    if (!password_verify($_POST['senha_atual'], $admin['senha'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Senha atual incorreta'
        ]);
        exit;
    }

    // Atualizar a senha
    $novaSenhaHash = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);
    $sql = "UPDATE administradores SET senha = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$novaSenhaHash, $_SESSION['admin_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Senha atualizada com sucesso'
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar senha: ' . $e->getMessage()
    ]);
}

$conn = null;
?>