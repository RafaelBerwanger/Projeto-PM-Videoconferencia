document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("checklistModal");
  const openModal = document.getElementById("openModal");
  const closeModal = document.getElementById("closeModal");
  const addItem = document.getElementById("addItem");
  const newItem = document.getElementById("newItem");
  const checklist = document.getElementById("checklist");

  // 🔹 Abre o modal (impede o clique de fechar imediatamente)
  openModal.addEventListener("click", (event) => {
    event.stopPropagation();
    modal.style.display = "block";
  });

  // 🔹 Fecha o modal ao clicar no X
  closeModal.addEventListener("click", () => {
    modal.style.display = "none";
  });

  // 🔹 Fecha o modal apenas se clicar fora do conteúdo
  window.addEventListener("click", (event) => {
    if (event.target === modal) {
      modal.style.display = "none";
    }
  });

  // 🔹 Adiciona nova tarefa
  addItem.addEventListener("click", () => {
    const texto = newItem.value.trim();
    if (texto !== "") {
      const div = document.createElement("div");
      div.classList.add("checklist-item");
      div.innerHTML = `<input type="checkbox"> ${texto}`;
      checklist.appendChild(div);
      newItem.value = "";
    }
  });
});
