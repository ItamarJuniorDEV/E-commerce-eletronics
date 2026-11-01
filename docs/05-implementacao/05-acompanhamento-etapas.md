# Acompanhamento das etapas — S01: Identidade, Conta, RBAC e Administração

> **Documento de continuidade entre sessões.** Atualizado em **01/11/2026**.
> **Fonte de verdade:** código + testes + execução no navegador + commits. O planejamento sozinho não comprova implementação.
>
> **Legenda:** ✅ implementado e validado no escopo indicado; ❌ falta implementar, completar ou comprovar. Não usar a quantidade de itens marcados para calcular uma porcentagem sem auditoria.

## 0. Ponto de retomada

- **Etapa:** S01 em andamento. **S02 (catálogo) ainda não iniciada.**
- **Última funcionalidade concluída:** cadastro de papéis, passo 15.
- **Próxima funcionalidade recomendada:** **passo 16, edição de papéis**, com testes Pest, proteção de acesso, navegador, `composer qa`, commit e push.
- **Últimos commits publicados:** `c2778f6` (automatização do histórico) e `54b6bf5` (cadastro de papéis), na `main`.
- **Qualidade local confirmada em 01/11/2026:** `composer qa`: **70 testes, 218 asserções**, PHPStan sem erros, TypeScript, lint e build aprovados.
- **CI remoto do commit `54b6bf5`:** **não confirmado** na última checagem, não registrar como aprovado sem evidência.
- **Decisões abertas:** PEN-10 (catálogo de permissões), PEN-15 (autenticação interna, 2FA e sessões), PEN-13 (privacidade e retenção).

## 1. Autenticação e conta do cliente

| Status | Funcionalidade | Evidência ou pendência |
|---|---|---|
| ✅ | Cadastro de cliente | Testes do starter Laravel/Fortify (`RegistrationTest`) |
| ✅ | Login do cliente | `AuthenticationTest` |
| ✅ | Logout | `AuthenticationTest` |
| ✅ | Recuperar senha | `PasswordResetTest` |
| ✅ | Verificar e-mail | `EmailVerificationTest` |
| ✅ | Limitar tentativas de login | `AuthenticationTest` |
| ✅ | Alterar nome e e-mail do perfil | `ProfileUpdateTest` |
| ✅ | Alterar senha | `SecurityTest` |
| ✅ | Suporte técnico a 2FA e passkeys | Fortify / `SecurityTest`; **ainda não são obrigatórios para internos** |
| ❌ | Cadastrar endereço | CRUD não comprovado |
| ❌ | Editar endereço | CRUD não comprovado |
| ❌ | Remover endereço | CRUD não comprovado |
| ❌ | Escolher endereço padrão | Não comprovado |
| ❌ | Visualizar sessões ativas | Não comprovado |
| ❌ | Encerrar sessões ativas | Não comprovado |
| ❌ | Registrar eventos relevantes de autenticação | Eventos/auditoria não comprovados |

## 2. Papéis (Roles)

| Status | Funcionalidade | Evidência ou pendência |
|---|---|---|
| ✅ | Tabela `roles` e Model `Role` | Migrations e model |
| ✅ | Tabela `role_user` | Relacionamento N:N e testes |
| ✅ | Usuário com mais de um papel | `UserRolesTest` |
| ✅ | Listagem no React/Inertia | `RoleController@index`, `roles.tsx`, `RoleAccessTest` |
| ✅ | Cadastrar papel pelo formulário | `POST /admin/roles`, `RoleController@store`, `StoreRoleRequest` |
| ✅ | Validar nome obrigatório | `RoleManagementTest` |
| ✅ | Validar slug obrigatório | `RoleManagementTest` |
| ✅ | Bloquear slug duplicado | `RoleManagementTest` e navegador |
| ✅ | Bloquear slug inválido | `RoleManagementTest` e navegador |
| ✅ | Bloquear acesso sem `roles.manage` | Rotas, Gate e testes GET/POST |
| ❌ | **Editar papel** | **Passo 16, próxima fatia** |
| ❌ | Definir se papel pode ser excluído/desativado | Regra de negócio a decidir, principalmente se estiver em uso |

