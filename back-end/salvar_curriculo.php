<?php
include("conexao.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Função simples para sanitizar texto
    function san($v) {
        return trim(htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }

    // Recebe e sanitiza os dados do formulário
    $nome = isset($_POST['nome']) ? san($_POST['nome']) : '';
    $idade = isset($_POST['idade']) ? (int) $_POST['idade'] : null;
    $telefone = isset($_POST['telefone']) ? san($_POST['telefone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $curso = isset($_POST['curso']) ? san($_POST['curso']) : '';
    $periodo = isset($_POST['periodo']) ? san($_POST['periodo']) : '';
    $ano_ingresso = isset($_POST['ano_ingresso']) ? (int) $_POST['ano_ingresso'] : null;
    $turno = isset($_POST['turno']) ? san($_POST['turno']) : '';
    $objetivo = isset($_POST['objetivo']) ? san($_POST['objetivo']) : '';
    $habilidades = isset($_POST['habilidades']) ? san($_POST['habilidades']) : '';
    $experiencia = isset($_POST['experiencia']) ? san($_POST['experiencia']) : '';
    $cursos = isset($_POST['cursos']) ? san($_POST['cursos']) : '';

    // Validações básicas
    if (empty($nome)) {
        echo "<script>alert('O campo Nome é obrigatório.'); window.history.back();</script>";
        exit;
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Email inválido.'); window.history.back();</script>";
        exit;
    }

    // Upload do arquivo (currículo) - somente PDF até 5MB
    $nomeArquivo = "";
    if (isset($_FILES['curriculo']) && $_FILES['curriculo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['curriculo'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo "<script>alert('Erro no upload do arquivo. Código: " . $file['error'] . "'); window.history.back();</script>";
            exit;
        }

        // Limite de 5 MB
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            echo "<script>alert('Arquivo muito grande. Máximo permitido: 5 MB.'); window.history.back();</script>";
            exit;
        }

        // Verifica MIME via finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['application/pdf'];
        if (!in_array($mime, $allowed, true)) {
            echo "<script>alert('Formato inválido. Envie apenas arquivos PDF.'); window.history.back();</script>";
            exit;
        }

        // Cria pasta de uploads (caminho absoluto)
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Gera nome seguro e único
        $ext = '.pdf';
        $novoNome = time() . '_' . bin2hex(random_bytes(8)) . $ext;
        $destino = $uploadDir . $novoNome;

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            echo "<script>alert('Falha ao salvar o arquivo no servidor.'); window.history.back();</script>";
            exit;
        }

        // Armazenar apenas o nome do arquivo no banco
        $nomeArquivo = $novoNome;
    }

    // Determine o email do aluno autenticado (se disponível na sessão)
    $emailAluno = '';
    if (isset($_SESSION['aluno'])) {
        $alunoId = intval($_SESSION['aluno']);
        $s = $conexao->prepare("SELECT email FROM alunos WHERE id = ? LIMIT 1");
        $s->bind_param("i", $alunoId);
        $s->execute();
        $res = $s->get_result();
        if ($r = $res->fetch_assoc()) {
            $emailAluno = $r['email'];
        }
        $s->close();
    }

    // Se não há email do aluno na sessão, usamos o email enviado no formulário (fallback)
    $emailParaSalvar = !empty($emailAluno) ? $emailAluno : $email;

    // Verificar se já existe um currículo para esse email (fazer update)
    $check = $conexao->prepare("SELECT id FROM curriculos WHERE email = ? ORDER BY data_envio DESC LIMIT 1");
    $check->bind_param("s", $emailParaSalvar);
    $check->execute();
    $resCheck = $check->get_result();

    $params = [
        'nome' => $nome,
        'idade' => $idade,
        'telefone' => $telefone,
        'email' => $emailParaSalvar,
        'curso' => $curso,
        'periodo' => $periodo,
        'ano_ingresso' => $ano_ingresso,
        'turno' => $turno,
        'objetivo' => $objetivo,
        'habilidades' => $habilidades,
        'experiencia' => $experiencia,
        'cursos' => $cursos,
        'curriculo' => $nomeArquivo
    ];

    if ($rowCheck = $resCheck->fetch_assoc()) {
        // Atualizar o registro existente
        $idExistente = intval($rowCheck['id']);
        $sql = "UPDATE curriculos SET nome = ?, idade = ?, telefone = ?, email = ?, curso = ?, periodo = ?, ano_ingresso = ?, turno = ?, objetivo = ?, habilidades = ?, experiencia = ?, cursos = ?, curriculo = ? WHERE id = ?";
        $stmt = $conexao->prepare($sql);
        if ($stmt === false) {
            die('Erro ao preparar statement: ' . $conexao->error);
        }
        $types = 'sissssissssssi';
        $stmt->bind_param(
            $types,
            $params['nome'],
            $params['idade'],
            $params['telefone'],
            $params['email'],
            $params['curso'],
            $params['periodo'],
            $params['ano_ingresso'],
            $params['turno'],
            $params['objetivo'],
            $params['habilidades'],
            $params['experiencia'],
            $params['cursos'],
            $params['curriculo'],
            $idExistente
        );

    } else {
        // Inserir novo registro
        $sql = "INSERT INTO curriculos (nome, idade, telefone, email, curso, periodo, ano_ingresso, turno, objetivo, habilidades, experiencia, cursos, curriculo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        if ($stmt === false) {
            die('Erro ao preparar statement: ' . $conexao->error);
        }
        $types = 'sissssissssss';
        $stmt->bind_param(
            $types,
            $params['nome'],
            $params['idade'],
            $params['telefone'],
            $params['email'],
            $params['curso'],
            $params['periodo'],
            $params['ano_ingresso'],
            $params['turno'],
            $params['objetivo'],
            $params['habilidades'],
            $params['experiencia'],
            $params['cursos'],
            $params['curriculo']
        );
    }

    if ($stmt->execute()) {
        echo "<script>alert('Currículo salvo com sucesso!'); window.location.href='../front-end/verCurriculos.html';</script>";
    } else {
        echo "Erro ao salvar: " . $stmt->error;
    }

    $stmt->close();
    $check->close();
    $conexao->close();
}
?>
