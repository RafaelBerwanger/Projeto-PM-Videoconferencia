/**
 * FUNÇÕES DE APOIO (todos enxergam)
 */
// Função piloto dos dados
const gV = (id) => document.getElementById(id) ? document.getElementById(id).value : "";

// Formatar hora no padrão documental
const formatarHora = (valorInput) => {
    if (!valorInput) return "";
    const [hora, minuto] = valorInput.split(":");
    return `${hora}h e ${minuto}min`;
};

// Escapa texto digitado pelo usuário antes de inseri-lo no HTML do termo
const escHtml = (valor) => String(valor ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");

// Aplica escHtml em todas as strings de um objeto (recursivo)
const escObjeto = (obj) => {
    const saida = Array.isArray(obj) ? [] : {};
    for (const chave in obj) {
        const v = obj[chave];
        saida[chave] = (v && typeof v === "object") ? escObjeto(v) : escHtml(v);
    }
    return saida;
};

// Oculta os 3 primeiros e os 2 últimos dígitos do CPF (ex: XXX.123.123-XX), como informado no formulário
const ocultarCPF = (cpf) => {
    const d = String(cpf || "").replace(/\D/g, "");
    if (d.length !== 11) return cpf || "";
    return `XXX.${d.substr(3, 3)}.${d.substr(6, 3)}-XX`;
};

// Formatar o texto para a data de nascimento
function formatarDataNasc() {
    const dataValor = gV('entnasc');
    if (!dataValor) return "";

    const [ano, mes, dia] = dataValor.split('-');
    const meses = [
        "janeiro", "fevereiro", "março", "abril", "maio", "junho",
        "julho", "agosto", "setembro", "outubro", "novembro", "dezembro"
    ];

    const mesExtenso = meses[parseInt(mes, 10) - 1];
    const diaNum = parseInt(dia, 10);
    const trechoDia = diaNum === 1 ? "ao primeiro dia" : `aos ${diaNum} dias`;
    return `nascido(a) ${trechoDia} do mês de ${mesExtenso} do ano de ${ano}`;
}

function tipoDepoente() {
    const elTipo = document.querySelector('input[name="tipoDepoente_name"]:checked');
    if (!elTipo) return;
    const depoente_tipo = elTipo.value;

    if (depoente_tipo === "militar") {
        const elProf = document.getElementById("profissao");
        if (elProf) elProf.value = "Policial Militar";

        const elPosto = document.getElementById("selecionaPosto");
        if (elPosto) {
            elPosto.innerHTML = `
            <br>
            <label for="quadro_depo">Quadro:</label>
                    <select id="quadro_depo">
                        <option value="QP PM">QP PM</option>
                        <option value="QOEM PM">QOEM PM</option>
                        <option value="PM">Sem quadro</option>
                    </select>

             <label for="selecionaPosto_selected">Posto/Graduação:</label>
                    <select id="selecionaPosto_selected">
                        <option value="Coronel">Coronel</option>
                        <option value="Tenente-Coronel">Tenente-Coronel</option>
                        <option value="Major">Major</option>
                        <option value="Capitão">Capitão</option>
                        <option value="1º Tenente">1º Tenente</option>
                        <option value="2º Tenente">2º Tenente</option>
                        <option value="Asp.">Asp. PM</option>
                        <option value="Cadete 1º do Ano">Cadete 1º do Ano</option>
                        <option value="Cadete 2º do Ano">Cadete 2º do Ano</option>
                        <option value="Cadete 3º do Ano">Cadete 3º do Ano</option>
                        <option value="Subtenente">Subtenente</option>
                        <option value="1º Sargento">1º Sargento</option>
                        <option value="2º Sargento">2º Sargento</option>
                        <option value="3º Sargento" selected>3º Sargento</option>
                        <option value="Cabo">Cabo</option>
                        <option value="Soldado">Soldado</option>
                    </select>
            `;
        }
    } else if (depoente_tipo === "civil") {
        const elProf = document.getElementById("profissao");
        if (elProf) elProf.value = "";

        const elPosto = document.getElementById("selecionaPosto");
        if (elPosto) elPosto.innerHTML = " ";
    }
}

// Função executada no clique do botão Gerar/Imprimir
function sbmt1() {
    const elEstc = document.querySelector('input[name="estc"]:checked');
    const elCostumesOpcao = document.querySelector('input[name="aos_costumes_opcao"]:checked');

    // Monta o endereço ignorando as partes não preenchidas (evita ", nº , bairro ,")
    const partesEndereco = [
        gV('entrua'),
        gV('entnumero') ? `nº ${gV('entnumero')}` : "",
        gV('entbairro') ? `bairro ${gV('entbairro')}` : "",
        gV('entcomp')
    ].filter(p => p && p.trim() !== "");
    const cidadeUf = [gV('entmun'), gV('entest')].filter(Boolean).join(' - ');
    const enderecoDepoente = partesEndereco.join(', ') + (cidadeUf ? `, ${cidadeUf}` : "");

    // Nome por extenso do estado da oitiva (ex.: "Paraná"), vindo do select
    const selEstadoOitiva = document.getElementById('est_oitiva');
    const nomeEstadoOitiva = selEstadoOitiva && selEstadoOitiva.selectedIndex >= 0
        ? selEstadoOitiva.options[selEstadoOitiva.selectedIndex].text
        : "Paraná";

    const dadosOitiva = {
        encarregado: {
            nome: gV('entnome_enc'),
            cpf: ocultarCPF(gV('entcni_enc')),
            posto: [gV('ent_posto'), gV('quadro_enc') !== 'PM' ? gV('quadro_enc') : ''].filter(Boolean).join(' '),
            opm: gV('opm'),
            crpm: gV('crpm'),
            procedimento: gV('ent_proc'),
            num: gV('ent_num_proc')
        },
        depoente: {
            nome: gV('entnome').toUpperCase(),
            qualidade: gV('condicao_oitiva') || gV('ent_qualidade'),
            quadro: gV('quadro_depo'),
            posto: gV('selecionaPosto_selected'),
            estado_civil: elEstc ? elEstc.value : "",
            cpf: gV('entcin'),
            nasc: formatarDataNasc(),
            estado_naturalidade: gV('est_naturalidade'),
            cidade_naturalidade: gV('cidade_naturalidade'),
            nacionalidade: gV('nacionalidade'),
            profissao: gV('profissao'),
            local_profissao: gV('local_profissao'),
            idade: gV('entidade'),
            mae: gV('entmae').toUpperCase(),
            pai: gV('entpai').toUpperCase(),
            endereco: enderecoDepoente,
            aos_costumes_opcao: elCostumesOpcao ? elCostumesOpcao.value : "nada",
            aos_costumes_detalhe: gV('aos_costumes_detalhe')
        },
        oitiva: {
            local: gV('loc_oitiva'),
            cidade: gV('cidade'),
            endereco: gV('endOitiva'),
            estado: nomeEstadoOitiva,
            inicio: gV('entinicio'),
            termino: gV('entfim'),
            texto: gV('depoimento_txt'),
            advogado: { nome: gV('nome_adv'), oab: gV('oab_adv'), uf: gV('est_oab') }
        }
    };

    if (!dadosOitiva.depoente.nome.trim()) {
        alert("Informe o nome do depoente antes de gerar o termo.");
        return;
    }
    if (dadosOitiva.depoente.aos_costumes_opcao === "impedimento"
        && (dadosOitiva.depoente.qualidade || "").toUpperCase().includes("INFORMANTE")
        && !dadosOitiva.depoente.aos_costumes_detalhe.trim()) {
        alert("Especifique a relação/parentesco declarada nos costumes.");
        return;
    }

    gerarPDF(dadosOitiva);
}

function diaParaExtenso(dia) {
    const diasExtenso = [
        "", "primeiro", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove", "dez",
        "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezessete", "dezoito", "dezenove", "vinte",
        "vinte e um", "vinte e dois", "vinte e três", "vinte e quatro", "vinte e cinco", "vinte e seis", "vinte e sete", "vinte e oito", "vinte e nove", "trinta", "trinta e um"
    ];
    return diasExtenso[dia] || dia;
}

function adv_presente() {
    const elAdv = document.querySelector('input[name="ent_adv"]:checked');
    if (!elAdv) return;
    const adv_var = elAdv.value;

    const divAdv = document.getElementById("adv");
    if (!divAdv) return;

    if (adv_var === "sim") {
        divAdv.innerHTML = `
        <input id="nome_adv" type="text" class="form-control" placeholder="Nome do defensor" style="margin-bottom: 5px;">
        <div style="display: flex; gap: 5px;">
            <input id="oab_adv" type="text" class="form-control" placeholder="nº OAB">
            <input id="est_oab" type="text" class="form-control" placeholder="UF" maxlength="2" style="max-width: 70px;">
        </div>
        `;
    } else if (adv_var === "nao") {
        divAdv.innerHTML = " ";
    }
}

/**
 * Função de Impressão e Geração da Certidão/Termo Final
 */
function gerarPDF(dadosBrutos) {
    // Todo texto digitado é escapado antes de entrar no HTML do termo
    const d = escObjeto(dadosBrutos);
    const dataOriginal = new Date();
    const diaNum = dataOriginal.getDate();
    const mesExtenso = new Intl.DateTimeFormat('pt-BR', { month: 'long' }).format(dataOriginal);
    const ano = dataOriginal.getFullYear();
    const horarioInicioFormatado = formatarHora(gV("entinicio"));
    const horarioFimFormatado = formatarHora(gV("entfim"));

    const dataPadraoOficial = diaNum === 1
        ? `Ao primeiro dia do mês de ${mesExtenso} de ${ano}`
        : `Aos ${diaParaExtenso(diaNum)} dias do mês de ${mesExtenso} de ${ano}`;

    // 1. TÍTULO DINÂMICO CONFORME A QUALIDADE
    let tituloTermo = "TERMO DE INQUIRIÇÃO DE TESTEMUNHA";
    const qualidadeUpper = (d.depoente.qualidade || "").toUpperCase();

    if (qualidadeUpper.includes("ACUSADO") || qualidadeUpper.includes("SINDICADO") || qualidadeUpper.includes("AUTOR") || qualidadeUpper.includes("INDICIADO") || qualidadeUpper.includes("NOTICIADO")) {
        tituloTermo = `TERMO DE QUALIFICAÇÃO E INTERROGATÓRIO DE ${qualidadeUpper}`;
    } else if (qualidadeUpper.includes("VÍTIMA") || qualidadeUpper.includes("OFENDIDO")) {
        tituloTermo = `TERMO DE DECLARAÇÕES DE ${qualidadeUpper}`;
    } else if (qualidadeUpper.includes("INFORMANTE")) {
        tituloTermo = "TERMO DE DECLARAÇÕES DE INFORMANTE";
    } else if (qualidadeUpper.includes("DECLARANTE")) {
        tituloTermo = "TERMO DE DECLARAÇÕES";
    }

    // 2. ADEQUAÇÃO DO TRECHO CONSTATANTE DA CERTIDÃO FINAL (TEXTO DE COMPROMISSO E COSTUMES)
    let textoCompromisso = "";
    const detalheTratado = (d.depoente.aos_costumes_detalhe || "").trim();
    const impedimentoDeclarado = (d.depoente.aos_costumes_opcao === "impedimento");

    if (qualidadeUpper.includes("INFORMANTE")) {
        if (impedimentoDeclarado && detalheTratado !== "") {
            textoCompromisso = `deixando de prestar o compromisso legal por lei facultado (Art. 208 do CPP) em razão de ${detalheTratado}, e aos costumes declarou que ${detalheTratado}`;
        } else {
            textoCompromisso = "deixando de prestar o compromisso legal por lei facultado por figurar na condição de informante (Art. 208 do CPP), e aos costumes nada disse nem lhe foi tocado";
        }
    } else if (qualidadeUpper.includes("TESTEMUNHA")) {
        textoCompromisso = "prestando o compromisso legal de dizer a verdade sob as penas da lei (Art. 203 do CPP), e aos costumes nada disse nem lhe foi tocado pelas disposições legais";
    } else {
        textoCompromisso = "deixando de prestar o compromisso legal por lei facultado por figurar no procedimento nessa condição, e aos costumes nada disse nem lhe foi tocado";
    }

    // CAPTURA DOS DADOS DO ESCRIVÃO (SE HOUVER)
    const nomeEscrivao = escHtml(gV("entnome_esc"));
    const cpfEscrivao = escHtml(ocultarCPF(gV("entcin_esc")));
    const quadroEscrivao = gV("quadro_esc") !== "PM" ? gV("quadro_esc") : "";
    const postoEscrivao = escHtml([gV("ent_posto_esc"), quadroEscrivao].filter(Boolean).join(" "));
    const temEscrivao = nomeEscrivao.trim() !== "";

    // 3. LÓGICA DO ADVOGADO
    const temAdvogado = d.oitiva.advogado && d.oitiva.advogado.nome && d.oitiva.advogado.nome.trim() !== "";
    const textoAdvogado = temAdvogado
        ? `, assistido(a) por seu(sua) advogado(a), Dr(a). ${d.oitiva.advogado.nome}, OAB nº ${d.oitiva.advogado.oab}/${d.oitiva.advogado.uf}`
        : "";

    // 4. TRATAMENTO DO TEXTO DO DEPOIMENTO
    const depoimentoFormatado = d.oitiva.texto
        ? d.oitiva.texto.split('\n').map(p => p.trim()).filter(p => p.length > 0).join('<br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;')
        : "<i>(Nenhum depoimento foi digitado)</i>";

    // Abertura da Janela de Impressão
    const novaJanela = window.open('', '_blank');
    if (!novaJanela) {
        alert("O navegador bloqueou a janela de impressão. Permita pop-ups para este site e tente novamente.");
        return;
    }

    novaJanela.document.write(`
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>${tituloTermo} - ${d.depoente.nome}</title>
        <style>
            @page {
                size: A4 portrait;
                margin: 1.5cm 1.5cm 1.5cm 2cm !important;
            }

            * {
                box-sizing: border-box;
                -webkit-print-color-adjust: exact;
            }

            html, body {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background-color: #ffffff;
                font-family: 'Times New Roman', Times, serif;
                color: #000000;
            }

            .cabecalho-container {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                width: 100% !important;
                margin-bottom: 25px;
            }

            .logo-topo {
                height: 70px;
                width: auto;
            }

            .texto-central {
                text-align: center;
                font-weight: bold;
                font-size: 11pt;
                line-height: 1.3;
            }

            .titulo-termo {
                text-align: center;
                margin-top: 15px;
                margin-bottom: 25px;
                font-size: 12pt;
                width: 100% !important;
            }

            .conteudo, .fecho {
                width: 100% !important;
                max-width: 100% !important;
                font-size: 11pt;
                line-height: 1.6;
                text-align: justify;
                text-justify: inter-word;
            }

            .paragrafo {
                text-align: justify;
            }

            .assinaturas {
                width: 100% !important;
                margin-top: 40px;
                text-align: center;
                page-break-inside: avoid;
            }

            .assinantes {
                margin-bottom: 25px;
                font-size: 11pt;
            }
        </style>
    </head>
    <body>
        <div class="cabecalho-container">
            <img src="img/brasao-pr.png" class="logo-topo">
            <div class="texto-central">
                ESTADO DO PARANÁ<br>
                POLÍCIA MILITAR<br>
                ${d.encarregado.opm}
            </div>
            <img src="img/logo.png" class="logo-topo">
        </div>

        <div class="titulo-termo">
            <span style="font-weight: bold; text-decoration: underline;">${tituloTermo}</span>
            <br>
            <span>(${d.depoente.posto ? d.depoente.posto + ' ' : ''}${d.depoente.nome})</span>
        </div>

        <div class="conteudo">
            <div class="paragrafo">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;${dataPadraoOficial}, nesta cidade de ${d.oitiva.cidade}, Estado do(a) ${d.oitiva.estado}, às ${horarioInicioFormatado}, no(a) ${d.oitiva.local}, situado na ${d.oitiva.endereco}, onde se encontrava presente o Encarregado do(a) ${d.encarregado.procedimento} nº ${d.encarregado.num}, ${d.encarregado.posto} ${d.encarregado.nome}, CPF nº ${d.encarregado.cpf}${textoAdvogado}, compareceu o(a) ${(d.depoente.qualidade || '').toLowerCase()}, Sr(a). <b>${d.depoente.nome}</b>, ${d.depoente.posto ? 'Posto/Graduação: ' + d.depoente.posto + ' (' + d.depoente.quadro + '), ' : ''}CPF nº ${d.depoente.cpf}, ${d.depoente.nasc}, ${d.depoente.cidade_naturalidade ? 'natural de ' + d.depoente.cidade_naturalidade + '/' + d.depoente.estado_naturalidade + ', ' : ''}nacionalidade ${d.depoente.nacionalidade}, estado civil ${d.depoente.estado_civil}, profissão ${d.depoente.profissao}${d.depoente.local_profissao ? ' (em ' + d.depoente.local_profissao + ')' : ''}, filho(a) de ${d.depoente.mae}${d.depoente.pai ? ' e de ' + d.depoente.pai : ''}, residente e domiciliado na ${d.depoente.endereco}, o(a) qual, ${textoCompromisso}, perguntado(a) sobre os fatos constantes no presente procedimento, respondeu e declarou:<br><br>

                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>PASSOU A DECLARAR:</b> "${depoimentoFormatado}"
            </div>
        </div>

        <br>
        <div class="fecho">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;E, como nada mais disse e nem lhe foi perguntado pelo Encarregado, deu-se por encerrado o presente termo às ${horarioFimFormatado}, o qual, depois de lido e achado conforme, vai devidamente assinado pelos presentes.
        </div>

        <br><br>
        <div class="assinaturas">
            <div class="assinantes">
                ____________________________________________________<br>
                <b>${d.encarregado.posto} ${d.encarregado.nome}</b><br>
                Encarregado do(a) ${d.encarregado.procedimento}
            </div>

            ${temEscrivao ? `
            <br><br>
            <div class="assinantes">
                ____________________________________________________<br>
                <b>${postoEscrivao} ${nomeEscrivao.toUpperCase()}</b><br>${cpfEscrivao ? 'CPF nº ' + cpfEscrivao + '<br>' : ''}
                <b>Escrivão</b>
            </div>` : ''}
            
            <br><br>
            <div class="assinantes">
                ____________________________________________________<br>
                <b>${d.depoente.nome}</b><br>${d.depoente.qualidade}
            </div>

            ${temAdvogado ? `
            <br><br>
            <div class="assinantes">
                ____________________________________________________<br>
                <b>Dr(a). ${d.oitiva.advogado.nome}</b><br>
                Advogado - OAB/${d.oitiva.advogado.uf} nº ${d.oitiva.advogado.oab}
            </div>` : ''}
        </div>
        
    </body>
    </html>
    `);

    novaJanela.document.close();

    novaJanela.onload = function () {
        novaJanela.focus();
        novaJanela.print();
    };
}

function pegaidade() {
    const elNasc = document.getElementById('entnasc');
    if (elNasc && elNasc.value) {
        const idade = moment().diff(moment(elNasc.value), 'years');
        const elIdade = document.getElementById('entidade');
        if (elIdade) elIdade.value = idade;
    }
}

function insereEscriba() {
    const div = document.getElementById('abre_escriba');
    if (div) {
        div.style.display = (div.style.display === 'none') ? 'block' : 'none';
    }
}

// Máscara 000.000.000-00 (funciona também ao colar ou apagar no meio do texto)
function aplicarMascaraCPF(i) {
    let v = i.value.replace(/\D/g, "").slice(0, 11);
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    i.value = v;
}

// Máscara (00) 00000-0000 ou (00) 0000-0000 para o campo de telefone do depoente
function aplicarMascaraTelefone() {
    const el = document.getElementById("telef");
    if (!el) return;
    let v = el.value.replace(/\D/g, "").slice(0, 11);
    v = v.replace(/^(\d{2})(\d)/, "($1) $2");
    v = v.replace(/(\d)(\d{4})$/, "$1-$2");
    el.value = v;
}

// ==========================================
// DITADO POR VOZ EM TEMPO REAL
// ==========================================
// Como funciona:
// - O texto já confirmado NUNCA é reescrito. Só a frase que está sendo falada
//   (resultado provisório) ocupa um trecho no FINAL do campo e vai sendo
//   atualizada; quando o navegador finaliza a frase, ela vira texto permanente
//   e a próxima frase começa depois dela.
// - Cada frase final é escrita uma única vez, mesmo no Chrome do Android, que
//   reinicia a lista de resultados a cada frase ou repete o texto anterior.
// - Ao cair a sessão (silêncio, ~60s, rede), o trecho provisório é mantido e a
//   escuta reinicia sozinha. Erros passageiros só reconectam.
// - O usuário pode editar o texto durante o ditado; a edição é preservada.
const Ditado = {
    rec: null,
    ativo: false,          // usuário quer ditar (botão ligado)
    escutando: false,      // sessão do navegador realmente aberta
    confirmados: {},       // índice do resultado -> texto já escrito (sessão atual)
    ultimoFinal: '',       // última frase final escrita (detecta repetição do Android)
    indicesProvisorios: [],
    ignorar: new Set(),    // resultados cujo trecho provisório o usuário já editou
    provInicio: -1,        // posição do trecho provisório no campo (-1 = nenhum)
    provTamanho: 0,
    falhasSeguidas: 0,
    timerReinicio: null
};

const MAX_FALHAS_DITADO = 6;
const CHAVE_RASCUNHO = 'rascunho_depoimento';

// Comandos de voz para pontuação (o reconhecimento do navegador não pontua sozinho)
const COMANDOS_VOZ = [
    [/\s*\bnovo par[aá]grafo\b\s*/gi, '\n\n'],
    [/\s*\bnova linha\b\s*/gi, '\n'],
    [/\s*\bponto de interroga[cç][aã]o\b/gi, '?'],
    [/\s*\bponto de exclama[cç][aã]o\b/gi, '!'],
    [/\s*\bponto e v[ií]rgula\b/gi, ';'],
    [/\s*\bponto final\b/gi, '.'],
    [/\s*\bdois pontos\b/gi, ':'],
    [/\s*\bv[ií]rgula\b/gi, ',']
];

// Aplica comandos de voz e maiúscula no início de frases
function formatarTrechoDitado(trecho, textoAnterior) {
    if (!trecho) return '';
    let t = trecho;
    COMANDOS_VOZ.forEach(([re, sub]) => { t = t.replace(re, sub); });
    t = t.replace(/([,.;:?!])(?=[^\s\n])/g, '$1 ').replace(/[ \t]*\n[ \t]*/g, '\n');

    // Maiúscula se começa frase (campo vazio ou anterior termina em . ? ! ou quebra de linha)
    const anterior = (textoAnterior || '').replace(/[ \t]+$/, '');
    if (anterior === '' || /[.?!]$|\n$/.test(anterior)) {
        t = t.replace(/^\s*(\p{L})/u, (m, l) => m.replace(l, l.toUpperCase()));
    }
    t = t.replace(/([.?!]\s+|\n)(\p{L})/gu, (m, sep, l) => sep + l.toUpperCase());
    return t;
}

// Espaço entre o texto anterior e o novo trecho (nenhum antes de pontuação ou após quebra/espaço)
function separadorDitado(anterior, novo) {
    if (!anterior || !novo) return '';
    if (/\s$/.test(anterior) || /^[\n,.;:?!]/.test(novo)) return '';
    return ' ';
}

// Remove do início de "texto" a frase anterior, se ela foi repetida (Chrome do Android)
function removerRepeticao(texto, anterior) {
    if (anterior && texto.toLowerCase().startsWith(anterior.toLowerCase())) {
        return texto.slice(anterior.length).trim();
    }
    return texto;
}

function atualizarStatusVoz(mensagem, tipo = 'info') {
    const el = document.getElementById('status_voz');
    if (!el) return;
    const cores = { info: '#64748b', ouvindo: '#16a34a', alerta: '#b45309', erro: '#b91c1c' };
    el.style.color = cores[tipo] || cores.info;
    el.innerHTML = mensagem;
}

function atualizarBotaoVoz(ligado) {
    const btnVoz = document.getElementById('btn_voz');
    const iconeVoz = document.getElementById('icone_voz');
    const textoBtnVoz = document.getElementById('texto_btn_voz');
    if (btnVoz) btnVoz.style.background = ligado ? '#dc2626' : '#2563eb';
    if (iconeVoz) iconeVoz.className = ligado ? 'fa-solid fa-microphone-slash' : 'fa-solid fa-microphone';
    if (textoBtnVoz) textoBtnVoz.innerText = ligado ? 'Parar Ditado' : 'Ditado por Voz';
}

// Guarda o texto na aba atual: um recarregamento acidental não perde o depoimento
function salvarRascunho(texto) {
    try { sessionStorage.setItem(CHAVE_RASCUNHO, texto); } catch (e) { /* armazenamento indisponível */ }
}

// Escreve (ou atualiza) o trecho da frase em andamento no final do campo,
// sem tocar no restante do texto nem na posição do cursor do usuário
function escreverTrechoDitado(texto) {
    const campo = document.getElementById('depoimento_txt');
    if (!campo) return;

    if (Ditado.provInicio < 0 || Ditado.provInicio + Ditado.provTamanho > campo.value.length) {
        Ditado.provInicio = campo.value.length;
        Ditado.provTamanho = 0;
    }
    const anterior = campo.value.slice(0, Ditado.provInicio);
    const formatado = formatarTrechoDitado(texto, anterior);
    const trecho = formatado ? separadorDitado(anterior, formatado) + formatado : '';

    const atual = campo.value.substr(Ditado.provInicio, Ditado.provTamanho);
    if (trecho === atual) return;

    const cursorNoFim = document.activeElement !== campo || campo.selectionEnd >= campo.value.length;
    campo.setRangeText(trecho, Ditado.provInicio, Ditado.provInicio + Ditado.provTamanho, 'preserve');
    Ditado.provTamanho = trecho.length;
    if (cursorNoFim) campo.scrollTop = campo.scrollHeight;

    salvarRascunho(campo.value);
    // O texto mudou: um "Desfazer Refinamento" agora apagaria o ditado novo
    if (typeof resetarEstadoIA === 'function') resetarEstadoIA();
}

// O trecho escrito vira texto permanente; a próxima frase começa depois dele
function fixarTrechoDitado() {
    Ditado.provInicio = -1;
    Ditado.provTamanho = 0;
}

function novaSessaoDitado() {
    Ditado.confirmados = {};
    Ditado.ultimoFinal = '';
    Ditado.indicesProvisorios = [];
    Ditado.ignorar = new Set();
}

function criarReconhecimento() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const rec = new SpeechRecognition();
    rec.lang = 'pt-BR';
    rec.continuous = true;
    rec.interimResults = true;
    rec.maxAlternatives = 1;

    rec.onstart = () => {
        Ditado.escutando = true;
        novaSessaoDitado();
        atualizarStatusVoz('<i class="fa-solid fa-circle fa-beat" style="font-size: 9px;"></i> Ouvindo... fale normalmente.', 'ouvindo');
    };

    rec.onresult = (event) => {
        Ditado.falhasSeguidas = 0;
        let textoFinal = '';
        let provisorio = '';
        const indicesProvisorios = [];

        // Resultados antes de resultIndex não mudaram desde o último evento
        for (let i = event.resultIndex; i < event.results.length; i++) {
            const texto = event.results[i][0].transcript.trim();
            if (!texto) continue;

            if (!event.results[i].isFinal) {
                indicesProvisorios.push(i);
                if (!Ditado.ignorar.has(i)) provisorio += (provisorio ? ' ' : '') + texto;
                continue;
            }

            // Trecho provisório já editado pelo usuário: não escreve de novo
            if (Ditado.ignorar.has(i)) {
                Ditado.ignorar.delete(i);
                Ditado.confirmados[i] = texto;
                Ditado.ultimoFinal = texto;
                continue;
            }

            const jaEscrito = Ditado.confirmados[i];
            if (jaEscrito === texto) continue; // mesmo resultado reenviado

            // Só escreve o que é novo (desconta repetição da frase anterior)
            const novo = removerRepeticao(texto, jaEscrito !== undefined ? jaEscrito : Ditado.ultimoFinal);
            Ditado.confirmados[i] = texto;
            Ditado.ultimoFinal = texto;
            if (novo) textoFinal += (textoFinal ? ' ' : '') + novo;
        }

        if (textoFinal) {
            escreverTrechoDitado(textoFinal);
            fixarTrechoDitado();
        }
        Ditado.indicesProvisorios = indicesProvisorios;
        escreverTrechoDitado(removerRepeticao(provisorio, Ditado.ultimoFinal));
    };

    rec.onerror = (event) => {
        console.warn('Ditado por voz:', event.error);
        switch (event.error) {
            case 'not-allowed':
            case 'service-not-allowed':
                pararDitado();
                atualizarStatusVoz('<i class="fa-solid fa-ban"></i> Acesso ao microfone negado. Libere a permissão no cadeado da barra de endereço e tente novamente.', 'erro');
                break;
            case 'language-not-supported':
                pararDitado();
                atualizarStatusVoz('O navegador não oferece reconhecimento em português.', 'erro');
                break;
            case 'no-speech':
                // Silêncio: normal durante o depoimento, a escuta é reiniciada
                break;
            default:
                // network, aborted, audio-capture: falhas passageiras, tenta reconectar
                Ditado.falhasSeguidas++;
                atualizarStatusVoz('<i class="fa-solid fa-rotate fa-spin"></i> Conexão do microfone instável, reconectando...', 'alerta');
        }
    };

    rec.onend = () => {
        Ditado.escutando = false;
        // A frase que estava em andamento fica no texto (não se perde na reconexão)
        fixarTrechoDitado();
        if (Ditado.ativo) {
            agendarReinicioDitado(Ditado.falhasSeguidas > 0 ? 800 : 150);
        } else {
            atualizarStatusVoz('Ditado pausado. O texto foi mantido.', 'info');
        }
    };

    return rec;
}

