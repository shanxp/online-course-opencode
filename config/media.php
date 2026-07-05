<?php

return [
    'sync_path' => env('MEDIA_SYNC_PATH', storage_path('app/media/source')),

    'thumbnail_max_size' => (int) env('THUMBNAIL_MAX_SIZE', 2048),

    'max_upload_size' => 102400,

    'allowed_mimes' => ['mp3', 'pdf'],

    'allowed_mime_types' => ['audio/mpeg', 'application/pdf'],
];
