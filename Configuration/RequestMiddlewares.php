<?php
return [
    'frontend' => [
        'typo3/cms-frontend/base-with-language-redirect-resolver' => [
            'target' => \Ig\IgLanguageDetection\Middleware\SiteBaseWithLanguageRedirectResolver::class,
            'after' => [
                'typo3/cms-frontend/site-resolver',
            ],
            'before' => [
                'typo3/cms-frontend/base-redirect-resolver'
            ]
        ],
        'typo3/cms-redirects/redirecthandler' => [
            'disabled' => true,
        ],
        'typo3/cms-redirects/redirecthandler-overwrite' => [
            'target' => \TYPO3\CMS\Redirects\Http\Middleware\RedirectHandler::class,
            'before' => [
                'typo3/cms-frontend/page-resolver',
                'typo3/cms-frontend/static-route-resolver',
                'typo3/cms-frontend/base-redirect-resolver',
                'typo3/cms-frontend/base-with-language-redirect-resolver',
            ],
            'after' => [
                'typo3/cms-frontend/tsfe',
                'typo3/cms-frontend/authentication',
            ],
        ],
    ],
];
