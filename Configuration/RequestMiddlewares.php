<?php
return [
    'frontend' => [
        'typo3/cms-frontend/base-with-language-redirect-resolver' => [
            'target' => \Ig\IgLanguageDetection\Middleware\SiteBaseWithLanguageRedirectResolver::class,
            'after' => [
                'typo3/cms-redirects/redirecthandler',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver'
            ]
        ],
    ],
];
