-- Identidade visual minima do PlayOT sobre o OTClient (HMO-77).
--
-- Regra: nada de C++ e nada de asset da CipSoft. So o que o OTClient permite
-- por Lua/OTML e por imagem propria em data/images/.
--
-- A versao aqui e a UNICA string que o suporte precisa pedir ao jogador
-- ("qual a versao do seu cliente?"). Ela aparece na tela de entrada e no log.

PlayOTBranding = {}

-- Suba este numero a cada kit publicado. Casa com o nome do zip e com o
-- LEIA-ME.txt entregue ao jogador.
PlayOTBranding.KIT_VERSION = '1'
PlayOTBranding.PROTOCOL = 1525
PlayOTBranding.WORLD = 'PlayOT'

function PlayOTBranding.getVersionString()
    return string.format('%s - protocolo %d.%d - kit %s', PlayOTBranding.WORLD,
        math.floor(PlayOTBranding.PROTOCOL / 100), PlayOTBranding.PROTOCOL % 100,
        PlayOTBranding.KIT_VERSION)
end

-- O nome grande da tela de entrada e um Label fixo em
-- modules/client_bottommenu/bottommenu.otui (text: OTClient Redemption).
-- Trocamos o texto em tempo de execucao em vez de versionar um .otui alterado:
-- um arquivo a menos divergindo do upstream a cada release.
local function renameWordmark(widget)
    if widget.getText and widget:getText() == 'OTClient Redemption' then
        widget:setText(PlayOTBranding.WORLD)
        return true
    end
    for _, child in ipairs(widget:getChildren()) do
        if renameWordmark(child) then
            return true
        end
    end
    return false
end

local retries = 0
local function applyWordmark()
    if renameWordmark(rootWidget) then
        g_logger.info('[playot] marca da tela de entrada trocada')
        return
    end
    -- O client_bottommenu carrega depois de nos; tenta de novo por ~5s.
    retries = retries + 1
    if retries < 20 then
        scheduleEvent(applyWordmark, 250)
    else
        g_logger.warning('[playot] nao achei o label da marca; tela de entrada fica com o nome do upstream')
    end
end

function init()
    g_logger.info('[playot] ' .. PlayOTBranding.getVersionString())

    -- setServerInfo escreve no serverInfoLabel da janela de entrada. E o lugar
    -- onde o jogador enxerga em que mundo e em que versao ele esta entrando --
    -- e o que o suporte pede quando alguem diz "meu cliente nao conecta".
    if modules.client_entergame and modules.client_entergame.EnterGame then
        pcall(modules.client_entergame.EnterGame.setServerInfo, PlayOTBranding.getVersionString())
    end

    applyWordmark()
end

function terminate()
    PlayOTBranding = nil
end
