/** * FUNÇÕES DE APOIO (todos enxergam) */
//Função piloto dos dados | Função Principal: Coleta os dados da página
const gV = (id) => document.getElementById(id) ? document.getElementById(id).value : "";

//formatar hora no padrão documental
const formatarHora = (valorInput) => {
    if (!valorInput) return ""; // Retorna vazio se não houver horário
    // O split(":") quebra "13:34" em um array: ["13", "34"]
    const [hora, minuto] = valorInput.split(":");
    return `${hora}h e ${minuto}min`;
};

//Fomatar o texto para a data de nascimento
function formatarDataNasc() {
    const dataValor = gV('entnasc') //Pega o date AAAA-MM-DD
    if (!dataValor) return "";

    // Dividimos a string para evitar problemas de fuso horário
    const [ano, mes, dia] = dataValor.split('-');

    // Criamos uma lista de meses
    const meses = [
        "janeiro", "fevereiro", "março", "abril", "maio", "junho",
        "julho", "agosto", "setembro", "outubro", "novembro", "dezembro"
    ];

    // O mês no array começa em 0, então subtraímos 1 do mês vindo do input
    const mesExtenso = meses[parseInt(mes) - 1];

    return `nascido aos ${parseInt(dia)} dias do mês de ${mesExtenso} do ano ${ano}`;
}

