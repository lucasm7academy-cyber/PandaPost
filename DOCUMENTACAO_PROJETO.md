# TryPost — Documentação do Projeto

> **Status:** Em desenvolvimento (SaaS de agendamento de posts em redes sociais)
> **Última atualização:** 27/05/2026
> **Stack:** Laravel 13 + Inertia 3 + Vue 3 + Tailwind 4 + MySQL + Redis

---

## 📌 Sobre o Projeto

**TryPost** é uma plataforma open-source (AGPL-3.0) de agendamento e gestão de redes sociais para criadores de conteúdo e equipes. Permite criar, agendar, publicar e analisar posts em múltiplas plataformas a partir de uma única interface, com recursos de IA para geração de conteúdo e imagens.

- **Homepage:** https://trypost.it
- **Docs oficiais:** https://docs.trypost.it
- **Modo Self-Hosted:** Suportado (sem necessidade de Stripe/assinatura)

---

## 🏗️ Stack Técnica

### Backend (PHP 8.4 / Laravel 13)
| Pacote | Versão | Função |
|---|---|---|
| `laravel/framework` | v13 | Framework principal |
| `inertiajs/inertia-laravel` | v3 | SPA server-driven |
| `laravel/ai` | v0.5 | SDK de IA oficial Laravel |
| `laravel/cashier` | v16 | Billing Stripe |
| `laravel/horizon` | v5 | Filas Redis (queue dashboard) |
| `laravel/reverb` | v1 | WebSockets / broadcasting |
| `laravel/passport` | v13 | OAuth2 server (API tokens) |
| `laravel/socialite` | v5 | OAuth com provedores sociais |
| `laravel/pennant` | v1 | Feature flags / limites de plano |
| `laravel/wayfinder` | v0 | Rotas tipadas para TypeScript |
| `laravel/mcp` | v0 | Model Context Protocol server |
| `laravel/nightwatch` | v1 | Observabilidade |
| `laravel/telescope` | v5 | Debug local |
| `google/apiclient` | v2 | Integração Google Drive |
| `intervention/image` | v4 | Processamento de imagens |
| `pestphp/pest` | v4 | Testes |

### Frontend (Vue 3 + TypeScript)
| Pacote | Função |
|---|---|
| `@inertiajs/vue3` v3 | Cliente Inertia |
| `vue` v3.5 | Framework UI |
| `tailwindcss` v4 | CSS |
| `@tabler/icons-vue` | Ícones (padrão obrigatório do projeto) |
| `reka-ui` | Headless components |
| `dayjs` | Datas (wrapper em `@/dayjs`) |
| `@unovis/vue` | Gráficos / charts |
| `embla-carousel-vue` | Carrossel |
| `posthog-js` | Analytics produto |
| `laravel-echo` + `pusher-js` | Realtime |
| `laravel-vue-i18n` | Internacionalização |

---

## 🎯 Funcionalidades Principais

### 1. Autenticação e Onboarding
- Login/Registro tradicional (email + senha)
- OAuth via **Google** e **GitHub** (Socialite) — opcional via env
- Sessões e logout de outros dispositivos
- Recuperação de senha
- Convites por email para entrar em workspaces (`WorkspaceInvite`)
- UTM tracking (`utm_*` columns em users)
- Registration IP tracking

### 2. Multi-Workspace (Multi-tenant)
- **Account** = entidade de cobrança (1 usuário owner)
- **Workspace** = espaço de trabalho com posts/contas sociais
- **User × Workspace** = membros com roles (`Admin`, `Member`, `Viewer`)
- Switch entre workspaces
- Convites pendentes (`WorkspaceInvite` model)
- Search de membros
- **Limites por plano** controlados via `WorkspaceLimit` e `MemberLimit` (Pennant features)

### 3. Conexões com Redes Sociais (OAuth)

**Plataformas ativas no enum `Platform`** (`app/Enums/SocialAccount/Platform.php`):
| Plataforma | Slug | Texto Máx | Mídia |
|---|---|---|---|
| **X (Twitter)** | `x` | 280 chars | Imagem/Vídeo (4 imgs) |
| **TikTok** | `tiktok` | 2200 chars | Vídeo |
| **YouTube Shorts** | `youtube` | 5000 chars | Vídeo |
| **YouTube (Videos)** | `youtube-long` | 5000 chars | Vídeo |
| **Facebook Page** | `facebook` | 10000 chars | Imagem/Vídeo (10 imgs) |
| **Instagram (Standalone)** | `instagram` | 2200 chars | Imagem/Vídeo (10 imgs) |
| **Instagram (Facebook Business)** | `instagram-facebook` | 2200 chars | Imagem/Vídeo (10 imgs) |
| **Threads** | `threads` | 500 chars | Imagem/Vídeo (10 imgs) |

