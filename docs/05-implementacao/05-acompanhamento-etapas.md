# Acompanhamento das etapas — S01: Identidade, Conta, RBAC e Administração

> **Documento de continuidade entre sessões.** Atualizado em **01/11/2026** (horário de Brasília).
>
> **Fonte de verdade:** código, testes executados, observação no navegador e commits publicados. Planejamento e relatos antigos não substituem evidências recentes.
>
> **Legenda:** ✅ implementado e comprovado no escopo indicado; ⏳ implementado localmente, mas aguardando publicação ou validação adicional; ❌ ainda falta implementar, concluir ou comprovar. Não calcular percentual de conclusão sem auditoria.

## 0. Ponto de retomada

- **Etapa:** S01 em andamento. **S02 (catálogo de produtos) não iniciada.**
- **Última funcionalidade implementada:** **passo 16, edição de papéis**, com backend, validação, proteção de acesso e formulário React. **Validada localmente; ainda não publicada.**
- **Última funcionalidade publicada:** passo 15, cadastro de papéis.
- **Próxima ação imediata:** revisar o diff completo dos arquivos alterados, incluir o novo `UpdateRoleRequest.php`, atualizar este acompanhamento, fazer commit e push após revisão e conferir o CI remoto.
- **Último commit publicado na `main`:** `ebbd1df` (`docs(s01): atualizar plano de acompanhamento`). O Git local informou `HEAD -> main, origin/main` nesse commit antes das alterações do passo 16.
- **Estado local do Git informado em 01/11/2026:** cinco arquivos modificados e um arquivo novo ainda não rastreado. Nenhum commit ou push do passo 16 foi comprovado.
- **Qualidade local comprovada em 01/11/2026:** `composer qa` aprovado com **85 testes e 262 asserções**; PHPStan sem erros; TypeScript, lint, formatação e build aprovados.
- **Testes de gestão de papéis:** `RoleManagementTest.php` com **23 testes e 66 asserções** aprovados (cadastro e edição).
- **Navegador:** formulário de edição abriu com dados preenchidos; alteração de nome foi salva e persistiu após F5; slug duplicado foi rejeitado e o registro permaneceu inalterado após F5.
- **Último CI remoto identificado:** execução do commit `ebbd1df` **falhou por formatação deste documento**, antes de executar os testes. O `composer qa` local posterior aplicou correção de formatação; **o CI ainda não validou essa correção no remoto**. Evidência: https://github.com/ItamarJuniorDEV/E-commerce-eletronics/actions/runs/41278242215
- **Decisões a formalizar ou concluir:** PEN-10 (o histórico de 31/10 registra catálogo de permissões controlado no código, mas ADR/documentação e gestão efetiva permanecem pendentes), PEN-15 (autenticação interna, 2FA e sessões) e PEN-13 (privacidade e retenção).

## 1. Autenticação e conta do cliente

| Status | Funcionalidade                               | Evidência ou pendência                                                                   |
| ------ | -------------------------------------------- | ---------------------------------------------------------------------------------------- |
| ✅     | Cadastro de cliente                          | Laravel/Fortify; `RegistrationTest`                                                      |
| ✅     | Login do cliente                             | `AuthenticationTest`                                                                     |
| ✅     | Logout                                       | `AuthenticationTest`                                                                     |
| ✅     | Recuperar senha                              | `PasswordResetTest`                                                                      |
| ✅     | Verificar e-mail                             | `EmailVerificationTest`                                                                  |
| ✅     | Limitar tentativas de login                  | `AuthenticationTest`                                                                     |
| ✅     | Alterar nome e e-mail do perfil              | `ProfileUpdateTest`                                                                      |
| ✅     | Alterar senha                                | `SecurityTest`                                                                           |
| ✅     | Suporte técnico a 2FA e passkeys             | Fortify e interface de segurança; **não equivale a 2FA obrigatório para equipe interna** |
| ❌     | Cadastrar endereço                           | CRUD não comprovado                                                                      |
| ❌     | Editar endereço                              | CRUD não comprovado                                                                      |
| ❌     | Remover endereço                             | CRUD não comprovado                                                                      |
| ❌     | Escolher endereço padrão                     | Não comprovado                                                                           |
| ❌     | Visualizar sessões ativas                    | Não comprovado                                                                           |
| ❌     | Encerrar sessões ativas                      | Não comprovado                                                                           |
| ❌     | Registrar eventos relevantes de autenticação | Auditoria não comprovada                                                                 |