function agendarReinicioDitado(atraso) {
    clearTimeout(Ditado.timerReinicio);
    if (Ditado.falhasSeguidas >= MAX_FALHAS_DITADO) {
        pararDitado();
        atualizarStatusVoz('<i class="fa-solid fa-triangle-exclamation"></i> Não foi possível manter o microfone ativo (verifique a internet e o microfone). O texto foi mantido; clique em "Ditado por Voz" para tentar de novo.', 'erro');
        return;
    }
    Ditado.timerReinicio = setTimeout(() => {
        // Já ouvindo (ex.: voltou para a aba sem a sessão ter caído): nada a fazer
        if (!Ditado.ativo || Ditado.escutando) return;
        // Com a aba em segundo plano o navegador não permite ouvir; retoma ao voltar
        if (document.hidden) return;
        try {
            Ditado.rec.start();
        } catch (e) {
            // "InvalidStateError": a sessão anterior ainda está fechando
            Ditado.falhasSeguidas++;
            agendarReinicioDitado(500);
        }
    }, atraso);
}

function iniciarReconhecimentoVoz() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!SpeechRecognition) {
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        if (isIOS) {
            alert("No iPhone/iPad, a transcrição de voz via navegador funciona exclusivamente no SAFARI.");
        } else {
            alert("O seu navegador não suporta a transcrição por voz. Recomendamos o Google Chrome ou Microsoft Edge.");
        }
        return;
    }

    if (Ditado.ativo) return; // evita duas escutas simultâneas (duplo clique)

    // Descarta uma instância antiga sem deixar os eventos dela agirem
    if (Ditado.rec) {
        Ditado.rec.onend = null;
        Ditado.rec.onresult = null;
        try { Ditado.rec.abort(); } catch (e) { /* já encerrada */ }
    }

    fixarTrechoDitado();
    novaSessaoDitado();
    Ditado.falhasSeguidas = 0;
    Ditado.ativo = true;
    Ditado.rec = criarReconhecimento();

    atualizarBotaoVoz(true);
    atualizarStatusVoz('<i class="fa-solid fa-spinner fa-spin"></i> Ativando o microfone...', 'info');

    try {
        Ditado.rec.start();
    } catch (e) {
        Ditado.falhasSeguidas++;
        agendarReinicioDitado(500);
    }
}

