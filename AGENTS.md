# Instruções para agentes — E-commerce Electronics

Estas regras valem para qualquer IA que participe da implementação do projeto, em todas as sessões e etapas.

## Ordem de leitura

1. `docs/05-implementacao/06-metodologia-implementacao-guiada.md` — regra **atual e prioritária**.
2. `docs/05-implementacao/05-acompanhamento-etapas.md` — ponto de retomada e pendências.
3. Código, testes, migrations, histórico de commits e CI — fontes de evidência.

## Regra fixa de trabalho

**Em cada funcionalidade:** entender objetivo e fluxo → **frontend visual React/Inertia primeiro, com dados de exemplo** → revisar a tela → **backend Laravel depois** → integrar → **testar no final da funcionalidade** → corrigir e validar → documentar e publicar.

- **Não iniciar por testes nem impor TDD vermelho.** Não intercalar testes com a criação de cada arquivo.
- **Não adiar testes para o fim da etapa ou projeto:** concluída é a funcionalidade testada e validada antes da publicação.
- O usuário escreve o código de forma guiada; a IA fornece **uma tarefa de cada vez**, em PT-BR, usando passos curtos. Só fornecer código pronto quando solicitado.
- Protótipos visuais com dados fictícios **não são funcionalidade integrada**. Autorização, validação e regras sensíveis são responsabilidade do backend.
- Identificar critérios de aceite e riscos antes do frontend não significa executar testes antes. Se a tarefa não tiver interface, explicar o fluxo ou contrato antes do backend, sem criar tela artificial.
- Não inventar resultados, não afirmar CI aprovado sem evidência e não descartar trabalho local sem autorização.
- **Preservar o histórico antigo:** eventuais referências antigas a backend-first ou TDD primeiro são registros do passado, não instruções para novas sessões.

Quando existir divergência, vale a metodologia de 02/11/2026 indicada acima.
