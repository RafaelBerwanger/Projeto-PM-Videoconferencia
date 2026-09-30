<!-- top-nav.php -->
<div class="top-nav">
    <div class="top-nav-left">
        <!-- <i class="fa-solid fa-bars"></i> -->
        <!-- <a href="#">Administração</a>
        <a href="#">Boletins</a>
        <a href="#">Sistemas</a> -->
    </div>

    <!-- DATA E HORA EM TEMPO REAL -->
    <div class="top-nav-center" style="color: #64748b; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 6px; margin-right: auto; margin-left: 15px;">
        <i class="fa-regular fa-clock"></i>
        <span id="relogio-brasilia">Carregando horário...</span>
    </div>

    <div class="top-nav-right">
        <span class="badge-email"><i class="fa-regular fa-envelope"></i> Emails</span>
        <i class="fa-regular fa-bell" style="color:#666;"></i>
        <div class="user-profile">
            <div class="user-avatar"><i class="fa-solid fa-user"></i></div>
            <span><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Policial Militar') ?></span>
        </div>
    </div>
</div>