<?php
header('Content-Type: application/json');
include 'conexao.php';

// Opcional: filtro por empresa_id via GET
$empresaId = isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : null;

try {
    if ($empresaId) {
        $stmt = $conexao->prepare("SELECT v.id, v.titulo, v.descricao, v.requisitos, v.salario, v.local, v.tipo, v.data_criacao, v.ativo, e.nome as empresa_nome FROM vagas v JOIN empresas e ON v.empresa_id = e.id WHERE v.empresa_id = ? ORDER BY v.data_criacao DESC");
        $stmt->bind_param('i', $empresaId);
    } else {
        $stmt = $conexao->prepare("SELECT v.id, v.titulo, v.descricao, v.requisitos, v.salario, v.local, v.tipo, v.data_criacao, v.ativo, e.nome as empresa_nome FROM vagas v JOIN empresas e ON v.empresa_id = e.id ORDER BY v.data_criacao DESC");
    }

    $stmt->execute();
    $res = $stmt->get_result();

    $vagas = [];
    while ($row = $res->fetch_assoc()) {
        $vagas[] = $row;
    }

    echo json_encode(['status' => 'sucesso', 'vagas' => $vagas]);

    $stmt->close();
    $conexao->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}

?>