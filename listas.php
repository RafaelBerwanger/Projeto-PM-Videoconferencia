<?php
// Listas padronizadas usadas em todos os formulários (cadastro, perfil e oitivas).
// Manter um único lugar evita valores divergentes entre telas (ex.: "28°" x "28º").

const LISTA_POSTOS = [
    'Oficiais Superiores' => [
        'Cel.'      => 'Coronel (Cel.)',
        'Ten.-Cel.' => 'Tenente-Coronel (Ten.-Cel.)',
        'Maj.'      => 'Major (Maj.)',
    ],
    'Oficiais Intermediários e Subalternos' => [
        'Cap.'       => 'Capitão (Cap.)',
        '1º Ten.'    => '1º Tenente (1º Ten.)',
        '2º Ten.'    => '2º Tenente (2º Ten.)',
        'Asp. a Of.' => 'Aspirante-a-Oficial (Asp. a Of.)',
    ],
    'Praças Especiais' => [
        'Cadete 3º Ano' => 'Cadete 3º Ano',
        'Cadete 2º Ano' => 'Cadete 2º Ano',
        'Cadete 1º Ano' => 'Cadete 1º Ano',
    ],
    'Praças Graduados' => [
        'Subten.' => 'Subtenente (Subten.)',
        '1º Sgt.' => '1º Sargento (1º Sgt.)',
        '2º Sgt.' => '2º Sargento (2º Sgt.)',
        '3º Sgt.' => '3º Sargento (3º Sgt.)',
        'Cabo'    => 'Cabo (Cb.)',
    ],
    'Praças' => [
        'Soldado 1ª Classe' => 'Soldado 1ª Classe (Sd. 1ª Cl.)',
        'Soldado 2ª Classe' => 'Soldado 2ª Classe (Sd. 2ª Cl. - Aluno)',
    ],
];

const LISTA_UNIDADES = [
    'Comandos Regionais (CRPM)' => [
        '1º CRPM - Curitiba', '2º CRPM - Londrina', '3º CRPM - Maringá', '4º CRPM - Ponta Grossa',
        '5º CRPM - Cascavel', '6º CRPM - São José dos Pinhais', '7º CRPM - Pato Branco',
    ],
    'Comandos Especializados e Apoio' => [
        'CME - Curitiba', 'CPE - Curitiba', 'COMAV - Curitiba', 'CIROCAM - Curitiba', 'COPOM - Curitiba',
    ],
    'Batalhões de Polícia Militar (BPM)' => [
        '1º BPM - Ponta Grossa', '2º BPM - Jacarezinho', '3º BPM - Pato Branco', '4º BPM - Maringá',
        '5º BPM - Londrina', '6º BPM - Cascavel', '7º BPM - Cruzeiro do Oeste', '8º BPM - Paranavaí',
        '9º BPM - Paranaguá', '10º BPM - Apucarana', '11º BPM - Campo Mourão', '12º BPM - Curitiba',
        '13º BPM - Curitiba', '14º BPM - Foz do Iguaçu', '15º BPM - Rolândia', '16º BPM - Guarapuava',
        '17º BPM - São José dos Pinhais', '18º BPM - Cornélio Procópio', '19º BPM - Toledo',
        '20º BPM - Curitiba', '21º BPM - Francisco Beltrão', '22º BPM - Colombo', '23º BPM - Curitiba',
        '25º BPM - Umuarama', '26º BPM - Telêmaco Borba', '27º BPM - União da Vitória', '28º BPM - Lapa',
        '29º BPM - Piraquara', '30º BPM - Londrina', '31º BPM - Assis Chateaubriand', '32º BPM - Sarandi',
        '33º BPM - Curitiba', '34º BPM - Almirante Tamandaré',
    ],
    'Companhias Independentes (CIPM)' => [
        '3ª CIPM - Loanda', '5ª CIPM - Cianorte', '6ª CIPM - Ivaiporã', '7ª CIPM - Arapongas',
        '8ª CIPM - Irati', '9ª CIPM - Colorado', '10ª CIPM - Laranjeiras do Sul', '11ª CIPM - Cambé',
        '12ª CIPM - Palmas',
    ],
    'Batalhões Especializados' => [
        'BOPE - Piraquara', 'BPAmb - São José dos Pinhais', 'BPChoque - Curitiba', 'BPEC - Curitiba',
        'BPFron - Marechal Cândido Rondon', 'BPRONE - Curitiba', 'BPRv - Curitiba', 'BPTran - Curitiba',
        'RPMon - Curitiba',
    ],
];

