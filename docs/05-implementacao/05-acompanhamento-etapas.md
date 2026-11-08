# Acompanhamento das etapas — S01: Identidade, Conta, RBAC e Administração

> **Documento de continuidade entre sessões.** Atualizado em **08/11/2026** (horário de Brasília).
>
> **Fonte de verdade:** código, testes executados, observação no navegador e commits publicados. Planejamento e relatos antigos não substituem evidências recentes.
>
> **Legenda:** ✅ implementado e comprovado no escopo indicado; ⏳ implementado localmente, mas aguardando publicação ou validação adicional; ❌ ainda falta implementar, concluir ou comprovar. Não calcular percentual de conclusão sem auditoria.

## 0. Ponto de retomada

- **Etapa:** S01 em andamento. **S02 (catálogo de produtos) não iniciada.** Passo 18 validado localmente; publicação e CI remoto ainda pendentes.
- **Última funcionalidade publicada e verificada no GitHub:** **Passo 17**, gestão de permissões por papel, pelo React/Inertia e Laravel. Merge na `main`: `4dde8c2` (`feat(auth): permitir gerenciar permissões dos papéis (#2)`). A branch remota `feature/s01-permissoes` estava em `c1755da` na última verificação anterior aos commits locais.
- **Última funcionalidade implementada localmente:** **Passo 18**, gestão de funcionários internos: cadastro, edição, atribuição de papéis, listagem de acessos e ativação/desativação, com proteção do último administrador e de contas desativadas.
- **Commits locais do Passo 18:** `9fe7cc3` (`feat(auth): concluir gestão de funcionários internos`) e `d82d671` (`test(auth): validar gestão e segurança de funcionários`). Na última saída de `git status -sb`, a branch `feature/s01-permissoes` estava **ahead 2** e restava `.gitignore` modificado. **Nenhum push ou CI remoto do Passo 18 comprovado.**
- **Qualidade local em 08/11/2026:** `composer qa` **aprovado** com **119 testes, 391 asserções, zero falhas**; PHPStan **54/54 sem erros**; TypeScript, lint, formatação e build aprovados (**2.315 módulos transformados**). `git diff --check` sem problemas.
- **Navegador:** a gestão de funcionários foi conferida manualmente em cadastro, edição e ativação/desativação; a edição de papéis já tinha verificação manual de persistência após F5 e rejeição de slug duplicado. **Suíte E2E automatizada não comprovada.**
- **Histórico diário:** preenchimento manual local; novos arquivos de `docs/09-historico/` são ignorados por `.gitignore`. Históricos antigos, já rastreados no Git, não foram removidos. Hook personalizado de histórico desativado **somente na configuração local**, por solicitação do desenvolvedor.
- **Próxima ação imediata:** atualizar este acompanhamento mantendo os status fiéis às evidências, versionar a mudança de `.gitignore` e este documento no terceiro commit, publicar a branch e verificar o CI remoto. Depois, escolher a primeira pendência real da S01 a partir dos requisitos e critérios de aceite.
- **Decisões/pendências abertas:** PEN-10 (formalização do catálogo de permissões), PEN-15 (autenticação interna, 2FA obrigatório e gestão de sessões) e PEN-13 (privacidade/retenção); exclusão/desativação de papéis, painel administrativo separado, endereços, Policies, auditoria e teste E2E.
- **Metodologia permanente vigente desde 02/11/2026:** [`06-metodologia-implementacao-guiada.md`](06-metodologia-implementacao-guiada.md). Construir frontend em partes reais e integrar o backend conforme necessário; testes ao final de cada funcionalidade, sem dados simulados na interface.

## 1. Autenticação e conta do cliente

