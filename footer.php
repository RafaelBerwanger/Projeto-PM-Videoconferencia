<!-- RODAPÉ MODULARIZADO (footer.php) -->
<footer>
    <div id="rodape" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap;">
        <span style="color: #64748b;">Desenvolvido por Cad 1º PM. Rafael A. Berwanger — EsFO/APMG</span>

        <a href="https://wa.me/5545984226612?text=Ol%C3%A1%2C%20tenho%20uma%20d%C3%BAvida%20sobre%20o%20Sistema%20de%20Oitivas%20da%20PMPR"
            target="_blank"
            rel="noopener noreferrer"
            title="Clique para falar direto no WhatsApp"
            class="link-whatsapp-dev">
            <!-- Ícone do WhatsApp à esquerda, maior e destacado -->
            <i class="fa-brands fa-whatsapp icono-wpp"></i>
            Feedback / Suporte
        </a>
    </div>
</footer>

<style>
    /* Estilização exclusiva do link do desenvolvedor no Rodapé */
    .link-whatsapp-dev {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #1e293b;
        text-decoration: none;
        font-weight: 700;
        font-size: 13.5px;
        padding: 4px 10px;
        background-color: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.3);
        border-radius: 20px;
        transition: all 0.2s ease-in-out;
    }

    /* Ícone do WhatsApp maior e chamativo */
    .link-whatsapp-dev .icono-wpp {
        font-size: 18px;
        color: #22c55e;
        transition: transform 0.2s ease;
    }

    /* Efeito ao passar o mouse */
    .link-whatsapp-dev:hover {
        background-color: #22c55e;
        color: #ffffff;
        border-color: #16a34a;
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
        transform: translateY(-1px);
    }

    .link-whatsapp-dev:hover .icono-wpp {
        color: #ffffff;
        transform: scale(1.15);
    }
</style>