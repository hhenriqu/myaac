# Camada PlayOT sobre o OTClient (HMO-77)

Cliente do PlayOT a partir do **OTClient Redemption** da opentibiabr
(`github.com/opentibiabr/otclient`, MIT), release **4.1** (commit `dd56414`).

Aqui não mora o cliente. Aqui mora **só o que é nosso**: a configuração que
aponta para o PlayOT, a identidade visual e o script que monta o pacote do
jogador. O binário e a árvore Lua vêm do release oficial, em tempo de
montagem, e os gráficos do jogo vêm da fonte pública, em tempo de execução.

## Por que existe

Decisão do fundador (HMO-65, 10/09): adotar o OTClient no lugar do kit de
`config.ini` sobre o cliente oficial. Mais espaço para inovar, e o jogador
baixa um pacote só.

## O que a camada faz

| Arquivo | O que resolve |
|---|---|
| `overlay/init.lua` | `Servers_init` com uma única entrada → some host, porta e combo de versão da tela de entrada; `Services` apontando para o site; nome/organização da aplicação |
| `overlay/playotrc.lua` | `g_app.setCompactName('playot')` faz o `init.lua` procurar `/playotrc.lua` em vez de `/otclientrc.lua` |
| `overlay/mods/playot_branding/` | versão visível para o suporte, e troca do nome grande da tela de entrada em runtime |
| `overlay/data/images/background.png` | fundo da tela de entrada — arte própria |
| `overlay/data/images/clienticon.png` | ícone da janela — arte própria |
| `overlay/init.lua.upstream-4.1` | o `init.lua` original do release 4.1, **só para diff**; não vai para o jogador |
| `montar-kit.sh` | monta o zip do jogador a partir do release oficial |
| `LEIA-ME.txt` | instruções do jogador; entra no zip com as variáveis substituídas |

## Três coisas que custaram tempo e não estão em documentação nenhuma

1. **Porta 7171 não serve para cliente moderno.** Em
   `repos/server/src/server/network/protocol/protocollogin.cpp`, o Canary
   derruba a conexão de qualquer cliente que não seja `oldProtocol`. Login de
   protocolo 15.25 é **sempre** por `login.php`. Por isso `Servers_init` usa
   porta 443 e não 7171.
2. **`httpLogin = false` não desliga login HTTP.** Em
   `src/framework/net/httplogin.cpp` o parâmetro escolhe o transporte:
   `false` → `loginHttpsJson` (TLS, com fallback para HTTP), `true` → HTTP
   puro. O PlayOT está atrás de TLS no Traefik, então é `false`.
3. **`EnterGame.setAccountName` / `setPassword` esperam texto cifrado.** Elas
   passam o valor por `g_crypt.decrypt`. Quem automatiza login tem de mandar
   `g_crypt.encrypt(texto)`, senão o campo chega vazio.

## Versão de protocolo

Travada em `PLAYOT_PROTOCOL = 1525` no `overlay/init.lua`, casando com
`CLIENT_VERSION = 1525` de `repos/server/src/core.hpp`.

```bash
grep -n "CLIENT_VERSION = " repos/server/src/core.hpp
```

Se o servidor mudar de `CLIENT_VERSION`, o cliente **tem** de ser remontado e
redistribuído — cliente e servidor em versões diferentes não conversam. Isso é
escalada para o fundador, não decisão técnica: obriga todo jogador a baixar de
novo.

## Assets da CipSoft

Não redistribuímos nenhum. O módulo `client_assets` baixa `data/things/1525/`
em tempo de execução do repositório `dudantas/tibia-client` — a **mesma** fonte
que o kit da HMO-65 já linkava — e confere SHA-256
(`strictManifestSha256 = true`). O `appearances` que ele instala bate com o
hash que o servidor espera (`aa44a154f30c...`).

## Montar o kit

```bash
# na VPS, dentro do container de apoio (tem curl, git, zip, unzip)
docker run --rm --user 1000:1000 -v "$PWD":/w -w /w \
  playot/otclient-runner:hmo77 bash montar-kit.sh
```

Saída: `dist/playot-otclient-4.1-windows-kit1.zip` e o `.sha256`.

`OTCLIENT_TAG` e `KIT_VERSION` são variáveis de ambiente. Ao subir a tag,
refaça o diff de `overlay/init.lua` contra o `init.lua` novo do upstream e
guarde o novo `init.lua.upstream-<tag>`.

## Como isso foi provado

Não dá para rodar cliente gráfico direto na VPS (sem Xvfb, e instalar exige
`sudo`, que é do DevOps). A prova roda **dentro de um container**: Xvfb +
Mesa llvmpipe, imagem `playot/otclient-runner:hmo77`.

O mod de prova (`mods/hmo77_probe`, só na árvore de teste, não vai no kit)
preenche conta e senha, deixa a camada mandar em host/porta/versão, entra no
jogo e anda. Prova aceita = log do servidor com
`has logged in. (Protocol: 1525)` **mais** screenshot do personagem no mapa.

## O que ficou de fora

- **Publicar** o kit (upload, link no site, DNS): é do Engenheiro DevOps, em
  issue própria.
- **Build próprio com branding compilado** (fork + GitHub Actions): depende de
  credencial do GitHub, pendente desde a HMO-66. Não foi feito.
- **Painel de boosted creature** na tela de entrada: continua mostrando a dica
  do upstream. Precisa de webservice configurado; não é bloqueio para jogar.
