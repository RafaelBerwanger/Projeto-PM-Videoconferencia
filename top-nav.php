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
            <span><?= e($_SESSION['usuario_nome'] ?? 'Policial Militar') ?></span>
        </div>
    </div>
</div>

<!-- LÓGICA GLOBAL DA NAVBAR E DO RELÓGIO -->
<script>
function alternarSidebar() {
    const sidebar = document.getElementById('sidebar_menu') || document.querySelector('.sidebar');
    const iconeToggle = document.getElementById('btn-toggle-sidebar');

    if (sidebar) {
        sidebar.classList.toggle('hidden');

        if (iconeToggle) {
            const oculto = sidebar.classList.contains('hidden');
            iconeToggle.classList.toggle('fa-chevron-left', !oculto);
            iconeToggle.classList.toggle('fa-chevron-right', oculto);
            iconeToggle.title = oculto ? 'Exibir Menu' : 'Ocultar Menu';
        }
    }
}

// Relógio único do sistema, sempre no fuso de Brasília
function atualizarRelogio() {
    const elementoRelogio = document.getElementById('relogio-brasilia');
    if (!elementoRelogio) return;

    const texto = new Date().toLocaleString('pt-BR', {
        timeZone: 'America/Sao_Paulo',
        weekday: 'short',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    elementoRelogio.textContent = texto.replace('.', '');
}

document.addEventListener('DOMContentLoaded', () => {
    // Auto-collapse do menu em mobile
    const sidebar = document.getElementById('sidebar_menu') || document.querySelector('.sidebar');
    if (window.innerWidth <= 768 && sidebar) {
        sidebar.classList.add('hidden');
    }

    atualizarRelogio();
    if (!window.intervaloRelogioPMPR) {
        window.intervaloRelogioPMPR = setInterval(atualizarRelogio, 1000);
    }
});
</script>
