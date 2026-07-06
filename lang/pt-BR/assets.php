<?php

declare(strict_types=1);

return [
    'title' => 'Mídias',

    'tabs' => [
        'my_uploads' => 'Meus uploads',
        'google_drive' => 'Google Drive',
    ],

    'upload' => [
        'drag_drop' => 'Arraste e solte seus arquivos aqui ou clique para selecionar',
        'formats' => 'JPEG, PNG, GIF, WebP, MP4',
        'uploading' => 'Enviando...',
    ],

    'empty' => [
        'title' => 'Nenhuma mídia ainda',
        'description' => 'Envie imagens e vídeos para criar sua biblioteca de mídia.',
    ],

    'save_to_assets' => 'Salvar na biblioteca',
    'saved' => 'Salvo na sua biblioteca!',
    'create_post' => 'Criar post',
    'add_to_post' => 'Adicionar ao post',
    'search_placeholder' => 'Buscar mídia...',

    'delete' => [
        'title' => 'Excluir mídia',
        'description' => 'Tem certeza que deseja excluir esta mídia? Esta ação não pode ser desfeita.',
        'confirm' => 'Excluir',
        'cancel' => 'Cancelar',
    ],

    'unsplash' => [
        'search_placeholder' => 'Buscar fotos gratuitas...',
        'no_results' => 'Nenhuma foto encontrada',
        'no_results_description' => 'Tente outro termo de busca.',
        'trending' => 'Em alta no Unsplash',
        'start_searching' => 'Busque fotos gratuitas do Unsplash',
    ],

    'giphy' => [
        'trending' => 'Em alta no Giphy',
        'search_placeholder' => 'Buscar GIFs...',
        'no_results' => 'Nenhum GIF encontrado',
        'no_results_description' => 'Tente outro termo de busca.',
        'powered_by' => 'Powered by GIPHY',
    ],

    'google_drive' => [
        'title' => 'Conectar Google Drive',
        'description' => 'Acesse arquivos diretamente do seu Google Drive',
        'connect_cta' => 'Conectar Google Drive',
        'connected' => 'Google Drive conectado com sucesso!',
        'error_connecting' => 'Erro ao conectar Google Drive',
        'error_loading' => 'Erro ao carregar arquivos do Google Drive',
        'error_no_connection' => 'Conecte sua conta do Google Drive primeiro',
        'session_expired' => 'Sessão expirada. Tente novamente.',
        'workspace_not_found' => 'Workspace não encontrado.',
        'coming_soon' => 'Google Drive em breve no modo independente',
        'empty' => [
            'title' => 'Sem mídia no Google Drive',
            'description' => 'Suas pastas do Google Drive parecem estar vazias ou não contêm imagens/vídeos.',
        ],
        'no_files' => 'Sem arquivos nesta pasta',
        'imported' => ':name importado para Assets',
    ],
];
