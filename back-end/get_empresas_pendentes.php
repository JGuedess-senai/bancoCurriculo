<?php
header('Content-Type: application/json');
require_once 'conexao.php';

try {
    // Buscar empresas pendentes de aprovação
    $sql = "SELECT id, nome_empresa, cnpj, email, data_cadastro 
            FROM empresas 
            WHERE status = 'pendente' 
            ORDER BY data_cadastro ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $empresasPendentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatar as datas para padrão brasileiro
    foreach ($empresasPendentes as &$empresa) {
        $data = new DateTime($empresa['data_cadastro']);
        $empresa['data_cadastro'] = $data->format('d/m/Y H:i:s');
    }

    // Retornar lista de empresas pendentes
    echo json_encode([
        'success' => true,
        'empresas' => $empresasPendentes
    ]);

} catch(PDOException $e) {
    // Em caso de erro, retornar mensagem de erro
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar empresas pendentes: ' . $e->getMessage()
    ]);
}

$conn = null;
?>