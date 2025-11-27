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

$vaga_id = isset($_POST['vaga_id']) ? intval($_POST['vaga_id']) : 0;
if (!$vaga_id) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID da vaga não informado.']);
    exit;
}

$empresaId = isset($_SESSION['empresa']) ? intval($_SESSION['empresa']) : null;
if (!$empresaId && isset($_POST['empresa_id'])) {
    $empresaId = intval($_POST['empresa_id']);
}

// Verificar propriedade da vaga
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
    echo json_encode(['status' => 'erro', 'mensagem' => 'Você não tem permissão para excluir esta vaga.']);
    exit;
}

// Excluir vaga (cascata cuidará das candidaturas)
$del = $conexao->prepare('DELETE FROM vagas WHERE id = ?');
if ($del === false) { echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao preparar exclusão: ' . $conexao->error]); exit; }
$del->bind_param('i', $vaga_id);

if ($del->execute()) {
    if ($del->affected_rows > 0) {
        echo json_encode(['status' => 'ok', 'mensagem' => 'Vaga excluída com sucesso.']);
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Nenhuma vaga foi excluída.']);
    }
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao excluir: ' . $del->error]);
}

$del->close();
$conexao->close();

?>