> ❌ **Removidos** (mas com publishers/analytics ainda existentes no código de Services/Social): LinkedIn, LinkedIn Page, Bluesky, Mastodon, Pinterest.

**Cada plataforma tem:**
- Controller OAuth dedicado em `app/Http/Controllers/Auth/` (ex: `XController`, `FacebookController`)
- **Publisher** em `app/Services/Social/<Plataforma>Publisher.php`
- **Analytics** em `app/Services/Social/<Plataforma>Analytics.php`
- Conexão criptografada (`access_token`/`refresh_token` com cast `encrypted`)
- Refresh automático de tokens via Job `RefreshSocialToken`
- Status: `active`, `expired`, `revoked`, `error`
- Flag `is_active` (pode desativar sem desconectar)

### 4. Sistema de Posts

**Model `Post`** com estados (`Post\Status`):
- `Draft` → `Scheduled` → `Publishing` → `Published` / `PartiallyPublished` / `Failed`

**Recursos:**
- Calendário visual de posts agendados
- Listagem filtrada por status (draft/scheduled/published)
- Criação manual com seleção de múltiplas plataformas (cross-posting)
- Mídia anexada com **DTO `MediaItem`** (suporta múltiplas imagens/vídeos)
- Agendamento (`scheduled_at`)
- **Duplicar post**
- **Labels** (tags coloridas) — relação many-to-many
- **Comentários** com reações (colaboração em equipe)
- **Métricas por plataforma** (impressões, likes, shares, etc.)
- **PostPlatform** = post × plataforma (cada combinação tem seu status individual)

**Actions:**
- `CreatePost`, `UpdatePost`, `DuplicatePost`, `DeletePost`, `SyncPostPlatforms`

**Jobs de publicação:**
- `PublishPost` — orquestra a publicação
- `PublishToSocialPlatform` — publica em cada plataforma específica

### 5. IA (Conteúdo + Imagens)

**Pacote:** `laravel/ai` v0.5 (SDK oficial Laravel)

**Agents em `app/Ai/Agents/`:**
| Agent | Função |
|---|---|
| `PostContentGenerator` | Gera conteúdo de post (estruturado) |
| `PostContentHumanizer` | Humaniza texto IA |
| `PostContentReviewer` | Revisa e melhora posts |
| `PostContentStreamer` | Streaming de conteúdo (realtime) |
| `BrandAnalyzer` | Analisa marca por URL (autofill) |

**Fluxo "Create with AI":**
- `PostAiCreateController::start` → cria sessão de criação
- Loading page com streaming
- Job `StreamPostCreation` / `StreamPostContent` broadcasta via Reverb
- Finaliza post + redireciona

**Geração de Imagens IA:**
- `AiImageClient` (`app/Services/Ai/AiImageClient.php`)
- Modelo padrão: **`gpt-image-2`** (OpenAI)
- Suporta:
  - `ImageStyle` enum (workspace config)
  - `Orientation` (portrait/landscape/square)
  - Cor de marca + descrição de marca no prompt
  - Quality control (`low`, etc.)
  - Fallback silencioso (retorna `null` se falhar)

**Sistema de Créditos (`CreditCost`):**
- Texto: cobra por tokens (`ceil(total_tokens / tokens_per_credit)`)
- Imagem: flat por modelo
- Vídeo: flat por modelo
- Configurável em `config/ai-credits.php`
- Tracking em `AiUsageLog` (tabela `workspace_ai_usages`)
- Limite mensal via `MonthlyCreditsLimit` (Pennant)
- Tipos (`Ai\UsageType`): `Template`, `Text`, `Image`

### 6. Templates de Post
- `PostTemplate` aplicáveis a posts (`/post-templates/{slug}/apply`)
- `TemplateContextResolver` — busca templates relevantes por contexto de plataforma (alimenta few-shot do agent)

### 7. Gestão de Mídia (Assets)

**Model `Media`** com trait `HasMedia`.

**Fontes (`Media\Source` enum):**
- `Ai` — geradas por IA
- `Unsplash` — banco de fotos (stock)
- `Giphy` — GIFs
- `GoogleDrive` — **NOVA** integração

