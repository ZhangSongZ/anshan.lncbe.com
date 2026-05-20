<?php

return [
    'autoload' => false,
    'hooks' => [
        'app_init' => [
            'crontab',
        ],
        'testhook' => [
            'csmradmin',
        ],
        'view_filter' => [
            'darktheme',
        ],
        'config_init' => [
            'darktheme',
            'summernote',
        ],
        'admin_login_init' => [
            'loginbg',
        ],
    ],
    'route' => [],
    'priority' => [],
    'domain' => '',
];
