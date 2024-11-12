<?php

use Ig\IgLanguageDetection\Tca\SiteLanguages;

return [
    'ctrl' => [
        'label' => 'route',
        'label_userFunc' => \Ig\IgLanguageDetection\Tca\UserFunctions::class . '->getAliasTitle',
        'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_siteconfiguration_tca.xlf:site_route.ctrl.title',
        'typeicon_classes' => [
            'default' => 'mimetypes-x-content-domain',
        ],
    ],
    'columns' => [
        'alias' => [
            'label' => 'LLL:EXT:ig_language_detection/Resources/Private/Language/locallang.xlf:alias',
            'config' => [
                'type' => 'input',
                'required' => true,
            ],
        ],
        'languageId' => [
            'label' => 'LLL:EXT:ig_language_detection/Resources/Private/Language/locallang.xlf:language-id',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => \TYPO3\CMS\Backend\Configuration\TCA\ItemsProcessorFunctions::class . '->populateAvailableLanguagesFromSites',
                //'itemsProcFunc' => SiteLanguages::class . '->get',
                'required' => true,
            ],
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '--palette--;;general, ',
        ],
    ],
    'palettes' => [
        'general' => [
            'showitem' => 'alias, languageId,',
        ],
    ],
    
];