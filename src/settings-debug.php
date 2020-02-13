<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        // Twig settings
        'view' => [
            'template_path' => __DIR__ . '/../templates/',
            'options' => [
                'debug' => true
            ]
        ],
        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
        ],
    ],
];