## 2. Papéis (Roles)

| Status | Funcionalidade                                                 | Evidência ou pendência                                                                                                                         |
| ------ | -------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| ✅     | Tabela `roles` e Model `Role`                                  | Migrations e model                                                                                                                             |
| ✅     | Tabela `role_user`                                             | Relacionamento N:N e testes                                                                                                                    |
| ✅     | Usuário com mais de um papel                                   | `UserRolesTest`                                                                                                                                |
| ✅     | Listagem no React/Inertia                                      | `RoleController@index`, `roles.tsx`, `RoleAccessTest`                                                                                          |
| ✅     | Cadastro de papel pelo formulário                              | `POST /admin/roles`, `RoleController@store`, `StoreRoleRequest`                                                                                |
| ✅     | Validação de nome e slug no cadastro                           | `RoleManagementTest`: obrigatoriedade, slug duplicado e formato inválido                                                                       |
| ✅     | Bloqueio de acesso à listagem e ao cadastro sem `roles.manage` | Middleware, Gate e testes GET/POST                                                                                                             |
| ⏳     | **Editar nome e slug de um papel**                             | **Passo 16:** `PATCH /admin/roles/{role}`, `RoleController@update`, `UpdateRoleRequest`, `roles.tsx`; aprovado localmente, publicação pendente |
| ⏳     | Manter o mesmo slug ao editar o próprio papel                  | Validação `Rule::unique(...)->ignore(...)`; teste Pest aprovado                                                                                |
| ⏳     | Bloquear edição com slug de outro papel                        | Teste Pest e navegador; erro exibido e valor antigo preservado após F5                                                                         |
| ⏳     | Validar edição com dados inválidos                             | Testes de nome/slug obrigatórios, formato, tipo e tamanho; aprovados localmente                                                                |
| ⏳     | Bloquear edição sem autorização                                | Testes de 403, visitante, e-mail não verificado e papel inexistente; aprovados localmente                                                      |
| ❌     | Definir exclusão/desativação de papéis                         | Regra de negócio pendente, especialmente para papéis vinculados                                                                                |

**Limite da evidência do passo 16:** o navegador foi conferido manualmente para abertura, salvamento e slug duplicado; não houve comprovação de uma suíte automatizada de navegador. O CI do passo 16 não foi executado remotamente.

## 3. Permissões (Permissions)

| Status | Funcionalidade                                                        | Evidência ou pendência                                                                                |
| ------ | --------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------- |
| ✅     | Tabela `permissions` e Model `Permission`                             | Migrations, model e testes                                                                            |
| ✅     | Tabela `permission_role`                                              | Relacionamento N:N e testes                                                                           |
| ✅     | Relacionar papéis a permissões no backend                             | `RolePermissionsTest`                                                                                 |
| ✅     | `User::hasPermission()`                                               | `UserPermissionsTest`                                                                                 |
| ✅     | Gate `roles.manage`                                                   | `AppServiceProvider`                                                                                  |
| ✅     | Proteger listagem e cadastro de papéis                                | `RoleAccessTest`, `RoleManagementTest`                                                                |
| ⏳     | Proteger edição de papéis                                             | Middleware `can:roles.manage` e testes locais aprovados; publicação pendente                          |
| ❌     | Formalizar a decisão PEN-10                                           | Histórico de 31/10 propõe catálogo controlado no código; ADR e atualização documental não comprovadas |
| ❌     | Implementar gestão do catálogo conforme PEN-10                        | Não comprovada como funcionalidade administrativa                                                     |
| ❌     | Atribuir/remover permissões de papéis pelo painel                     | Relacionamento existe no backend; interface não comprovada                                            |
| ❌     | Consultar permissões de cada papel pelo painel                        | Não comprovado                                                                                        |
| ❌     | Proteger as demais operações administrativas com permissões adequadas | Evidência atual restrita às operações de papéis                                                       |

## 4. Funcionários e administração