| Status | Funcionalidade | Evidência ou pendência |
| ------ | -------------- | --------------------- |
| ✅ | Cadastro de cliente | Laravel/Fortify; `RegistrationTest` |
| ✅ | Login do cliente | `AuthenticationTest` |
| ✅ | Logout | `AuthenticationTest` |
| ✅ | Recuperar senha | `PasswordResetTest` |
| ✅ | Verificar e-mail | `EmailVerificationTest` |
| ✅ | Limitar tentativas de login | `AuthenticationTest` |
| ✅ | Alterar nome e e-mail do perfil | `ProfileUpdateTest` |
| ✅ | Alterar senha | `SecurityTest` |
| ✅ | Suporte técnico a 2FA e passkeys | Fortify e interface de segurança; **não equivale a 2FA obrigatório para equipe interna** |
| ❌ | Cadastrar endereço | CRUD não comprovado |
| ❌ | Editar endereço | CRUD não comprovado |
| ❌ | Remover endereço | CRUD não comprovado |
| ❌ | Escolher endereço padrão | Não comprovado |
| ❌ | Visualizar sessões ativas | Não comprovado |
| ❌ | Encerrar sessões ativas | Não comprovado |
| ❌ | Registrar eventos relevantes de autenticação | Auditoria não comprovada |

## 2. Papéis (Roles)

| Status | Funcionalidade | Evidência ou pendência |
| ------ | -------------- | --------------------- |
| ✅ | Tabela `roles` e Model `Role` | Migrations e model |
| ✅ | Tabela `role_user` | Relacionamento N:N e testes |
| ✅ | Usuário com mais de um papel | `UserRolesTest` |
| ✅ | Listagem no React/Inertia | `RoleController@index`, `roles.tsx`, `RoleAccessTest` |
| ✅ | Cadastro de papel pelo formulário | `POST /admin/roles`, `RoleController@store`, `StoreRoleRequest` |
| ✅ | Validação de nome e slug no cadastro | `RoleManagementTest`: obrigatoriedade, slug duplicado e formato inválido |
| ✅ | Bloqueio de acesso à listagem e ao cadastro sem `roles.manage` | Gate, middleware e testes GET/POST |
| ✅ | **Editar nome e slug de um papel** | **Passo 16:** `PATCH /admin/roles/{role}`, `RoleController@update`, `UpdateRoleRequest`, `roles.tsx`; publicado em `25da8fb`; CI aprovado |
| ✅ | Manter o mesmo slug ao editar o próprio papel | `Rule::unique(...)->ignore(...)`; teste Pest aprovado |
| ✅ | Bloquear edição com slug de outro papel | Testes Pest; navegador conferido anteriormente |
| ✅ | Validar edição com dados inválidos | Testes de nome/slug obrigatórios, formato, tipo e tamanho; aprovados |
| ✅ | Bloquear edição sem autorização | Testes de 403, visitante, e-mail não verificado e papel inexistente |
| ✅ | Atribuir/remover permissões dos papéis pelo painel | **Passo 17**, commit remoto `4dde8c2`; `RolePermissionAssignmentTest` |
| ❌ | Definir exclusão/desativação de papéis | Regra de negócio pendente, especialmente para papéis vinculados |

**Limite da evidência:** o Passo 16 teve validação manual de navegador; o Passo 17 foi publicado. A suíte E2E automatizada ainda não foi comprovada. No Passo 18, os testes de papéis foram ajustados para representar funcionários internos, sem retirar as regras de segurança.

## 3. Permissões (Permissions)

| Status | Funcionalidade | Evidência ou pendência |
| ------ | -------------- | --------------------- |
| ✅ | Tabela `permissions` e Model `Permission` | Migrations, model e testes |
| ✅ | Tabela `permission_role` | Relacionamento N:N e testes |
| ✅ | Relacionar papéis a permissões no backend | `RolePermissionsTest` |
| ✅ | `User::hasPermission()` | `UserPermissionsTest` |
| ✅ | Gate `roles.manage` | `AppServiceProvider`; exige funcionário interno ativo e permissão |
| ✅ | Proteger listagem, cadastro e edição de papéis | `RoleAccessTest`, `RoleManagementTest`, `RoleAuthorizationTest` |
| ⏳ | Gate `users.manage` para a gestão de funcionários | Implementado no Passo 18 e validado localmente; publicação pendente |
| ❌ | Formalizar a decisão PEN-10 | Catálogo controlado em código foi discutido, mas ADR/decisão formal não comprovada |
| ❌ | Implementar gestão do catálogo conforme PEN-10 | Não há evidência de cadastro administrativo do catálogo; diferenciar catálogo de atribuição |
| ✅ | Atribuir/remover permissões de papéis pelo painel | **Passo 17** publicado, React/Inertia, validações, transações e vínculo com `sync()` |
| ✅ | Consultar permissões de cada papel pelo painel | **Passo 17** publicado; dados reais enviados pelo controller |
| ⏳ | Proteger operações administrativas de funcionários | Rotas do Passo 18 protegidas por `users.manage`, `auth` e `verified`; testes locais aprovados; demais módulos ainda não avaliados |

