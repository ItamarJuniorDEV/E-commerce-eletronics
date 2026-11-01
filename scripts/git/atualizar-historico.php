<?php

declare(strict_types=1);

/**
 * Gera/atualiza o historico diario a partir do INDEX do Git (arquivos staged).
 * Nao executa testes, nao inventa resultados e nao modifica arquivos de codigo.
 * O pre-commit adiciona APENAS o arquivo diario gerado ao mesmo commit.
 * A mensagem e o SHA do commit nao existem, com seguranca, nesta fase.
 */
$repoRoot = dirname(__DIR__, 2);
chdir($repoRoot);

/** @param list<string> $args */
function git(array $args): string
{
    $process = proc_open(
        array_merge(['git'], $args),
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
    );

    if (! is_resource($process)) {
        throw new RuntimeException('Nao foi possivel iniciar git.');
    }

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    if ($exitCode !== 0) {
        throw new RuntimeException('git '.implode(' ', $args).': '.trim($stderr));
    }

    return $stdout === false ? '' : $stdout;
}

function area(string $path): string
{
    return match (true) {
        str_starts_with($path, 'tests/') => 'Testes',
        str_starts_with($path, 'app/'), str_starts_with($path, 'routes/') => 'Backend',
        str_starts_with($path, 'resources/') => 'Frontend',
        str_starts_with($path, 'database/') => 'Banco de dados',
        str_starts_with($path, '.github/') => 'CI',
        str_starts_with($path, 'docs/') => 'Documentacao',
        default => 'Configuracao/outros',
    };
}

function fileTemplate(string $date): string
{
    return <<<TXT
HISTÓRICO DE DESENVOLVIMENTO
Data: {$date} (America/Sao_Paulo)
Projeto: E-commerce de Equipamentos Eletrônicos

========================================
1. CONFIGURAÇÃO E CONTEXTO
========================================

- Sessão: registrar manualmente, se necessário.
- Etapa Sxx atual: conferir no plano de implementação.

========================================
2. ALTERAÇÕES DOS COMMITS (AUTOMÁTICO)
========================================

<!-- AUTO-HISTORICO-INICIO -->
<!-- AUTO-HISTORICO-FIM -->

========================================
3. BANCO DE DADOS
========================================

- Registrar mudanças e migrations somente quando verificadas.

========================================
4. BACKEND E AUTORIZAÇÃO
========================================

- Registrar comportamentos realmente implementados e testados.

========================================
5. FRONTEND
========================================

- Registrar telas e fluxos realmente demonstrados.

========================================
6. TESTES E QUALIDADE
========================================

- O hook não executa testes: anexar resultado real do composer qa / CI.

========================================
7. GIT E PUBLICAÇÃO
========================================

- Este histórico registra arquivos PREPARADOS para commit; consulte git log para o hash final.
- Push não é realizado pelo hook; só registrar após confirmação.

========================================
8. DECISÕES E PENDÊNCIAS
========================================

- Preencher a partir das decisões verificadas no desenvolvimento.

========================================
9. PRÓXIMO PASSO / PONTO DE RETOMADA (REVISAR AO ENCERRAR)
========================================

- Etapa atual: conferir plano e código.
- Última funcionalidade validada: preencher com evidências.
- Próximo arquivo/ação: preencher ao encerrar a sessão.
- Testes pendentes: registrar os resultados reais.
- Código não commitado: conferir com git status -sb.

TXT;
}

try {
    $staged = git(['diff', '--cached', '--name-only', '-z', '--diff-filter=ACMRTUXB', '--']);
    $paths = array_values(array_filter(explode("\0", $staged), static fn (string $p): bool => $p !== ''));
    $paths = array_values(array_filter($paths, static fn (string $p): bool => ! str_starts_with($p, 'docs/09-historico/')));

    // Um commit sem mudancas relevantes nao gera registro automatico.
    if ($paths === []) {
        exit(0);
    }

    // Nao espalhar nomes de arquivos sensiveis no historico versionado.
    foreach ($paths as $path) {
        if (preg_match('~(^|/)(?:\.env(?:\..*)?|auth\.json|\.?id_rsa|\.?id_ed25519)$|\.(?:pem|p12|pfx|key|sqlite|db)$~i', $path)) {
            fwrite(STDERR, "[historico] Arquivo potencialmente sensivel no stage. Revise antes do commit.\n");
            exit(1);
        }
    }

    $tz = new DateTimeZone('America/Sao_Paulo');
    $now = new DateTimeImmutable('now', $tz);
    $date = $now->format('d-m-Y');
    $displayDate = $now->format('d/m/Y');
    $time = $now->format('H:i:s');
    $relativePath = "docs/09-historico/{$date}.txt";
    $absolutePath = $repoRoot.'/'.$relativePath;

    $diff = git(['diff', '--cached', '--binary', '--', '.', ':!docs/09-historico']);
    $fingerprint = substr(hash('sha256', $diff), 0, 16);

    if ($diff === '') {
        exit(0);
    }

    if (! is_dir(dirname($absolutePath)) && ! mkdir(dirname($absolutePath), 0775, true) && ! is_dir(dirname($absolutePath))) {
        throw new RuntimeException('Nao foi possivel criar docs/09-historico.');
    }

    $isNew = ! file_exists($absolutePath);
    $content = $isNew ? fileTemplate($displayDate) : file_get_contents($absolutePath);
    if (! is_string($content)) {
        throw new RuntimeException('Nao foi possivel ler o historico diario.');
    }

    $start = '<!-- AUTO-HISTORICO-INICIO -->';
    $end = '<!-- AUTO-HISTORICO-FIM -->';
    if (! str_contains($content, $start) || ! str_contains($content, $end)) {
        // Preserva integralmente o historico anterior, inclusive o checkpoint no final.
        $section = "REGISTROS AUTOMÁTICOS (COMMITS PREPARADOS)\n{$start}\n{$end}\n\n";
        $content = $section.$content;
    }

    if (str_contains($content, "[{$fingerprint}]")) {
        // O hook pode ser chamado novamente com o mesmo stage: nao duplica.
        git(['add', '--', $relativePath]);
        fwrite(STDOUT, "[historico] Registro do stage ja existe: {$relativePath}\n");
        exit(0);
    }

    $rows = array_map(static fn (string $p): string => '  - '.area($p).": {$p}", $paths);
    $record = "\nRegistro {$displayDate} {$time} BRT [{$fingerprint}]\n"
        .'Arquivos preparados no stage ('.count($paths)."):\n"
        .implode("\n", $rows)."\n"
        ."Validações: não inferidas pelo hook; conferir logs do composer qa/CI.\n"
        ."Commit/SHA: não disponível antes da criação; consultar git log.\n";

    $content = preg_replace('/'.preg_quote($end, '/').'/', $record.$end, $content, 1, $count);
    if (! is_string($content) || $count !== 1) {
        throw new RuntimeException('Marcador de historico invalido.');
    }

    if (file_put_contents($absolutePath, $content, LOCK_EX) === false) {
        throw new RuntimeException('Nao foi possivel gravar o historico.');
    }

    // Usa git add normal: o instalador abre excecao pontual no .gitignore.
    git(['add', '--', $relativePath]);
    fwrite(STDOUT, "[historico] {$relativePath} atualizado e incluido no commit.\n");
} catch (Throwable $e) {
    fwrite(STDERR, "[historico] ERRO: {$e->getMessage()}\n");
    fwrite(STDERR, "[historico] Verifique a configuracao em .gitignore e .githooks/README.md.\n");
    exit(1);
}
