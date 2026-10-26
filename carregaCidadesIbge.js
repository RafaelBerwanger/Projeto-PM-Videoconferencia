document.addEventListener("DOMContentLoaded", async () => {
    // 1. Carrega a lista de estados UMA vez e preenche todos os selects (PR como padrão)
    const estados = await obterEstadosIBGE();
    preencherEstados("est_naturalidade", estados, "PR");
    preencherEstados("entest", estados, "PR");
    preencherEstados("est_oitiva", estados, "PR");

    // 2. Dispara a carga de cidades para os campos que agora são "PR" por padrão
    buscarCidadesIBGE("PR", "cidade_naturalidade"); // Cidade da Naturalidade
    buscarCidadesIBGE("PR", "entmun");              // Cidade do Endereço
    buscarCidadesIBGE("PR", "cidade", "CASCAVEL");  // Cidade da Oitiva
});

// --- BUSCA OS ESTADOS NA API DO IBGE ---
const obterEstadosIBGE = async () => {
    try {
        const response = await fetch("https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome");
        if (!response.ok) throw new Error("HTTP " + response.status);
        return await response.json();
    } catch (err) {
        console.error("Erro ao buscar estados:", err);
        return [];
    }
};

// --- PREENCHE UM SELECT DE ESTADOS (mantém o conteúdo atual se a API falhar) ---
const preencherEstados = (idSelect, estados, ufPadrao = "") => {
    const selectEstado = document.getElementById(idSelect);
    if (!selectEstado || estados.length === 0) return;

    selectEstado.innerHTML = '<option value="">Selecione o Estado</option>';
    estados.forEach(uf => {
        const option = document.createElement("option");
        option.value = uf.sigla;
        option.textContent = uf.nome;
        option.selected = (uf.sigla === ufPadrao);
        selectEstado.appendChild(option);
    });
};

// --- CARREGA AS CIDADES DE UM ESTADO ---
const buscarCidadesIBGE = async (uf, idSelect, valorPreSelecionado = "") => {
    const selectAlvo = document.getElementById(idSelect);
    if (!selectAlvo) return;

    if (!uf) {
        selectAlvo.innerHTML = '<option value="">Selecione o estado primeiro</option>';
        selectAlvo.disabled = true;
        return;
    }

    selectAlvo.disabled = true;
    selectAlvo.innerHTML = '<option value="">Carregando cidades...</option>';

    try {
        const response = await fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${encodeURIComponent(uf)}/municipios?orderBy=nome`);
        if (!response.ok) throw new Error("HTTP " + response.status);
        const cidades = await response.json();

        selectAlvo.innerHTML = '<option value="">Selecione a cidade</option>';
        cidades.forEach(cidade => {
            const option = document.createElement("option");
            option.value = cidade.nome;
            option.textContent = cidade.nome;

            if (valorPreSelecionado && cidade.nome.toUpperCase() === valorPreSelecionado.toUpperCase()) {
                option.selected = true;
            }
            selectAlvo.appendChild(option);
        });
        selectAlvo.disabled = false;
    } catch (err) {
        console.error("Erro ao buscar cidades:", err);
        selectAlvo.innerHTML = '<option value="">Erro ao carregar cidades</option>';
    }
};
