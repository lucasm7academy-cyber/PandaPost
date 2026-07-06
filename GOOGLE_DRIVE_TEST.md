# 🧪 Guia de Teste - Google Drive Integration

## Pré-requisitos
- ✅ Servidor rodar: `npm run dev` ou `composer run dev`
- ✅ Ter um Google Drive com pelo menos uma pasta compartilhada

## Passo 1: Acessar o Gerenciador de Google Drive

1. Acesse: **Menu → Workspace → Google Drive** (ou vá direto para `/workspace/{id}/google-drive-folders`)
2. Você verá:
   - Seção "Adicionar Pasta" com formulário
   - Seção "Pastas Conectadas" (vazio no início)
   - Seção "Explorador de Arquivos" (se tiver pastas)

## Passo 2: Adicionar uma Pasta do Google Drive

1. Clique em **"Nova Pasta"**
2. Preencha:
   - **Nome da Pasta**: Ex: "Lucas" ou "Meus Arquivos"
   - **Link da Pasta**: 
     - Abra seu Google Drive
     - Clique com botão direito em uma pasta
     - Selecione "Compartilhar"
     - Copie o link gerado (formato: `https://drive.google.com/drive/folders/FOLDER_ID`)
3. Clique em **"Adicionar Pasta"**
4. ✅ A pasta deve aparecer em "Pastas Conectadas"

## Passo 3: Conectar ao Google Drive

1. Na seção **"Explorador de Arquivos"**, você verá:
   - Se NÃO tiver conexão: Card com botão **"Conectar Google Drive"**
   - Se tiver conexão: List de pastas para navegar

2. Clique em **"Conectar Google Drive"**
3. Uma janela popup abrirá com a tela de consentimento do Google
4. Autorize o acesso:
   - Escolha a conta Google
   - Clique em "Permitir" quando Google pedir permissão
5. ✅ O popup fechará automaticamente

## Passo 4: Navegar pelos Arquivos

1. Após a conexão, a página recarregará
2. Na seção **"Explorador de Arquivos"**, clique em uma pasta
3. Você verá:
   - Uma grid com thumbnails dos arquivos
   - Nome do arquivo e tipo MIME
   - Botão "Voltar" para retornar à lista de pastas

## O Que Você Deve Ver

### ✅ Sucessos Esperados
```
1. Pasta adicionada → aparece em "Pastas Conectadas"
2. Clica "Conectar Google Drive" → abre popup
3. Autoriza no Google → popup fecha
4. Clica na pasta → mostra lista de arquivos com thumbnails
5. Cada arquivo mostra: [thumbnail] nome arquivo (tipo)
```

### ❌ Possíveis Erros
```
Erro 1: "Google Drive not connected"
→ Você não completou o passo 3 (autorização)
→ Solução: Clique em "Conectar Google Drive" novamente

Erro 2: "Failed to load files"
→ Problema de rede ou permissão de pasta
→ Solução: Verifique se a pasta está compartilhada corretamente

Erro 3: Pasta vazia
→ A pasta realmente não tem arquivos
→ Solução: Adicione arquivos à pasta no Google Drive
```

## Dados Esperados na Resposta

### Response de Pastas
```json
{
  "folders": [
    {
      "id": "uuid",
      "folder_id": "GOOGLE_FOLDER_ID",
      "folder_name": "Lucas",
      "folder_link": "https://drive.google.com/drive/folders/...",
      "is_active": true,
      "added_by": "seu@email.com",
      "created_at": "2026-05-22"
    }
  ]
}
```

### Response de Arquivos
```json
{
  "files": [
    {
      "id": "FILE_ID",
      "name": "imagem.jpg",
      "mimeType": "image/jpeg",
      "thumbnailLink": "https://...",
      "webContentLink": "https://...",
      "modifiedTime": "2026-05-22T10:00:00Z",
      "size": 123456
    }
  ],
  "nextPageToken": null
}
```

## URLs Envolvidas

| Função | Método | URL |
|--------|--------|-----|
| Listar Pastas | GET | `/workspace/{id}/google-drive-folders` |
| Adicionar Pasta | POST | `/workspace/{id}/google-drive-folders` |
| Conectar ao Drive | GET | `/connect/google-drive` |
| Callback do OAuth | GET | `/accounts/google-drive/callback` |
| Listar Arquivos | GET | `/workspace/{id}/google-drive-folders/{folder_id}/files` |

## Próximas Etapas (Após Confirmar Funcionamento)

- [ ] Usar os arquivos em posts (seleção + upload)
- [ ] Implementar download automático dos arquivos selecionados
- [ ] Adicionar suporte para paginação (se pasta tem muitos arquivos)
- [ ] Adicionar busca/filtro de arquivos por nome

---

**Que tal começar? Tente os 4 passos acima e me avisa como foi!**
