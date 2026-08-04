# Proposta técnica — Sistema de avatar personalizável (Game School)

**Documento para apresentação e exportação em PDF**  
**Data de referência:** junho de 2026  
**Valor hora de desenvolvimento:** R$ 80,00  
**Stack atual do projeto:** Laravel 12 (API + painel admin) · React (app web aluno) · Sanctum  

---

## 1. Objetivo

Implementar um **sistema de personagem personalizável** para o aluno, integrado à gamificação existente (coins, XP, roleta, inventário, emotes, presentes), com:

- Catálogo de peças desbloqueáveis (cabelo, roupa, calçado, acessórios)
- Editor de customização no app React
- Avatar visível no perfil, ranking e uso de emotes (ex.: acenar ao usar 👋)
- Painel administrativo para cadastro de peças

Este documento compara duas abordagens visuais — **2.5D** e **3D** — com prós, contras, escopo detalhado, horas e custo estimado.

> **Nota:** estimativas de **arte** (desenho 2D, rigging 3D, animações) são listadas à parte. Os valores abaixo referem-se ao **desenvolvimento de software** (backend + frontend + integrações).

---

## 2. Contexto — o que já existe no Game School

| Módulo existente | Reaproveitamento no avatar |
|------------------|----------------------------|
| API Sanctum + auth | Mesmo fluxo de token para React |
| Inventário (`aluno_itens`) | Lógica similar para peças do avatar |
| Roleta / loja / coins | Desbloqueio de peças como prêmio |
| Emotes (`roleta_itens` tipo emote) | Mapeamento emote → animação |
| Upload de imagens + silhueta (figurinhas) | Versão bloqueada de peças 2D/2.5D |
| Painel Blade (professor/direção) | Novo CRUD de peças de avatar |

O avatar **não substitui** figurinhas (álbum colecionável 2D); convivem como produtos distintos.

---

## 3. Comparativo: 2.5D vs 3D

### 3.1 O que é cada abordagem

| | **2.5D** | **3D** |
|---|----------|--------|
| **Definição** | Personagem 2D com sensação de profundidade: animações (Spine/Lottie), sprites multi-ângulo ou parallax | Modelo tridimensional (GLB/GLTF) com câmera orbitável |
| **Render no React** | Spine WebGL, Lottie, sprite sheets ou camadas PNG animadas | React Three Fiber + Three.js |
| **Arte necessária** | Sprites ou esqueleto 2D animado (Spine/DragonBones) | Modelos 3D com rig único + peças modulares |
| **Referência visual** | Jogos mobile 2D, avatares animados estilo cartoon | Avatares rotacionáveis, ambientes 3D |

### 3.2 Prós e contras

#### 2.5D

| Prós | Contras |
|------|---------|
| Visual mais rico que PNG estático, com animações fluidas (idle, acenar) | Arte exige pipeline Spine/Lottie ou vários sprites por ação |
| Melhor desempenho que 3D full na maioria dos dispositivos | Rotação 360° limitada (a menos que desenhem vários ângulos) |
| Equilíbrio entre custo de dev e impacto visual | Integração Spine/Lottie tem curva de aprendizado |
| Emotes (acenar, pular) naturais via timeline de animação | Peças modulares precisam ser desenhadas para encaixar no rig 2D |
| Funciona bem em React web e mobile WebView | Menos “wow factor” que 3D em rotação livre |
| **Menor prazo e custo de desenvolvimento** | Dependência de ferramenta de arte (Spine, etc.) |

#### 3D

| Prós | Contras |
|------|---------|
| Rotação 360°, zoom, iluminação — experiência premium | **Maior custo e prazo** de desenvolvimento |
| Peças modulares (mesh swap) bem estabelecidas em jogos | Exige modelador 3D + **rig compatível** entre todas as peças |
| Escalável para futuro (cenários 3D, AR) | Performance: mobile fraco pode sofrer |
| Personalização por cor/material no runtime | Arquivos GLB maiores → CDN, cache, loading |
| Impressiona em demos comerciais | Debug de encaixe de peças (skinning) consome tempo |
| | React Three Fiber + otimização exigem perfil mais especializado |

### 3.3 Quando escolher cada uma

| Critério | Recomendação |
|----------|--------------|
| Prazo curto / orçamento limitado | **2.5D** |
| Público infantil, escola, tablets variados | **2.5D** |
| Quer animações ricas (acenar, comemorar) sem 3D | **2.5D** |
| Quer avatar rotacionável estilo “boneco 3D” | **3D** |
| Planeja metaverso / cenário 3D no futuro | **3D** |
| Orçamento maior e artista 3D disponível | **3D** |