function pararDitado() {
    const estavaAtivo = Ditado.ativo;
    Ditado.ativo = false;
    clearTimeout(Ditado.timerReinicio);

    if (Ditado.rec) {
        // Mantém a frase em andamento e desliga os ouvintes: um resultado atrasado
        // não pode escrever no campo depois (ex.: por cima de um texto refinado pela IA)
        fixarTrechoDitado();
        Ditado.rec.onresult = null;
        Ditado.rec.onend = () => {
            Ditado.escutando = false;
        };
        try { Ditado.rec.stop(); } catch (e) { /* já encerrada */ }
    }
    Ditado.escutando = false;
    atualizarBotaoVoz(false);
    if (estavaAtivo) atualizarStatusVoz('Ditado pausado. O texto foi mantido.', 'info');
}

function alternarDitado() {
    if (Ditado.ativo) {
        pararDitado();
    } else {
        iniciarReconhecimentoVoz();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const campo = document.getElementById('depoimento_txt');
    if (!campo) return;

    // Restaura o rascunho se a página foi recarregada sem querer
    try {
        const rascunho = sessionStorage.getItem(CHAVE_RASCUNHO);
        if (rascunho && !campo.value) {
            campo.value = rascunho;
            atualizarStatusVoz('<i class="fa-solid fa-clock-rotate-left"></i> Texto restaurado do rascunho desta aba.', 'info');
        }
    } catch (e) { /* armazenamento indisponível */ }

    // Edição manual durante o ditado: o que está no campo (inclusive a frase em
    // andamento) passa a ser do usuário e não é mais reescrito pelo ditado
    // (o evento "input" só dispara para alterações feitas pelo usuário)
    campo.addEventListener('input', () => {
        salvarRascunho(campo.value);
        if (Ditado.ativo) {
            Ditado.indicesProvisorios.forEach(i => Ditado.ignorar.add(i));
            fixarTrechoDitado();
        }
    });

    // Ao voltar para a aba, retoma a escuta que o navegador interrompeu
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && Ditado.ativo) {
            agendarReinicioDitado(300);
        }
    });
});

