const estadoSelect = document.getElementById("estado");
const cidadeSelect = document.getElementById("cidade");

// Carregar estados
fetch("https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome")
  .then(res => res.json())
  .then(estados => {
    estados.forEach(estado => {
      estadoSelect.innerHTML += `
        <option value="${estado.sigla}">${estado.nome}</option>
      `;
    });
  });

// Quando muda o estado, carrega cidades
estadoSelect.addEventListener("change", () => {
  const uf = estadoSelect.value;

  if (!uf) {
    cidadeSelect.disabled = true;
    cidadeSelect.innerHTML = "<option>Selecione a cidade</option>";
    return;
  }

  cidadeSelect.disabled = true;
  cidadeSelect.innerHTML = "<option>Carregando...</option>";

  fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${uf}/municipios`)
    .then(res => res.json())
    .then(cidades => {
      cidadeSelect.innerHTML = "<option value=''>Selecione a cidade</option>";
      cidades.forEach(cidade => {
        cidadeSelect.innerHTML += `
          <option value="${cidade.nome}">${cidade.nome}</option>
        `;
      });
      cidadeSelect.disabled = false;
    });
});