### 3.4 Referência rápida — 2D puro (alternativa)

Para comparação, **2D em camadas PNG** (sem Spine) seria a opção mais barática (~40% menos horas que 2.5D), porém **sem animação corporal fluida** — emotes seriam CSS simples (balançar personagem inteiro). Não detalhado nesta proposta, mas citado como plano C.

---

## 4. Escopo funcional comum (2.5D e 3D)

Ambas as opções incluem o **mesmo backend** e **mesmas regras de negócio**; muda principalmente o **frontend React** e o **formato dos assets**.

### 4.1 Funcionalidades

**Aluno (React)**  
- Visualizar avatar atual  
- Editor por abas/slots: base, cabelo, camisa, calçado, acessório  
- Equipar apenas peças **possuídas**  
- Pré-visualização destructure bloqueada (silhueta ou modelo cinza)  
- Salvar customização  
- Usar emote → animação vinculada (ex.: 👋 = acenar)  
- Exibir avatar no perfil e ranking (thumbnail)

**Administrador (Laravel Blade)**  
- CRUD de peças por slot, raridade, unidade  
- Upload de assets (PNG/sprites/Spine/GLB conforme opção)  
- Ativar/desativar peças  
- Associar peça à roleta/loja (integração)

**API (Laravel JSON)**  
- `GET /api/avatar` — configuração atual + URLs dos assets  
- `PUT /api/avatar` — salvar slots equipados + cores  
- `GET /api/avatar/pecas` — catálogo com `possui: true/false`  
- `GET /api/auth/me` — incluir resumo do avatar  
- Integração: roleta/loja/missões desbloqueiam `aluno_avatar_pecas`

### 4.2 Modelo de dados (resumo)

```text
avatar_pecas
  id, unidade_id, titulo, slot, asset_url, thumbnail_url,
  tipo_asset (spine|glb|sprite|png), raridade, coins_loja, status, meta_json

aluno_avatar_pecas
  aluno_id, avatar_peca_id, quantidade, desbloqueado_em

aluno_avatars
  aluno_id, base_id, configuracao_json, thumbnail_url, updated_at
```

---

## 5. Detalhamento do desenvolvimento por parte

### Legenda

- **Backend** = Laravel (API, banco, painel admin, integrações)  
- **Frontend** = React (app aluno)  
- **QA** = testes manuais, ajustes, documentação  
- Horas são estimativas para **1 desenvolvedor full-stack** familiarizado com o projeto  

---

## 6. Opção A — Avatar 2.5D

### 6.1 Premissas técnicas

- Personagem base exportado em **Spine** (ou Lottie para emotes pontuais)  
- Peças modulares como **attachments/skins** Spine OU sprite sheets por animação  
- React: `@esotericsoftware/spine-webgl` ou `spine-react` / Lottie para emotes  
- 5 emotes animados iniciais: idle, wave, celebrate, jump, thumbs  

### 6.2 Breakdown de horas

| # | Parte | Descrição | Horas |
|---|-------|-----------|------:|
| **A1** | **Backend — Fundação** | Migrations, models, relacionamentos, seeders de exemplo | 6 |
| **A2** | **Backend — API avatar** | GET/PUT avatar, listagem peças, validação de posse, equipar | 11 |
| **A3** | **Backend — Painel admin** | CRUD peças, upload assets/thumbnail, filtros por slot/unidade | 13 |
| **A4** | **Backend — Integrações** | Desbloqueio via roleta, loja (coins), inventário unificado | 8 |
| **A5** | **Backend — Thumbnail** | Geração de preview estático (frame Spine ou composição PNG) para ranking | 6 |
| **A6** | **Backend — Emotes** | Campo `animacao` nos emotes, endpoint usar emote, duração | 5 |
| **A7** | **Frontend — Setup Spine/Lottie** | Integração biblioteca, loader, cache, fallback loading | 9 |
| **A8** | **Frontend — Avatar viewer** | Canvas/player, idle loop, troca de skins/attachments | 14 |
| **A9** | **Frontend — Editor** | UI abas, grid peças, bloqueado/desbloqueado, salvar | 16 |
| **A10** | **Frontend — Emotes** | State machine idle → emote → idle, 5 animações mapeadas | 12 |
| **A11** | **Frontend — Perfil/Ranking** | Exibir avatar/thumbnail, integração `/auth/me` | 6 |
| **A12** | **Frontend — API client** | Hooks, tipos TypeScript, tratamento erros, loading | 6 |
| **A13** | **QA + Docs** | Testes E2E manuais, `API.md`, ajustes pós-integração | 7 |
| | | **Subtotal Opção 2.5D** | **119 h** |

