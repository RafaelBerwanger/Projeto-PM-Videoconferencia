// ==========================================================================
// OITIVA POR VIDEOCONFERÊNCIA COM GRAVAÇÃO INTEGRADA
// --------------------------------------------------------------------------
// - Chamada: MiroTalk P2P (software livre, sem login, sem limite de tempo)
//   embutido na própria página. Áudio e vídeo vão direto entre os
//   participantes (ponto a ponto).
// - Gravação: dois botões (Gravar / Encerrar). Como a chamada está nesta
//   mesma aba, o navegador só pede "Permitir gravar esta guia". Durante a
//   gravação a chamada vira uma janela flutuante fixa e o vídeo é recortado
//   nela: o usuário pode rolar e preencher o formulário sem aparecer no vídeo.
// - Ao encerrar: o arquivo é baixado para o computador e carregado no
//   relatório (data, início, término, duração e HASH preenchidos).
// ==========================================================================

const Oitiva = {
    sala: '',
    captura: null,       // stream da aba
    microfone: null,
    audioCtx: null,
    gravador: null,
    pedacos: [],
    tipoArquivo: '',
    extensao: 'webm',
    nomeArquivo: '',
    inicio: null,
    timer: null,
    gravando: false
};

const dominioSala = () => (window.CONFIG_OITIVA && window.CONFIG_OITIVA.dominio) || 'p2p.mirotalk.com';

// ---------- utilidades ----------
const doisDigitos = n => String(n).padStart(2, '0');
const horaHHMM = d => `${doisDigitos(d.getHours())}:${doisDigitos(d.getMinutes())}`;
const dataISO = d => `${d.getFullYear()}-${doisDigitos(d.getMonth() + 1)}-${doisDigitos(d.getDate())}`;
const tempoDecorrido = () => Oitiva.inicio ? Math.floor((Date.now() - Oitiva.inicio.getTime()) / 1000) : 0;
const formatarDuracao = s => `${doisDigitos(Math.floor(s / 3600))}:${doisDigitos(Math.floor(s % 3600 / 60))}:${doisDigitos(s % 60)}`;

function statusOitiva(msg, tipo = 'info') {
    const el = document.getElementById('oitiva_status');
    if (!el) return;
    const cores = { info: '#475569', ok: '#15803d', alerta: '#b45309', erro: '#b91c1c' };
    el.style.color = cores[tipo] || cores.info;
    el.innerHTML = msg;
}

function escaparTexto(t) {
    const div = document.createElement('div');
    div.textContent = t;
    return div.innerHTML;
}

// Nome de sala difícil de adivinhar (quem tiver o link entra na sala)
function gerarNomeSala() {
    const bytes = crypto.getRandomValues(new Uint8Array(10));
    const aleatorio = Array.from(bytes, b => b.toString(36).padStart(2, '0')).join('').slice(0, 14);
    return `pmpr-oitiva-${aleatorio}`;
}

const linkDepoente = () => `https://${dominioSala()}/join?room=${encodeURIComponent(Oitiva.sala)}`;

function nomeEncarregado() {
    const posto = document.getElementById('ent_posto')?.value || '';
    const nome = document.getElementById('entnome_enc')?.value || 'Encarregado';
    return `${posto} ${nome} (Encarregado)`.trim();
}

// ---------- SALA ----------
function iniciarSalaOitiva() {
    if (Oitiva.gravando) return;
    Oitiva.sala = gerarNomeSala();

    const params = new URLSearchParams({
        room: Oitiva.sala,
        name: nomeEncarregado(),
        audio: '1', video: '1', screen: '0', chat: '0', hide: '0', notify: '0',
        noiseSuppression: '1', duration: 'unlimited'
    });
    const iframe = document.getElementById('oitiva_iframe');
    iframe.src = `https://${dominioSala()}/join?${params}`;

    document.getElementById('oitiva_sala').style.display = 'block';
    document.getElementById('oitiva_link').value = linkDepoente();
    document.getElementById('btn_oitiva_sala').innerHTML = '<i class="fa-solid fa-rotate"></i> Nova sala';
    document.getElementById('btn_oitiva_gravar').disabled = false;

    statusOitiva('<i class="fa-solid fa-circle-check"></i> Sala aberta. Envie o link ao depoente; quando ele entrar, clique em <b>Gravar</b>.', 'ok');
}

