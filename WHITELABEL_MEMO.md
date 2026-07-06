# TryPost — White Label (Uso Comercial)

> **Data:** 27/05/2026
> **Mantenedor do fork:** [seu nome]
> **Objetivo:** Operar este projeto como produto **white label proprietário** (não open source) com marca própria, novas funcionalidades e venda comercial.

---

## 🎯 Intenção do Projeto (LEIA PRIMEIRO)

Este repositório é um **fork comercial / white label** do TryPost (https://github.com/trypost-it/trypost).

- ❌ **NÃO** será distribuído como open source.
- ❌ **NÃO** vamos manter a marca "TryPost" no produto final (logo, nome, copy, emails, domínio).
- ✅ Será **rebrandeado** (nome próprio, logo, cores, domínio, copy).
- ✅ Funcionalidades novas serão construídas em cima do código existente.
- ✅ Vendido comercialmente como SaaS sob a minha marca.

**Toda decisão de produto, código e dependências deve ser avaliada sob esta ótica.** Funcionalidades pensadas só para o ecossistema open source do TryPost original (ex.: exposição pública de um servidor MCP "TryPost", branding "trypost.it", referências a docs.trypost.it para o usuário final) devem ser **removidas, renomeadas ou repropósito** conforme o caso.

---

## ⚖️ Licença — AGPL-3.0 (atenção!)

O upstream é **AGPL-3.0-only** (`composer.json` → `"license": "AGPL-3.0-only"`).

Implicações para white label SaaS:

- AGPL exige que, se o software for **oferecido como serviço** pela rede (SaaS), o **código-fonte modificado** deve ser disponibilizado aos usuários do serviço.
- Isso é **incompatível com manter o fork totalmente fechado** se mantivermos a base AGPL.

**Opções (a decidir com aconselhamento jurídico):**
1. **Licença comercial com o autor original** (Paulo Castellano) — pagar/negociar uma licença comercial paralela à AGPL para poder operar como produto fechado.
2. **Cumprir a AGPL** — publicar o código modificado em um repositório acessível aos clientes do SaaS (não é "open source" no sentido de promoção pública, mas o código fica acessível).
3. **Reescrever as partes proprietárias** sobre uma fundação própria, isolando o que veio da AGPL.

> ⚠️ Antes de qualquer venda comercial em produção, **resolver a questão de licenciamento**. Não comitar/pushar para repositórios públicos enquanto isso.

---

## 🔧 Sobre o MCP (Model Context Protocol) — Decisão

### O que é o MCP neste projeto

- `laravel/mcp` v0.6 é um **pacote do Laravel** que expõe um servidor MCP genérico (não tem nada a ver com "open source"; é uma feature do framework).
- O servidor está em `app/Mcp/Servers/TryPostServer.php` e é registrado em `routes/ai.php`:
  ```php
  Mcp::oauthRoutes();
  Mcp::web('/mcp/trypost', TryPostServer::class)
  ```
- Ele expõe **tools** (ações) que LLMs externos (Claude Desktop, Cursor, ChatGPT com MCP, etc.) podem invocar para gerenciar a conta do usuário: criar/listar/publicar posts, gerenciar labels, signatures, social accounts, API keys, workspace.
- A autenticação é via OAuth2 (Passport) — cada usuário do SaaS pode conectar sua própria conta a um cliente MCP externo.

### Para que serve, na prática

É uma **API pensada para agentes de IA**:
- Um cliente meu pode dizer pro ChatGPT/Claude/Cursor: "agende um post no [Minha Marca] sobre X" e o LLM, conectado via MCP à conta dele, executa a ação.
- É uma **vantagem competitiva** para usuários que vivem dentro de ferramentas de IA (devs, marketers técnicos, automações).
- Diferente de "open source" — o MCP é apenas um protocolo de integração. **Funciona perfeitamente em produto fechado / white label.**

### Decisão para o white label

✅ **MANTER o MCP**, mas:
- **Renomear** o servidor: `TryPostServer` → `<MinhaMarca>Server` (atributos `#[Name(...)]` e `#[Instructions(...)]` também).
- **Renomear a rota**: `/mcp/trypost` → `/mcp/<minha-marca>`.
- **Atualizar a string** `Instructions` para refletir a nova marca.
- **Renomear o namespace** `App\Mcp\Servers\TryPostServer` se quisermos coerência total (opcional).
- Considerar **gating do MCP por plano** (Pennant feature) — pode virar feature paga premium ("Acesso via IA / MCP").

❌ **NÃO remover.** É útil, é uma diferenciação e custa pouco para manter.

---

## 🏷️ Checklist de Rebranding (visão geral — não executar ainda)

Onde a marca "TryPost" / `trypost.it` aparece e precisa ser substituída em algum momento:

### Identidade e domínio
- `config/trypost.php` (todo o arquivo de config — considerar renomear para `config/<minha-marca>.php`)
- Helpers de config: `config('trypost.platforms.x.api')` em todo o código
- `composer.json` → `name`, `description`, `keywords`, `homepage`, `support`, `authors`
- `package.json` → nome, descrição
- Domínio: `https://trypost.it`, `https://trypost.test`, `https://docs.trypost.it`

### Código
- `app/Mcp/Servers/TryPostServer.php` (nome da classe + atributos)
- Rota `/mcp/trypost`
- `App\Mcp\Tools\*` (se houver referências hardcoded)
- Strings em emails (`app/Mail/`, templates Maizzle em `maizzle/`)
- Strings de UI Vue em `resources/js/pages/` e componentes
- `lang/` (strings traduzidas)

### Docs/Memo internos
- `CLAUDE.md`, `AGENTS.md`, `GEMINI.md` — referências a "TryPost.it" → minha marca
- `DOCUMENTACAO_PROJETO.md` — atualizar quando rebrandear
- Referências a `https://docs.trypost.it` — apontar pra docs próprias (ou remover do produto final)

### Frontend
- Logos, favicon, OG images em `public/`
- Cores da landing
- Copy da `Landing.vue`
- `resources/js/pages/Landing.vue`

### Email / templates
- Maizzle (`maizzle/`)
- Mail classes (`app/Mail/`)

---

## 🚫 Coisas que NÃO trazem o produto original que devo evitar

- **Não promover** o produto como "open source" ou "self-hosted gratuito" (mesmo que a base permita).
- **Não linkar** clientes finais para `github.com/trypost-it/trypost` ou `docs.trypost.it`.
- **Não usar** o nome "TryPost" em copy comercial, marketing, emails transacionais, faturas, etc.
- **Não deixar** créditos visíveis ao usuário final apontando para o upstream (atribuição interna no código/repo é OK e provavelmente exigida pela AGPL; o produto final ao cliente é que precisa ser limpo).

---

## ✅ Coisas que faço questão de manter / quero priorizar

- **MCP Server** (rebrandeado) — diferencial premium.
- **Múltiplas plataformas sociais** já reduzidas pra 8 (X, TikTok, YouTube Shorts/Videos, Facebook, Instagram x2, Threads).
- **IA** (geração de texto + imagem, autofill de marca, humanizer, reviewer) — core do produto.
- **Google Drive** como fonte de assets (em andamento).
- **Stripe / Cashier** para cobrança.
- **API REST pública** + **API Keys** (também rebrandear endpoints se quiser).

---

## 🗒️ TODO (quando for hora de rebrandear de fato)

- [ ] Resolver licenciamento (AGPL vs comercial) — **bloqueante** para venda.
- [ ] Decidir nome / marca final do produto.
- [ ] Comprar domínio.
- [ ] Renomear `config/trypost.php` → `config/<marca>.php` e fazer find/replace em todas as chamadas `config('trypost...')`.
- [ ] Renomear `TryPostServer` → `<Marca>Server` + atualizar rota MCP + atualizar `Instructions`.
- [ ] Substituir logos, favicon, OG images, landing copy.
- [ ] Atualizar emails (Maizzle + Mail classes).
- [ ] Atualizar `composer.json` / `package.json`.
- [ ] Limpar referências a `docs.trypost.it` / `trypost.it` no produto.
- [ ] Atualizar `CLAUDE.md` / `AGENTS.md` / `GEMINI.md` para refletir nova marca.
- [ ] Decidir se MCP fica disponível em todos os planos ou só Pro/Max (via Pennant).

---

**Mantenedor do fork white label:** [seu nome]
**Base upstream:** TryPost (Paulo Castellano) — AGPL-3.0
