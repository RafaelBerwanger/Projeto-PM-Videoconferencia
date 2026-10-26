@echo off
rem =====================================================================
rem  DEPLOY - envia para a hospedagem (FTP) somente o que mudou
rem ---------------------------------------------------------------------
rem  Uso:
rem    deploy.bat            envia o que mudou desde o ultimo envio
rem    deploy.bat simular    mostra o que seria enviado, sem enviar nada
rem    deploy.bat tudo       envia todos os arquivos do sistema
rem    deploy.bat testar     testa a conexao e lista a pasta do servidor
rem
rem  - Envia a versao COMMITADA de cada arquivo (faca o commit antes).
rem  - Sempre sobrescreve os arquivos do servidor.
rem  - Apaga do servidor os arquivos removidos do projeto.
rem  - Nunca envia config.php (o do servidor tem os dados de producao).
rem  - Usuario e senha do FTP ficam em deploy.netrc (fora do Git).
rem =====================================================================
setlocal EnableExtensions EnableDelayedExpansion
chcp 65001 >nul
cd /d "%~dp0"

rem ---------- Configuracao ----------
set "FTP_HOST=ftpupload.net"
rem Pasta do site no FTP (painel InfinityFree: "Directory" do dominio)
set "FTP_DIR=/ajudadoencarregado.free.nf/htdocs"
set "CREDENCIAIS=deploy.netrc"
set "MARCADOR=.deploy_ultimo"
rem Commit de referencia quando nunca houve envio por este script
set "COMMIT_INICIAL=a574760"
rem Arquivos que nunca vao para o servidor
set "EXCLUIR= config.php config.example.php database.sql README.md LICENSE .gitignore .gitattributes Obfuscate.txt gerar_senha.php deploy.bat "

set "CURL=%SystemRoot%\System32\curl.exe"
set "MODO=%~1"
if "%MODO%"=="" set "MODO=enviar"

echo.
echo ==============================================
echo   DEPLOY - Ajuda do Encarregado  [modo: %MODO%]
echo ==============================================
echo.

rem ---------- Verificacoes ----------
where git >nul 2>&1 || (echo [ERRO] Git nao encontrado. Instale o Git for Windows. & goto :fim_erro)
if not exist "%CURL%" (echo [ERRO] curl.exe nao encontrado no Windows. & goto :fim_erro)

if /i "%MODO%"=="simular" goto :pular_credenciais
if not exist "%CREDENCIAIS%" (
    > "%CREDENCIAIS%" echo machine %FTP_HOST%
    >> "%CREDENCIAIS%" echo login if0_37053278
    >> "%CREDENCIAIS%" echo password COLOQUE_A_SENHA_AQUI
    echo [AVISO] Criei o arquivo %CREDENCIAIS%.
    echo         Coloque nele a senha do FTP ^(a mesma "MySQL Password" do painel^),
    echo         salve e rode o deploy.bat de novo.
    start "" notepad "%CREDENCIAIS%"
    goto :fim_erro
)
findstr /c:"COLOQUE_A_SENHA_AQUI" "%CREDENCIAIS%" >nul && (
    echo [ERRO] Preencha a senha do FTP no arquivo %CREDENCIAIS% antes de continuar.
    start "" notepad "%CREDENCIAIS%"
    goto :fim_erro
)

:pular_credenciais
set "CURL_FTP="%CURL%" --ssl-reqd --netrc-file "%CREDENCIAIS%" --silent --show-error --connect-timeout 20"

if /i "%MODO%"=="testar" (
    echo Conectando em %FTP_HOST%%FTP_DIR% ...
    %CURL_FTP% --list-only "ftp://%FTP_HOST%%FTP_DIR%/"
    if errorlevel 1 (echo. & echo [ERRO] Falha na conexao. Confira a senha e a pasta FTP_DIR. & goto :fim_erro)
    echo.
    echo [OK] Conexao funcionando. Acima, os arquivos da pasta do site.
    goto :fim_ok
)

for /f %%H in ('git rev-parse --short HEAD') do set "HEAD=%%H"

rem Alteracoes nao commitadas nao sao enviadas: avisa
set "PENDENTES=0"
for /f "delims=" %%L in ('git status --porcelain') do set /a PENDENTES+=1
if !PENDENTES! gtr 0 (
    echo [AVISO] Ha !PENDENTES! alteracao^(oes^) NAO commitada^(s^). Elas NAO serao enviadas:
    git status --short
    echo         Faca o commit antes se quiser envia-las.
    echo.
)

