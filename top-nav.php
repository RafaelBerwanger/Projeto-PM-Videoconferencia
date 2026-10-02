<!-- NAVBAR SUPERIOR DA INTRANET PMPR (top-nav.php) -->
<style>
/* Regras para manter a Navbar fixa no topo */
.top-nav {
    position: sticky !important;
    top: 0 !important;
    z-index: 9999 !important;
    background-color: #ffffff;
    height: 50px;
    border-bottom: 1px solid #dddddd;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 15px;
    width: 100%;
    box-sizing: border-box;
}

/* Regras de Responsividade para Celulares */
@media (max-width: 768px) {
    .top-nav {
        padding: 0 8px !important;
        height: 45px !important;
    }

    .top-nav-center {
        font-size: 11px !important;
        gap: 3px !important;
        margin-left: 5px !important;
    }

    .badge-email {
        display: none !important;
    }

    .user-profile span {
        font-size: 11px !important;
        max-width: 85px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-avatar {
        width: 26px !important;
        height: 26px !important;
        font-size: 11px !important;
    }

    .top-nav-right {
        gap: 8px !important;
    }
}
</style>

<div class="top-nav">
    <div class="top-nav-left" style="display: flex; align-items: center;">
        <!-- Ícone para reabrir/alternar o menu -->
        <i class="fa-solid fa-bars" id="btn-top-bars" onclick="alternarSidebar()" title="Exibir/Ocultar Menu" style="font-size: 18px; cursor: pointer; color: #475569; padding: 5px;"></i>
    </div>

    <!-- DATA E HORA EM TEMPO REAL -->
    <div class="top-nav-center" style="color: #64748b; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 6px; margin-right: auto; margin-left: 10px;">
        <i class="fa-regular fa-clock"></i>
        <span id="relogio-brasilia">Carregando horário...</span>
    </div>

    <div class="top-nav-right" style="display: flex; align-items: center; gap: 12px;">
        <span class="badge-email"><i class="fa-regular fa-envelope"></i> Emails</span>
        <i class="fa-regular fa-bell" style="color:#666; font-size: 14px;"></i>
        <div class="user-profile" style="display: flex; align-items: center; gap: 6px;">
            <div class="user-avatar"><i class="fa-solid fa-user"></i></div>
            <span><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Policial Militar') ?></span>
        </div>
    </div>
</div>

<!-- LÓGICA GLOBAL DA NAVBAR E PROTEÇÃO DO RELÓGIO -->
<script>
if (window.intervaloRelogioPMPR) {
    clearInterval(window.intervaloRelogioPMPR);
}

function alternarSidebar() {
    const sidebar = document.getElementById('sidebar_menu') || document.querySelector('.sidebar');
    const iconeToggle = document.getElementById('btn-toggle-sidebar');

    if (sidebar) {
        sidebar.classList.toggle('hidden');

        if (iconeToggle) {
            if (sidebar.classList.contains('hidden')) {
                iconeToggle.className = 'fa-solid fa-chevron-right';
                iconeToggle.title = 'Exibir Menu';
            } else {
                iconeToggle.className = 'fa-solid fa-chevron-left';
                iconeToggle.title = 'Ocultar Menu';
            }
        }
    }
}

// Bloqueio contra sobrescrita de scripts externos
window.bloqueioAtualizacao = false;

function atualizarRelogio() {
    const elementoRelogio = document.getElementById('relogio-brasilia');
    if (!elementoRelogio) return;

    const agora = new Date();
    const diasSemana = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sáb'];
    const diaSemana = diasSemana[agora.getDay()];

    const dia = String(agora.getDate()).padStart(2, '0');
    const mes = String(agora.getMonth() + 1).padStart(2, '0');
    const ano = agora.getFullYear();

    const horas = String(agora.getHours()).padStart(2, '0');
    const minutos = String(agora.getMinutes()).padStart(2, '0');

    const textoFormatado = `${diaSemana}, ${dia}/${mes}/${ano}, ${horas}:${minutos}`;

    // Atualiza apenas se for diferente para evitar loop
    if (elementoRelogio.innerText !== textoFormatado) {
        window.bloqueioAtualizacao = true;
        elementoRelogio.innerText = textoFormatado;
        window.bloqueioAtualizacao = false;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // 1. Auto-collapse do menu em mobile
    const sidebar = document.getElementById('sidebar_menu') || document.querySelector('.sidebar');
    if (window.innerWidth <= 768 && sidebar) {
        sidebar.classList.add('hidden');
    }

    // 2. Proteção via Observer para garantir que nenhum outro script altere a data
    const elementoRelogio = document.getElementById('relogio-brasilia');
    if (elementoRelogio) {
        const observer = new MutationObserver(() => {
            if (!window.bloqueioAtualizacao) {
                atualizarRelogio();
            }
        });
        observer.observe(elementoRelogio, { childList: true, characterData: true, subtree: true });
    }

    // 3. Atualização contínua a cada minuto
    atualizarRelogio();
    window.intervaloRelogioPMPR = setInterval(atualizarRelogio, 60000);
});
</script>