function copiarLinkSala() {
    const campo = document.getElementById('oitiva_link');
    navigator.clipboard.writeText(campo.value)
        .then(() => statusOitiva('<i class="fa-solid fa-copy"></i> Link copiado.', 'ok'))
        .catch(() => { campo.select(); document.execCommand('copy'); });
}

function enviarLinkWhatsapp() {
    const proc = document.getElementById('ent_proc')?.value || '';
    const num = document.getElementById('ent_num_proc')?.value || '';
    const texto = `Olá. Você está sendo convidado(a) para oitiva por videoconferência referente ao procedimento ${proc} nº ${num}. No horário combinado, abra este link no navegador do celular ou computador e permita o uso da câmera e do microfone: ${linkDepoente()}`;
    window.open('https://wa.me/?text=' + encodeURIComponent(texto), '_blank', 'noopener');
}

// ---------- GRAVAÇÃO ----------
function escolherFormatoGravacao() {
    // MP4 (H.264/AAC) é o formato mais aceito; WebM quando o navegador não grava MP4
    const opcoes = [
        ['video/mp4;codecs=avc1.42E01E,mp4a.40.2', 'mp4'],
        ['video/mp4', 'mp4'],
        ['video/webm;codecs=vp9,opus', 'webm'],
        ['video/webm;codecs=vp8,opus', 'webm'],
        ['video/webm', 'webm']
    ];
    return opcoes.find(([tipo]) => MediaRecorder.isTypeSupported(tipo)) || ['', 'webm'];
}

