<!-- NAVBAR SUPERIOR DA INTRANET PMPR (top-nav.php) -->
<style>
/* Regras para manter a Navbar fixa no topo */
.top-nav {
    position: sticky !important;
    top: 0 !important;
    z-index: 1000 !important;
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

    /* Reduz a fonte do relógio e ícone no celular */
    .top-nav-center {
        font-size: 11px !important;
        gap: 4px !important;
        margin-left: 5px !important;
    }

    /* Oculta o selo de e-mails em telas pequenas */
    .badge-email {
        display: none !important;
    }

    /* Adequa o nome e avatar do policial */
    .user-profile span {
        font-size: 11px !important;
        max-width: 85px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis; /* Adiciona '...' se o nome for longo */
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

<!-- LÓGICA GLOBAL DE ALTERNÂNCIA DA SIDEBAR E RELÓGIO -->
<script>
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

// Relógio em tempo real (Horário de Brasília)
function atualizarRelogio() {
    const elementoRelogio = document.getElementById('relogio-brasilia');
    if (!elementoRelogio) return;

    const agora = new Date();
    const opcoesData = { 
        timeZone: 'America/Sao_Paulo',
        weekday: 'short', 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit' 
    };

    const dataFormatada = new Intl.DateTimeFormat('pt-BR', opcoesData).format(agora);
    elementoRelogio.innerText = dataFormatada.replace('.', '');
}

document.addEventListener('DOMContentLoaded', () => {
    atualizarRelogio();
    setInterval(atualizarRelogio, 1000);
});
</script>