**Recursos:**
- Upload direto e chunked (arquivos grandes via `storeChunked`)
- Upload via URL (`storeFromUrl`)
- Token de upload assinado (`upload_token` no modelo, rota `/uploads/{token}`)
- Galeria de assets do workspace
- Limites em MB configuráveis (`config/trypost.php`):
  - Imagens: 10 MB (default)
  - Vídeos: 1024 MB (default)
- **Image processing** via `intervention/image` v4
- Extração de cores dominantes (`league/color-extractor`)

**Google Drive Integration:**
- Models: `GoogleDriveConnection`, `GoogleDriveFolder`
- Controller: `GoogleDriveFolderController`
- Conectar pastas → listar arquivos → baixar como asset
- OAuth via `google/apiclient`

**Serviços externos:**
- `UnsplashService` — search/trending
- `GiphyService` — search/trending

### 8. Branding do Workspace
- Configurações de marca: nome, website, descrição, tom de voz, notas de voz
- Cores: `brand_color`, `background_color`, `text_color`
- **Fonte** (`BrandFont` enum)
- **Estilo de imagem** (`ImageStyle` enum)
- Logo (upload + URL pública via `HasMedia`)
- **Autofill via IA** (`AutofillBrand` action + `BrandAnalyzer` agent) — analisa o site e preenche tudo
- Linguagem de conteúdo (`content_language`)

### 9. Billing (Stripe)
- **Cashier v16** com `Account` como Billable
- **Planos** (`Plan\Slug` enum): `Starter`, `Plus`, `Pro`, `Max`
- Checkout via Stripe (mensal/anual) — `BillingController`
- Customer portal Stripe
- Swap entre planos
- Webhooks Stripe (configuração padrão Cashier)
- Modo `self_hosted=true` desabilita todo o billing
- **Features** controladas por plano via Pennant:
  - `WorkspaceLimit` — qtd. de workspaces
  - `MemberLimit` — membros por workspace
  - `SocialAccountLimit` — contas sociais conectadas
  - `MonthlyCreditsLimit` — créditos IA/mês

### 10. Analytics
- Página geral (`/analytics`) + por conta (`/analytics/{account}`)
- Analytics fetcher por plataforma (`<Plataforma>Analytics` services)
- Métricas por `PostPlatform` (engajamento individual)
- Gráficos com `@unovis/vue`
- **PostHog** integrado (`PostHogService` + jobs em `app/Jobs/PostHog/`)

### 11. Notificações
- **In-app** (`/notifications`) — model `Notification`
- **Email** (Mail classes em `app/Mail/`)
- Preferências por canal/tipo (`NotificationPreference`)
- Enums: `Notification\Channel`, `Notification\Type`
- Job `SendNotification`
- Realtime broadcast (Reverb)
- Mark as read / read-all / archive-all

### 12. API Pública (REST)
- **OAuth2 via Passport** + tokens via `ApiKeyController`
- Middleware `auth:api` + `workspace.token` (token escopado por workspace)
- Endpoints em `routes/api.php`:
  - Posts (CRUD + media + metrics + preview)
  - Signatures, Labels, Social Accounts (toggle)
  - Workspace info
  - Content types metadata
  - API Keys CRUD
- Rate limit: `throttle:api`

### 13. MCP Server (Model Context Protocol)
- Server: `App\Mcp\Servers\TryPostServer` (Laravel MCP v0.6)
- **Tools expostas para LLMs externos:**
  - **Posts:** list, get, create, update, publish, preview, delete, attach media (URL/upload), get metrics, request upload
  - **Signatures:** list, create, update, delete
  - **Labels:** list, create, update, delete
  - **Social Accounts:** list, toggle
  - **Workspace:** get
  - **API Keys:** list, create, delete
  - **Platform:** list content types (read-only metadata)
- Permite agentes IA gerenciarem a conta do usuário via MCP

### 14. Outras Funcionalidades
- **Assinaturas de email** (`WorkspaceSignature`) — assinaturas reutilizáveis em posts
- **Presence** (heartbeat) — quem está online no workspace agora
- **Verify Workspace Connections** (Job recorrente) — valida tokens ainda ativos
- **i18n** via `laravel-vue-i18n` (frontend) + `lang/` (backend)
- **Templates de email Maizzle** (pasta `maizzle/`)
- **Sendkit** integrado (`sendkit-laravel`)
- **Maintenance / observabilidade:** Nightwatch + Telescope (dev) + Pail (logs)

---

## 📂 Estrutura de Diretórios

