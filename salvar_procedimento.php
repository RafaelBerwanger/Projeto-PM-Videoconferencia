<?php
require_once 'sessao.php';

header('Content-Type: application/json; charset=utf-8');

$user       = exigirLogin(true);
exigirModuloProcedimentos(true);
$id_usuario = (int) $user['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
    echo json_encode(['sucesso' => false, 'erro' => 'Requisição inválida. Recarregue a página e tente novamente.']);
    exit;
}

const TIPOS_PROCEDIMENTO = ['FATD', 'SINDICÂNCIA', 'IPM', 'CD', 'CJ', 'IT', 'APFD', 'ADL'];

// Captura dos dados enviados pelo formulário
$tipo             = trim($_POST['tipo'] ?? '');
$numero           = trim($_POST['numero'] ?? '');
$portaria         = trim($_POST['portaria'] ?? '');
$data_instauracao = trim($_POST['data_instauracao'] ?? '');
$prazo_dias       = (int) ($_POST['prazo_dias'] ?? 10);
$sintese          = trim($_POST['sintese'] ?? '');

if ($tipo === '' || $numero === '' || $data_instauracao === '' || $prazo_dias <= 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'Por favor, preencha todos os campos obrigatórios.']);
    exit;
}

if (!in_array($tipo, TIPOS_PROCEDIMENTO, true)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Tipo de procedimento inválido.']);
    exit;
}

// Aceita apenas datas reais no formato AAAA-MM-DD
$dateInst = DateTime::createFromFormat('!Y-m-d', $data_instauracao);
if (!$dateInst || $dateInst->format('Y-m-d') !== $data_instauracao) {
    echo json_encode(['sucesso' => false, 'erro' => 'Data de instauração inválida.']);
    exit;
}

if ($prazo_dias > 3650) {
    echo json_encode(['sucesso' => false, 'erro' => 'Prazo em dias inválido.']);
    exit;
}

try {
    // Calcula a data de vencimento (Soma dos dias à data de instauração)
    $data_vencimento = (clone $dateInst)->modify("+{$prazo_dias} days")->format('Y-m-d');

    // Procedimento e primeiro ato são gravados juntos (ou nenhum dos dois)
    $pdo->beginTransaction();

    $sql = "INSERT INTO procedimentos
            (id_usuario, tipo, numero, portaria, data_instauracao, prazo_dias, data_vencimento, status, sintese)
            VALUES
            (:id_usuario, :tipo, :numero, :portaria, :data_instauracao, :prazo_dias, :data_vencimento, 'Em Andamento', :sintese)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_usuario'       => $id_usuario,
        ':tipo'             => $tipo,
        ':numero'           => $numero,
        ':portaria'         => $portaria,
        ':data_instauracao' => $data_instauracao,
        ':prazo_dias'       => $prazo_dias,
        ':data_vencimento'  => $data_vencimento,
        ':sintese'          => $sintese
    ]);

    $id_procedimento = (int) $pdo->lastInsertId();

    // Registra o primeiro ato na timeline: Instauração do Feito
    $sqlAto = "INSERT INTO procedimento_atos (id_procedimento, id_usuario, titulo, descricao, tipo_ato, data_ato)
               VALUES (:id_proc, :id_user, :titulo, :descr, 'Instauração', :data_ato)";
    $stmtAto = $pdo->prepare($sqlAto);
    $stmtAto->execute([
        ':id_proc'  => $id_procedimento,
        ':id_user'  => $id_usuario,
        ':titulo'   => "Instauração do Feito ({$tipo} nº {$numero})",
        ':descr'    => $portaria ? "Portaria/Documento de Origem: {$portaria}" : "Procedimento cadastrado na Intranet PMPR.",
        ':data_ato' => $data_instauracao
    ]);

    $pdo->commit();

    echo json_encode(['sucesso' => true, 'id_procedimento' => $id_procedimento]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro ao salvar procedimento: ' . $e->getMessage());
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao gravar no banco de dados. Tente novamente.']);
}
