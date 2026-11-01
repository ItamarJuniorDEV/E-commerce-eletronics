# Histórico diário automático do E-commerce Electronics

## Onde colocar

Copie a pasta `.githooks` e `scripts` para a **raiz** do repositório `E-commerce-eletronics`.

```text
E-commerce-eletronics/
├── .githooks/
│   ├── pre-commit
│   ├── instalar.ps1
│   └── README.md
├── scripts/
│   └── git/
│       └── atualizar-historico.php
└── docs/
    ├── 09-historico/
    │   └── DD-MM-AAAA.txt   (automaticamente criado)
    └── 05-implementacao/
        └── 05-acompanhamento-etapas.md  (plano atualizado manualmente)
```

## Instalar no Windows (uma vez em cada clone)

No PowerShell, na raiz do projeto:

```powershell
powershell -ExecutionPolicy Bypass -File .githooks/instalar.ps1
git status -sb
git diff -- .gitignore
```

O instalador faz duas alterações locais:

1. Permite versionar **somente** arquivos `docs/09-historico/*.txt` e o plano `docs/05-implementacao/05-acompanhamento-etapas.md`, mantendo outras partes de `/docs` ignoradas.
2. Configura `git config --local core.hooksPath .githooks`. Se já houver outro caminho de hooks, interrompe sem substituí-lo.

Não executa `git add`, `git commit` ou `git push` sozinho.

## Uso normal

1. Edite seu código e faça os testes habituais, por exemplo `composer qa`.
2. Revise `git status` e `git diff`, depois adicione apenas os arquivos intencionais com `git add <arquivo>`.
3. Execute normalmente `git commit -m "feat(auth): cadastrar papéis"`.
4. O **pre-commit** gera/atualiza `docs/09-historico/DD-MM-AAAA.txt` na data de Brasília e adiciona **somente esse histórico** ao mesmo commit.
5. Confira `git show --stat HEAD`, `git status -sb` e faça `git push origin main` quando quiser publicar.

## O que fica automático e o que não fica

**Automático:** data em `America/Sao_Paulo`, horário, nomes dos arquivos que estavam no stage, área técnica (Backend, Frontend, Testes etc.), atualização do arquivo do dia e inclusão no commit.

**Manual e obrigatório ao encerrar o dia:** objetivo alcançado, resultados reais dos testes, decisões, pendências, estado final do Git e **ponto exato para retomar**. O hook preserva o fim do arquivo para essa revisão.

**Não faz:** executar testes, alterar código de negócio, ler suas conversas, saber a mensagem/identificador final do commit antes de criá-lo, decidir se o trabalho está correto, fazer push automaticamente ou atualizar o progresso S00–S14 sem evidência.

O script não publica o conteúdo do diff; registra somente os caminhos, que devem ser revisados para não expor arquivos sensíveis.

## Verificações importantes

- `.gitignore` original contém `/docs`; a exceção controlada é necessária. Não usar `git add -f` indiscriminadamente.
- Em commits com **somente** mudanças de `docs/09-historico`, o hook não gera registro recursivo.
- A mesma mudança staged não cria entradas duplicadas, graças a uma assinatura da diferença no índice.
- As entradas são adicionadas antes do fechamento manual do dia.
- Em operações de merge, rebase ou `git commit --no-verify`, o hook pode não executar; nunca o trate como controle de segurança.
- Git Hooks são locais. Um clone novo precisa executar `instalar.ps1` novamente.
- Depois de instalar, confira `.gitignore`, `.githooks` e `scripts` e versiona-os normalmente após revisão.

## Conferir instalação

```powershell
git config --local --get core.hooksPath
php -l scripts/git/atualizar-historico.php
git status -sb
```

O comando `git config` deve responder `.githooks`.