**Atenção:** atribuir permissões a papéis **não** significa permitir criar/editar livremente o catálogo de permissões. A formalização da PEN-10 continua pendente.

## 4. Funcionários e administração

| Status | Funcionalidade | Evidência ou pendência |
| ------ | -------------- | --------------------- |
| ⏳ | Cadastrar funcionários pelo painel | `EmployeeController@store`, `SaveEmployeeRequest`, `employees.tsx`; `EmployeeManagementTest`; local validado, push pendente |
| ⏳ | Editar funcionários | `EmployeeController@update`, React/Inertia e teste de substituição de papéis |
| ⏳ | Ativar/desativar funcionários | `EmployeeController@status`; testes de desativação e reativação |
| ⏳ | Atribuir um ou vários papéis a funcionários pelo painel | `role_user`, formulário React, validação de IDs e impedimento de atribuição superior |
| ⏳ | Consultar os acessos de cada funcionário | Listagem com papéis/permissões; teste de listagem somente de funcionários internos |
| ⏳ | Impedir operação de funcionário desativado usando sessão antiga | `EnsureAccountIsActive`; teste Pest de sessão antiga aprovado |
| ⏳ | Impedir ficar sem administrador ativo | `LastAdministratorGuard`; testes de último administrador, papel e permissões aprovados |
| ⏳ | Impedir login por senha de conta desativada | `FortifyServiceProvider`; teste Pest aprovado |
| ❌ | Separar painel administrativo da área do cliente | Existem páginas `admin/roles` e `admin/employees`, mas não foi comprovado painel administrativo completo e separado |
| ❌ | Proteger dashboard inicial por perfil | Página inicial do starter não equivale a dashboard administrativo por permissão |
| ❌ | Auditar alterações de papéis, permissões e funcionários | Trilha de auditoria não comprovada |

**Passo 18:** frontend e backend implementados, validações e testes locais aprovados; commits locais `9fe7cc3` e `d82d671`. **Não confundir com publicação remota ou conclusão da S01.**

## 5. Segurança obrigatória

| Status | Funcionalidade | Evidência ou pendência |
| ------ | -------------- | --------------------- |
| ✅ | Restringir GET `/admin/roles` | `auth`, `verified`, `can:roles.manage` e testes |
| ✅ | Restringir POST `/admin/roles` | `auth`, `verified`, `can:roles.manage` e testes |
| ✅ | Negar cadastro de papel a visitante ou funcionário sem permissão | `RoleManagementTest` |
| ✅ | Restringir PATCH `/admin/roles/{role}` | `auth`, `verified`, `can:roles.manage`; testes de permissão e autenticação |
| ✅ | Impedir alterações inválidas ou de papel inexistente | `UpdateRoleRequest`; testes de validação e resposta 404 |
| ⏳ | Restringir operações em `/admin/employees` | `auth`, `verified`, `can:users.manage`, com testes locais aprovados; CI remoto pendente |
| ⏳ | Negar acesso administrativo a clientes, mesmo com papel administrativo | Gate com `is_staff` + `is_active` + permissão; teste Pest aprovado |
| ⏳ | Invalidar ou negar operação de funcionário desativado | Middleware `EnsureAccountIsActive` e bloqueio de autenticação por senha; testes locais aprovados |
| ⏳ | Proteger último administrador ativo | `LastAdministratorGuard`; cenários de desativação, troca de papéis e remoção de permissões |
| ❌ | Definir login interno, 2FA e sessões (PEN-15) | Há bloqueio de conta inativa, mas decisão completa e gestão de sessões ainda pendentes |
| ❌ | Exigir 2FA para operações administrativas | Suporte do Fortify não equivale à exigência obrigatória |
| ❌ | Implementar Policies para titularidade de recursos | Não comprovado |
| ❌ | Impedir cliente A de acessar recurso privado de cliente B | Comprovar nos recursos pertinentes quando implementados; pedidos pertencem à S04 |
| ❌ | Ampliar testes de acesso HTTP direto às futuras operações | Escopo atual comprovado para papéis e funcionários; outros módulos ainda pendentes |
| ❌ | Decidir privacidade/retenção da demonstração (PEN-13) | Pendente |

