#!/usr/bin/env bash
#
# HMO-77 - monta o kit do OTClient do PlayOT para o jogador de Windows.
#
# O que ele faz:
#   1. baixa o binario oficial do release do OTClient (opentibiabr, MIT);
#   2. baixa a arvore Lua/OTML do repositorio na MESMA tag do release;
#   3. aplica a camada PlayOT de overlay/ por cima;
#   4. gera o zip do jogador + LEIA-ME.txt + sha256.
#
# O que ele NAO faz, de proposito:
#   - nao compila nada (a VPS tem 2 vCPU; build de C++ com vcpkg derruba a producao);
#   - nao empacota asset da CipSoft. Sprites e appearances continuam sendo
#     baixados em tempo de execucao pelo modulo client_assets, da mesma fonte
#     publica que o kit da HMO-65 ja usava.
#
# Dependencias: bash, curl, git, unzip, zip, sha256sum.
# Na VPS, rode dentro do container de apoio (tem tudo):
#   docker run --rm --user 1000:1000 -v "$PWD":/w -w /w playot/otclient-runner:hmo77 bash montar-kit.sh
#
set -euo pipefail

# Tag do release do OTClient. Binario e arvore Lua TEM de vir da mesma tag:
# modules/ novo com exe velho (ou o contrario) quebra de formas dificeis de ler.
OTCLIENT_TAG="${OTCLIENT_TAG:-4.1}"
# Suba junto com PlayOTBranding.KIT_VERSION em overlay/mods/playot_branding/branding.lua.
KIT_VERSION="${KIT_VERSION:-1}"

BASE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WORK_DIR="${WORK_DIR:-$BASE_DIR/.build}"
DIST_DIR="$BASE_DIR/dist"
KIT_NAME="playot-otclient-${OTCLIENT_TAG}-windows-kit${KIT_VERSION}"

REPO_URL="https://github.com/opentibiabr/otclient.git"
EXE_URL="https://github.com/opentibiabr/otclient/releases/download/${OTCLIENT_TAG}/otclient-windows-cmake-release.zip"

echo "==> kit ${KIT_NAME}"
rm -rf "$WORK_DIR"
mkdir -p "$WORK_DIR" "$DIST_DIR"

echo "==> baixando binario de Windows do release ${OTCLIENT_TAG}"
curl -fsSL -o "$WORK_DIR/windows-release.zip" "$EXE_URL"
# O zip do release traz otclient.exe + otclient.ilk + otclient.pdb (~720 MB).
# .ilk e .pdb sao artefato de debug do linker: nao vao para o jogador.
unzip -q -o "$WORK_DIR/windows-release.zip" otclient.exe -d "$WORK_DIR/exe"

echo "==> clonando a arvore Lua/OTML na tag ${OTCLIENT_TAG}"
git clone --quiet --depth 1 --branch "$OTCLIENT_TAG" "$REPO_URL" "$WORK_DIR/src"
UPSTREAM_COMMIT="$(git -C "$WORK_DIR/src" rev-parse HEAD)"
echo "    commit ${UPSTREAM_COMMIT}"

echo "==> montando a arvore do kit"
KIT="$WORK_DIR/$KIT_NAME"
mkdir -p "$KIT"
cp "$WORK_DIR/exe/otclient.exe" "$KIT/otclient.exe"
# Só o que o cliente le em tempo de execucao. src/, android/, browser/, tests/,
# tools/, docs/, cmake/, vc18/ e overlay-ports/ sao coisa de build.
cp -r "$WORK_DIR/src/data" "$KIT/data"
cp -r "$WORK_DIR/src/modules" "$KIT/modules"
cp -r "$WORK_DIR/src/mods" "$KIT/mods"
cp "$WORK_DIR/src/config.ini" "$KIT/config.ini"
cp "$WORK_DIR/src/cacert.pem" "$KIT/cacert.pem"   # sem isto o login HTTPS falha
cp "$WORK_DIR/src/meta.lua" "$KIT/meta.lua"

echo "==> aplicando a camada PlayOT"
# init.lua da camada + branding + imagens. O init.lua.upstream-4.1 fica so no
# nosso repositorio, como base de diff; nao vai para o jogador.
cp -r "$BASE_DIR/overlay/." "$KIT/"
rm -f "$KIT/init.lua.upstream-"*
# g_app.setCompactName('playot') faz o init.lua procurar /playotrc.lua.
rm -f "$KIT/otclientrc.lua"
test -f "$KIT/playotrc.lua" || { echo "ERRO: playotrc.lua ausente na camada"; exit 1; }

# Verificacoes que valem mais que um 'deu certo'.
grep -q 'g_app.setName("PlayOT")' "$KIT/init.lua" || { echo "ERRO: init.lua sem branding"; exit 1; }
grep -q 'protocol = PLAYOT_PROTOCOL' "$KIT/init.lua" || { echo "ERRO: init.lua sem Servers_init"; exit 1; }
grep -q 'PLAYOT_PROTOCOL = 1525' "$KIT/init.lua" || { echo "ERRO: protocolo diferente de 1525"; exit 1; }

sed -e "s|@@KIT@@|${KIT_VERSION}|g" -e "s|@@TAG@@|${OTCLIENT_TAG}|g" \
    -e "s|@@COMMIT@@|${UPSTREAM_COMMIT}|g" "$BASE_DIR/LEIA-ME.txt" > "$KIT/LEIA-ME.txt"
cp "$WORK_DIR/src/LICENSE" "$KIT/LICENSE-OTCLIENT.txt"   # MIT do OTClient, obrigatorio na redistribuicao

echo "==> gerando o zip"
rm -f "$DIST_DIR/$KIT_NAME.zip"
( cd "$WORK_DIR" && zip -q -r -9 "$DIST_DIR/$KIT_NAME.zip" "$KIT_NAME" )
( cd "$DIST_DIR" && sha256sum "$KIT_NAME.zip" > "$KIT_NAME.zip.sha256" )

echo
echo "kit pronto:"
ls -la "$DIST_DIR/$KIT_NAME.zip"
cat "$DIST_DIR/$KIT_NAME.zip.sha256"
