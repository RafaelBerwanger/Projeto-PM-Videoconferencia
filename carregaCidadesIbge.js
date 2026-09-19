document.addEventListener("DOMContentLoaded", async () => {
    // 1. Carrega estados e já define PR como padrão nos campos desejados
    // Usamos await para garantir que os estados existam antes de buscar as cidades
    await carregarEstadosIBGE("est_naturalidade", "PR");
    await carregarEstadosIBGE("entest", "PR");
    
    // 2. Dispara a carga de cidades para os campos que agora são "PR" por padrão
    buscarCidadesIBGE("PR", "cidade_naturalidade"); // Cidade da Naturalidade
    buscarCidadesIBGE("PR", "entmun");              // Cidade do Endereço
    
    // 3. Mantém a carga original da Oitiva (PR/Cascavel)
    buscarCidadesIBGE("PR", "cidade", "CASCAVEL");
});

// --- FUNÇÃO PARA CARREGAR OS ESTADOS (Adaptada para aceitar padrão) ---
const carregarEstadosIBGE = async (idSelect, ufPadrao = "") => {
    const selectEstado = document.getElementById(idSelect);
    if (!selectEstado) return;

    try {
        const response = await fetch("https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome");
        const estados = await response.json();

        selectEstado.innerHTML = '<option value="">Selecione o Estado</option>';
        estados.forEach(uf => {
            const option = document.createElement("option");
            option.value = uf.sigla; 
            option.textContent = uf.nome; 
            
            // Se a sigla for igual ao padrão (ex: "PR"), marca como selecionado
            if (uf.sigla === ufPadrao) {
                option.selected = true;
            }
            
            selectEstado.appendChild(option);
        });
    } catch (err) {
        console.error("Erro ao buscar estados:", err);
    }
};

// --- FUNÇÃO PARA CARREGAR AS CIDADES (Mantida) ---
const buscarCidadesIBGE = async (uf, idSelect, valorPreSelecionado = "") => {
    const selectAlvo = document.getElementById(idSelect);
    if (!uf || !selectAlvo) return;

    selectAlvo.disabled = true;
    selectAlvo.innerHTML = '<option value="">Carregando cidades...</option>';

    try {
        const response = await fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${uf}/municipios?orderBy=nome`);
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
        selectAlvo.innerHTML = '<option>Erro ao carregar</option>';
    }
};