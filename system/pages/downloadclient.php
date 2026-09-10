<?php
defined('MYAAC') or die('Direct access not allowed!');
$title = 'Download do Cliente';

// ---------------------------------------------------------------------------
// PlayOT — pagina de download do cliente (HMO-79).
//
// Fundador confirmou (HMO-79, 2026-09-10): o
// kit1 (OTClient 4.1 + camada PlayOT) rodou numa maquina Windows limpa,
// incluindo o download de ~1 GB de assets pelo caminho do jogador. Ver
// issue HMO-79 - publicado em producao a partir daqui.
//
// Agora o PlayOT distribui cliente proprio: o OTClient (opentibiabr, MIT)
// com a camada PlayOT ja aplicada (servidor, porta e visual). O jogador so
// extrai e abre — nao ha config.ini para copiar por cima. Nenhum asset da
// CipSoft e redistribuido por nos: o proprio cliente baixa data/things/1525
// em runtime, da mesma fonte publica que o kit antigo ja linkava.
//
// $playot_show_legacy_kit controla se o kit antigo (config.ini sobre o
// cliente oficial, HMO-65) continua visivel como alternativa. Fundador
// decidiu (HMO-79, 2026-09-10): manter como alternativa por enquanto.
// ---------------------------------------------------------------------------
$playot_kit1_url    = '/kit/playot-otclient-4.1-windows-kit1.zip';
$playot_kit1_sha256 = '61136b63721a07a2301feb0af3b3a1e57d16e7f1dad3b4e279b73bc985d6ec5e';
$playot_kit1_size   = '~43 MB (mais ~1 GB de graficos baixados na primeira abertura)';

$playot_show_legacy_kit = true;
$playot_client_tag = '15.25.0a00a0';
$playot_client_url = 'https://github.com/dudantas/tibia-client/releases/tag/15.25.0a00a0';
$playot_kit_url    = '/kit/playot-client-config-15.25.zip';
$playot_kit_sha256 = 'b5d9ec425c345fb14cf02f5692649fd7dedbbfe7c4e5f0b5cf86a7b284b36ef5';

$playot_server = configLua('serverName');

if (!function_exists('playot_download_box')) {
    function playot_download_box(string $caption, string $html, string $tpl): void
    {
        $edge = $tpl . '/images/global/content/box-frame-edge.gif';
        $border = $tpl . '/images/global/content/table-headline-border.gif';
        $vert = $tpl . '/images/global/content/box-frame-vertical.gif';
        ?>
        <div class="TableContainer">
            <div class="CaptionContainer">
                <div class="CaptionInnerContainer">
                    <span class="CaptionEdgeLeftTop" style="background-image:url(<?= $edge ?>);"></span>
                    <span class="CaptionEdgeRightTop" style="background-image:url(<?= $edge ?>);"></span>
                    <span class="CaptionBorderTop" style="background-image:url(<?= $border ?>);"></span>
                    <span class="CaptionVerticalLeft" style="background-image:url(<?= $vert ?>);"></span>
                    <div class="Text"><?= htmlspecialchars($caption) ?></div>
                    <span class="CaptionVerticalRight" style="background-image:url(<?= $vert ?>);"></span>
                    <span class="CaptionBorderBottom" style="background-image:url(<?= $border ?>);"></span>
                    <span class="CaptionEdgeLeftBottom" style="background-image:url(<?= $edge ?>);"></span>
                    <span class="CaptionEdgeRightBottom" style="background-image:url(<?= $edge ?>);"></span>
                </div>
            </div>
            <table class="Table1" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td>
                        <div class="InnerTableContainer" style="padding:0.75rem 1rem;"><?= $html ?></div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <br>
        <?php
    }
}