async function gravarOitiva() {
    if (Oitiva.gravando) return;
    if (!navigator.mediaDevices?.getDisplayMedia || !window.MediaRecorder) {
        statusOitiva('Este navegador não permite gravar. Use o Google Chrome ou o Microsoft Edge no computador.', 'erro');
        return;
    }

    // A chamada passa a flutuar fixa na tela (fica sempre visível para a gravação)
    const painel = document.getElementById('oitiva_painel');
    painel.classList.add('flutuante');

    try {
        // Chamada embutida nesta aba: o navegador oferece gravar a própria guia
        Oitiva.captura = await navigator.mediaDevices.getDisplayMedia({
            video: { frameRate: { ideal: 24, max: 30 } },
            audio: { suppressLocalAudioPlayback: false },
            preferCurrentTab: true,
            selfBrowserSurface: 'include',
            surfaceSwitching: 'exclude',
            systemAudio: 'exclude'
        });
    } catch (e) {
        painel.classList.remove('flutuante');
        statusOitiva('Gravação não iniciada: é preciso clicar em <b>Permitir</b> na janela do navegador.', 'alerta');
        return;
    }

    const trilhaVideo = Oitiva.captura.getVideoTracks()[0];

    // Recorta o vídeo para mostrar apenas a chamada (Chrome/Edge); nos demais grava a guia inteira
    if (window.CropTarget && trilhaVideo.cropTo) {
        try {
            const alvo = await CropTarget.fromElement(document.getElementById('oitiva_iframe'));
            await trilhaVideo.cropTo(alvo);
        } catch (e) {
            console.warn('Recorte da gravação indisponível:', e);
        }
    }

    try {
        Oitiva.microfone = await navigator.mediaDevices.getUserMedia({ audio: { echoCancellation: true, noiseSuppression: true } });
    } catch (e) {
        Oitiva.microfone = null;
    }

    // Mistura o áudio da guia (voz do depoente) com o microfone do Encarregado
    Oitiva.audioCtx = new AudioContext();
    const destino = Oitiva.audioCtx.createMediaStreamDestination();
    const audioGuia = Oitiva.captura.getAudioTracks()[0];
    if (audioGuia) Oitiva.audioCtx.createMediaStreamSource(new MediaStream([audioGuia])).connect(destino);
    if (Oitiva.microfone) Oitiva.audioCtx.createMediaStreamSource(Oitiva.microfone).connect(destino);

    const streamFinal = new MediaStream([trilhaVideo, ...destino.stream.getAudioTracks()]);

    [Oitiva.tipoArquivo, Oitiva.extensao] = escolherFormatoGravacao();
    const opcoes = { videoBitsPerSecond: 1500000, audioBitsPerSecond: 128000 };
    if (Oitiva.tipoArquivo) opcoes.mimeType = Oitiva.tipoArquivo;

    Oitiva.pedacos = [];
    Oitiva.gravador = new MediaRecorder(streamFinal, opcoes);
    Oitiva.gravador.ondataavailable = ev => { if (ev.data && ev.data.size) Oitiva.pedacos.push(ev.data); };
    Oitiva.gravador.onerror = ev => statusOitiva('Erro na gravação: ' + (ev.error?.message || 'desconhecido') + '. Clique em Encerrar.', 'erro');

    // Se o usuário clicar em "Parar compartilhamento" na barra do navegador
    trilhaVideo.addEventListener('ended', () => { if (Oitiva.gravando) encerrarOitiva(); });

    const inicio = new Date();
    Oitiva.nomeArquivo = `Oitiva_${(document.getElementById('entnome')?.value || 'depoente').trim().replace(/[^\p{L}\p{N}]+/gu, '_')}_${dataISO(inicio)}_${horaHHMM(inicio).replace(':', 'h')}.${Oitiva.extensao}`;
    Oitiva.gravador.start(1000);
    Oitiva.inicio = inicio;
    Oitiva.gravando = true;

    // Dados do relatório já conhecidos no início
    document.getElementById('datagrav').value = dataISO(inicio);
    document.getElementById('entinicio').value = horaHHMM(inicio);
    const presenca = document.getElementById('ent_presenca');
    if (presenca) presenca.value = 'nao'; // depoente ouvido por videoconferência

    document.getElementById('oitiva_rec').style.display = 'inline-flex';
    Oitiva.timer = setInterval(() => {
        document.getElementById('oitiva_tempo').textContent = formatarDuracao(tempoDecorrido());
    }, 500);

    document.getElementById('btn_oitiva_gravar').disabled = true;
    document.getElementById('btn_oitiva_encerrar').disabled = false;
    document.getElementById('btn_oitiva_sala').disabled = true;
    document.getElementById('lnk_oitiva_intercorrencia').style.display = 'inline';

    const avisos = [];
    if (!audioGuia) avisos.push('<b>A voz do depoente não está sendo gravada</b> (o áudio da guia não foi liberado). Encerre e grave de novo deixando marcada a opção de áudio.');
    if (!Oitiva.microfone) avisos.push('<b>Microfone não liberado</b>: a sua voz não será gravada.');
    if (avisos.length) {
        statusOitiva('<i class="fa-solid fa-triangle-exclamation"></i> ' + avisos.join('<br>'), 'alerta');
    } else {
        statusOitiva('<i class="fa-solid fa-circle" style="color:#dc2626;"></i> Gravando. Você pode rolar a página e preencher o formulário: só a chamada aparece no vídeo. Diga a data e a hora de início para a gravação.', 'ok');
    }
}