rem ---------- Lista de arquivos ----------
set "LISTA_ENVIO=%TEMP%\deploy_envio.txt"
set "LISTA_APAGAR=%TEMP%\deploy_apagar.txt"
type nul > "%LISTA_ENVIO%"
type nul > "%LISTA_APAGAR%"

if /i "%MODO%"=="tudo" (
    git ls-files > "%LISTA_ENVIO%"
    echo Enviando TODOS os arquivos do sistema ^(versao %HEAD%^).
) else (
    set "BASE=%COMMIT_INICIAL%"
    if exist "%MARCADOR%" set /p BASE=<"%MARCADOR%"
    git cat-file -t !BASE! >nul 2>&1 || (echo [ERRO] Commit de referencia !BASE! nao encontrado. Use: deploy.bat tudo & goto :fim_erro)
    git diff --name-only --no-renames --diff-filter=ACMRT !BASE! HEAD > "%LISTA_ENVIO%"
    git diff --name-only --no-renames --diff-filter=D !BASE! HEAD > "%LISTA_APAGAR%"
    echo Alteracoes desde !BASE! ate %HEAD%.
)
echo.

set "TMPDIR=%TEMP%\deploy_pm"
if exist "%TMPDIR%" rmdir /s /q "%TMPDIR%"
mkdir "%TMPDIR%"

set /a ENVIADOS=0, APAGADOS=0, FALHAS=0, IGNORADOS=0

rem ---------- Envio ----------
for /f "usebackq delims=" %%F in ("%LISTA_ENVIO%") do (
    call :excluido "%%F"
    if "!IGNORAR!"=="1" (
        set /a IGNORADOS+=1
    ) else (
        if /i "%MODO%"=="simular" (
            echo   [enviaria] %%F
            set /a ENVIADOS+=1
        ) else (
            set "TEMPFILE=%TMPDIR%\arquivo.tmp"
            git show "HEAD:%%F" > "!TEMPFILE!" 2>nul
            if errorlevel 1 (
                echo   [FALHA] %%F ^(nao foi possivel ler do Git^)
                set /a FALHAS+=1
            ) else (
                %CURL_FTP% --ftp-create-dirs -T "!TEMPFILE!" "ftp://%FTP_HOST%%FTP_DIR%/%%F"
                if errorlevel 1 (
                    echo   [FALHA] %%F
                    set /a FALHAS+=1
                ) else (
                    echo   [enviado] %%F
                    set /a ENVIADOS+=1
                )
            )
        )
    )
)

rem ---------- Remocao ----------
for /f "usebackq delims=" %%F in ("%LISTA_APAGAR%") do (
    call :excluido "%%F"
    if "!IGNORAR!"=="0" (
        if /i "%MODO%"=="simular" (
            echo   [apagaria] %%F
            set /a APAGADOS+=1
        ) else (
            %CURL_FTP% -o nul "ftp://%FTP_HOST%%FTP_DIR%/" -Q "DELE %FTP_DIR%/%%F" 2>nul
            if errorlevel 1 (
                echo   [aviso] %%F nao estava no servidor
            ) else (
                echo   [apagado] %%F
                set /a APAGADOS+=1
            )
        )
    )
)

if exist "%TMPDIR%" rmdir /s /q "%TMPDIR%"
del "%LISTA_ENVIO%" "%LISTA_APAGAR%" 2>nul

echo.
echo ----------------------------------------------
if /i "%MODO%"=="simular" (
    echo SIMULACAO: !ENVIADOS! a enviar, !APAGADOS! a apagar, !IGNORADOS! ignorado^(s^).
    echo Nada foi enviado. Para enviar de verdade: deploy.bat
    goto :fim_ok
)
echo Enviados: !ENVIADOS!   Apagados: !APAGADOS!   Ignorados: !IGNORADOS!   Falhas: !FALHAS!
if !FALHAS! gtr 0 (
    echo [ERRO] Houve falhas. O marcador NAO foi atualizado: rode de novo para tentar outra vez.
    goto :fim_erro
)
> "%MARCADOR%" echo %HEAD%
echo [OK] Site atualizado com a versao %HEAD%.
goto :fim_ok

rem ---------- Funcoes ----------
:excluido
set "IGNORAR=0"
set "NOME=%~1"
if not "!EXCLUIR: %NOME% =!"=="!EXCLUIR!" set "IGNORAR=1"
if /i "%~x1"==".md" set "IGNORAR=1"
if /i "%~x1"==".sql" set "IGNORAR=1"
if /i "%NOME%"=="%CREDENCIAIS%" set "IGNORAR=1"
if /i "%NOME%"=="%MARCADOR%" set "IGNORAR=1"
exit /b 0

:fim_ok
echo.
pause
exit /b 0

:fim_erro
echo.
pause
exit /b 1