playot_download_box(
    'Passo 1 — baixe o cliente do ' . $playot_server,
    '<p>O cliente do ' . htmlspecialchars($playot_server) . ' ja vem pronto: e so extrair e jogar, sem
        baixar nada do site oficial do Tibia e sem copiar arquivo de configuracao por cima.</p>
     <p style="text-align:center;">
        <a href="' . htmlspecialchars($playot_kit1_url) . '">
            <img alt="Download do cliente" style="width:90px;height:90px;border:0;"
                 src="' . htmlspecialchars($template_path) . '/images/download_windows.gif"><br>
            <span style="font-size:12pt;">Baixar o cliente do ' . htmlspecialchars($playot_server) . '</span>
        </a>
     </p>
     <p>Tamanho do zip: ' . htmlspecialchars($playot_kit1_size) . '.</p>
     <p>Extraia numa pasta sua — por exemplo <code>C:\\PlayOT</code>. Nao rode de dentro do zip e nao deixe
        em Arquivos de Programas: o cliente precisa gravar os graficos na propria pasta.</p>
     <p style="font-size:9pt;color:#5a4a3a;">Se quiser conferir o download:
        <code>sha256 = ' . htmlspecialchars($playot_kit1_sha256) . '</code></p>',
    $template_path
);

playot_download_box(
    'Passo 2 — primeira abertura',
    '<p>Abra <code>otclient.exe</code>. Na primeira vez vai aparecer "Assets for client 15.25 are not
        installed. Download them now?" — clique em <strong>Download</strong> e espere a barra terminar
        (alguns minutos, cerca de 1 GB). So acontece uma vez.</p>',
    $template_path
);

playot_download_box(
    'Passo 3 — crie sua conta e entre',
    '<p>A conta e criada no site, em <a href="?subtopic=createaccount">Create Account</a>. No cliente voce
        entra com o <strong>e-mail e a senha da conta</strong>, nao com o nome do personagem — e nao precisa
        digitar endereco nem porta, o cliente ja vem apontado para o ' . htmlspecialchars($playot_server) . '.</p>',
    $template_path
);

playot_download_box(
    'Problemas comuns',
    '<p><strong>"Assets for client 15.25 are not installed"</strong> — e o passo 2, normal na primeira vez.
        Clique em Download.</p>
     <p><strong>"No assets source found for client 15.25"</strong> — o download dos graficos nao conseguiu
        sair. Quase sempre e antivirus ou firewall bloqueando o otclient.exe. Libere e abra de novo.</p>
     <p><strong>"Email or password is not correct"</strong> — use o e-mail e a senha da conta do site, nao
        o nome do personagem.</p>
     <p><strong>O cliente abre e fecha na hora</strong> — extraia numa pasta com permissao de escrita
        (<code>C:\\PlayOT</code> serve); pastas de sistema podem bloquear a gravacao dos graficos.</p>',
    $template_path
);

if ($playot_show_legacy_kit) {
    playot_download_box(
        'Cliente antigo (config.ini sobre o oficial) — ainda disponivel',
        '<p>Se preferir o metodo antigo (baixar o cliente oficial ' . htmlspecialchars($playot_client_tag) . '
            e copiar um kit de configuracao por cima), ele continua no ar por enquanto:</p>
         <p style="text-align:center;">
            <a href="' . htmlspecialchars($playot_client_url) . '" target="_blank" rel="noopener">cliente oficial ' . htmlspecialchars($playot_client_tag) . '</a>
            &nbsp;+&nbsp;
            <a href="' . htmlspecialchars($playot_kit_url) . '">kit de configuracao</a>
            <span style="font-size:9pt;color:#5a4a3a;"> (sha256 ' . htmlspecialchars($playot_kit_sha256) . ')</span>
         </p>',
        $template_path
    );
}

playot_download_box(
    'Disclaimer',
    '<p>O cliente e distribuido "como esta", sem garantia de nenhum tipo. O risco de uso e do jogador.
        A CipSoft GmbH nao responde por danos ao seu computador ou perda de dados.</p>',
    $template_path
);
