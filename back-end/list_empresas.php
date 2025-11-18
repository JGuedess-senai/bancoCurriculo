<?php
header('Content-Type: application/json');
include 'conexao.php';

try {
    // Seleciona explicitamente as colunas relevantes e filtra por estado = 'pendente'
    $sql = "SELECT id, nome AS nome, email, estado FROM empresas WHERE estado = 'pendente' ORDER BY id ASC";
    $result = $conexao->query($sql);
    if (!$result) {
        throw new Exception('Erro na consulta: ' . $conexao->error);
    }

    $empresas = [];
    while ($row = $result->fetch_assoc()) {
        $empresas[] = [
            'id' => $row['id'] ?? null,
            'nome' => $row['nome'] ?? '',
            'email' => $row['email'] ?? '',
            'estado' => strtolower(trim($row['estado'] ?? ''))
        ];
    }

    echo json_encode($empresas);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}

$conexao->close();
?>