## 6. Testes, CI e acompanhamento

| Status | Entrega ou verificação |
| ------ | ---------------------- |
| ✅ | **`composer qa` local em 08/11/2026: 119 testes, 391 asserções, 0 falhas** |
| ✅ | PHPStan/Larastan **54/54, sem erros**; TypeScript, lint e formatação aprovados em 08/11/2026 |
| ✅ | Build de produção aprovado: **2.315 módulos transformados** em 08/11/2026 |
| ✅ | `git diff --check` sem problemas na validação antes dos commits |
| ✅ | `EmployeeManagementTest`: **25 testes aprovados** |
| ✅ | `RoleManagementTest`: **23 testes, 66 asserções** |
| ✅ | `RolePermissionAssignmentTest`: **9 testes aprovados** |
| ✅ | `RoleAccessTest`: **5 testes, 34 asserções** |
| ✅ | `RoleAuthorizationTest`: **3 testes, 3 asserções** |
| ✅ | Passo 15 (cadastro de papéis) publicado; Passo 16 publicado em `25da8fb` |
| ✅ | Passo 17 (gestão de permissões por papel) publicado em `4dde8c2` |
| ⏳ | Passo 18: implementação local commitada em `9fe7cc3`; testes em `d82d671`; **aguardando push e CI remoto** |
| ⏳ | Traduções de validação `pt_BR` incluídas no Passo 18; validação local concluída, publicação pendente |
| ✅ | Navegador manual: gestão de funcionários conferida em cadastro/edição/status; edição de papéis verificada anteriormente |
| ❌ | Suíte automatizada de navegador (E2E): não comprovada |
| ❌ | Testes de 2FA obrigatório para equipe interna: pendentes |
| ❌ | Testes de acesso a recurso privado de outro cliente: pendentes nos módulos correspondentes |
| ❌ | Revisão dos critérios AC-001 a AC-006 aplicáveis à S01: pendente |
| ❌ | Auditoria final da S01 e gate para S02: pendentes |

**Histórico preservado:** o CI de `ebbd1df` falhou por formatação na execução 41278242215; execuções posteriores dos commits `25da8fb` e `72cb4d6` foram aprovadas. Esses registros são checkpoints antigos, não validações do Passo 18. O texto do merge `4dde8c2` registra qualidade e CI do Passo 17, sem extrapolar para o Passo 18.

**Regra de testes:** pensar critérios de aceite antes de implementar; desenvolver e integrar frontend/backend reais; criar e executar a bateria formal no fim da funcionalidade; corrigir e validar antes de publicar.

## 7. Itens de Minha Conta adiados: não bloqueiam S01

| Funcionalidade | Etapa responsável |
| -------------- | ----------------- |
| Visualizar meus pedidos | S04 |
| Visualizar minhas propostas | S07 |
| Consultar solicitações de pós-venda | S08 |
| Visualizar favoritos | S09 |
| Indicadores operacionais completos | S13 |

## 8. Ordem de implementação sugerida

**Dentro de cada funcionalidade:** explicar objetivo → parte visível real → integrar backend e banco conforme necessário → revisão visual → testes ao final → correções/QA → documentação e publicação.

1. **PEN-10:** formalizar a regra do catálogo controlado de permissões (pendente; atribuição por papel já foi implementada).
2. **Passo 17 — gestão de permissões por papel:** concluído e publicado em `4dde8c2`; não refazer.
3. **Exclusão/desativação de papéis:** definir primeiro as regras de negócio, especialmente com vínculos existentes (pendente).
4. **Passo 18 — gestão de funcionários:** implementado, testado e commitado localmente; falta push e CI remoto.
5. **Painel administrativo separado:** avaliar escopo e proteção de acesso conforme requisitos (pendente).
6. **PEN-15:** definir política de autenticação da equipe, exigência de 2FA e gestão de sessões; o bloqueio de contas inativas do Passo 18 já existe, mas não conclui a PEN-15.
7. **Minha Conta:** CRUD de endereços, endereço padrão e gestão de sessões, conforme critérios da S01.
8. **Policies, titularidade e auditoria:** implementar e comprovar em recursos existentes; concluir PEN-13.
9. **Gate S01 → S02:** revisar critérios, testes de navegador, CI remoto, segurança e documentação antes de iniciar S02.

