# TryPost SaaS - Memória do Projeto

**Status:** Em desenvolvimento para SaaS de recorrência com foco em postagem automática

## Contexto Atual
- ✅ App baixado do Git e rodando localmente
- ✅ Laragon configurado (PHP 8.3, MySQL)
- ✅ Base de dados estruturada
- ✅ Autenticação e workspaces funcionando
- 🎯 Próximo: Reduzir plataformas + Adicionar Google Drive

---

## 1️⃣ ALTERAÇÃO: Remover Plataformas Não Utilizadas + Adicionar YouTube Videos

### ❌ Remover (não usadas no BR):
- LinkedIn / LinkedIn Page
- Bluesky
- Mastodon
- Pinterest

### ✅ Manter (principais):
- **X** (Twitter)
- **TikTok**
- **YouTube Shorts** (YouTube)
- **YouTube Videos** (YouTubeLong) — **NOVO** — até 12 horas, título+descrição
- **Facebook Page** (Facebook)
- **Instagram** (Standalone)
- **Instagram (Facebook Business)** (InstagramFacebook)
- **Threads**

### Arquivo Afetado:
- `app/Enums/SocialAccount/Platform.php` — remover cases (LinkedIn, LinkedInPage, Bluesky, Mastodon, Pinterest)
- Atualizações em cascata em métodos: `label()`, `color()`, `allowedMediaTypes()`, `maxImages()`, `maxContentLength()`, `recommendedAiContentLength()`, `requiredPublishScopes()`, `supportsTextOnly()`, `publishConfig()`

### Componentes Vue a Remover:
- `resources/js/pages/accounts/LinkedInPageSelect.vue`
- `resources/js/pages/accounts/BlueskyConnect.vue`
- `resources/js/pages/accounts/MastodonConnect.vue`

---

## 2️⃣ ALTERAÇÃO: Adicionar Google Drive como Fonte de Assets

### Nova Entrada em Assets > MY UPLOADS:
- Adicionar **"Google Drive"** como opção de conexão
- Remover: "Stock Photos" e "GIFs"
- Manter: Uploads locais (padrão)

### Arquivos Afetados:
- `app/Enums/Media/Source.php` — adicionar case `GoogleDrive = 'google-drive'` 
- `resources/js/pages/assets/Index.vue` — atualizar UI para mostrar Google Drive e remover Stock Photos/GIFs
- Componentes de conexão para Google Drive (seguir padrão OAuth existente)
- `app/Models/Media.php` ou similar — adicionar suporte ao source GoogleDrive

### Padrão a Seguir:
- Usar a estrutura OAuth existente (similar a InstagramFacebook, etc)
- Seguir o flow de conexão em CONNECTIONS
- Integrar ao sistema de Asset Management existente

---

## 🏗️ Arquitetura & Padrões (NÃO ALTERAR)

### Estrutura Mantida:
- Enums para dados estáticos (Platform, Source, etc)
- Models para persistência (SocialAccount, Media, Post)
- Vue Pages para UI (resources/js/pages/)
- Controllers para lógica de negócio
- OAuth flow para autorizações sociais
- Media/Asset system existente

### Convenções:
- Nomes em TitleCase para Enums
- Métodos case/match para comportamentos por plataforma
- Deferred props em componentes Vue3
- Validações em Form Requests

---

## ✅ ALTERAÇÕES REALIZADAS (22/05/2026)

### 1️⃣ Plataformas Reduzidas + YouTube Videos ✅
- ✅ `app/Enums/SocialAccount/Platform.php` — Removidos 5 cases (LinkedIn, LinkedInPage, Bluesky, Mastodon, Pinterest)
- ✅ Adicionado `YouTubeLong` para videos longos (até 12 horas)
- ✅ Atualizados todos os métodos do enum para as **8 plataformas restantes**
- ✅ Componentes Vue removidos: LinkedInPageSelect.vue, BlueskyConnect.vue, MastodonConnect.vue

### 2️⃣ Google Drive Adicionado ✅
- ✅ `app/Enums/Media/Source.php` — Adicionado case `GoogleDrive = 'google-drive'`
- ✅ `resources/js/components/assets/GalleryBrowser.vue` — Removidas abas "Stock Photos" e "GIFs"
- ✅ Assets > MY UPLOADS agora mostra apenas uploads locais
- ⏳ Google Drive integration (UI + OAuth) — ainda será implementada seguindo padrão existente

---

## 📋 TODO Futuro
1. Implementar Google Drive OAuth integration em CONNECTIONS
2. Implementar APIs oficiais das 7 plataformas mantidas
3. Adicionar pagamento (Stripe/Cashier - já no projeto)
4. Lançar em produção

---

---

## 📌 DETALHES TÉCNICOS - YouTube Videos

**YouTubeLong Enum:**
- `case YouTubeLong = 'youtube-long'`
- **Label:** "YouTube (Videos)"
- **Color:** #FF0000 (mesmo vermelho do YouTube)
- **Allowed Media:** Video
- **Max Images:** 0
- **Max Content:** 5000 caracteres (título + descrição)
- **Recommended AI Length:** 1500 caracteres
- **Required Scopes:** `https://www.googleapis.com/auth/youtube.upload` (compartilhado com Shorts)
- **Supports Text Only:** false
- **Comportamento:** Mesma API do YouTube Shorts, mas com suporte a videos longos

---

**Última atualização:** 22/05/2026
**Status:** Etapa 1 e 2 + YouTube Videos concluídas, pronto para integração do Google Drive
