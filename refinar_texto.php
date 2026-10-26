<?php
require_once 'sessao.php'; // inclui db.php, que carrega o config.php (GROQ_API_KEY)
header('Content-Type: application/json; charset=utf-8');

// Trava de segurança: apenas usuários logados podem usar
exigirLogin(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
    echo json_encode(['sucesso' => false, 'erro' => 'Requisição inválida. Recarregue a página e tente novamente.']);
    exit;
}

if (!defined('GROQ_API_KEY') || GROQ_API_KEY === '') {
    echo json_encode(['sucesso' => false, 'erro' => 'Chave da API de IA não configurada no servidor (config.php).']);
    exit;
}

// Recebe o texto do POST
$input = json_decode(file_get_contents('php://input'), true);
$textoOriginal = trim((string) ($input['texto'] ?? ''));

if ($textoOriginal === '') {
    echo json_encode(['sucesso' => false, 'erro' => 'Nenhum texto informado para correção.']);
    exit;
}

if (mb_strlen($textoOriginal) > 20000) {
    echo json_encode(['sucesso' => false, 'erro' => 'Texto muito longo para refinamento (máx. 20.000 caracteres). Divida em partes.']);
    exit;
}

$apiKey = GROQ_API_KEY;

$promptSystem = "Você é um assistente jurídico especialista na redação de termos de oitiva e depoimentos policiais da Polícia Militar do Paraná (PMPR).
Sua tarefa é REFINAR e CORRIGIR o texto a seguir.
REGRAS OBRIGATÓRIAS:
1. Corrija a pontuação (vírgulas, pontos), erros ortográficos, concordância verbal/nominal e erros de transcrição de voz.
2. NUNCA altere o sentido dos fatos narrados nem adicione informações que não foram ditas.
3. Mantenha a linguagem formal e adequada a um documento oficial de instrução provisória.
4. Retorne APENAS e TÃO SOMENTE o texto corrigido, sem saudações, observações, aspas ou introduções.";

// -------------------------------------------------------------------
// PASSO 1: DESCOBRE OS MODELOS DE CHAT ATIVOS NA CONTA GROQ
// A Groq retira modelos com frequência, por isso a lista é consultada na API
// (com cache de 1h na sessão) em vez de fixada no código.
// -------------------------------------------------------------------
function listarModelosGroq(string $apiKey): array
{
    $cache = $_SESSION['groq_modelos'] ?? null;
    if ($cache && $cache['expira'] > time()) {
        return $cache['lista'];
    }

    $ch = curl_init('https://api.groq.com/openai/v1/models');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $ids = [];
    if ($code === 200) {
        foreach (json_decode($res, true)['data'] ?? [] as $m) {
            $id = $m['id'] ?? '';
            if ($id === '' || (isset($m['active']) && !$m['active'])) {
                continue;
            }
            // Ignora modelos que não servem para texto: áudio, voz, moderação, visão, embeddings
            if (preg_match('/whisper|guard|orpheus|tts|playai|vision|embed|allam/i', $id)) {
                continue;
            }
            $ids[] = $id;
        }
    }

    // Ordem de preferência (modelos maiores e melhores em português primeiro)
    $preferencia = ['gpt-oss-120b', 'llama-3.3-70b', 'llama-4', 'qwen', 'kimi', 'gpt-oss-20b', 'llama'];
    $peso = function (string $id) use ($preferencia): int {
        foreach ($preferencia as $i => $trecho) {
            if (stripos($id, $trecho) !== false) {
                return $i;
            }
        }
        return count($preferencia);
    };
    usort($ids, fn($a, $b) => $peso($a) <=> $peso($b));

    if ($ids) {
        $_SESSION['groq_modelos'] = ['lista' => $ids, 'expira' => time() + 3600];
    }
    return $ids;
}

// Tenta no máximo 3 modelos para não demorar demais em caso de falha geral
$modelos = array_slice(listarModelosGroq($apiKey), 0, 3);
if (!$modelos) {
    $modelos = ['openai/gpt-oss-120b', 'openai/gpt-oss-20b'];
}

$textoRefinado = '';
$ultimoErro    = '';

foreach ($modelos as $modelo) {
    $data = [
        'model'       => $modelo,
        'messages'    => [
            ['role' => 'system', 'content' => $promptSystem],
            ['role' => 'user', 'content' => $textoOriginal]
        ],
        'temperature' => 0.2
    ];

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 60,
        // Verificação SSL ativa: o depoimento contém dados pessoais
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErro = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200) {
        $responseData = json_decode($response, true);
        if (isset($responseData['choices'][0]['message']['content'])) {
            // Modelos de raciocínio (ex.: Qwen) podem devolver o "pensamento" entre <think>
            $conteudo = preg_replace('/<think>.*?<\/think>/s', '', $responseData['choices'][0]['message']['content']);
            $textoRefinado = trim($conteudo);
            if ($textoRefinado !== '') {
                break; // Requisição bem-sucedida
            }
        }
    }
    // Modelo retirado/indisponível: limpa o cache para a próxima chamada buscar a lista nova
    if ($httpCode === 404) {
        unset($_SESSION['groq_modelos']);
    }
    $ultimoErro = "Modelo {$modelo}: HTTP {$httpCode} {$curlErro} " . substr((string) $response, 0, 500);

    // Chave inválida não melhora trocando de modelo
    if ($httpCode === 401) {
        break;
    }
}

if ($textoRefinado !== '') {
    echo json_encode(['sucesso' => true, 'texto' => $textoRefinado]);
} else {
    // Detalhes técnicos vão para o log do servidor, não para o usuário
    error_log('Falha no refinamento via Groq: ' . $ultimoErro);
    echo json_encode([
        'sucesso' => false,
        'erro'    => 'Não foi possível refinar o texto agora. Tente novamente em instantes.'
    ]);
}
