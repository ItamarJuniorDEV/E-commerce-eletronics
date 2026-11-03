# Metodologia oficial de implementação guiada — E-commerce Electronics

> **Vigência: 02/11/2026.** Válida para **todas as próximas sessões** e etapas, não apenas S01. Substitui orientações antigas de TDD obrigatório e backend antes da interface. Os históricos anteriores são registros do passado e não devem ser reescritos.

## Objetivo

Facilitar o aprendizado visual: enxergar a funcionalidade na tela antes de desenvolver sua parte interna. **Concentrar os testes no final de cada funcionalidade**, sem interromper o desenvolvimento a cada arquivo.

## Sequência obrigatória

1. **Entender o objetivo.** Explicar o fluxo, os dados de entrada e saída e os critérios de aceite. Pensar nas regras de segurança antes de codificar, mas **não começar pelos testes**.
2. **Frontend primeiro.** Construir a tela React/Inertia, campos, mensagens e estados visuais. Usar dados fictícios **claramente identificados** enquanto não existe backend.
3. **Revisar visualmente.** Mostrar o que foi construído e ajustar interface/experiência. Pré-visualização e checagens leves de sintaxe/build são permitidas; não iniciar a suíte formal de testes nesta fase.
4. **Backend depois.** Implementar o necessário no Laravel: migrations, Models, Form Requests, controllers, rotas, Gates/Policies e regras de negócio. Não adicionar camadas desnecessárias.
5. **Integrar.** Ligar os formulários e a interface aos dados reais do backend; implementar respostas, erros e persistência. Conferência manual rápida é permitida para orientar o trabalho.
6. **Testes ao final da funcionalidade.** Agora escrever e executar os testes pertinentes (Pest, permissões/negações, entradas inválidas, casos-limite, integração e E2E/navegador quando necessário). **Não exigir TDD vermelho, nem testes intercalados.**
7. **Corrigir e validar.** Corrigir os achados e executar QA, build e demais checagens relevantes. Não considerar concluído sem evidência real; o backend é responsável por proteger operações sensíveis.
8. **Finalizar.** Revisar diff, atualizar `docs/05-implementacao/05-acompanhamento-etapas.md`, registrar resultados, criar Conventional Commit em PT-BR, fazer push autorizado e verificar CI remoto.

## Limites e cuidados

- **Testes no final da funcionalidade não significam testes somente no final da S01 ou do projeto.** Nenhuma funcionalidade é considerada pronta antes das validações necessárias.
- Critérios de aceite são pensados no começo, mas a implementação da suíte formal fica para o passo 6.
- Tela com dados fictícios serve para visualizar o trabalho; não é prova de integração nem de segurança.
- Preços, estoque, autorização, privacidade e demais regras sensíveis são validados no backend, independentemente da ordem visual escolhida.
- Em riscos críticos de segurança ou perda de dados, tratar a segurança antes de continuar; a preferência de ordem não justifica riscos.
- Em tarefas exclusivamente internas, sem interface, explicar primeiro o fluxo ou contrato de entrada/saída e depois implementar o backend; não inventar uma tela apenas para cumprir a ordem visual.
- O usuário implementa com orientação: uma tarefa curta de cada vez, linguagem simples em português brasileiro, fluxos visuais quando ajudarem. **Não entregar código pronto sem que seja solicitado.**
- Não refazer trabalho já publicado; verificar último commit, CI e Git local antes de indicar o próximo passo.

## Retomada entre sessões

1. Ler `AGENTS.md`, esta metodologia e o checkpoint da S01 (ou da etapa vigente).
2. Conferir GitHub/CI; pedir `git status -sb` e `git log -1 --oneline` somente quando for necessário verificar o computador local.
3. Identificar a primeira funcionalidade realmente pendente e começar pela explicação e pela interface visual, **não por testes**.
4. Ao encerrar, registrar **feito, pendente, próximo passo, testes, commit e CI** sem supor evidências.
