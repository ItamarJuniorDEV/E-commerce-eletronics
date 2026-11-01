# Execute no PowerShell na raiz do repositorio:
# powershell -ExecutionPolicy Bypass -File .githooks/instalar.ps1
$ErrorActionPreference = 'Stop'
$root = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Set-Location $root

if (-not (Test-Path '.git')) { throw 'Execute em um repositorio Git, na raiz do projeto.' }
if (-not (Test-Path '.gitignore')) { throw 'Arquivo .gitignore nao encontrado.' }
if (-not (Test-Path 'scripts/git/atualizar-historico.php')) { throw 'Script PHP do historico nao encontrado.' }

$currentHooks = (& git config --local --get core.hooksPath)
if ($LASTEXITCODE -eq 0 -and $currentHooks -and $currentHooks -ne '.githooks') {
    throw "Este repositorio ja tem core.hooksPath='$currentHooks'. Preserve/revise hooks anteriores antes de trocar."
}

$ignorePath = Join-Path $root '.gitignore'
$ignore = [System.IO.File]::ReadAllText($ignorePath)
$block = @'
# Documentacao permitida: historicos diarios e plano de acompanhamento
/docs/*
!/docs/09-historico/
/docs/09-historico/*
!/docs/09-historico/*.txt
!/docs/05-implementacao/
/docs/05-implementacao/*
!/docs/05-implementacao/05-acompanhamento-etapas.md
'@

if ($ignore.Contains('!/docs/09-historico/*.txt')) {
    Write-Host '[historico] .gitignore ja possui excecao para historicos.'
} else {
    $pattern = '(?m)^/docs[ \t]*\r?$'
    if (-not [regex]::IsMatch($ignore, $pattern)) {
        throw 'Nao encontrei a linha /docs no .gitignore. Revise manualmente antes de prosseguir.'
    }
    $ignore = ([regex]::new($pattern)).Replace($ignore, $block, 1)
    [System.IO.File]::WriteAllText($ignorePath, $ignore, [System.Text.UTF8Encoding]::new($false))
    Write-Host '[historico] .gitignore ajustado somente para os historicos e plano.'
}

& git config --local core.hooksPath .githooks
if ($LASTEXITCODE -ne 0) { throw 'Falha ao configurar core.hooksPath.' }
Write-Host '[historico] Hook instalado. Nenhum commit ou push foi executado.'
Write-Host '[historico] Confira git diff -- .gitignore e git status -sb antes de commitar.'