```
app/
├── Actions/              # Single-purpose business logic
│   ├── Ai/               # AutofillBrand
│   ├── Invite/
│   ├── Label/
│   ├── Post/             # CreatePost, UpdatePost, DuplicatePost, DeletePost, SyncPostPlatforms
│   ├── PostComment/
│   ├── Signature/
│   ├── SocialAccount/    # ToggleSocialAccount
│   ├── User/
│   └── Workspace/        # CreateWorkspace, DeleteWorkspace
├── Ai/Agents/            # Agentes laravel/ai (Generator, Humanizer, Reviewer, Streamer, BrandAnalyzer)
├── Broadcasting/         # Canais de WebSocket
├── DataTransferObjects/  # DTOs (MediaItem, etc.)
├── Enums/                # Platform, Source, Status, Role, etc.
├── Features/             # Pennant features (limites por plano)
├── Http/
│   ├── Controllers/
│   │   ├── Api/          # API REST pública
│   │   ├── App/          # Controllers da SPA (Inertia)
│   │   ├── Auth/         # OAuth de todas as plataformas + login
│   │   └── LandingController.php
│   ├── Middleware/       # EnsureAccountReady, workspace.token, etc.
│   └── Requests/App/     # FormRequests (validação)
├── Jobs/
│   ├── Ai/               # StreamPostContent, StreamPostCreation
│   ├── PostHog/
│   ├── PublishPost.php
│   ├── PublishToSocialPlatform.php
│   ├── RefreshSocialToken.php
│   ├── SendNotification.php
│   └── VerifyWorkspaceConnections.php
├── Mcp/
│   ├── Servers/TryPostServer.php
│   └── Tools/            # Tools MCP (Post, Signature, Label, SocialAccount, Workspace, ApiKey, Platform)
├── Models/               # Eloquent models
├── Services/
│   ├── Ai/               # AiImageClient, CreditCost, RecordAiUsage, TemplateContextResolver
│   ├── Brand/
│   ├── GoogleDrive/
│   ├── Image/
│   ├── Media/
│   ├── Post/
│   ├── PostTemplate/
│   ├── Social/           # Publishers + Analytics por plataforma
│   ├── GiphyService.php
│   ├── PostHogService.php
│   └── UnsplashService.php
└── Socialite/            # Provedores customizados (Instagram, LinkedInPage)

resources/js/pages/
├── Landing.vue
├── accounts/             # Conexão de redes (página + seletores Facebook/IG/YouTube)
├── analytics/
├── assets/               # Galeria de mídia + Google Drive
├── auth/                 # Login, register, etc.
├── billing/
├── labels/
├── posts/                # Calendar, Create, Edit, Show, Index, ai/, templates/
├── settings/             # workspace/, account/, profile/
├── signatures/
├── workspace/
└── workspaces/

routes/
├── ai.php       # Rotas de streaming IA
├── api.php      # API REST (Passport)
├── app.php      # SPA Inertia
├── auth.php     # Autenticação
├── channels.php # Broadcasting
├── console.php  # Schedule
└── web.php      # Landing, webhooks
```

---

## 🗄️ Banco de Dados (Tabelas Principais)

- `users` — usuários (com `github_id`, `registration_ip`, UTM cols)
- `accounts` — entidade de billing (Cashier Billable)
- `subscriptions`, `subscription_items` — Stripe
- `plans` — planos com `stripe_monthly_price_id`, `stripe_yearly_price_id`, `sort`, `active`
- `workspaces` — espaços de trabalho (UUID)
- `user_workspace` — pivot com `role`
- `workspace_invites` — convites pendentes
- `social_accounts` — contas conectadas (tokens criptografados)
- `posts` — posts (UUID, status, scheduled_at, published_at, media JSON)
- `post_platforms` — posts × plataformas (indexada)
- `post_workspace_label` — pivot tags
- `post_comments` — comentários com reações
- `medias` — assets (`upload_token`)
- `workspace_signatures`, `workspace_labels`
- `notifications`, `notification_preferences`
- `workspace_ai_usages` — log de uso IA
- `features` — Pennant
- `google_drive_connections`, `google_drive_folders`
- `oauth_*` — tabelas Passport (clients, tokens, refresh, auth_codes, device_codes)
- `telescope_entries` — debug local

---

## 🔑 Convenções do Projeto (Regras Críticas)