### 6.3 Custos — Opção 2.5D

| Item | Cálculo | Valor |
|------|---------|------:|
| Desenvolvimento (119 h × R$ 80) | 119 × 80 | R$ 9.520,00 |
| **Valor fechado proposta 2.5D** | | **R$ 9.500,00** |

### 6.4 Arte necessária (não incluída no valor acima)

| Entregável artístico | Quantidade sugerida | Observação |
|----------------------|---------------------|------------|
| Personagem base Spine (M/F) | 2 | Com rig e skin default |
| Peças modulares (slots) | 15–20 | Cabelo, roupa, calçado, etc. |
| Animações | 6 | idle, wave, celebrate, jump, thumbs, sleep |
| Thumbnails | Automático ou 1 por peça | Pode ser gerado no admin |

*Orçamento de arte: negociar à parte com ilustrador/animador Spine.*

### 6.5 Prazo estimado

| Cenário | Duração |
|---------|---------|
| 1 dev full-time (40 h/sem) | ~3 semanas |
| 1 dev part-time (20 h/sem) | ~6 semanas |

*Depende de assets Spine prontos; atraso na arte bloqueia A7–A10.*

---

## 7. Opção B — Avatar 3D

### 7.1 Premissas técnicas

- Modelo base **GLB** (masculino/feminino) com skeleton compartilhado  
- Peças 3D modulares (mesh swap ou skinned mesh)  
- React: `@react-three/fiber` + `@react-three/drei` + `three`  
- OrbitControls (rotação), iluminação básica, HDR opcional  
- Emotes: animações clip no GLB ou overlay 2D (emoji + tween)  

### 7.2 Breakdown de horas

| # | Parte | Descrição | Horas |
|---|-------|-----------|------:|
| **B1** | **Backend — Fundação** | Migrations, models (mesma base da opção A) | 8 |
| **B2** | **Backend — API avatar** | GET/PUT avatar, peças GLB, metadados materiais/cores | 16 |
| **B3** | **Backend — Painel admin** | CRUD + upload GLB/ thumbnails + validação tamanho/mime | 19 |
| **B4** | **Backend — Integrações** | Roleta, loja, desbloqueio (igual 2.5D) | 11 |
| **B5** | **Backend — Thumbnail** | Render preview (screenshot server-side ou placeholder) | 11 |
| **B6** | **Backend — Emotes** | Mapeamento emote → animation clip / overlay | 8 |
| **B7** | **Frontend — Setup R3F** | Canvas, lights, OrbitControls, DRACO/GLTF loader | 16 |
| **B8** | **Frontend — Avatar 3D core** | Carregar base, anexar peças, materiais, cores | 29 |
| **B9** | **Frontend — Editor 3D** | UI slots + preview 3D ao vivo, peças bloqueadas (wireframe/cinza) | 26 |
| **B10** | **Frontend — Animações** | idle + 5 emotes (clips GLB ou procedural tween) | 23 |
| **B11** | **Frontend — Performance** | LOD, lazy load, cache GLB, mobile fallback | 13 |
| **B12** | **Frontend — Perfil/Ranking** | Thumbnail estático + link para viewer | 10 |
| **B13** | **Frontend — API client** | Hooks, types, error boundaries WebGL | 10 |
| **B14** | **QA + Docs** | Testes cross-browser, GPU fraca, documentação | 13 |
| | | **Subtotal Opção 3D** | **213 h** |

### 7.3 Custos — Opção 3D

| Item | Cálculo | Valor |
|------|---------|------:|
| Desenvolvimento (213 h × R$ 80) | 213 × 80 | R$ 17.040,00 |
| **Valor fechado proposta 3D** | | **R$ 17.000,00** |

### 7.4 Arte necessária (não incluída)

| Entregável artístico | Quantidade sugerida | Observação |
|----------------------|---------------------|------------|
| Modelo base rigged (M/F) | 2 | GLB, ~5k–15k tris low-poly stylized |
| Peças modulares | 15–20 | Mesmo skeleton |
| Animações (clips) | 6 | Exportadas no GLB ou separadas |
| Texturas | PBR ou solid colors | Por peça |