## 3. Permissões (Permissions)

| Status | Funcionalidade | Evidência ou pendência |
|---|---|---|
| ✅ | Tabela `permissions` e Model `Permission` | Migrations, model e testes |
| ✅ | Tabela `permission_role` | Relacionamento e testes |
| ✅ | Relacionar papéis com permissões no backend | `RolePermissionsTest` |
| ✅ | `User::hasPermission()` | `UserPermissionsTest` |
| ✅ | Gate `roles.manage` | `AppServiceProvider` |
| ✅ | Bloquear listagem/cadastro de papéis sem permissão | `RoleAccessTest`, `RoleManagementTest` |
| ❌ | **Definir catálogo de permissões (PEN-10)** | Decidir permissões fixas no código ou criadas no painel |
| ❌ | Gerenciar catálogo de permissões conforme decisão PEN-10 | Interface/funcionalidade não comprovada |
| ❌ | Atribuir e remover permissões dos papéis pelo painel | Não comprovado |
| ❌ | Consultar permissões de cada papel pelo painel | Não comprovado |
| ❌ | Proteger todas as demais operações administrativas com permissões adequadas | Hoje há prova específica para `roles.manage` |

## 4. Funcionários e administração

| Status | Funcionalidade | Evidência ou pendência |
|---|---|---|
| ❌ | Cadastrar funcionários pelo painel | Não comprovado |
| ❌ | Editar funcionários | Não comprovado |
| ❌ | Ativar/desativar funcionários | Não comprovado |
| ❌ | Atribuir um ou vários papéis a funcionários pelo painel | Relação N:N existe; gestão no painel não |
| ❌ | Consultar os acessos de cada funcionário | Não comprovado |
| ❌ | Impedir operação de funcionário desativado com sessão antiga | Não comprovado |
| ❌ | Impedir ficar sem administrador ativo | Regra RF-13-014 não comprovada |
| ❌ | Área administrativa separada da área do cliente | Existe uma página `admin/roles`, não um painel administrativo separado completo |
| ❌ | Dashboard inicial protegido por perfil | Página do starter não equivale a dashboard administrativo por permissões |
| ❌ | Auditar alterações de papéis e permissões | Não comprovado |

## 5. Segurança obrigatória

| Status | Funcionalidade | Evidência ou pendência |
|---|---|---|
| ✅ | Restringir GET `/admin/roles` | Middleware + testes |
| ✅ | Restringir POST `/admin/roles` | Middleware + testes |
| ✅ | Negar cadastro a visitante ou usuário sem permissão | `RoleManagementTest` |
| ❌ | **Definir login interno, 2FA e sessões (PEN-15)** | Decisão pendente |
| ❌ | **Exigir 2FA para operações administrativas** | Suporte do Fortify não equivale a exigência |
| ❌ | Invalidar ou negar operação de funcionário desativado | Não comprovado |
| ❌ | Implementar Policies para titularidade de recursos | Não comprovado |
| ❌ | Impedir cliente A de acessar recurso privado de cliente B | Testar em recurso existente na S01; pedidos são da S04 |
| ❌ | Ampliar testes de acesso por HTTP direto a operações novas | Prova atual apenas nas rotas de papéis |
| ❌ | **Decidir privacidade/retenção da demonstração (PEN-13)** | Decisão pendente |

## 6. Testes, CI e acompanhamento

| Status | Entrega ou verificação |
|---|---|
| ✅ | `composer qa` local: 70 testes e 218 asserções aprovados em 01/11/2026 |
| ✅ | PHPStan 0 erros, TypeScript, lint e build local aprovados |
| ✅ | `RoleManagementTest`: 8 testes, 22 asserções |
| ✅ | Testes de autorização permitida e negada para listagem e cadastro de papéis |
| ✅ | Teste no navegador: cadastro válido, slug repetido e slug inválido |
| ✅ | Commits `c2778f6` e `54b6bf5` publicados no GitHub |
| ✅ | Hook `pre-commit` gerou histórico diário durante os dois commits locais |
| ❌ | Conferir resultado final do GitHub Actions para `54b6bf5` |
| ❌ | Testes de funcionário desativado e sessão antiga |
| ❌ | Testes de 2FA obrigatório para internos |
| ❌ | Testes de tentativa de acesso a recursos de outro cliente |
| ❌ | Verificar critérios AC-001 a AC-006 aplicáveis à S01, separando AC dependentes da S04 |
| ❌ | Auditoria final dos requisitos da S01 e gate para S02 |

