<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Inclui o arquivo de configuração
require_once __DIR__ . '/config.php';

// Trava de segurança: apenas usuários logados podem usar
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

// Recebe o texto do POST
$input = json_decode(file_get_contents('php://input'), true);
$textoOriginal = trim($input['texto'] ?? '');

if (empty($textoOriginal)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Nenhum texto informado para correção.']);
    exit;
}

// Obtém a chave definida no config.php
$apiKey = GROQ_API_KEY;

$promptSystem = "Você é um assistente jurídico especialista na redação de termos de oitiva e depoimentos policiais da Polícia Militar do Paraná (PMPR). 
Sua tarefa é REFINAR e CORRIGIR o texto a seguir.
REGRAS OBRIGATÓRIAS:
1. Corrija a pontuação (vírgulas, pontos), erros ortográficos, concordância verbal/nominal e erros de transcrição de voz.
2. NUNCA altere o sentido dos fatos narrados nem adicione informações que não foram ditas.
3. Mantenha a linguagem formal e adequada a um documento oficial de instrução provisória.
4. Retorne APENAS e TÃO SOMENTE o texto corrigido, sem saudações, observações, aspas ou introduções.";

// -------------------------------------------------------------------
// PASSO 1: CONSULTA EM TEMPO REAL OS MODELOS ATIVOS NA SUA CONTA GROQ
// -------------------------------------------------------------------
$chModels = curl_init('https://api.groq.com/openai/v1/models');
curl_setopt($chModels, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chModels, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($chModels, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($chModels, CURLOPT_SSL_VERIFYHOST, false);

$resModels = curl_exec($chModels);
$httpCodeModels = curl_getinfo($chModels, CURLINFO_HTTP_CODE);
curl_close($chModels);

$modelosValidos = [];

if ($httpCodeModels === 200) {
    $dataModels = json_decode($resModels, true);
    if (!empty($dataModels['data'])) {
        foreach ($dataModels['data'] as $m) {
            $id = $m['id'] ?? '';
            $idLower = strtolower($id);

            // Filtra exclusivamente modelos válidos para chat/texto
            // Ignora modelos de moderação/segurança, áudio e de imagem
            if (
                !str_contains($idLower, 'guard') &&
                !str_contains($idLower, 'whisper') &&
                !str_contains($idLower, 'vision') &&
                !str_contains($idLower, 'safeguard')
            ) {
                $modelosValidos[] = $id;
            }
        }
    }
}

// Se por algum motivo a busca automática falhar, utiliza os nomes atuais
if (empty($modelosValidos)) {
    $modelosValidos = ['llama-3.3-70b-versatile', 'llama-3.1-8b-instant'];
}

// -------------------------------------------------------------------
// PASSO 2: TESTA OS MODELOS ENCONTRADOS ATÉ OBTER SUCESSO
// -------------------------------------------------------------------
$sucesso = false;
$textoRefinado = '';
$ultimoErro = '';

foreach ($modelosValidos as $modelo) {
    $data = [
        'model' => $modelo,
        'messages' => [
            ['role' => 'system', 'content' => $promptSystem],
            ['role' => 'user', 'content' => $textoOriginal]
        ],
        'temperature' => 0.2
    ];

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $responseData = json_decode($response, true);
        if (isset($responseData['choices'][0]['message']['content'])) {
            $textoRefinado = trim($responseData['choices'][0]['message']['content']);
            $sucesso = true;
            break; // Requisição bem-sucedida
        }
    } else {
        $ultimoErro = "Modelo {$modelo} (HTTP {$httpCode}): " . $response;
    }
}

// -------------------------------------------------------------------
// RETORNO DA RESPOSTA
// -------------------------------------------------------------------
if ($sucesso) {
    echo json_encode(['sucesso' => true, 'texto' => $textoRefinado]);
} else {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Falha ao comunicar com a Groq. Detalhes: ' . $ultimoErro
    ]);
}