**Prioridade imediata em 08/11:** finalizar documentação local do Passo 18, push e CI. A próxima funcionalidade deve ser definida pelas pendências e critérios aplicáveis; não declarar S01 concluída por causa do QA verde.

## 9. Critérios para declarar S01 concluída

- [ ] Cadastro, login, recuperação, verificação, perfil e endereços essenciais validados (**endereços pendentes**).
- [ ] Gestão de papéis, permissões e funcionários implementada conforme escopo aprovado (**Passo 18 ainda não publicado; exclusão/desativação de papéis pendente de decisão**).
- [ ] Rotas administrativas segregadas e protegidas por permissões apropriadas (**proteções implementadas para papéis e funcionários; avaliação do painel completo pendente**).
- [ ] Política de autenticação interna e 2FA obrigatório demonstrada (**PEN-15 pendente**).
- [x] Usuários desativados bloqueados, incluindo sessões antigas (**comprovado nos testes locais do Passo 18; CI remoto pendente**).
- [ ] Policies/autorização por titularidade testadas em requisições HTTP diretas.
- [ ] Auditoria relevante e decisões PEN-10, PEN-15 e PEN-13 registradas.
- [ ] Testes locais, CI remoto, navegador e critérios de aceite com evidências (**testes locais aprovados; E2E e CI do Passo 18 pendentes**).
- [ ] Documentação e checkpoint atualizados; gate S01 aprovado para liberar S02 (**atualização local preparada; publicação e gate pendentes**).

**Observação:** Passos 16 e 17 publicados e Passo 18 validado localmente **não significam que a S01 inteira está concluída**.

## 10. Regras para todas as próximas sessões

**Este arquivo é o checkpoint de acompanhamento da S01.** Ao iniciar uma sessão, ler também `AGENTS.md` e `docs/05-implementacao/06-metodologia-implementacao-guiada.md`. Essas regras vigentes substituem roteiros antigos de backend primeiro ou TDD obrigatório; históricos antigos não devem ser reescritos.

1. Verificar a versão atual do GitHub, `git status -sb` e o código antes de confiar em status antigos.
2. Fazer uma funcionalidade por vez; não marcar uma entrega como concluída apenas porque existe tabela, esqueleto ou teste incompleto.
3. **Atualizar este arquivo ao finalizar cada fatia**, no mesmo ciclo de commit e push da funcionalidade. Registrar status, evidências, testes reais, hash do commit e próximo passo.
4. Não inventar resultados, percentuais, CI aprovado, decisões ou funcionalidades não comprovadas. Distinguir explicitamente o que está apenas local do que foi publicado.
5. O hook de histórico em `.githooks/pre-commit` existe no repositório, mas foi **desativado na configuração local** em 08/11/2026. Os históricos diários serão preenchidos manualmente e os novos `docs/09-historico/*.txt` estão ignorados pelo `.gitignore` local. Isso não remove históricos antigos já rastreados.
6. Havendo discordância entre documentos e execução, prevalecem o código e as evidências recentes; revisar o acompanhamento.
7. Encerrar a sessão informando: **feito / pendente / decisão bloqueante / próxima ação / commit / testes / situação do CI**.
8. Após publicar, atualizar o SHA de referência e o resultado exato do CI remoto; nunca antecipar a aprovação.

**Referências documentais:** `docs/05-implementacao/01-roadmap-vertical.md`, `docs/02-requisitos/01-rf-consolidados.md`, `docs/02-requisitos/04-criterios-aceite.md` e `docs/04-arquitetura/04-seguranca-e-autorizacao.md`. Alguns desses documentos podem existir somente no ambiente local e não devem ser presumidos publicados no GitHub.