let textoOriginalBackup = null;
let textoEstaRefinado = false;
let textoRefinadoAtual = null;

// Volta o botão da IA para "Refinar" e descarta o backup do texto original
function resetarEstadoIA() {
    if (!textoEstaRefinado) return;
    textoEstaRefinado = false;
    textoOriginalBackup = null;
    textoRefinadoAtual = null;

    const btnIa = document.getElementById('btn_ia');
    const iconeIa = document.getElementById('icone_ia');
    const textoBtnIa = document.getElementById('texto_btn_ia');
    if (btnIa) btnIa.style.background = '#8b5cf6';
    if (iconeIa) iconeIa.className = 'fa-solid fa-wand-magic-sparkles';
    if (textoBtnIa) textoBtnIa.innerText = 'Refinar com IA';
}

// ==========================================
// FUNÇÃO PARA REFINAR VIA IA
// ==========================================
async function refinarTextoIA() {
    const campoTexto = document.getElementById('depoimento_txt');
    const btnIa = document.getElementById('btn_ia');
    const iconeIa = document.getElementById('icone_ia');
    const textoBtnIa = document.getElementById('texto_btn_ia');

    if (textoEstaRefinado) {
        // Segurança extra: se o texto mudou depois do refinamento, desfazer apagaria a mudança
        if (campoTexto && campoTexto.value !== textoRefinadoAtual
            && !confirm("O texto foi alterado depois do refinamento. Desfazer vai voltar ao texto original e PERDER essas alterações. Continuar?")) {
            return;
        }
        if (textoOriginalBackup !== null && campoTexto) {
            campoTexto.value = textoOriginalBackup;
            salvarRascunho(campoTexto.value);
        }
        resetarEstadoIA();
        return;
    }

    const textoAtual = campoTexto ? campoTexto.value.trim() : '';

    if (!textoAtual) {
        alert("Por favor, digite ou dite um depoimento antes de solicitar o refinamento.");
        return;
    }

    // LGPD: o texto sai do navegador e vai para um serviço nos EUA; confirma uma vez por página
    if (!window.iaEnvioConfirmado) {
        const ok = confirm("O texto do depoimento será enviado ao serviço de Inteligência Artificial Groq (Estados Unidos) apenas para correção de ortografia e pontuação.\n\nEvite incluir dados pessoais que não sejam necessários ao texto. Deseja continuar?");
        if (!ok) return;
        window.iaEnvioConfirmado = true;
    }

    // Para o ditado antes de enviar (o texto ouvido até aqui é incorporado ao campo)
    if (Ditado.ativo) {
        pararDitado();
    }

    const textoParaRefinar = campoTexto.value.trim();
    textoOriginalBackup = campoTexto.value;

    if (btnIa) {
        btnIa.disabled = true;
        btnIa.style.opacity = '0.7';
    }
    if (iconeIa) iconeIa.className = 'fa-solid fa-spinner fa-spin';
    if (textoBtnIa) textoBtnIa.innerText = 'Refinando...';

    try {
        const resposta = await fetch('refinar_texto.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ texto: textoParaRefinar })
        });

        const dados = await resposta.json();

        if (dados.sucesso) {
            campoTexto.value = dados.texto;
            salvarRascunho(campoTexto.value);
            textoEstaRefinado = true;
            textoRefinadoAtual = dados.texto;

            if (btnIa) btnIa.style.background = '#d97706';
            if (iconeIa) iconeIa.className = 'fa-solid fa-rotate-left';
            if (textoBtnIa) textoBtnIa.innerText = 'Desfazer Refinamento';
        } else {
            alert("Erro ao refinar texto: " + (dados.erro || "Erro desconhecido."));
            textoOriginalBackup = null;
        }
    } catch (erro) {
        console.error("Erro na requisição da IA:", erro);
        alert("Não foi possível conectar ao servidor de IA. Verifique sua conexão.");
        textoOriginalBackup = null;
    } finally {
        if (btnIa) {
            btnIa.disabled = false;
            btnIa.style.opacity = '1';
        }
    }
}