*Orçamento 3D costuma ser **superior** ao 2.5D.*

### 7.5 Prazo estimado

| Cenário | Duração |
|---------|---------|
| 1 dev full-time | ~5,5 semanas |
| 1 dev part-time | ~11 semanas |

---

## 8. Comparativo financeiro resumido

| Opção | Horas dev | Referência (R$ 80/h) | **Valor fechado** | Prazo (40h/sem) |
|-------|----------:|-------------------:|------------------:|----------------:|
| **2.5D** | 119 h | R$ 9.520 | **R$ 9.500** | ~3 semanas |
| **3D** | 213 h | R$ 17.040 | **R$ 17.000** | ~5,5 semanas |
| **Diferença (3D − 2.5D)** | +94 h | +R$ 7.520 | **+R$ 7.500** | +2,5 sem |

---

## 9. Fases de entrega (ambas as opções)

### Fase 1 — MVP (entrega parcial)

**Escopo:** base + 3 slots (cabelo, camisa, calçado) + idle + 2 emotes + API + admin básico  

| | 2.5D | 3D |
|---|-----:|---:|
| Horas | 69 h | 125 h |
| Valor | **R$ 5.500** | **R$ 10.000** |

### Fase 2 — Completo

**Escopo:** todos os slots, 5 emotes, ranking, roleta/loja, polish  

| | 2.5D | 3D |
|---|-----:|---:|
| Horas adicionais | 50 h | 88 h |
| Valor adicional | **R$ 4.000** | **R$ 7.000** |

*Fase 1 permite validar com usuários antes de investir no restante.*

---

## 10. Riscos e dependências

| Risco | Impacto | Mitigação |
|-------|---------|-----------|
| Arte atrasada | Bloqueia frontend | MVP com assets placeholder |
| Peças não encaixam (3D) | +20–40 h extras | Protótipo com 2 peças antes do lote |
| Performance mobile (3D) | UX ruim | Fallback thumbnail 2D + viewer simplificado |
| Spine sem licença comercial | Legal | Confirmar licença Spine ou usar Lottie |
| Escopo creep (AR, chat avatar) | Orçamento estoura | Contrato por fases |

---

## 11. O que **não** está incluído

- Criação de modelos 3D, sprites ou animações Spine  
- App Flutter nativo (este documento considera **React web**; portar 3D para Flutter seria projeto aparte)  
- Hospedagem CDN para GLB (configuração básica pode ser incluída; custo mensal não)  
- Sistema de evolução/nível do personagem (RPG)  
- Multiplayer / salas 3D  

---

## 12. Recomendação

Para o **Game School** (contexto escolar, gamificação, React web, integração com roleta já existente):

> **Recomenda-se a Opção 2.5D (Spine/Lottie)**  
> Melhor equilíbrio entre **custo (R$ 9.500)**, **prazo (~3 semanas)** e **animações de emote** (acenar, comemorar).  
> A Opção 3D (**R$ 17.000**) faz sentido se houver requisito explícito de **rotação 360°** e orçamento de arte 3D.

---

## 13. Como exportar este documento em PDF

### Opção A — HTML (recomendado no Windows, sem Pandoc)

```bash
npm install
node scripts/generate-proposta-html.mjs
```

Abra `docs/proposta-avatar.html` no Chrome/Edge → **Ctrl+P** → **Salvar como PDF**.

### Opção B — Pandoc (se instalado)

```bash
winget install JohnMacFarlane.Pandoc
pandoc docs/PROPOSTA-SISTEMA-AVATAR-PERSONAGEM.md -o docs/proposta-avatar.pdf
```

### Opção C — Manual

Abrir o `.md` no Cursor/VS Code → preview → imprimir como PDF.

---

## 14. Aceite e próximos passos

| Passo | Responsável |
|-------|-------------|
| Escolher opção (2.5D ou 3D) e fase (MVP ou completo) | Cliente |
| Definir quantidade de peças e estilo visual | Cliente + arte |
| Contratar/desenvolver assets | Arte (paralelo) |
| Iniciar Fase 1 backend + placeholders | Desenvolvimento |
| Integrar assets finais | Desenvolvimento + arte |

---

**Documento gerado para o projeto Game School**  
Arquivo: `docs/PROPOSTA-SISTEMA-AVATAR-PERSONAGEM.md`  
Valor hora aplicado: **R$ 80,00** em todos os cálculos.
