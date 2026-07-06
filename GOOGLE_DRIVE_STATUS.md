# Google Drive — Status antes da implementação final

> Snapshot do estado da integração com Google Drive ANTES da sessão que fechou as lacunas. Documento de referência histórica.

## Objetivo da feature

Permitir que o usuário do TryPost vincule pastas do Google Drive ao seu workspace, autorize acesso via OAuth, navegue pelos arquivos (vendo título e thumbnail) e use esses arquivos como mídia ao criar postagens.

## Peças que já existiam

### Banco de dados

- Migration `2026_05_22_033606_create_google_drive_connections_table.php` — guarda credenciais OAuth do workspace.
- Migration `2026_05_22_042551_create_google_drive_folders_table.php` — pastas vinculadas pelo usuário (folder_id + folder_name + folder_link + is_active).

### Models

- `app/Models/GoogleDriveConnection.php` — campos OAuth criptografados (`encrypted` cast), helpers `isTokenExpired`, `isTokenExpiringSoon`, `markAsConnected()`, `markAsDisconnected()`.
- `app/Models/GoogleDriveFolder.php` — relação `belongsTo(Workspace)`.
- `app/Models/Workspace.php` — relações `googleDriveConnections()`, `googleDriveConnection()`, `googleDriveFolders()`.

### Backend — OAuth e API

- `app/Http/Controllers/Auth/GoogleDriveController.php` — `connect()` e `callback()` usando Socialite, popup flow.
- `config/services.php` — entrada `services.google-drive` apontando para `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` e `GOOGLE_DRIVE_CLIENT_REDIRECT`.
- `app/Services/GoogleDrive/GoogleDriveService.php` — `listFiles`, `listFilesInFolder`, `searchFiles`, `downloadFile`, `refreshToken` (interno). Usa `Illuminate\Support\Facades\Http`, não a SDK do Google.

### Backend — Pastas e assets

- `app/Http/Controllers/App/GoogleDriveFolderController.php` — `index`, `store`, `toggle`, `destroy`, `files`. Extrai folder_id do link via regex.
- `app/Http/Requests/App/GoogleDrive/StoreGoogleDriveFolderRequest.php` — validação para cadastrar pasta.
- `app/Http/Controllers/App/AssetController.php` — métodos `listGoogleDrive()` (lista do Drive inteiro filtrando image/video) e `downloadGoogleDrive()` (baixa e cria `Media` com `source: Source::GoogleDrive`).
- `app/Enums/Media/Source.php` — case `GoogleDrive = 'google-drive'`.

### Frontend

- `resources/js/pages/workspace/GoogleDriveFolders.vue` — página dedicada com formulário "Adicionar Pasta" + tabela de pastas conectadas + componente `GoogleDriveBrowser` em modo standalone.
- `resources/js/components/assets/GoogleDriveBrowser.vue` — navega pastas/arquivos, mostra thumbnail + nome + mimeType. Tem prop `mode: 'standalone' | 'picker'` mas a seleção no modo picker não estava ligada.
- `resources/js/components/assets/GoogleDriveConnect.vue` — card "Conectar Google Drive" que abre popup OAuth.
- `resources/js/components/assets/GalleryBrowser.vue` — galeria do criador de post com aba "Google Drive" embutindo o `GoogleDriveBrowser`.
- `resources/js/components/AppSidebar.vue` — item de menu lateral "Google Drive".
- Traduções em `lang/en|es|pt-BR/assets.php` para `assets.tabs.google_drive` e `assets.google_drive.*`.

### Rotas

- `GET /connect/google-drive` → inicia OAuth (`app.social.google-drive.connect`)
- `GET /accounts/google-drive/callback` → callback
- `GET/POST /workspace/{workspace}/google-drive-folders` → CRUD de pastas
- `PATCH /workspace/{workspace}/google-drive-folders/{folder}/toggle` e `DELETE` para destroy
- `GET /workspace/{workspace}/google-drive-folders/{folder}/files` → arquivos da pasta (JSON)
- `GET /assets/google-drive/list` e `POST /assets/google-drive/download` (não escopadas por pasta)

### Testes

- `tests/Feature/GoogleDriveFolderControllerTest.php` — 7 testes (index, store, validações, toggle, destroy, autorização). Não cobriam OAuth nem o picker.

## Lacunas que impediam o fluxo de funcionar

1. **Driver Socialite `google-drive` não registrado** em `app/Providers/AppServiceProvider.php::configureSocialite()`. `Socialite::driver('google-drive')` lançava `InvalidArgumentException: Driver [google-drive] not supported`. Bug raiz.
2. **`GOOGLE_DRIVE_CLIENT_REDIRECT` ausente em `.env.example`** — `config/services.php` referenciava `env('GOOGLE_DRIVE_CLIENT_REDIRECT')` mas o `.env.example` só tinha o redirect do YouTube.
3. **`GoogleDriveBrowser.vue` em modo `picker` não tinha handler de seleção** — props e emits declarados, grid renderizada, mas nenhum click no arquivo emitia `update:selected`.
4. **Sem ponte entre o picker e o `downloadGoogleDrive` do `AssetController`** — escolher um arquivo no picker não criava `Media` para anexar ao post.
5. **`AssetController::listGoogleDrive` ignorava as pastas vinculadas** — listava o Drive inteiro do usuário, contrariando o fluxo desejado ("eu vinculo a pasta → vejo os arquivos dela").
6. **`GoogleDriveBrowser` iniciava com `hasConnection = true`** — usuário só descobria que faltava OAuth ao clicar numa pasta e receber erro.

## Estado dos dois fluxos

| Fluxo | Status antes |
|-------|--------------|
| Adicionar pasta por link na página `/workspace/{id}/google-drive-folders` | ✅ Funcionava |
| Conectar Google Drive via OAuth popup | ❌ Quebrado (driver não registrado + redirect_uri vazio) |
| Listar arquivos da pasta com título + thumbnail | ⚠️ Código pronto, mas só funcionava depois do OAuth (que estava quebrado) |
| Selecionar arquivo do Drive no `GalleryBrowser` do criador de post | ❌ Sem handler de seleção, sem download |
| Anexar `Media` com `source: google-drive` ao post | ⚠️ Endpoint `downloadGoogleDrive` pronto mas inalcançável pelo picker |

## Próximo passo

Ver `GOOGLE_DRIVE_IMPLEMENTATION.md` para o registro do que foi implementado para fechar essas lacunas.
