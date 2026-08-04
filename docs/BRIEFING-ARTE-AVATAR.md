# Briefing de arte — Avatar em camadas (Game School)

**Produto:** personagem personalizável modular (masculino / feminino)  
**Referência visual:** sheet “Personagem completo” (base + cabelos + rostos + roupas + calçados + acessórios)  
**Formato:** PNG transparente em camadas (mesmo canvas)  
**Data:** julho 2026  

---

## 1. Objetivo

Cada peça é um **PNG transparente** no mesmo tamanho. O app empilha as layers na ordem abaixo para montar o personagem.

Estilo: cartoon / chibi amigável (jogo escolar). Não realista.

---

## 2. Canvas e exportação

| Item | Valor |
|------|------:|
| **Canvas** | **512 × 820 px** |
| Fundo | **Transparente** |
| Formato | PNG-24 + alpha |
| Thumb (opcional) | **256 × 256** crop da peça |

Templates em: `public/imgs/avatar/templates/`

---

## 3. Slots e ordem das camadas (atrás → frente)

Igual ao sheet de montagem:

| Z | Slot (sistema) | Sheet | Conteúdo |
|--:|----------------|-------|----------|
| 1 | `base` | Base (corpo) | Corpo careca **sem** rosto/cabelo/roupa/sapato (pode ter underwear) |
| 2 | `sombra` | Sombras / efeitos | Elipse no chão / FX |
| 3 | `calcado` | Calçados | Só sapatos |
| 4 | `roupa_inferior` | Roupas (parte inferior) | Calças, shorts, saias |
| 5 | `roupa_superior` | Roupas (parte superior) | Camisetas, hoodies, vestidos |
| 6 | `rosto` | Rostos / expressões | Olhos + boca (+ blush) |
| 7 | `cabelo` | Cabelos | Só cabelo (cores = arquivos separados) |
| 8 | `acessorio_cabeca` | Acessórios (cabeça) | Boné, fone, gorro |
| 9 | `acessorio_rosto` | Acessórios (rosto) | Óculos |
| 10 | `acessorio_outro` | Acessórios (outros) | Mochila, colar, relógio |

**Gênero:** `masculino` | `feminino` | `unissex`

---

## 4. Regras por slot

**base** — Silhueta completa; sem face desenhada (o slot `rosto` cuida disso).  
**rosto** — Só elementos do rosto, alinhados à cabeça da base.  
**cabelo** — Não redesenhar a cabeça. Variantes de cor = arquivos distintos.  
**roupa_superior** — Mangas cobrem os braços da base.  
**roupa_inferior** — Cintura alinhada; não redesenhar o torso inteiro.  
**calcado** — Só a região dos pés.  
**acessorio_*** — Uma peça por arquivo.

---

## 5. Nomenclatura

```text
{slot}-{genero}-{slug}.png
```

Exemplos:

```text
base-masculino-padrao.png
base-feminino-padrao.png
sombra-unissex-padrao.png
rosto-unissex-neutro.png
rosto-unissex-feliz.png
cabelo-masculino-curto-castanho.png
roupa_superior-unissex-hoodie-azul.png
roupa_inferior-unissex-jeans.png
calcado-unissex-tenis-preto.png
acessorio_cabeca-unissex-fone.png
acessorio_rosto-unissex-oculos.png
acessorio_outro-unissex-mochila.png
```

---

## 6. Quantidade sugerida (v1)

| Slot | Ideal |
|------|------:|
| base | 2 |
| sombra | 1–2 |
| rosto | 4 |
| cabelo | 8–12 |
| roupa_superior | 8–12 |
| roupa_inferior | 6–8 |
| calcado | 4–6 |
| acessorio_cabeca | 4 |
| acessorio_rosto | 3 |
| acessorio_outro | 4 |

---

## 7. Cadastro no admin

**Avatar / Peças** → escolher o slot correto da tabela §3 → upload PNG → marcar Starter se for desbloqueio automático.

---

## 8. Checklist

- [ ] 512×820, transparente  
- [ ] Alinhado ao template (stack com a base)  
- [ ] Nome no padrão §5  
- [ ] Thumb 256×256 (recomendado)  
