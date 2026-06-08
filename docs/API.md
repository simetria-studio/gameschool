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
| GET | `/roletas` | Lista roletas ativas (paginado). Aluno: da sua turma; inclui status do giro grátis. |
| GET | `/roletas/{id}` | Segmentos da roleta (para desenhar a UI). |
| GET | `/roletas/{id}/status` | Giro grátis disponível, limite semanal, custo em coins, saldo do aluno. |
| POST | `/roletas/{id}/giros` | Gira a roleta. Body: `tipo` = `gratis` \| `pago` (omitido se `somente_gratis`). Retorna prêmio e saldo. |
| GET | `/roletas/{id}/giros` | Histórico de giros. |
| GET | `/inventario` | Inventário do aluno. Query: `tipo` = `personagem` \| `figurinha` \| `emote`; staff: `id_aluno`. Retorna resumo, categorias e `imagem_url`. |
| GET | `/inventario/{aluno_item_id}` | Detalhe de um item do inventário (com datas). |
| GET | `/presentes/destinatarios` | Busca alunos para enviar presente. Query: `search` (mín. 2 chars). Mesma escola, exceto o próprio aluno. |
| GET | `/presentes` | Presentes recebidos/enviados. Query: `tipo` = `recebidos` \| `enviados`. |
| POST | `/presentes` | Envia presente: `nome_destino`, `aluno_item_id`, opcional `quantidade`, `mensagem`. |
| GET | `/atitudes` | Lista atitudes (paginado). Não-master: só unidade do utilizador. |
| GET | `/loja-itens` | Itens da loja (paginado). Query: `apenas_ativos` (default true). |
| GET | `/pedidos` | Lista pedidos (paginado). Aluno: só os seus; outros: regras por unidade. |
| GET | `/ranking` | Ranking. Query: `por` = `coins` \| `xp`, `per_page` (1–100), `unidade_id` (só master). |

---

## Roleta Premiada (com token, aluno)

- **Giro grátis:** 1 por semana (segunda a domingo).
- **Giro pago:** desconta `custo_coins` da roleta.
- **Prêmios:** personagens, figurinhas, emotes, coins, XP ou **baú** (2–4 itens aleatórios do pool).
- Emotes e itens vão para o inventário e podem ser enviados como presente.

### Fluxo no app

1. `GET /roletas` — lista roletas disponíveis  
2. `GET /roletas/{id}` — **segmentos para desenhar a roleta** (fatias/cores/imagens)  
3. `GET /roletas/{id}/status` — giro grátis disponível + saldo de coins  
4. `POST /roletas/{id}/giros` — girar; retorna prêmio ganho  
5. `GET /inventario` — itens que o aluno já possui  

### Exemplo: `GET /roletas/1`

```json
{
  "data": {
    "id": 1,
    "titulo": "Roleta Premiada",
    "descricao": "Gire e ganhe prêmios!",
    "custo_coins": 50,
    "giros_gratis_por_semana": 1,
    "somente_gratis": false,
    "status": "ativa",
    "total_segmentos": 6,
    "unidade": { "id": 1, "titulo": "Escola Demo" },
    "turmas": [{ "id": 2, "nome": "3º Ano A" }],
    "giro_gratis": {
      "disponivel": true,
      "ilimitado": false,
      "somente_gratis": false,
      "restantes": 1,
      "limite_semana": 1,
      "usados_semana": 0,
      "proximo_gratis_em": null
    },
    "segmentos": [
      {
        "id": 1,
        "titulo": "Herói Espacial",
        "tipo": "item",
        "cor": "#F2B233",
        "ordem": 1,
        "item": {
          "id": 3,
          "titulo": "Herói Espacial",
          "tipo": "personagem",
          "emoji": null,
          "imagem": "/imgs/roleta/heroi-1717600000.png",
          "imagem_url": "https://seudominio.test/imgs/roleta/heroi-1717600000.png",
          "raridade": "raro"
        }
      },
      {
        "id": 2,
        "titulo": "50 Coins",
        "tipo": "coins",
        "cor": "#FFD700",
        "ordem": 2,
        "coins": 50,
        "emoji": "🪙"
      },
      {
        "id": 3,
        "titulo": "Foguete",
        "tipo": "item",
        "cor": "#4CAF50",
        "ordem": 3,
        "item": {
          "id": 5,
          "titulo": "Foguete",
          "tipo": "emote",
          "emoji": "🚀",
          "imagem": null,
          "imagem_url": null,
          "raridade": "comum"
        }
      },
      {
        "id": 4,
        "titulo": "Baú Misterioso",
        "tipo": "bau",
        "cor": "#9C27B0",
        "ordem": 4,
        "emoji": "🎁"
      },
      {
        "id": 5,
        "titulo": "Item Surpresa",
        "tipo": "item_aleatorio",
        "cor": "#FF5722",
        "ordem": 5,
        "emoji": "🎲"
      }
    ]
  }
}
```

**Como usar no front:** itere `data.segmentos` para montar cada fatia da roleta.

