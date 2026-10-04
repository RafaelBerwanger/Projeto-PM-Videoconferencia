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

// Formatar o texto para a data de nascimento
function formatarDataNasc() {
    const dataValor = gV('entnasc');
    if (!dataValor) return "";

    const [ano, mes, dia] = dataValor.split('-');
    const meses = [
        "janeiro", "fevereiro", "março", "abril", "maio", "junho",
        "julho", "agosto", "setembro", "outubro", "novembro", "dezembro"
    ];

    const mesExtenso = meses[parseInt(mes) - 1];
    return `nascido aos ${parseInt(dia)} dias do mês de ${mesExtenso} do ano ${ano}`;
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

    const dadosOitiva = {
        encarregado: {
            nome: gV('entnome_enc'),
            cpf: gV('entcni_enc'),
            posto: gV('ent_posto'),
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
            endereco: `${gV('entrua')}, nº ${gV('entnumero')}, bairro ${gV('entbairro')}, ${gV('entcomp')}, ${gV('entmun')} - ${gV('entest')}`,
            aos_costumes_opcao: elCostumesOpcao ? elCostumesOpcao.value : "nada",
            aos_costumes_detalhe: gV('aos_costumes_detalhe')
        },
        oitiva: {
            local: gV('loc_oitiva'),
            cidade: gV('cidade'),
            endereco: gV('endOitiva'),
            estado: gV('entest'),
            inicio: gV('entinicio'),
            termino: gV('entfim'),
            texto: gV('depoimento_txt'),
            advogado: { nome: gV('nome_adv'), oab: gV('oab_adv'), uf: gV('est_oab') }
        }
    };

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
        divAdv.innerHTML = `<br>
        <input id="nome_adv" type="text" size="30" placeholder="nome do defensor"></input><br>
        <input id="oab_adv" type="text" size="7" placeholder="nº OAB"></input>
        <input id="est_oab" type="text" size="2" placeholder="UF"></input>
        `;
    } else if (adv_var === "nao") {
        divAdv.innerHTML = " ";
    }
}

/**
 * Função de Impressão e Geração da Certidão/Termo Final
 */
function gerarPDF(d) {
    const dataOriginal = new Date();
    const diaNum = dataOriginal.getDate();
    const mesExtenso = new Intl.DateTimeFormat('pt-BR', { month: 'long' }).format(dataOriginal);
    const ano = dataOriginal.getFullYear();
    const horarioInicioFormatado = formatarHora(gV("entinicio"));
    const horarioFimFormatado = formatarHora(gV("entfim"));

    const dataPadraoOficial = `Aos ${diaParaExtenso(diaNum)} dias do mês de ${mesExtenso} de ${ano}`;

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
    const nomeEscrivao = gV("entnome_esc");
    const cpfEscrivao = gV("entcin_esc");
    const postoEscrivao = gV("ent_posto_esc");
    const temEscrivao = nomeEscrivao && nomeEscrivao.trim() !== "";

    // 3. LÓGICA DO ADVOGADO
    const temAdvogado = d.oitiva.advogado && d.oitiva.advogado.nome && d.oitiva.advogado.nome.trim() !== "";
    const textoAdvogado = temAdvogado
        ? `, assistido por seu advogado, Dr. ${d.oitiva.advogado.nome}, OAB nº ${d.oitiva.advogado.oab}/${d.oitiva.advogado.uf}`
        : "";

    // 4. TRATAMENTO DO TEXTO DO DEPOIMENTO
    const depoimentoFormatado = d.oitiva.texto
        ? d.oitiva.texto.split('\n').map(p => p.trim()).filter(p => p.length > 0).join('<br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;')
        : "<i>(Nenhum depoimento foi digitado)</i>";

    // Abertura da Janela de Impressão
    const novaJanela = window.open('', '_blank');

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
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;${dataPadraoOficial}, nesta cidade de ${d.oitiva.cidade}, Estado do Paraná, às ${horarioInicioFormatado}, no(a) ${d.oitiva.local}, situado na ${d.oitiva.endereco}, onde se encontrava presente o Encarregado do(a) ${d.encarregado.procedimento} nº ${d.encarregado.num}, ${d.encarregado.posto} ${d.encarregado.nome}, CPF nº ${d.encarregado.cpf}${textoAdvogado}, compareceu o(a) ${(d.depoente.qualidade || '').toLowerCase()}, Sr(a). <b>${d.depoente.nome}</b>, ${d.depoente.posto ? 'Posto/Graduação: ' + d.depoente.posto + ' (' + d.depoente.quadro + '), ' : ''}CPF nº ${d.depoente.cpf}, ${d.depoente.nasc}, natural de ${d.depoente.cidade_naturalidade}/${d.depoente.estado_naturalidade}, nacionalidade ${d.depoente.nacionalidade}, estado civil ${d.depoente.estado_civil}, profissão ${d.depoente.profissao} (em ${d.depoente.local_profissao}), filho(a) de ${d.depoente.mae}${d.depoente.pai ? ' e de ' + d.depoente.pai : ''}, residente e domiciliado na ${d.depoente.endereco}, o(a) qual, ${textoCompromisso}, perguntado(a) sobre os fatos constantes no presente procedimento, respondeu e declarou:<br><br>

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
                <b>Dr. ${d.oitiva.advogado.nome}</b><br>
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

function aplicarMascaraCPF(i) {
    let v = i.value;
    if (isNaN(v[v.length - 1])) {
        i.value = v.substring(0, v.length - 1);
        return;
    }
    i.setAttribute("maxlength", "14");
    if (v.length == 3 || v.length == 7) i.value += ".";
    if (v.length == 11) i.value += "-";
}

// ==========================================
// DITADO POR VOZ EM TEMPO REAL
// ==========================================
let recognition = null;
let gravando = false;
let textoBase = '';

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

    recognition = new SpeechRecognition();
    recognition.lang = 'pt-BR';
    recognition.continuous = true;
    recognition.interimResults = true;

    const campoTexto = document.getElementById('depoimento_txt');
    const btnVoz = document.getElementById('btn_voz');
    const iconeVoz = document.getElementById('icone_voz');
    const textoBtnVoz = document.getElementById('texto_btn_voz');

    textoBase = campoTexto ? campoTexto.value : '';
    if (textoBase.length > 0 && !textoBase.endsWith(' ')) {
        textoBase += ' ';
    }

    recognition.onresult = (event) => {
        let transcricaoFinal = '';
        let transcricaoProvisoria = '';

        for (let i = event.resultIndex; i < event.results.length; i++) {
            const texto = event.results[i][0].transcript;
            if (event.results[i].isFinal) {
                transcricaoFinal += texto;
            } else {
                transcricaoProvisoria += texto;
            }
        }

        if (transcricaoFinal) {
            textoBase += transcricaoFinal + ' ';
        }

        if (campoTexto) {
            campoTexto.value = textoBase + transcricaoProvisoria;
            campoTexto.scrollTop = campoTexto.scrollHeight;
        }
    };

    recognition.onerror = (event) => {
        console.error("Erro na transcrição de voz:", event.error);
        if (event.error !== 'no-speech') {
            pararDitado();
        }
    };

    recognition.onend = () => {
        if (gravando) {
            recognition.start();
        } else {
            pararDitado();
        }
    };

    recognition.start();
    gravando = true;

    if (btnVoz) btnVoz.style.background = '#dc2626';
    if (iconeVoz) iconeVoz.className = 'fa-solid fa-microphone-slash';
    if (textoBtnVoz) textoBtnVoz.innerText = 'Parar Ditado';
}

