<?php
defined('MYAAC') or die('Direct access not allowed!');
$title = 'Download do Cliente';

// ---------------------------------------------------------------------------
// PlayOT — pagina de download do cliente 15.25 (HMO-66).
//
// O PlayOT nao distribui cliente proprio: o jogador baixa o cliente oficial
// empacotado por dudantas/tibia-client e sobrescreve um unico arquivo,
// conf/config.ini, com o kit de configuracao publicado aqui (~2 KB).
// Nenhum asset da CipSoft e redistribuido por nos.
//
// Para atualizar de versao: troque as variaveis abaixo e publique o kit novo
// em /kit/. O gerador do kit e client/15.25/make-kit.mjs no projeto.
// ---------------------------------------------------------------------------
$playot_client_tag = '15.25.0a00a0';
$playot_client_url = 'https://github.com/dudantas/tibia-client/releases/tag/15.25.0a00a0';
$playot_kit_url    = '/kit/playot-client-config-15.25.zip';
$playot_kit_sha256 = 'b5d9ec425c345fb14cf02f5692649fd7dedbbfe7c4e5f0b5cf86a7b284b36ef5';

// PLAYOT-AVISO-VIRADA: deixe string vazia depois que a virada terminar (HMO-66).
$playot_switch_notice = '<strong>A virada para o protocolo 15.25 esta acontecendo agora.</strong> O cliente antigo deixa de conectar; o cliente desta pagina e o certo daqui para a frente. Se o servidor nao responder nos proximos minutos, e a propria virada — tente de novo em seguida. Contas e personagens nao mudam.';

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

// A pagina e a mesma com ou sem ?step=downloadagreement: o botao do template
// aponta para o segundo, e nao ha acordo de licenca proprio para aceitar aqui.

if ($playot_switch_notice !== '') {
    playot_download_box(
        'Atencao — leia antes de baixar',
        '<p style="margin:0;">' . $playot_switch_notice . '</p>',
        $template_path
    );
}

playot_download_box(
    'Passo 1 — baixe o cliente oficial ' . $playot_client_tag,
    '<p>O cliente do ' . htmlspecialchars($playot_server) . ' e o cliente oficial na versao
        <strong>' . htmlspecialchars($playot_client_tag) . '</strong> (protocolo 15.25), Windows 64 bits,
        cerca de 415 MB.</p>
     <p style="text-align:center;">
        <a href="' . htmlspecialchars($playot_client_url) . '" target="_blank" rel="noopener">
            <img alt="Download do cliente" style="width:90px;height:90px;border:0;"
                 src="' . htmlspecialchars($template_path) . '/images/download_windows.gif"><br>
            <span style="font-size:12pt;">Baixar o cliente ' . htmlspecialchars($playot_client_tag) . '</span>
        </a>
     </p>
     <p>Extraia o zip numa pasta sua — por exemplo <code>C:\\PlayOT</code>. Dentro dela voce vai ver
        <code>assets</code>, <code>bin</code>, <code>conf</code> e <code>sounds</code>.</p>',
    $template_path
);

playot_download_box(
    'Passo 2 — baixe o kit de configuracao do ' . $playot_server,
    '<p>Este pacote tem cerca de 2 KB e contem um unico arquivo de texto: e ele que faz o cliente oficial
        apontar para o ' . htmlspecialchars($playot_server) . '.</p>
     <p style="text-align:center;font-size:12pt;">
        <a href="' . htmlspecialchars($playot_kit_url) . '"><strong>Baixar o kit de configuracao 15.25</strong></a>
     </p>
     <p>Extraia o kit e copie o <code>conf\\config.ini</code> dele <strong>por cima</strong> do
        <code>conf\\config.ini</code> que veio do cliente, substituindo o original. Nenhum outro arquivo muda.</p>
     <p style="font-size:9pt;color:#5a4a3a;">Se quiser conferir o download:
        <code>sha256 = ' . htmlspecialchars($playot_kit_sha256) . '</code></p>',
    $template_path
);

playot_download_box(
    'Passo 3 — crie sua conta e entre',
    '<p>A conta e criada no site, em <a href="?subtopic=createaccount">Create Account</a>. No cliente voce
        entra com o <strong>e-mail e a senha da conta</strong>, nao com o nome do personagem.</p>
     <p>Abra <code>bin\\client.exe</code>, faca login e escolha o mundo
        <strong>' . htmlspecialchars($playot_server) . '</strong>.</p>',
    $template_path
);

playot_download_box(
    'Problemas comuns',
    '<p><strong>"Only clients with protocol 15.25 are allowed"</strong> — o cliente e de outra versao.
        Baixe exatamente a release ' . htmlspecialchars($playot_client_tag) . ' do passo 1.</p>
     <p><strong>"Email or password is not correct"</strong> — use o e-mail e a senha da conta do site.</p>
     <p><strong>O cliente abre mas nao acha o servidor</strong> — o <code>conf\\config.ini</code> do kit nao
        foi copiado por cima. Abra o arquivo e confira se a linha <code>loginWebService</code> aponta para
        este site.</p>
     <p><strong>Antivirus reclamando do BattlEye</strong> — o cliente oficial vem com BattlEye. Voce nao
        precisa instala-lo para jogar aqui.</p>
     <p style="font-size:9pt;color:#5a4a3a;">Ao pedir ajuda, informe a versao do cliente: ela esta em
        <code>package.json</code>, campo <code>version</code>, dentro da pasta do cliente.</p>',
    $template_path
);

playot_download_box(
    'Disclaimer',
    '<p>O cliente e distribuido "como esta", sem garantia de nenhum tipo. O risco de uso e do jogador.
        A CipSoft GmbH nao responde por danos ao seu computador ou perda de dados.</p>',
    $template_path
);
