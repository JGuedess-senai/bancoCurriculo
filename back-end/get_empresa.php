<?php
header('Content-Type: application/json');
include 'conexao.php';
session_start();

// Retorna dados da empresa autenticada ou dados fictícios para testes
$empresaId = isset($_SESSION['empresa']) ? intval($_SESSION['empresa']) : (isset($_GET['id']) ? intval($_GET['id']) : 1);

if (!$empresaId) {
    http_response_code(401);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Empresa não autenticada.']);
    exit;
}

// Buscar empresa e perfil no banco
$stmt = $conexao->prepare("SELECT e.id, e.nome, e.email, p.razao_social, p.nome_fantasia, p.cnpj AS perfil_cnpj, p.descricao, p.site, p.telefone, p.endereco, p.cidade, p.estado, p.setor, p.numero_funcionarios, p.logo_path
    FROM empresas e
    LEFT JOIN empresa_perfil p ON p.empresa_id = e.id
    WHERE e.id = ?");
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao preparar statement.']);
    exit;
}

$stmt->bind_param('i', $empresaId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $empresa = $result->fetch_assoc();
    // Normaliza nomes de campos para o front-end
    $response = [
        'status' => 'sucesso',
        'id' => $empresa['id'],
        'nome' => $empresa['nome'],
        'email' => $empresa['email'],
        'razao_social' => $empresa['razao_social'] ?? null,
        'nome_fantasia' => $empresa['nome_fantasia'] ?? null,
        'cnpj' => $empresa['perfil_cnpj'] ?? null,
        'descricao' => $empresa['descricao'] ?? null,
        'site' => $empresa['site'] ?? null,
        'telefone' => $empresa['telefone'] ?? null,
        'endereco' => $empresa['endereco'] ?? null,
        'cidade' => $empresa['cidade'] ?? null,
        'estado' => $empresa['estado'] ?? null,
        'setor' => $empresa['setor'] ?? null,
        'numero_funcionarios' => $empresa['numero_funcionarios'] ?? null,
        'logo_path' => $empresa['logo_path'] ?? null
    ];

    echo json_encode($response);
} else {
    http_response_code(404);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Empresa não encontrada.']);
}

$stmt->close();
$conexao->close();
?>