| Status | Funcionalidade                                                  | Evidência ou pendência                                                  |
| ------ | --------------------------------------------------------------- | ----------------------------------------------------------------------- |
| ❌     | Cadastrar funcionários pelo painel                              | Não comprovado                                                          |
| ❌     | Editar funcionários                                             | Não comprovado                                                          |
| ❌     | Ativar/desativar funcionários                                   | Não comprovado                                                          |
| ❌     | Atribuir um ou vários papéis a funcionários pelo painel         | Relação N:N existe; gestão pela interface não                           |
| ❌     | Consultar os acessos de cada funcionário                        | Não comprovado                                                          |
| ❌     | Impedir operação de funcionário desativado usando sessão antiga | Não comprovado                                                          |
| ❌     | Impedir ficar sem administrador ativo                           | Regra RF-13-014 não comprovada                                          |
| ❌     | Separar painel administrativo da área do cliente                | Existe `admin/roles`, mas não painel administrativo completo e separado |
| ❌     | Proteger dashboard inicial por perfil                           | Página do starter não equivale a dashboard administrativo por permissão |
| ❌     | Auditar alterações de papéis e permissões                       | Não comprovado                                                          |

## 5. Segurança obrigatória

| Status | Funcionalidade                                               | Evidência ou pendência                                                                      |
| ------ | ------------------------------------------------------------ | ------------------------------------------------------------------------------------------- |
| ✅     | Restringir GET `/admin/roles`                                | `auth`, `verified`, `can:roles.manage` e testes                                             |
| ✅     | Restringir POST `/admin/roles`                               | `auth`, `verified`, `can:roles.manage` e testes                                             |
| ✅     | Negar cadastro a visitante ou usuário sem permissão          | `RoleManagementTest`                                                                        |
| ⏳     | Restringir PATCH `/admin/roles/{role}`                       | `auth`, `verified`, `can:roles.manage`; testes locais de permissão e autenticação aprovados |
| ⏳     | Impedir alterações inválidas ou de papel inexistente         | `UpdateRoleRequest`; testes locais de validação e resposta 404 aprovados                    |
| ❌     | Definir login interno, 2FA e sessões (PEN-15)                | Decisão e implementação pendentes                                                           |
| ❌     | Exigir 2FA para operações administrativas                    | Suporte técnico do Fortify não equivale a exigência                                         |
| ❌     | Invalidar ou negar operação de funcionário desativado        | Não comprovado                                                                              |
| ❌     | Implementar Policies para titularidade de recursos           | Não comprovado                                                                              |
| ❌     | Impedir cliente A de acessar recurso privado de cliente B    | Comprovar em recursos existentes; pedidos pertencem à S04                                   |
| ❌     | Ampliar os testes de acesso HTTP direto às futuras operações | Evidência atual restrita às rotas de papéis                                                 |
| ❌     | Decidir privacidade/retenção da demonstração (PEN-13)        | Pendente                                                                                    |

## 6. Testes, CI e acompanhamento

| Status | Entrega ou verificação                                                                                                      |
| ------ | --------------------------------------------------------------------------------------------------------------------------- |
| ✅     | `composer qa` local em 01/11/2026: **85 testes, 262 asserções**, sem falhas                                                 |
| ✅     | `RoleManagementTest`: **23 testes, 66 asserções**, incluindo os cenários de edição                                          |
| ✅     | PHPStan/Larastan: **0 erros**; TypeScript, lint e build aprovados localmente                                                |
| ✅     | Formatação local aprovada; `composer qa` corrigiu automaticamente um detalhe de estilo em `RoleManagementTest.php`          |
| ✅     | Navegador: abrir edição preenchida, alterar e salvar nome, persistência após F5                                             |
| ✅     | Navegador: tentar slug duplicado, exibir erro e preservar dados após F5                                                     |
| ✅     | Cadastro de papéis do passo 15 publicado anteriormente na `main`                                                            |
| ❌     | **CI do commit `ebbd1df` aprovado:** não; falhou pela formatação de `05-acompanhamento-etapas.md`, antes da etapa de testes |
| ⏳     | **Corrigir o CI de formatação no remoto:** `composer qa` local reformatou o documento; publicar e conferir nova execução    |
| ⏳     | **Publicar o passo 16:** revisar diff, incluir `UpdateRoleRequest.php`, fazer commit/push e validar CI remoto               |
| ❌     | Traduzir a mensagem de validação exibida em inglês (melhoria de interface; não bloqueou a validação funcional)              |
| ❌     | Testes de funcionário desativado e sessão antiga: pendentes                                                                 |
| ❌     | Testes de 2FA obrigatório para equipe interna: pendentes                                                                    |
| ❌     | Testes de tentativa de acesso a recursos de outro cliente: pendentes                                                        |
| ❌     | Verificar critérios AC-001 a AC-006 aplicáveis à S01, distinguindo dependências da S04: pendente                            |
| ❌     | Auditoria final dos requisitos da S01 e gate para S02: pendente                                                             |