// ==========================================
// FUNÇÃO DINÂMICA: TRATAR AOS COSTUMES E COMPROMISSO LEGAL
// ==========================================
function tratarAosCostumes() {
    const cardCostumes = document.getElementById('card_aos_costumes');
    const boxDetalhe = document.getElementById('box_detalhe_costumes');
    const inputDetalhe = document.getElementById('aos_costumes_detalhe');
    const alertaCompromisso = document.getElementById('alerta_compromisso');
    const textoStatus = document.getElementById('texto_status_compromisso');

    const selectCondicao = document.getElementById('condicao_oitiva') || document.getElementById('ent_qualidade');
    const condicao = selectCondicao ? selectCondicao.value : 'Testemunha';
    const condicaoUpper = condicao.toUpperCase();

    const elOption = document.querySelector('input[name="aos_costumes_opcao"]:checked');
    const opcaoCostumes = elOption ? elOption.value : 'nada';

    // 1. VISIBILIDADE DO CARD AOS COSTUMES: SÓ EXIBE SE FOR INFORMANTE
    if (condicaoUpper.includes('INFORMANTE')) {
        if (cardCostumes) cardCostumes.style.display = 'block';

        if (opcaoCostumes === 'impedimento') {
            if (boxDetalhe) boxDetalhe.style.display = 'block';
            if (inputDetalhe) inputDetalhe.setAttribute('required', 'required');
        } else {
            if (boxDetalhe) boxDetalhe.style.display = 'none';
            if (inputDetalhe) {
                inputDetalhe.removeAttribute('required');
                inputDetalhe.value = '';
            }
        }

        // ALERTA VISUAL PARA INFORMANTE
        if (alertaCompromisso) {
            alertaCompromisso.style.backgroundColor = '#fff3cd';
            alertaCompromisso.style.borderLeftColor = '#ffc107';
            alertaCompromisso.style.color = '#856404';
        }
        if (textoStatus) {
            textoStatus.innerHTML = '<strong>Situação:</strong> O depoente é <strong>ISENTO do compromisso legal de dizer a verdade</strong> por figurar como Informante (Art. 208 do CPP).';
        }

    } else {
        // OCULTA O CARD AOS COSTUMES PARA OUTRAS QUALIFICAÇÕES
        if (cardCostumes) cardCostumes.style.display = 'none';
        if (boxDetalhe) boxDetalhe.style.display = 'none';
        if (inputDetalhe) {
            inputDetalhe.removeAttribute('required');
            inputDetalhe.value = '';
        }

        // 2. ALERTAS VISUAIS PARA DEMAIS QUALIFICAÇÕES
        if (condicaoUpper.includes('TESTEMUNHA')) {
            if (alertaCompromisso) {
                alertaCompromisso.style.backgroundColor = '#e3f2fd';
                alertaCompromisso.style.borderLeftColor = '#2196f3';
                alertaCompromisso.style.color = '#0d47a1';
            }
            if (textoStatus) {
                textoStatus.innerHTML = '<strong>Situação:</strong> O depoente <strong>PRESTARÁ o compromisso legal</strong> de dizer a verdade (Art. 203 do CPP).';
            }
        } else {
            if (alertaCompromisso) {
                alertaCompromisso.style.backgroundColor = '#f8d7da';
                alertaCompromisso.style.borderLeftColor = '#dc3545';
                alertaCompromisso.style.color = '#721c24';
            }
            if (textoStatus) {
                textoStatus.innerHTML = '<strong>Situação:</strong> O depoente <strong>NÃO presta compromisso legal</strong> de dizer a verdade por figurar no procedimento nessa condição.';
            }
        }
    }
}

// Executa os ouvintes ao carregar o DOM
document.addEventListener('DOMContentLoaded', () => {
    tratarAosCostumes();

    // Edição manual depois do refinamento: o "Desfazer" deixa de valer
    const campoTexto = document.getElementById('depoimento_txt');
    if (campoTexto) {
        campoTexto.addEventListener('input', resetarEstadoIA);
    }
});