function pararDitado() {
    if (recognition) {
        gravando = false;
        recognition.stop();
    }

    const btnVoz = document.getElementById('btn_voz');
    const iconeVoz = document.getElementById('icone_voz');
    const textoBtnVoz = document.getElementById('texto_btn_voz');

    if (btnVoz) {
        btnVoz.style.background = '#2563eb';
        if (iconeVoz) iconeVoz.className = 'fa-solid fa-microphone';
        if (textoBtnVoz) textoBtnVoz.innerText = 'Ditado por Voz';
    }
}

function alternarDitado() {
    if (gravando) {
        pararDitado();
    } else {
        iniciarReconhecimentoVoz();
    }
}

let textoOriginalBackup = null;
let textoEstaRefinado = false;

// ==========================================
// FUNÇÃO PARA REFINAR VIA IA
// ==========================================
async function refinarTextoIA() {
    const campoTexto = document.getElementById('depoimento_txt');
    const btnIa = document.getElementById('btn_ia');
    const iconeIa = document.getElementById('icone_ia');
    const textoBtnIa = document.getElementById('texto_btn_ia');

    if (textoEstaRefinado) {
        if (textoOriginalBackup !== null && campoTexto) {
            campoTexto.value = textoOriginalBackup;
        }

        textoEstaRefinado = false;
        textoOriginalBackup = null;

        if (btnIa) btnIa.style.background = '#8b5cf6';
        if (iconeIa) iconeIa.className = 'fa-solid fa-wand-magic-sparkles';
        if (textoBtnIa) textoBtnIa.innerText = 'Refinar com IA';
        return;
    }

    const textoAtual = campoTexto ? campoTexto.value.trim() : '';

    if (!textoAtual) {
        alert("Por favor, digite ou dite um depoimento antes de solicitar o refinamento.");
        return;
    }

    if (typeof gravando !== 'undefined' && gravando) {
        pararDitado();
    }

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
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ texto: textoAtual })
        });

        const dados = await resposta.json();

        if (dados.sucesso) {
            campoTexto.value = dados.texto;
            textoEstaRefinado = true;

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

    const campoTexto = document.getElementById('depoimento_txt');
    if (campoTexto) {
        campoTexto.addEventListener('input', () => {
            if (textoEstaRefinado) {
                textoEstaRefinado = false;
                textoOriginalBackup = null;

                const btnIa = document.getElementById('btn_ia');
                const iconeIa = document.getElementById('icone_ia');
                const textoBtnIa = document.getElementById('texto_btn_ia');

                if (btnIa) {
                    btnIa.style.background = '#8b5cf6';
                    if (iconeIa) iconeIa.className = 'fa-solid fa-wand-magic-sparkles';
                    if (textoBtnIa) textoBtnIa.innerText = 'Refinar com IA';
                }
            }
        });
    }
});