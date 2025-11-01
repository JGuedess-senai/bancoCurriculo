<?php
header('Content-Type: application/json');
require_once 'conexao.php';

try {
    // Estatísticas de empresas
    $sqlEmpresas = "SELECT COUNT(*) as total FROM empresas WHERE status = 'ativo'";
    $stmtEmpresas = $conn->prepare($sqlEmpresas);
    $stmtEmpresas->execute();
    $totalEmpresas = $stmtEmpresas->fetch(PDO::FETCH_ASSOC)['total'];

    // Estatísticas de alunos
    $sqlAlunos = "SELECT COUNT(*) as total FROM alunos WHERE status = 'ativo'";
    $stmtAlunos = $conn->prepare($sqlAlunos);
    $stmtAlunos->execute();
    $totalAlunos = $stmtAlunos->fetch(PDO::FETCH_ASSOC)['total'];

    // Vagas abertas
    $sqlVagas = "SELECT COUNT(*) as total FROM vagas WHERE status = 'aberta'";
    $stmtVagas = $conn->prepare($sqlVagas);
    $stmtVagas->execute();
    $vagasAbertas = $stmtVagas->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de contratações
    $sqlContratacoes = "SELECT COUNT(*) as total FROM candidaturas WHERE status = 'contratado'";
    $stmtContratacoes = $conn->prepare($sqlContratacoes);
    $stmtContratacoes->execute();
    $totalContratacoes = $stmtContratacoes->fetch(PDO::FETCH_ASSOC)['total'];

    // Retornar estatísticas em formato JSON
    echo json_encode([
        'success' => true,
        'totalEmpresas' => $totalEmpresas,
        'totalAlunos' => $totalAlunos,
        'vagasAbertas' => $vagasAbertas,
        'totalContratacoes' => $totalContratacoes
    ]);

} catch(PDOException $e) {
    // Em caso de erro, retornar mensagem de erro
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar estatísticas: ' . $e->getMessage()
    ]);
}

$conn = null;
?>