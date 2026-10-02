<!-- BARRA LATERAL DA INTRANET PMPR (sidebar.php) -->
<?php
$pagina_atual = basename($_SERVER['PHP_SELF']);
$perfil_usuario = $_SESSION['usuario_perfil'] ?? 'usuario';
?>

<!-- Estilos específicos para o botão mobile dentro da sidebar -->
<style>
/* Por padrão (Computador), oculta o botão inferior mobile */
.mobile-toggle-footer {
    display: none;
}

/* Regras exclusivas para Celular (telas menores que 768px) */
@media (max-width: 768px) {
    /* Esconde a setinha pequena do topo no celular */
    .btn-toggle-desktop {
        display: none !important;
    }

    /* Exibe o botão largo de recolher na parte inferior no celular */
    .mobile-toggle-footer {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 12px 15px;
        margin: 15px 10px 5px 10px;
        background-color: #1e293b;
        color: #94a3b8;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        border: 1px solid #334155;
    }

    .mobile-toggle-footer:active {
        background-color: #334155;
        color: #ffffff;
    }
}
</style>

<div class="sidebar" id="sidebar_menu">
    <div class="sidebar-header" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 15px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <img src="img/logo.png" alt="Logo PMPR" class="sidebar-logo">
            <span style="font-size: 14px; font-weight: bold;">Ajuda do Encarregado</span>
        </div>
        
        <!-- Ícone para fechar no DESKTOP (fica no topo) -->
        <i class="fa-solid fa-chevron-left btn-toggle-desktop" id="btn-toggle-sidebar" onclick="alternarSidebar()" title="Ocultar Menu" style="cursor: pointer; font-size: 16px; color: #a0aec0; padding: 5px;"></i>
    </div>

    <div class="sidebar-title">NAVEGAÇÃO</div>

    <a href="index.php" class="menu-item <?= ($pagina_atual === 'index.php') ? 'active' : '' ?>">
        <i class="fa-solid fa-house"></i>
        <span>Início</span>
    </a>

    <div class="sidebar-title">OITIVAS</div>

    <a href="audiovisual.php" class="menu-item <?= ($pagina_atual === 'audiovisual.php') ? 'active' : '' ?>">
        <i class="fa-solid fa-video"></i>
        <span>Oitivas Audiovisuais</span>
    </a>
    <a href="presencial.php" class="menu-item <?= ($pagina_atual === 'presencial.php') ? 'active' : '' ?>">
        <i class="fa-solid fa-file-pen"></i>
        <span>Oitivas Escritas</span>
    </a>

    <?php if ($perfil_usuario === 'admin'): ?>
        <div class="sidebar-title" style="margin-top: 15px; color: #f59e0b;">ADMINISTRAÇÃO</div>
        <a href="admin_usuarios.php" class="menu-item <?= ($pagina_atual === 'admin_usuarios.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-user-gear" style="color: #f59e0b;"></i>
            <span>Gestão de Acessos</span>
        </a>
    <?php endif; ?>

    <a href="meu_perfil.php" class="menu-item <?= ($pagina_atual === 'meu_perfil.php') ? 'active' : '' ?>">
        <i class="fa-solid fa-user-pen"></i>
        <span>Meu Perfil</span>
    </a>

    <!-- Botão de recolher no CELULAR (fica na parte inferior do menu) -->
    <div class="mobile-toggle-footer" onclick="alternarSidebar()">
        <i class="fa-solid fa-chevron-left"></i>
        <span>Recolher Menu</span>
    </div>

    <a href="logout.php" class="menu-item logout-item">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Sair</span>
    </a>
</div>