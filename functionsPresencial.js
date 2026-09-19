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
 * Função de Desenho pelo HTML
 */
function gerarPDF(d) {
    // 1. Prepara a data por extenso (Lógica que já validamos)
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


    //Lógica do Advogado Presente
    // Verificamos se existe o nome do advogado e se não está vazio
    const temAdvogado = d.oitiva.advogado && d.oitiva.advogado.nome.trim() !== "";
    const textoAdvogado = temAdvogado
        ? `, com a presença do advogado do acusado, Dr. ${d.oitiva.advogado.nome}, OAB nº ${d.oitiva.advogado.oab}/${d.oitiva.advogado.uf}`
        : ", parte não representada por advogado";

    // Abre a Nova Aba
    const novaJanela = window.open('', '_blank');

    // Escreve o conteúdo (Injetando o cabeçalho corrigido)
    novaJanela.document.documentElement.innerHTML = (`
       <html>

<head>
    <link rel="stylesheet" href="style_impress_pdf.css">
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
        <span style="font-weight: bold; text-decoration: underline;">TERMO DE INQUIRIÇÃO DE TESTEMUNHA</span>
        <br>
        <span>(1º Ten. QOPM Fulano de Tal/RG 1222222222222)</span>
    </div>

    <div class="conteudo">
        <div class="paragrafo">
            ${dataPadraoOficial}, nesta cidade de ${d.oitiva.cidade}, Estado do Paraná, Bairro Centro, sito a
            ${d.oitiva.endereco}, na(o) ${d.oitiva.local}, onde se encontrava presente o Encarregado, ${d.encarregado.posto} ${d.encarregado.nome}, CPF inscrito sob nº ${d.encarregado.cpf}, Encarregado do ${d.encarregado.procedimento} nº ${d.encarregado.num} ${textoAdvogado}, às <b>${horarioInicioFormatado} </b>, compareceu o  Sr(a). ${d.depoente.posto} ${d.depoente.quadro} / CPF ${d.depoente.cpf}, filho de ${d.depoente.mae}             
            ${d.depoente.pai ? ` e de ${d.depoente.pai}` : ''}, natural de ${d.depoente.cidade_naturalidade} - ${d.depoente.estado_naturalidade}, estado civil ${d.depoente.estado_civil}, ${d.depoente.nasc}, nacionalidade ${d.depoente.nacionalidade}, de profissão ${d.depoente.profissao}, em ${d.depoente.local_profissao}, residente na ${d.depoente.endereco}, sem qualquer tipo de constrangimento, coação física ou moral, e na qualidade de ${d.depoente.qualidade}, aos costumes nada disse, 
            
            COMPROMISSOOOOOOOOOOOOO
            prestando o compromisso de dizer a verdade
            prometeu dizer a verdade sobre os fatos que deram origem ao presente FATD, perguntado sobre a situação a
            respeito dos fatos geradores do presente procedimento, passou a declarar: Que compareceu...
        </div>
    </div>

    <div class="fecho">

        E, como nada mais disse e nem foi perguntado pelo sindicante, deu-se por encerrado o presente termo às <b> ${horarioFimFormatado} </b>, 
        depois de ter sido lido achado conforme, vai devidamente assinado.
    </div>
    <br>
    <div class="assinaturas">
        <div class="assinantes">
            1º Ten. QOPM Bagual, RG: 1111111111-1,
            <br>
            <b>Encarregado do FATD.</b>
        </div>
        <br>
        <div class="assinantes">
            1º Ten. QOPM Bagual/RG 111111111-1,
            <br>
            <b>Testemunha</b>.
        </div>
        <br>
        ${temAdvogado ? `<div class="assinantes"> ${d.oitiva.advogado.nome}, OAB ${d.oitiva.advogado.oab + '/' + d.oitiva.advogado.uf}<br>
        <strong>Advogado.</strong>
    </div>` : ''}

    <br>

    </div>
    </div>
</body>
<script>
    /*     // Dispara a impressão assim que tudo (incluindo imagens e CSS) carregar
        window.onload = function () {
            setTimeout(() => {
                window.print();
            }, 500);
        }; */
</script>

</html>
    `);

    novaJanela.document.close();
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



