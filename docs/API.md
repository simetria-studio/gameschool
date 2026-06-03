# API Game School

Base URL: `{APP_URL}/api` (ex.: `https://seudominio.test/api`).

Rotas autenticadas: header `Authorization: Bearer {token}` (Laravel Sanctum).

---

## Autenticação (sem token)

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/auth/login` | Login: `username`, `password`, opcional `device_name`. Retorna `token`, `token_type`, `user`. |
| POST | `/auth/qr-login` | Login por QR: `qr_token`, opcional `device_name`. Mesmo formato de resposta. |

---

## Autenticação (com token)

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/auth/me` | Dados do utilizador autenticado. |
| POST | `/auth/logout` | Revoga o token atual. |

---

## Dados (com token)

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/unidades` | Lista unidades (`id`, `titulo`). Master: todas; outros: só a própria. |
| GET | `/turmas` | Lista turmas. Query: `unidade_id` opcional. Filtrado por papel/unidade/turmas do professor. |
| GET | `/alunos` | Lista alunos (paginação `per_page`, default 20). Query: `search`, `turma_id`. |
| GET | `/missoes` | Lista missões (paginado), filtrado por unidade/turma conforme o papel. |
| GET | `/quizzes` | Lista quizzes (paginado). Aluno: só ativos da sua turma; inclui tentativas restantes. |
| GET | `/quizzes/{id}` | Detalhe do quiz com perguntas (sem marcar opção correta). |
| POST | `/quizzes/{id}/tentativas` | Aluno envia respostas: `respostas[]` com `pergunta_id`, `opcao_id`. Grava em `quiz_respostas`, corrige e recompensa se aprovado. |
| GET | `/quizzes/{id}/tentativas` | Histórico de tentativas. Query `com_respostas=true` inclui detalhe por pergunta. |
| GET | `/quizzes/{id}/tentativas/{tentativa_id}` | Detalhe de uma tentativa com todas as respostas. |
| GET | `/atitudes` | Lista atitudes (paginado). Não-master: só unidade do utilizador. |
| GET | `/loja-itens` | Itens da loja (paginado). Query: `apenas_ativos` (default true). |
| GET | `/pedidos` | Lista pedidos (paginado). Aluno: só os seus; outros: regras por unidade. |
| GET | `/ranking` | Ranking. Query: `por` = `coins` \| `xp`, `per_page` (1–100), `unidade_id` (só master). |

---

## Pedidos (com token)

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/pedidos` | Cria pedido: `id_aluno`, `id_produto`, opcional `quantidade` (default 1). Status `pendente`. |
| POST | `/pedidos/{pedido}/aprovar` | Aprova e processa (direção/master). Desconta coins, baixa estoque, notifica aluno. |

---

## Notificações (com token, perfil aluno)

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/notificacoes` | Lista notificações; `meta.unread_count`; `per_page` 1–50. |
| POST | `/notificacoes/marcar-todas-lidas` | Marca todas como lidas. |
| POST | `/notificacoes/{id}/marcar-lida` | Marca uma como lida (`id` = UUID). |

---

## Códigos de erro comuns

- **401**: token ausente ou inválido.
- **403**: sem permissão para o recurso.
- **422**: validação ou regra de negócio (ex.: aluno não vinculado em notificações).
