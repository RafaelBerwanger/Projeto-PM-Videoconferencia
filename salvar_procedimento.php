<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

// Captura dos dados enviados por formulário ou JSON
$tipo             = trim($_POST['tipo'] ?? '');
$numero           = trim($_POST['numero'] ?? '');
$portaria         = trim($_POST['portaria'] ?? '');
$data_instauracao = trim($_POST['data_instauracao'] ?? '');
$prazo_dias       = intval($_POST['prazo_dias'] ?? 10);
$sintese          = trim($_POST['sintese'] ?? '');

if (empty($tipo) || empty($numero) || empty($data_instauracao) || $prazo_dias <= 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'Por favor, preencha todos os campos obrigatórios.']);
    exit;
}

try {
    // Calcula a data de vencimento (Soma dos dias à data de instauração)
    $dateInst = new DateTime($data_instauracao);
    $dateInst->modify("+{$prazo_dias} days");
    $data_vencimento = $dateInst->format('Y-m-d');

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

    $id_procedimento = $pdo->lastInsertId();

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

    echo json_encode(['sucesso' => true, 'id_procedimento' => $id_procedimento]);

} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao gravar no banco de dados: ' . $e->getMessage()]);
}
?>