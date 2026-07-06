<?php

return [
    'title' => 'Assets',

    'tabs' => [
        'my_uploads' => 'My Uploads',
        'google_drive' => 'Google Drive',
    ],

    'upload' => [
        'drag_drop' => 'Drag & drop your files here, or click to select',
        'formats' => 'JPEG, PNG, GIF, WebP, MP4',
        'uploading' => 'Uploading...',
    ],

    'empty' => [
        'title' => 'No assets yet',
        'description' => 'Upload images and videos to build your media library.',
    ],

    'save_to_assets' => 'Save to Assets',
    'saved' => 'Saved to your assets!',
    'create_post' => 'Create post',
    'add_to_post' => 'Add to post',
    'search_placeholder' => 'Search media...',

    'delete' => [
        'title' => 'Delete asset',
        'description' => 'Are you sure you want to delete this asset? This action cannot be undone.',
        'confirm' => 'Delete',
        'cancel' => 'Cancel',
    ],

    'unsplash' => [
        'search_placeholder' => 'Search free photos...',
        'no_results' => 'No photos found',
        'no_results_description' => 'Try a different search term.',
        'trending' => 'Trending on Unsplash',
        'start_searching' => 'Search for free stock photos from Unsplash',
    ],

    'giphy' => [
        'trending' => 'Trending on Giphy',
        'search_placeholder' => 'Search GIFs...',
        'no_results' => 'No GIFs found',
        'no_results_description' => 'Try a different search term.',
        'powered_by' => 'Powered by GIPHY',
    ],

    'google_drive' => [
        'title' => 'Connect Google Drive',
        'description' => 'Access files directly from your Google Drive',
        'connect_cta' => 'Connect Google Drive',
        'connected' => 'Google Drive connected successfully!',
        'error_connecting' => 'Failed to connect Google Drive',
        'error_loading' => 'Failed to load files from Google Drive',
        'error_no_connection' => 'Please connect your Google Drive account first',
        'session_expired' => 'Session expired. Please try again.',
        'workspace_not_found' => 'Workspace not found.',
        'coming_soon' => 'Google Drive is coming soon in standalone mode',
        'empty' => [
            'title' => 'No media in Google Drive',
            'description' => 'Your Google Drive folders appear to be empty or contain no images/videos.',
        ],
        'no_files' => 'No files in this folder',
        'imported' => ':name imported into Assets',
    ],
];
