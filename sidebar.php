<!-- BARRA LATERAL DA INTRANET PMPR (sidebar.php) -->
<?php
// Define a página atual para destacar o item ativo no menu dinamicamente
$pagina_atual = basename($_SERVER['PHP_SELF']);
$perfil_usuario = $_SESSION['usuario_perfil'] ?? 'usuario';
?>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="img/logo.png" alt="Logo PMPR" class="sidebar-logo">
        <span>Ajuda do Encarregado</span>
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

    <!-- ITEM VISÍVEL APENAS SE O USUÁRIO FOR ADMIN -->
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

    <a href="logout.php" class="menu-item logout-item">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Sair</span>
    </a>
</div>