// Marca algo ocorrido durante a gravação (ex.: queda de conexão) com o horário do vídeo
function registrarIntercorrenciaOitiva() {
    if (!Oitiva.gravando) return;
    const marca = formatarDuracao(tempoDecorrido());
    const descricao = prompt(`Intercorrência aos ${marca} da gravação.\nDescreva o ocorrido:`, 'Queda de conexão do depoente');
    if (!descricao) return;

    const radioSim = document.querySelector('input[name="ent_alt"][value="sim"]');
    if (radioSim && !radioSim.checked) {
        radioSim.checked = true;
        func_alteracao();
    }
    const campo = document.getElementById('altera_campo');
    if (campo) {
        campo.removeAttribute('maxlength');
        campo.value = (campo.value ? campo.value.trim() + ' ' : '') + `Aos ${marca} da gravação: ${descricao.trim()}.`;
    }
    statusOitiva(`<i class="fa-solid fa-flag"></i> Intercorrência registrada aos ${marca}. A gravação continua.`, 'ok');
}

async function encerrarOitiva() {
    if (!Oitiva.gravando) return;
    Oitiva.gravando = false;
    clearInterval(Oitiva.timer);
    document.getElementById('btn_oitiva_encerrar').disabled = true;
    statusOitiva('<i class="fa-solid fa-spinner fa-spin"></i> Finalizando o arquivo...', 'info');

    const fim = new Date();
    const duracao = tempoDecorrido();

    await new Promise(resolve => {
        Oitiva.gravador.onstop = resolve;
        Oitiva.gravador.stop();
    });

    const arquivo = new File(Oitiva.pedacos, Oitiva.nomeArquivo, { type: Oitiva.tipoArquivo || 'video/webm' });
    Oitiva.pedacos = [];

    // Libera câmera da captura, microfone e áudio
    [Oitiva.captura, Oitiva.microfone].forEach(s => s && s.getTracks().forEach(t => t.stop()));
    Oitiva.captura = Oitiva.microfone = null;
    if (Oitiva.audioCtx) { Oitiva.audioCtx.close(); Oitiva.audioCtx = null; }

    document.getElementById('oitiva_painel').classList.remove('flutuante');
    document.getElementById('oitiva_rec').style.display = 'none';
    document.getElementById('lnk_oitiva_intercorrencia').style.display = 'none';
    document.getElementById('btn_oitiva_gravar').disabled = false;
    document.getElementById('btn_oitiva_sala').disabled = false;

    baixarArquivo(arquivo);

    // Preenche o relatório
    document.getElementById('entfim').value = horaHHMM(fim);
    document.getElementById('min').value = Math.floor(duracao / 60);
    document.getElementById('seg').value = duracao % 60;
    carregarArquivoNoRelatorio(arquivo);

    statusOitiva(`<i class="fa-solid fa-circle-check"></i> Gravação encerrada (${formatarDuracao(duracao)}). O arquivo <b>${escaparTexto(arquivo.name)}</b> foi salvo na pasta de downloads e carregado no relatório abaixo, onde o HASH está sendo calculado. Clique na prévia do vídeo para escolher a imagem da certidão.`, 'ok');
}

function baixarArquivo(arquivo) {
    const url = URL.createObjectURL(arquivo);
    const a = document.createElement('a');
    a.href = url;
    a.download = arquivo.name;
    document.body.appendChild(a);
    a.click();
    a.remove();
    setTimeout(() => URL.revokeObjectURL(url), 60000);
}

// Coloca o arquivo gravado no campo "Carregue o arquivo da oitiva" e dispara
// o cálculo do HASH e a prévia, como se o usuário tivesse escolhido o arquivo
function carregarArquivoNoRelatorio(arquivo) {
    const input = document.getElementById('fileInput');
    const dt = new DataTransfer();
    dt.items.add(arquivo);
    input.files = dt.files;
    calculateHash();
    geraprevia();
    document.getElementById('dados_gravacao')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Alterna o tamanho da janela flutuante durante a gravação
function alternarTamanhoChamada() {
    document.getElementById('oitiva_painel').classList.toggle('ampliado');
}

// Evita fechar a página no meio da gravação
window.addEventListener('beforeunload', (ev) => {
    if (Oitiva.gravando) {
        ev.preventDefault();
        ev.returnValue = '';
    }
});
