<?php
header('Content-Type: application/json');
include 'conexao.php';
session_start();

// Permitir apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Método não permitido']);
    exit;
}

// Obter empresa id da sessão (fallback para POST)
$empresa_id = isset($_SESSION['empresa']) ? intval($_SESSION['empresa']) : (isset($_POST['empresa_id']) ? intval($_POST['empresa_id']) : 0);
if (!$empresa_id) {
    http_response_code(401);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Empresa não autenticada']);
    exit;
}

// Coletar campos (limpeza básica)
$razao_social = $_POST['razao_social'] ?? null;
$nome_fantasia = $_POST['nome_fantasia'] ?? null;
$cnpj = $_POST['cnpj'] ?? null;
$descricao = $_POST['descricao'] ?? null;
$site = $_POST['site'] ?? null;
$telefone = $_POST['telefone'] ?? null;
$endereco = $_POST['endereco'] ?? null;
$cidade = $_POST['cidade'] ?? null;
$estado = $_POST['estado'] ?? null;
$setor = $_POST['setor'] ?? null;
$numero_funcionarios = $_POST['numero_funcionarios'] ?? null;
// Nome exibido (pode ser salvo na tabela empresas)
$nome = $_POST['nome'] ?? null;

// Tratar upload de logo, se houver
$logo_path = null;
if (!empty($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../img/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $tmpName = $_FILES['logo']['tmp_name'];
    $origName = basename($_FILES['logo']['name']);
    $ext = pathinfo($origName, PATHINFO_EXTENSION);
    $safeName = 'logo_empresa_' . $empresa_id . '_' . time() . '.' . $ext;
    $destPath = $uploadDir . '/' . $safeName;

    if (move_uploaded_file($tmpName, $destPath)) {
        // caminho relativo para o front-end (perfilEmpresa.html está em front-end/)
        $logo_path = '../img/uploads/' . $safeName;
    }
}

// Inserir ou atualizar usando ON DUPLICATE KEY UPDATE (chave única em empresa_id)
$sql = "INSERT INTO empresa_perfil (empresa_id, razao_social, nome_fantasia, cnpj, descricao, site, telefone, endereco, cidade, estado, setor, numero_funcionarios, logo_path)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            razao_social=VALUES(razao_social),
            nome_fantasia=VALUES(nome_fantasia),
            cnpj=VALUES(cnpj),
            descricao=VALUES(descricao),
            site=VALUES(site),
            telefone=VALUES(telefone),
            endereco=VALUES(endereco),
            cidade=VALUES(cidade),
            estado=VALUES(estado),
            setor=VALUES(setor),
            numero_funcionarios=VALUES(numero_funcionarios),
            logo_path=VALUES(logo_path)
        ";

$stmt = $conexao->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao preparar statement: ' . $conexao->error]);
    exit;
}

// Bind params
// tipos: i (empresa_id) + 12 strings
$razao_social = $razao_social ?: null;
$nome_fantasia = $nome_fantasia ?: null;
$cnpj = $cnpj ?: null;
$descricao = $descricao ?: null;
$site = $site ?: null;
$telefone = $telefone ?: null;
$endereco = $endereco ?: null;
$cidade = $cidade ?: null;
$estado = $estado ?: null;
$setor = $setor ?: null;
$numero_funcionarios = $numero_funcionarios ?: null;
$logo_path = $logo_path ?: null;

// Se veio nome, atualiza também a tabela empresas (nome principal)
if ($nome) {
    $upd = $conexao->prepare("UPDATE empresas SET nome = ? WHERE id = ?");
    if ($upd) {
        $upd->bind_param('si', $nome, $empresa_id);
        $upd->execute();
        $upd->close();
    }
}

$stmt->bind_param('issssssssssss', $empresa_id, $razao_social, $nome_fantasia, $cnpj, $descricao, $site, $telefone, $endereco, $cidade, $estado, $setor, $numero_funcionarios, $logo_path);

if ($stmt->execute()) {
    echo json_encode(['status' => 'sucesso', 'mensagem' => 'Perfil atualizado com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao salvar perfil: ' . $stmt->error]);
}

$stmt->close();
$conexao->close();