| Campo | Uso na UI |
|-------|-----------|
| `cor` | cor de fundo da fatia |
| `titulo` | label do prêmio |
| `tipo` | `item` \| `item_aleatorio` \| `coins` \| `xp` \| `bau` |
| `item.imagem_url` | `<img>` para personagem/figurinha |
| `item.emoji` ou `emoji` | texto emoji para emote/coins/baú |
| `coins` / `xp` | valor numérico quando `tipo` for coins/xp |

> O conteúdo exato do **baú** e do **item aleatório** só vem depois do giro (sorteio no servidor).

### Exemplo: `GET /roletas/1/status`

```json
{
  "data": {
    "giro_gratis": {
      "disponivel": true,
      "ilimitado": false,
      "somente_gratis": false,
      "restantes": 1,
      "limite_semana": 1,
      "usados_semana": 0,
      "proximo_gratis_em": null
    },
    "somente_gratis": false,
    "giros_gratis_por_semana": 1,
    "custo_coins": 50,
    "coins_aluno": 120
  }
}
```

**Modos configuráveis no painel:**

| Config | Comportamento |
|--------|----------------|
| `somente_gratis: true` | Giros ilimitados, sem custo. Body do POST pode omitir `tipo`. |
| `giros_gratis_por_semana: N` | N giros grátis por semana; depois usa `custo_coins`. |
| `giros_gratis_por_semana: 0` | Sem giros grátis; só giro pago (`tipo: "pago"`). |
| `custo_coins: 0` | Só giros grátis (até o limite semanal). |

### Exemplo: `POST /roletas/1/giros`

Body: `{ "tipo": "gratis" }` ou `{ "tipo": "pago" }` (se `somente_gratis`, envie `{}` ou `"gratis"`)

```json
{
  "message": "Roleta girada com sucesso!",
  "data": {
    "giro": {
      "id": 10,
      "tipo": "gratis",
      "custo_coins": 0,
      "coins_ganho": 0,
      "xp_ganho": 0,
      "premios": [
        {
          "id": 3,
          "titulo": "Herói Espacial",
          "tipo": "personagem",
          "emoji": null,
          "raridade": "raro"
        }
      ],
      "segmento": {
        "id": 1,
        "titulo": "Herói Espacial",
        "tipo": "item"
      },
      "created_at": "2026-06-05T16:30:00+00:00"
    },
    "coins_aluno": 120,
    "xp_aluno": 340
  }
}
```

Use `giro.segmento.id` para animar a roleta parando na fatia correta.  
Use `giro.premios[]` para mostrar o popup de recompensa.

### Exemplo: `GET /inventario`

Inventário completo com imagens absolutas (`imagem_url`), agrupado por tipo:

```json
{
  "data": {
    "aluno": {
      "id": 3,
      "nome": "Ana Silva",
      "coins": 120,
      "xp": 340
    },
    "resumo": {
      "total_quantidade": 5,
      "total_unicos": 3,
      "por_tipo": {
        "personagem": { "unicos": 1, "quantidade": 1 },
        "figurinha": { "unicos": 1, "quantidade": 2 },
        "emote": { "unicos": 1, "quantidade": 2 }
      }
    },
    "categorias": [
      {
        "tipo": "personagem",
        "titulo": "Personagens",
        "total": 1,
        "unicos": 1,
        "itens": [
          {
            "id": 15,
            "quantidade": 1,
            "pode_enviar": true,
            "updated_at": "2026-06-02T14:30:00+00:00",
            "item": {
              "id": 5,
              "titulo": "Herói Espacial",
              "label": "Herói Espacial",
              "tipo": "personagem",
              "tipo_label": "Personagem",
              "emoji": null,
              "imagem": "/imgs/roleta/heroi-123.png",
              "imagem_url": "https://seudominio.test/imgs/roleta/heroi-123.png",
              "raridade": "raro",
              "raridade_label": "Raro"
            }
          }
        ]
      }
    ],
    "itens": []
  }
}
```

**No app Flutter:** use `item.imagem_url` para personagens/figurinhas; para emotes use `item.emoji` quando `imagem_url` for `null`.

Query opcional: `?tipo=emote` | `personagem` | `figurinha`  
Staff (master/direção/professor): `?id_aluno=3`

---

## Presentes (com token, perfil aluno)

### Exemplo: `GET /presentes/destinatarios?search=ana`

```json
{
  "data": [
    {
      "id": 5,
      "nome": "Ana Silva",
      "turma": { "id": 2, "nome": "3º Ano A" }
    },
    {
      "id": 12,
      "nome": "Ana Paula",
      "turma": { "id": 3, "nome": "3º Ano B" }
    }
  ]
}
```

Use para autocomplete ao digitar o nome. No envio, use o **nome completo** se houver homônimos.

### Exemplo: `POST /presentes`

```json
{
  "nome_destino": "Ana Silva",
  "aluno_item_id": 17,
  "quantidade": 1,
  "mensagem": "Para você!"
}
```

Resposta:

```json
{
  "message": "Presente enviado com sucesso!",
  "data": {
    "id": 4,
    "quantidade": 1,
    "destinatario": {
      "id": 5,
      "nome": "Ana Silva"
    }
  }
}
```

Se o nome for ambíguo, retorna **422** com a lista de nomes encontrados.

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
