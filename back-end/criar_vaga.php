<?php
// Garantir que a API sempre retorne JSON (evitar páginas HTML em caso de warning/erro)
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Captura qualquer saída inesperada (warnings, echoes) durante includes
ob_start();
$__errors = [];
set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$__errors){
    $__errors[] = trim("$errstr in $errfile on line $errline");
    // Registrar para diagnóstico
    @file_put_contents(__DIR__ . '/error.log', date('c') . " - WARNING: $errstr in $errfile on line $errline" . PHP_EOL, FILE_APPEND);
    return true; // evita o handler interno do PHP
});

// Captura erros fatais no shutdown e garante resposta JSON
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Limpa qualquer buffer e retorna JSON de erro
        while (ob_get_level()) {
            ob_end_clean();
        }
        http_response_code(500);
        $msg = isset($err['message']) ? $err['message'] : 'Erro fatal';
        @file_put_contents(__DIR__ . '/error.log', date('c') . " - FATAL: $msg in {$err['file']} on line {$err['line']}" . PHP_EOL, FILE_APPEND);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro interno no servidor.', 'detalhe' => substr($msg, 0, 300)]);
    }
});

include 'conexao.php';
// Limpa buffer e capture output do include
$__buf = ob_get_clean();
if (!empty($__buf)) {
    // Retornar como JSON (removendo tags HTML se houver)
    $msg = strip_tags($__buf);
    @file_put_contents(__DIR__ . '/error.log', date('c') . ' - UNEXPECTED OUTPUT: ' . $msg . PHP_EOL, FILE_APPEND);
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Output inesperado durante o processamento.', 'detalhe' => substr($msg, 0, 300)]);
    exit;
}

session_start();

// Apenas empresas autenticadas podem criar vagas
$empresaId = isset($_SESSION['empresa']) ? intval($_SESSION['empresa']) : null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Requisição inválida.']);
    exit;
}

// Permitir criação mesmo sem sessão para facilitar testes locais (comentar se desejar forçar login)
// if (!$empresaId) {
//     echo json_encode(['status' => 'erro', 'mensagem' => 'Empresa não autenticada.']);
//     exit;
// }

// Função simples de sanitização
function san($v) {
    return trim(htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
}

$titulo = isset($_POST['titulo']) ? san($_POST['titulo']) : '';
$descricao = isset($_POST['descricao']) ? san($_POST['descricao']) : '';
$requisitos = isset($_POST['requisitos']) ? san($_POST['requisitos']) : '';
$carga_horaria = isset($_POST['carga_horaria']) ? san($_POST['carga_horaria']) : '';
$salario = isset($_POST['salario']) ? san($_POST['salario']) : '';
$local = isset($_POST['local']) ? san($_POST['local']) : '';
$tipo = isset($_POST['tipo']) ? san($_POST['tipo']) : '';
$data_expiracao = isset($_POST['data_expiracao']) ? san($_POST['data_expiracao']) : '';

// Validações básicas
if (empty($titulo) || empty($descricao)) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Título e descrição são obrigatórios.']);
    exit;
}

// Se empresa não autenticada, aceitar empresa_id enviado (útil para testes)
if (!$empresaId) {
    $empresaId = isset($_POST['empresa_id']) ? intval($_POST['empresa_id']) : 1;
}

$stmt = $conexao->prepare("INSERT INTO vagas (empresa_id, titulo, descricao, requisitos, carga_horaria, salario, local, tipo, data_expiracao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
if ($stmt === false) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao preparar: ' . $conexao->error]);
    exit;
}

$stmt->bind_param('issssssss', $empresaId, $titulo, $descricao, $requisitos, $carga_horaria, $salario, $local, $tipo, $data_expiracao);

if ($stmt->execute()) {
    echo json_encode(['status' => 'sucesso', 'mensagem' => 'Vaga criada com sucesso!']);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro: ' . $stmt->error]);
}

$stmt->close();
$conexao->close();
?>