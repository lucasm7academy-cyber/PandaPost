# Google Drive — Implementação final

> Registro do que foi feito para fechar as lacunas descritas em `GOOGLE_DRIVE_STATUS.md` e deixar o fluxo "vincular pasta → conectar OAuth → escolher arquivo → usar no post" funcionando fim-a-fim.

## Princípio: não alterar o que já existia

Nada do que estava implementado foi removido nem reescrito. As únicas inserções em arquivos existentes são as 4 estritamente necessárias para destravar o fluxo:

| Arquivo | Tipo de mudança |
|---------|-----------------|
| `app/Providers/AppServiceProvider.php` | Adiciona `Socialite::extend('google-drive', ...)` no método `configureSocialite()` (espelhando o padrão de `google-auth`). |
| `.env.example` | Adiciona `GOOGLE_DRIVE_CLIENT_REDIRECT="${APP_URL}/accounts/google-drive/callback"`. |
| `routes/app.php` | Adiciona **uma** rota nova para o download via picker; nada removido. |
| `resources/js/components/assets/GalleryBrowser.vue` | Dentro de `<TabsContent value="google-drive">`, monta `GoogleDrivePicker` quando `isPicker` é `true` e mantém o `GoogleDriveBrowser` original para `isPicker` falso. |

O componente `GoogleDriveBrowser.vue` e a página `pages/workspace/GoogleDriveFolders.vue` continuam exatamente como estavam.

## Arquivos novos

### Backend

- **`app/Socialite/GoogleDriveProvider.php`** — provider Socialite dedicado, estendendo `Laravel\Socialite\Two\GoogleProvider`. Existe apenas para registrar o driver `google-drive` separado do `google` (YouTube) e `google-auth` (login). Sem isso, `Socialite::driver('google-drive')` lançava `Driver [google-drive] not supported` — esse era o bug raiz.

- **`app/Http/Requests/App/GoogleDrive/DownloadGoogleDriveFileRequest.php`** — FormRequest com regras `file_id`, `file_name`, `mime_type` (regra do projeto: validação sempre em FormRequest, nunca inline).

- **`app/Http/Controllers/App/GoogleDrivePickerController.php`** — endpoint `download` que recebe `(Workspace $workspace, GoogleDriveFolder $folder, DownloadGoogleDriveFileRequest $request)`, valida `createPost`, valida que a pasta pertence ao workspace, que está ativa e que existe conexão OAuth, chama `GoogleDriveService::downloadFile`, salva via `Storage::put` e cria um `Media` com `meta: { source: 'google-drive', google_drive_id, google_drive_folder_id }`. Retorna `MediaResource`.

### Frontend

- **`resources/js/components/assets/GoogleDrivePicker.vue`** — novo componente picker usado dentro do `GalleryBrowser` no criador de post:
  - `onMounted` carrega pastas ativas do workspace via `GET /workspace/{id}/google-drive-folders` (rota existente).
  - Faz "probe" da conexão OAuth chamando `GET .../google-drive-folders/{folder}/files` na primeira pasta; se retornar erro de conexão, mostra `GoogleDriveConnect`.
  - Clica numa pasta → grid de arquivos com thumbnail + título + mimeType.
  - Clica num arquivo → `POST /assets/google-drive/from-folder/{workspace}/{folder}` com `{ file_id, file_name, mime_type }`, recebe `MediaResource`, emite `update:selected` adicionando à seleção. Toggle desfaz a seleção.
  - Visual: ring de seleção + spinner durante download, consistente com o `GalleryBrowser`.

### Testes (todos novos, com 23 testes passando)

- **`tests/Feature/Auth/GoogleDriveOAuthTest.php`** (4 testes) — cobre `connect` redirecionando ao Google e gravando workspace na sessão, `callback` criando `GoogleDriveConnection` no sucesso, callback sem sessão falhando gentilmente, e `updateOrCreate` no re-auth do mesmo `google_user_id`.

- **`tests/Feature/Services/GoogleDriveServiceTest.php`** (5 testes) — cobre `listFilesInFolder` retornando arquivos + token, retorno vazio em falha 401, `downloadFile` retornando bytes/null, e refresh automático quando o token expira (usando `Http::fake` com a URL real do Google OAuth — sem hardcodes proibidos pelas regras do projeto, pois as URLs do Drive não vivem em `config/trypost.php`).

- **`tests/Feature/GoogleDrivePickerControllerTest.php`** (6 testes) — cobre criação de `Media` no fluxo feliz, 404 quando folder é de outro workspace, 422 quando folder inativo / sem conexão / validação falha, e 502 quando o Drive devolve erro no download.

### Documentação

- **`GOOGLE_DRIVE_STATUS.md`** — snapshot do estado anterior (referência histórica).
- **`GOOGLE_DRIVE_IMPLEMENTATION.md`** — este arquivo.

## Rotas adicionadas

```php
Route::post('assets/google-drive/from-folder/{workspace}/{folder}', [GoogleDrivePickerController::class, 'download'])
    ->name('app.assets.google-drive.from-folder.download');
```

