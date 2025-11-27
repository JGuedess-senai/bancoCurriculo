<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

ob_start();
$__errors = [];
set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$__errors){
    $__errors[] = trim("$errstr in $errfile on line $errline");
    @file_put_contents(__DIR__ . '/error.log', date('c') . " - WARNING: $errstr in $errfile on line $errline" . PHP_EOL, FILE_APPEND);
    return true;
});

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) ob_end_clean();
        http_response_code(500);
        @file_put_contents(__DIR__ . '/error.log', date('c') . " - FATAL: {$err['message']} in {$err['file']} on line {$err['line']}" . PHP_EOL, FILE_APPEND);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro interno no servidor.']);
    }
});

include 'conexao.php';
$__buf = ob_get_clean();
if (!empty($__buf)) {
    @file_put_contents(__DIR__ . '/error.log', date('c') . ' - UNEXPECTED OUTPUT: ' . strip_tags($__buf) . PHP_EOL, FILE_APPEND);
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Output inesperado durante o processamento.']);
    exit;
}

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Requisição inválida.']);
    exit;
}

function san($v) {
    return trim(htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
}

$vaga_id = isset($_POST['vaga_id']) ? intval($_POST['vaga_id']) : 0;
if (!$vaga_id) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID da vaga não informado.']);
    exit;
}

$empresaId = isset($_SESSION['empresa']) ? intval($_SESSION['empresa']) : null;
if (!$empresaId && isset($_POST['empresa_id'])) {
    $empresaId = intval($_POST['empresa_id']);
}

// Buscar vaga e validar propriedade
$stmt = $conexao->prepare('SELECT empresa_id FROM vagas WHERE id = ? LIMIT 1');
if ($stmt === false) { echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao preparar: ' . $conexao->error]); exit; }
$stmt->bind_param('i', $vaga_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Vaga não encontrada.']);
    exit;
}
$row = $res->fetch_assoc();
$stmt->close();

if ($empresaId && intval($row['empresa_id']) !== $empresaId) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Você não tem permissão para editar esta vaga.']);
    exit;
}

$titulo = isset($_POST['titulo']) ? san($_POST['titulo']) : '';
$descricao = isset($_POST['descricao']) ? san($_POST['descricao']) : '';
$requisitos = isset($_POST['requisitos']) ? san($_POST['requisitos']) : '';
$carga_horaria = isset($_POST['carga_horaria']) ? san($_POST['carga_horaria']) : '';
$salario = isset($_POST['salario']) ? san($_POST['salario']) : '';
$local = isset($_POST['local']) ? san($_POST['local']) : '';
$tipo = isset($_POST['tipo']) ? san($_POST['tipo']) : '';
$data_expiracao = isset($_POST['data_expiracao']) ? san($_POST['data_expiracao']) : null;

// Atualiza todos os campos permitidos
$upd = $conexao->prepare('UPDATE vagas SET titulo = ?, descricao = ?, requisitos = ?, carga_horaria = ?, salario = ?, `local` = ?, tipo = ?, data_expiracao = ? WHERE id = ?');
if ($upd === false) { echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao preparar atualização: ' . $conexao->error]); exit; }
$upd->bind_param('ssssssssi', $titulo, $descricao, $requisitos, $carga_horaria, $salario, $local, $tipo, $data_expiracao, $vaga_id);

if ($upd->execute()) {
    echo json_encode(['status' => 'ok', 'mensagem' => 'Vaga atualizada com sucesso.']);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao atualizar: ' . $upd->error]);
}

$upd->close();
$conexao->close();

?>