**Referência do último CI remoto:** https://github.com/ItamarJuniorDEV/E-commerce-eletronics/actions/runs/41278242215

## 7. Itens de Minha Conta adiados: não bloqueiam S01

| Funcionalidade                      | Etapa responsável |
| ----------------------------------- | ----------------- |
| Visualizar meus pedidos             | S04               |
| Visualizar minhas propostas         | S07               |
| Consultar solicitações de pós-venda | S08               |
| Visualizar favoritos                | S09               |
| Indicadores operacionais completos  | S13               |

## 8. Ordem de implementação sugerida

1. **Fechar a publicação do passo 16:** revisar diff e arquivo novo; atualizar este checkpoint; executar commit/push autorizado; verificar CI remoto e corrigir falhas, se houver.
2. **Formalizar PEN-10:** registrar/alinhar a decisão sobre catálogo de permissões e preparar gestão adequada.
3. Implementar atribuição/remoção de permissões dos papéis pelo painel.
4. Implementar gestão administrativa de funcionários, atribuição de papéis e proteção do último administrador ativo.
5. Construir painel administrativo separado com acesso restrito.
6. Resolver PEN-15 e exigir 2FA para equipe; gerenciar sessões e bloqueio de usuários desativados.
7. Implementar CRUD de endereços, endereço padrão e gestão de sessões na área do cliente.
8. Implementar Policies, autorização por titularidade, auditoria e concluir PEN-13.
9. Executar validações de ponta a ponta da S01, CI, documentação e gate antes de iniciar S02.

## 9. Critérios para declarar S01 concluída

- [ ] Cadastro, login, recuperação, verificação, perfil e endereços essenciais validados.
- [ ] Gestão de papéis, permissões e funcionários implementada conforme escopo aprovado.
- [ ] Rotas administrativas segregadas e protegidas por permissões apropriadas.
- [ ] Política de autenticação interna e 2FA obrigatório demonstrada.
- [ ] Usuários desativados bloqueados, incluindo sessões antigas.
- [ ] Policies/autorização por titularidade testadas em requisições HTTP diretas.
- [ ] Auditoria relevante e decisões PEN-10, PEN-15 e PEN-13 registradas.
- [ ] Testes locais, CI remoto, navegador e critérios de aceite com evidências.
- [ ] Documentação e checkpoint atualizados; gate S01 aprovado para liberar S02.

**Observação:** a implementação local do passo 16 não significa que a S01 inteira está concluída.

## 10. Regras para todas as próximas sessões

**Este arquivo é o checkpoint de acompanhamento da S01.** Ao iniciar uma sessão, ler este documento antes de decidir a próxima tarefa.

1. Verificar a versão atual do GitHub, `git status -sb` e o código antes de confiar em status antigos.
2. Fazer uma funcionalidade por vez; não marcar uma entrega como concluída apenas porque existe tabela, esqueleto ou teste incompleto.
3. **Atualizar este arquivo ao finalizar cada fatia**, no mesmo ciclo de commit e push da funcionalidade. Registrar status, evidências, testes reais, hash do commit e próximo passo.
4. Não inventar resultados, percentuais, CI aprovado, decisões ou funcionalidades não comprovadas. Distinguir explicitamente o que está apenas local do que foi publicado.
5. O hook em `.githooks/pre-commit` **atualiza somente** `docs/09-historico/*.txt`; **não atualiza este plano automaticamente**.
6. Havendo discordância entre documentos e execução, prevalecem o código e as evidências recentes; revisar o acompanhamento.
7. Encerrar a sessão informando: **feito / pendente / decisão bloqueante / próxima ação / commit / testes / situação do CI**.
8. Após publicar, atualizar o SHA de referência e o resultado exato do CI remoto; nunca antecipar a aprovação.

**Referências documentais:** `docs/05-implementacao/01-roadmap-vertical.md`, `docs/02-requisitos/01-rf-consolidados.md`, `docs/02-requisitos/04-criterios-aceite.md` e `docs/04-arquitetura/04-seguranca-e-autorizacao.md`. Alguns desses documentos podem existir somente no ambiente local e não devem ser presumidos publicados no GitHub.
