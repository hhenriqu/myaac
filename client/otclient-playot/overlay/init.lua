-- this is the first file executed when the application starts
-- we have to load the first modules form here

-- ==== CAMADA PLAYOT (HMO-77) ====
-- Derivado de otclient 4.1 (commit dd56414) init.lua.
-- O arquivo original esta ao lado como init.lua.upstream-4.1: ao subir de
-- release do OTClient, refaca o diff em vez de reescrever do zero.
local PLAYOT_SITE = "https://playot.srv702207.hstgr.cloud"
local PLAYOT_LOGIN = PLAYOT_SITE .. "/login.php"
-- Travado na versao do servidor: repos/server/src/core.hpp -> CLIENT_VERSION = 1525.
-- Se o servidor mudar de CLIENT_VERSION, esta linha muda junto e o cliente e
-- redistribuido; nao existe cliente 15.25 falando com servidor de outra versao.
local PLAYOT_PROTOCOL = 1525

Services = {
    status = PLAYOT_LOGIN, --./client_entergame | ./client_topmenu
    websites = PLAYOT_SITE .. "/?subtopic=accountmanagement", --./client_entergame "Forgot password and/or email"
    createAccount = PLAYOT_SITE .. "/?subtopic=createaccount", --./client_entergame -- createAccount.lua
    getCoinsUrl = PLAYOT_SITE .. "/?subtopic=shop", --./game_market
    -- Assets: continuam vindo em runtime da mesma fonte publica que o kit da
    -- HMO-65 ja usava. Nada de asset da CipSoft e redistribuido por nos.
    clientAssets = {
        enabled = true,
        repository = "dudantas/tibia-client",
        installSounds = true,
        strictManifestSha256 = true,
        allowRawFallbackHashMismatch = false,
        allowMissingPackedRawFallback = true,
        preferArchive = true,
        fallbackToArchiveOnManifestFailure = false,
        installArchiveExtras = true,
        archiveExtraPrefixes = { "bin" },
        installPackagedFiles = true
    }, -- ./client_assets
}

-- Uma unica entrada em Servers_init faz o client_entergame chamar
-- setUniqueServer automaticamente: some o campo de host, de porta e o combo de
-- versao da tela de entrada. O jogador so digita e-mail e senha.
--
-- httpLogin = false NAO desliga o login por HTTP. Em httplogin.cpp o parametro
-- escolhe o transporte: false -> loginHttpsJson (TLS, com fallback para HTTP),
-- true -> HTTP puro. O PlayOT esta atras de TLS no Traefik, entao e false.
--
-- A porta 7171 (login server TCP) NAO serve aqui: em
-- protocollogin.cpp o Canary derruba a conexao de qualquer cliente de
-- protocolo moderno. Login moderno e sempre via login.php.
Servers_init = {
    [PLAYOT_LOGIN] = {
        port = 443,
        protocol = PLAYOT_PROTOCOL,
        httpLogin = false,
        useAuthenticator = false
    }
}

g_app.setName("PlayOT");
g_app.setCompactName("playot");
g_app.setOrganizationName("hmoraes");

g_app.hasUpdater = function()
    return (Services.updater and Services.updater ~= "" and g_modules.getModule("updater"))
end

-- setup logger
g_logger.setLogFile(g_resources.getWorkDir() .. g_app.getCompactName() .. '.log')
g_logger.info("Operating system: " .. g_platform.getOSName())

-- print first terminal message
g_logger.info(g_app.getName() .. ' ' .. g_app.getVersion() .. ' rev ' .. g_app.getBuildRevision() .. ' (' ..
    g_app.getBuildCommit() .. ') built on ' .. g_app.getBuildDate() .. ' for arch ' ..
    g_app.getBuildArch())

-- setup lua debugger
if os.getenv("LOCAL_LUA_DEBUGGER_VSCODE") == "1" then
    require("lldebugger").start()
    g_logger.debug("Started LUA debugger.")
else
    g_logger.debug("LUA debugger not started (not launched with VSCode local-lua).")
end

-- add data directory to the search path
if not g_resources.addSearchPath(g_resources.getWorkDir() .. 'data', true) then
    g_logger.fatal('Unable to add data directory to the search path.')
end

-- add modules directory to the search path
if not g_resources.addSearchPath(g_resources.getWorkDir() .. 'modules', true) then
    g_logger.fatal('Unable to add modules directory to the search path.')
end

g_html.addGlobalStyle('/data/styles/html.css')
g_html.addGlobalStyle('/data/styles/custom.css')

-- try to add mods path too
g_resources.addSearchPath(g_resources.getWorkDir() .. 'mods', true)

-- setup directory for saving configurations
g_resources.setupUserWriteDir(('%s/'):format(g_app.getCompactName()))

-- search all packages
g_resources.searchAndAddPackages('/', '.otpkg', true)

-- load settings
g_configs.loadSettings('/config.otml')

g_modules.discoverModules()

-- libraries modules 0-99
g_modules.autoLoadModules(99)
g_modules.ensureModuleLoaded('corelib')
g_modules.ensureModuleLoaded('gamelib')
g_modules.ensureModuleLoaded('modulelib')
g_modules.ensureModuleLoaded("startup")

g_modules.autoLoadModules(999)
g_modules.ensureModuleLoaded('game_shaders') -- pre load

local function loadModules()
    -- client modules 100-499
    g_modules.autoLoadModules(499)
    g_modules.ensureModuleLoaded('client')

    -- game modules 500-999
    g_modules.autoLoadModules(999)
    g_modules.ensureModuleLoaded('game_interface')

    -- mods 1000-9999
    g_modules.autoLoadModules(9999)
    g_modules.ensureModuleLoaded('client_mods')

    local script = '/' .. g_app.getCompactName() .. 'rc.lua'

    if g_resources.fileExists(script) then
        dofile(script)
    end

    -- uncomment the line below so that modules are reloaded when modified. (Note: Use only mod dev)
    -- g_modules.enableAutoReload()
end

-- run updater, must use data.zip
if g_app.hasUpdater() then
    g_modules.ensureModuleLoaded("updater")
    return Updater.init(loadModules)
end

loadModules()
