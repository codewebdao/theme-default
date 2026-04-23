<?php
return [
    // SECURITY: Allowed file types (CRITICAL - cannot be overridden from client)
    'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'json', 'pdf', 'docx', 'doc', 'xls', 'xlsx', 'csv', 'ppt', 'pptx', 'txt', 'rar', 'zip', 'iso', 'mp3', 'wav', 'mkv', 'mp4', 'srt', 'svg'],
    'images_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'document_types' => ['pdf', 'docx', 'doc', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'txt'],
    'video_types' => ['mp4', 'mkv', 'avi', 'mov', 'wmv', 'flv', 'mpeg'],
    'audio_types' => ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac', 'wma', 'opus', 'mid', 'midi'],
    'archive_types' => ['zip', 'rar', 'iso', '7z', 'tar', 'gz', 'bz2', 'xz'],

    'files' => [
        'base_path' => 'uploads', // Path full is PATH_CONTENT/uploads
        'files_url' => '/content/uploads/',
        /* CONFIG FILES GROUP */
        // File size limits
        'max_file_size' => 104857600, // Maximum file size: 100MB (in bytes)
        'min_file_size' => null, // Minimum file size (null = no limit)
        'max_file_count' => 10, // Maximum files per batch upload
        // Chunk upload settings (for large files)
        'max_chunk_size' => 10485760, // 10MB per chunk
        'max_total_chunks' => 1000, // Maximum chunks per upload session
        'chunk_temp_path' => null, // Temp directory for chunks (null = use PATH_TEMP constant)
        // Rate limiting (chunk uploads)
        'rate_limit_enabled' => true, // Enable rate limiting
        'max_chunk_requests' => 100, // Max chunk requests per window
        'rate_limit_window' => 60, // Time window in seconds
        'max_concurrent_sessions' => 5, // Max concurrent upload sessions per user/IP

        /* CONFIG IMAGES GROUP */
        // Image Field Default when Create Field/Posttype
        'max_images_size' => 10485760, // Maximum image size: 10MB (in bytes)
        'images_quality' => 90,
        'images_format' => 'jpg', //if want original, set to ''. This is default value
        'images_webp' => true,
        'images_optimize' => false,
        'images_sizes_auto' => [
            'thumbnail' => [
                'width' => 250,
                'height' => 250,
            ],
            'medium' => [
                'width' => 600,
                'height' => 600,
            ],
            'large' => [
                'width' => 1000,
                'height' => 1000,
            ],
        ],
        // Image dimension constraints (null = no limit)
        'min_width' => null,
        'min_height' => null,
        'max_width' => null,
        'max_height' => 20000,

        /* CONFIG MIME TYPE GROUP */
        // SECURITY: MIME type validation (strict_mime requires allowed_mimes)
        'strict_mime' => false, // Set true to enable strict MIME checking
        'allowed_mimes' => [
            // Images
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            // Documents
            'json' => 'application/json',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'csv' => 'text/csv',
            'txt' => 'text/plain',
            // Archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'iso' => 'application/x-iso9660-image',
            // Media
            'mp3' => 'audio/mpeg',
            'ogg' => 'audio/ogg',
            'wav' => 'audio/wav',
            'm4a' => 'audio/mp4',
            'aac' => 'audio/aac',
            'flac' => 'audio/flac',
            'wma' => 'audio/x-ms-wma',
            'opus' => 'audio/opus',
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'avi' => 'video/x-msvideo',
            'mov' => 'video/quicktime',
            'wmv' => 'video/x-ms-wmv',
            'flv' => 'video/x-flv',
            'mkv' => 'video/x-matroska',
            'srt' => 'application/x-subrip',
        ],

        // Pagination
        'limit' => 48, // Files per page


        /* CONFIG ZIP/UNZIP PLUGINS/THEMES GROUP */
        'zip' => [
            'max_files' => 1000, // Maximum number of files in ZIP
            'max_size' => 104857600, // Maximum ZIP extraction size: 100MB (in bytes)
            'overwrite' => false, // Default overwrite behavior
            'validate_structure' => true, // Validate ZIP structure by default
            'required_files' => [], // Default required files (empty for generic ZIP)
            'theme' => [
                'required_files' => ['Config/Config.php'], // Required files for themes
                'max_size' => 52428800, // 50MB for themes
            ],
            'plugin' => [
                'required_files' => ['Config/Config.php'], // Required files for plugins
                'max_size' => 52428800, // 50MB for plugins
            ],
        ],
    ],
];