## 7. Itens de Minha Conta adiados: não bloqueiam S01

| Funcionalidade | Etapa responsável |
|---|---|
| Visualizar meus pedidos | S04 |
| Visualizar minhas propostas | S07 |
| Consultar solicitações de pós-venda | S08 |
| Visualizar favoritos | S09 |
| Indicadores operacionais completos | S13 |

## 8. Ordem de implementação sugerida

1. **Passo 16:** edição de papéis (TDD, backend, formulário, testes no navegador).
2. Resolver **PEN-10**; preparar gestão apropriada de permissões.
3. Associar/remover permissões de papéis pelo painel.
4. CRUD administrativo de funcionários, atribuição de papéis e prevenção de ausência de administrador.
5. Painel administrativo separado, com acesso restrito.
6. Resolver **PEN-15** e obrigar 2FA interno; gerir sessões e usuários desativados.
7. CRUD de endereços, endereço padrão e sessões na área do cliente.
8. Policies, autorização por titularidade, auditoria e **PEN-13**.
9. Testes de ponta a ponta da S01, CI, documentação e fechamento do gate.

## 9. Critérios para declarar S01 concluída

- [ ] Cadastro, login, recuperação, verificação, perfil e endereços essenciais validados.
- [ ] Gestão de papéis, permissões e funcionários implementada conforme escopo aprovado.
- [ ] Rotas administrativas segregadas e protegidas por permissão.
- [ ] Política de autenticação interna e 2FA obrigatório demonstrada.
- [ ] Usuários desativados bloqueados, incluindo sessões antigas.
- [ ] Policies/autorização por titularidade testadas em requisições HTTP diretas.
- [ ] Auditoria relevante e decisões PEN-10, PEN-15 e PEN-13 registradas.
- [ ] Testes locais, CI remoto, navegador e critérios de aceite com evidência.
- [ ] Documentação e checkpoint atualizados; liberar S02.

## 10. REGRAS PARA TODAS AS PRÓXIMAS SESSÕES

**Este arquivo é o checkpoint de acompanhamento da S01.** Ao iniciar uma sessão, ler este documento antes de decidir a próxima tarefa.

1. Verificar a versão atual do GitHub, `git status -sb` e o código antes de confiar em status antigos.
2. Fazer uma funcionalidade por vez. Evitar marcar uma funcionalidade como feita apenas porque já existe tabela, esqueleto ou teste incompleto.
3. **Atualizar este arquivo ao finalizar cada fatia**, no mesmo ciclo de commit e push da funcionalidade. Ajustar os status, a última entrega, os testes reais, o hash do commit e o próximo passo.
4. Não inventar resultados, percentuais, CI verde, decisões ou funcionalidades não comprovadas. Para requisitos definidos em etapas futuras, registrar o adiamento explicitamente.
5. O hook atual em `.githooks/pre-commit` **atualiza automaticamente apenas** `docs/09-historico/*.txt`. **Ele não modifica este plano**. Sua atualização precisa ser feita na sessão de desenvolvimento ou por uma automação específica.
6. Havendo discordância entre documento e execução, prevalecem o código e as evidências recentes; corrigir o plano imediatamente.
7. Ao encerrar a sessão, deixar claro: **feito / pendente / decisão bloqueante / próxima ação / commit / testes / situação do CI**.

**Referências:** `docs/05-implementacao/01-roadmap-vertical.md`, `docs/02-requisitos/01-rf-consolidados.md`, `docs/02-requisitos/04-criterios-aceite.md`, `docs/04-arquitetura/04-seguranca-e-autorizacao.md`. Alguns desses arquivos podem existir apenas no ambiente local e não devem ser presumidos publicados no GitHub.