### PHP / Laravel
- **PHP 8.4**, sempre `declare(strict_types=1)`
- Constructor property promotion
- Type hints + return types obrigatórios
- Curly braces sempre (mesmo em single-line)
- **Validação:** sempre em `FormRequest` em `app/Http/Requests/App/<Grupo>/`, nunca inline
- **Array access:** usar `data_get()` em Actions e Services
- **String interpolation:** preferir `"texto {$var}"` em vez de concatenação
- **Imports:** sempre `use` no topo, nunca `\Class::method()` inline
- **HTTP status:** usar constantes `Response::HTTP_*` em vez de números mágicos
- **URLs externas:** sempre `config('trypost.platforms.x.api')`, nunca hardcoded
- **Pint:** rodar `vendor/bin/pint --dirty --format agent` antes de finalizar

### Frontend (Vue/TS)
- **Arrow functions sempre** — nunca `function` declarations
- **Ícones:** `@tabler/icons-vue` (nunca `lucide`), todos prefixados `Icon`
- **Datas:** `@/dayjs` para manipular, `@/date` para formatar — nunca `new Date()`
- **Rotas:** Wayfinder (`@/routes/...`), nunca URLs hardcoded
- **Validação HTML5:** proibida (`required`, `pattern`, etc.) — só backend
- **Paginação:** sempre `->paginate()` + `Inertia::scroll()` + `<InfiniteScroll>` (nunca cursor, nunca page links)
- **Componente Vue:** sempre single root element

### Testes
- **Pest v4**, todo teste deve usar `route()` (named routes)
- Dusk: usar seletores `@dusk-name`, nunca classes CSS
- Rodar com `php artisan test --compact --filter=...`

### Git
- ❌ Nunca commitar/pushar sem pedido explícito
- ❌ Nunca adicionar `Co-Authored-By`
- ✅ Sempre criar branch nova para feature

---

## 🚦 Status das Funcionalidades

### ✅ Implementado
- Autenticação completa (email + Google + GitHub)
- Multi-workspace com roles e convites
- 8 plataformas sociais ativas (X, TikTok, YouTube Shorts/Videos, Facebook, Instagram x2, Threads)
- OAuth + refresh automático de tokens
- Criação/edição/agendamento de posts cross-platform
- Calendário visual
- IA: geração de texto (streaming), humanização, review, autofill de marca
- IA: geração de imagens (gpt-image-2)
- Sistema de créditos por plano
- Templates de post
- Comentários colaborativos com reações
- Labels (tags) coloridas
- Assets: upload local + chunked + URL + Unsplash + Giphy + Google Drive
- Analytics por post/plataforma
- Notificações in-app + email + realtime (Reverb)
- API pública (Passport) + API Keys
- MCP Server completo
- Billing Stripe (Starter/Plus/Pro/Max) — opcional via self-hosted
- Branding de workspace (com autofill IA)
- Assinaturas reutilizáveis
- i18n
- PostHog analytics

### 🔄 Em Andamento / Próximos Passos
- Google Drive integration UI completa (OAuth já feito, refinamento de UX pendente)
- Implementar APIs oficiais finais das 7 plataformas
- Lançamento em produção
- Documentação pública em https://docs.trypost.it

### ❌ Removido (não usado no BR)
- LinkedIn / LinkedIn Page
- Bluesky
- Mastodon
- Pinterest
> *Obs: Publishers/Analytics destes ainda existem em `app/Services/Social/` mas não estão no enum `Platform`.*

---

## 🔧 Comandos Úteis

```bash
# Setup
composer setup

# Dev (server + queue + logs + vite)
composer run dev

# Dev com SSR
composer run dev:ssr

# Testes
php artisan test --compact
php artisan test --compact --filter=NomeDoTeste

# Lint PHP
vendor/bin/pint --dirty --format agent

# Lint JS
npm run lint
npm run format

# Wayfinder (após mexer em rotas/controllers)
php artisan wayfinder:generate

# Build frontend
npm run build       # produção
npm run dev         # dev server

# Horizon
php artisan horizon

# Reverb
php artisan reverb:start

# Tinker
php artisan tinker --execute 'User::count();'
```

---

## 📦 Ambiente

- **Servidor local:** Laravel Herd → `https://trypost.test`
- **Database:** MySQL (Laragon)
- **Queue/Cache:** Redis (Predis)
- **Storage:** Filesystem local + suporte a S3 (`league/flysystem-aws-s3-v3`)
- **Filas:** Horizon (driver Redis)
- **Broadcasting:** Reverb (WebSockets)

---

**Mantenedor:** Paulo Castellano
**Licença:** AGPL-3.0