function tipoDepoente() {
    depoente_tipo = document.querySelector('input[name="tipoDepoente_name"]:checked').value

    if (depoente_tipo === "militar") {

        document.getElementById("profissao").value = "Policial Militar";

        document.getElementById("selecionaPosto").innerHTML = `
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
    else if (depoente_tipo === "civil") {
        document.getElementById("profissao").value = "";

        document.getElementById("selecionaPosto").innerHTML = " ";
        //Nao precisa pois o conteudo já foi limpado
        //document.getElementById("selecionaPosto_selected").value = " ";

    }

}


//Função quando o botão Imprimir é clicado
function sbmt1() {

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
            qualidade: gV('ent_qualidade'),
            quadro: gV('quadro_depo'),
            posto: gV('selecionaPosto_selected'),
            estado_civil: document.querySelector('input[name="estc"]:checked').value,
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
            endereco: `${gV('entrua')}, nº ${gV('entnumero')}, bairro ${gV('entbairro')}, ${gV('entcomp')}, ${gV('entmun')} - ${gV('entest')}`

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

/* Função para converter os dias em dias por extenso */
function diaParaExtenso(dia) {
    const diasExtenso = [
        "", "primeiro", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove", "dez",
        "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezessete", "dezoito", "dezenove", "vinte",
        "vinte e um", "vinte e dois", "vinte e três", "vinte e quatro", "vinte e cinco", "vinte e seis", "vinte e sete", "vinte e oito", "vinte e nove", "trinta", "trinta e um"
    ];
    return diasExtenso[dia];
}

const dataOriginal = new Date();
const diaNum = dataOriginal.getDate();
const mesExtenso = new Intl.DateTimeFormat('pt-BR', { month: 'long' }).format(dataOriginal);
const ano = dataOriginal.getFullYear();

// Monta a frase: "Aos três dias do mês de fevereiro de 2026"
const dataPadraoOficial = `Aos ${diaParaExtenso(diaNum)} dias do mês de ${mesExtenso} de ${ano}`;

//Função para verificar seleção de advogado
function adv_presente() {
    adv_var = document.querySelector('input[name="ent_adv"]:checked').value

    if (adv_var === "sim") {

        document.getElementById("adv").innerHTML = `<br>
        <input id="nome_adv" type="text" size="30" placeholder="nome do defensor"></input><br>
        <input id="oab_adv" type="text" size="7" placeholder="nº OAB"></input>
        <input id="est_oab" type="text" size="2" placeholder="UF"></input>
            `
    }
    else if (adv_var === "nao") {
        document.getElementById("adv").innerHTML = " "
        advogado_resposta = "Parte não representada por advogado/defensor"

    }
}



/**
 * Função de Impressão e Geração do Termo de Oitiva Escrita
 */
function gerarPDF(d) {
    const dataOriginal = new Date();
    const diaNum = dataOriginal.getDate();
    const mesExtenso = new Intl.DateTimeFormat('pt-BR', { month: 'long' }).format(dataOriginal);
    const ano = dataOriginal.getFullYear();
    const horarioInicioFormatado = formatarHora(gV("entinicio"));
    const horarioFimFormatado = formatarHora(gV("entfim"));

    function diaParaExtenso(dia) {
        const dias = ["", "primeiro", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove", "dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezessete", "dezoito", "dezenove", "vinte", "vinte e um", "vinte e dois", "vinte e três", "vinte e quatro", "vinte e cinco", "vinte e seis", "vinte e sete", "vinte e oito", "vinte e nove", "trinta", "trinta e um"];
        return dias[dia];
    }
    const dataPadraoOficial = `Aos ${diaParaExtenso(diaNum)} dias do mês de ${mesExtenso} de ${ano}`;

    // 1. TÍTULO DINÂMICO CONFORME A QUALIDADE
    let tituloTermo = "TERMO DE INQUIRIÇÃO DE TESTEMUNHA";
    const qualidadeUpper = d.depoente.qualidade.toUpperCase();

    if (qualidadeUpper.includes("ACUSADO") || qualidadeUpper.includes("SINDICADO") || qualidadeUpper.includes("AUTOR") || qualidadeUpper.includes("INDICIADO")) {
        tituloTermo = `TERMO DE QUALIFICAÇÃO E INTERROGATÓRIO DE ${qualidadeUpper}`;
    } else if (qualidadeUpper.includes("VÍTIMA") || qualidadeUpper.includes("OFENDIDO")) {
        tituloTermo = `TERMO DE DECLARAÇÕES DE ${qualidadeUpper}`;
    }

    // 2. LÓGICA DO COMPROMISSO LEGAL (Lendo diretamente a Qualidade)
    const qualidadeDepoente = d.depoente.qualidade ? d.depoente.qualidade.toUpperCase() : "";
    let textoCompromisso = "";

    // Se a qualidade for TESTEMUNHA, presta o compromisso. Caso contrário, não presta.
    if (qualidadeDepoente.includes("TESTEMUNHA")) {
        textoCompromisso = "prestando o compromisso legal de dizer a verdade sob as penas da lei, e aos costumes nada disse";
    } else {
        textoCompromisso = "deixando de prestar o compromisso legal por lei facultado, e aos costumes nada disse";
    }

    // CAPTURA DOS DADOS DO ESCRIVÃO (SE HOUVER)
    const nomeEscrivao = gV("entnome_esc");
    const cpfEscrivao = gV("entcin_esc");
    const postoEscrivao = gV("ent_posto_esc");
    const temEscrivao = nomeEscrivao && nomeEscrivao.trim() !== "";

    // 3. LÓGICA DO ADVOGADO
    const temAdvogado = d.oitiva.advogado && d.oitiva.advogado.nome.trim() !== "";
    const textoAdvogado = temAdvogado
        ? `, assistido por seu advogado, Dr. ${d.oitiva.advogado.nome}, OAB nº ${d.oitiva.advogado.oab}/${d.oitiva.advogado.uf}`
        : "";

    // 4. TRATAMENTO DO TEXTO DO DEPOIMENTO
    // Converte as quebras de linha digitadas no textarea para parágrafos no documento
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
            /* CONFIGURAÇÕES DE PÁGINA PARA IMPRESSÃO A4 */
            @page {
                size: A4 portrait;
                margin: 1.5cm 1.5cm 1.5cm 2cm !important; /* Margem padrão documental */
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

            /* CABEÇALHO E BRASÕES */
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

            /* TÍTULO DO TERMO */
            .titulo-termo {
                text-align: center;
                margin-top: 15px;
                margin-bottom: 25px;
                font-size: 12pt;
                width: 100% !important;
            }

            /* CORPO DO TEXTO (OCUPA 100% DA LARGURA) */
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

            /* BLOCO DE ASSINATURAS */
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
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;${dataPadraoOficial}, nesta cidade de ${d.oitiva.cidade}, Estado do Paraná, às ${horarioInicioFormatado}, no(a) ${d.oitiva.local}, situado na ${d.oitiva.endereco}, onde se encontrava presente o Encarregado do(a) ${d.encarregado.procedimento} nº ${d.encarregado.num}, ${d.encarregado.posto} ${d.encarregado.nome}, CPF nº ${d.encarregado.cpf}${textoAdvogado}, compareceu o(a) ${d.depoente.qualidade.toLowerCase()}, Sr(a). <b>${d.depoente.nome}</b>, ${d.depoente.posto ? 'Posto/Graduação: ' + d.depoente.posto + ' (' + d.depoente.quadro + '), ' : ''}CPF nº ${d.depoente.cpf}, ${d.depoente.nasc}, natural de ${d.depoente.cidade_naturalidade}/${d.depoente.estado_naturalidade}, nacionalidade ${d.depoente.nacionalidade}, estado civil ${d.depoente.estado_civil}, profissão ${d.depoente.profissao} (em ${d.depoente.local_profissao}), filho(a) de ${d.depoente.mae}${d.depoente.pai ? ' e de ' + d.depoente.pai : ''}, residente e domiciliado na ${d.depoente.endereco}, o(a) qual, ${textoCompromisso}, perguntado(a) sobre os fatos constantes no presente procedimento, respondeu e declarou:<br><br>

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

    // DISPARO AUTOMÁTICO DA IMPRESSÃO ASSIM QUE O DOCUMENTO/IMAGENS CARREGAREM
    novaJanela.onload = function() {
        novaJanela.focus();
        novaJanela.print();
    };
}


// Função para calcular idade automaticamente
function pegaidade() {
    const nasc = document.getElementById('entnasc').value;
    if (nasc) {
        const idade = moment().diff(moment(nasc), 'years');
        document.getElementById('entidade').value = idade;
    }
}

// Função para exibir o campo do escrivão
function insereEscriba() {
    const div = document.getElementById('abre_escriba');
    div.style.display = (div.style.display === 'none') ? 'block' : 'none';
}

// Máscara de CPF básica
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