// Normaliza variações de digitação gravadas no banco por versões antigas
// ("28° BPM – Lapa" passa a casar com "28º BPM - Lapa").
function normalizarOpcao(?string $valor): string
{
    return trim(str_replace(['°', '–', '—'], ['º', '-', '-'], (string) $valor));
}

// Gera os <optgroup>/<option> de uma lista agrupada, marcando o valor selecionado.
// Aceita listas no formato [grupo => [valor => rótulo]] ou [grupo => [valor, ...]].
function renderOpcoes(array $grupos, ?string $selecionado = '', string $placeholder = 'Selecione...'): string
{
    $sel = normalizarOpcao($selecionado);
    $encontrou = false;
    $html = '';

    foreach ($grupos as $grupo => $itens) {
        // Grupo com nome vazio gera opções soltas, sem <optgroup>
        if ($grupo !== '') {
            $html .= '<optgroup label="' . e($grupo) . '">';
        }
        foreach ($itens as $valor => $rotulo) {
            if (is_int($valor)) {
                $valor = $rotulo;
            }
            $marcado = ($sel !== '' && normalizarOpcao($valor) === $sel);
            $encontrou = $encontrou || $marcado;
            $html .= '<option value="' . e($valor) . '"' . ($marcado ? ' selected' : '') . '>' . e($rotulo) . '</option>';
        }
        if ($grupo !== '') {
            $html .= '</optgroup>';
        }
    }

    // Sem valor válido, o placeholder fica selecionado (evita o navegador
    // escolher a primeira opção e gravar um dado errado sem o usuário perceber)
    $opcaoVazia = '<option value="" disabled' . ($encontrou ? '' : ' selected') . '>' . e($placeholder) . '</option>';
    return $opcaoVazia . $html;
}

// Validação dos dados cadastrais (usada no cadastro e no perfil).
// Retorna a mensagem de erro ou null se estiver tudo certo.
function validarDadosCadastrais(array $d): ?string
{
    foreach (['nome', 'rg', 'cpf', 'email', 'posto_graduacao', 'unidade', 'telefone'] as $campo) {
        if (($d[$campo] ?? '') === '') {
            return 'Preencha todos os campos obrigatórios.';
        }
    }
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
        return 'Informe um e-mail válido.';
    }
    if (strlen(preg_replace('/\D/', '', $d['cpf'])) !== 11) {
        return 'O CPF deve conter 11 dígitos.';
    }
    $digitosTelefone = strlen(preg_replace('/\D/', '', $d['telefone']));
    if ($digitosTelefone < 10 || $digitosTelefone > 11) {
        return 'Informe um telefone válido com DDD.';
    }
    if (!opcaoValida(LISTA_POSTOS, $d['posto_graduacao'])) {
        return 'Posto/Graduação inválido.';
    }
    if (!opcaoValida(LISTA_UNIDADES, $d['unidade'])) {
        return 'Unidade/OPM inválida.';
    }
    return null;
}

// Valida se um valor enviado pertence à lista
function opcaoValida(array $grupos, string $valor): bool
{
    foreach ($grupos as $itens) {
        foreach ($itens as $chave => $rotulo) {
            if ((is_int($chave) ? $rotulo : $chave) === $valor) {
                return true;
            }
        }
    }
    return false;
}