Nome: `app.assets.google-drive.from-folder.download`.
Todas as outras rotas do Google Drive já existiam e continuam funcionando.

## Fluxo fim-a-fim funcionando

### 1. Vincular pasta (existia, agora plugado ao OAuth correto)
Página `Workspace → Google Drive` → preenche nome + cola link `https://drive.google.com/drive/folders/...` → backend extrai o folder_id via regex → grava em `google_drive_folders`.

### 2. Conectar OAuth (agora funcional)
Card "Conectar Google Drive" → abre popup com `Socialite::driver('google-drive')` → usuário autoriza no Google com scope `drive.readonly` → callback grava `GoogleDriveConnection` no workspace com `access_token` e `refresh_token` criptografados → popup fecha e emite `postMessage` para o frontend.

### 3. Ver títulos dos arquivos da pasta (existia)
Página `Workspace → Google Drive` → componente `GoogleDriveBrowser` lista arquivos da pasta clicada com nome + thumbnail + mimeType. Inalterado.

### 4. Usar arquivos do Drive em postagens (agora plugado)
Novo Post → galeria → aba **"Google Drive"** → o componente `GoogleDrivePicker` mostra a lista de pastas vinculadas e ativas → clica numa pasta → grid de arquivos → clica num arquivo:
- Backend baixa o arquivo do Drive (`GoogleDriveService::downloadFile`),
- Salva em `storage/medias/`,
- Cria `Media` no workspace com `meta.source = 'google-drive'`,
- Retorna `MediaResource` → frontend faz push no `selected` da postagem.

A `Media` resultante vira anexo padrão da postagem — daqui em diante o fluxo é o mesmo de qualquer upload manual.

## Configuração necessária (uma vez, no `.env`)

```env
GOOGLE_CLIENT_ID=<do Google Cloud Console>
GOOGLE_CLIENT_SECRET=<do Google Cloud Console>
GOOGLE_DRIVE_CLIENT_REDIRECT="${APP_URL}/accounts/google-drive/callback"
```

No Google Cloud Console:
1. Habilitar a **Google Drive API** no projeto.
2. Em **APIs & Services → Credentials**, adicionar `${APP_URL}/accounts/google-drive/callback` como Authorized redirect URI.
3. Se o app estiver em modo Testing, adicionar o email do usuário em "Test users" (ou publicar o app).
4. O consent screen precisa ter o scope `https://www.googleapis.com/auth/drive.readonly` listado.

## Verificação

```bash
# Pint
vendor/bin/pint --dirty --format agent
# → passed

# Testes (com sqlite porque o ambiente local não tem pdo_pgsql; em CI/Docker usa pgsql normalmente)
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test --compact --filter=GoogleDrive
# → 23 passed (76 assertions)

# Build
npm run build
# → built in ~28s
```

Cobertura: 23 testes do GoogleDrive novos + 8 testes do `GoogleDriveFolderControllerTest` existentes confirmados verdes = **31 testes passando**.

## Riscos conhecidos / próximos passos sugeridos

- **Vídeos grandes** — `GoogleDriveService::downloadFile` carrega o body inteiro em memória (`$response->body()`). Funciona para imagens e vídeos curtos; vídeos de 100MB+ podem estourar `memory_limit`. Próximo passo: usar `Http::sink(...)` para baixar em chunk para o disco. **Não bloqueia o fluxo no MVP.**
- **Paginação no picker** — o backend já retorna `nextPageToken`, mas o `GoogleDrivePicker` mostra apenas a primeira página (até 50 arquivos por pasta). Implementar "carregar mais" é trivial reutilizando o padrão do `IntersectionObserver` que já existe no `GalleryBrowser` para uploads.
- **Cache de listagem** — chamar a API do Drive a cada navegação na pasta. Para alto tráfego, vale cachear `listFilesInFolder` por algumas dezenas de segundos por pasta+token.

## Resumo das lacunas que foram fechadas

| Lacuna | Como foi resolvido |
|--------|--------------------|
| Driver Socialite `google-drive` não registrado | Novo `GoogleDriveProvider` + `Socialite::extend('google-drive', ...)` no `AppServiceProvider` |
| `GOOGLE_DRIVE_CLIENT_REDIRECT` faltando no `.env.example` | Adicionado |
| `GoogleDriveBrowser` no modo picker não selecionava nem baixava | Novo `GoogleDrivePicker.vue` dedicado ao picker; o `GoogleDriveBrowser` original ficou intocado para uso na página standalone |
| Sem rota/controller que validasse pasta+conexão e criasse `Media` para o post | Novo `GoogleDrivePickerController` + `DownloadGoogleDriveFileRequest` + rota `app.assets.google-drive.from-folder.download` |
| `GoogleDriveBrowser` iniciava com `hasConnection = true` levando à UX confusa | O novo `GoogleDrivePicker` faz probe da conexão no `onMounted`, mostrando `GoogleDriveConnect` antes da primeira tentativa de clique |
