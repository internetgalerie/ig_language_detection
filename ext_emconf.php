<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Language Detection Redirect Resolver',
    'description' => 'Language detection in PSR-15 middleware stack on uri=/. Based on the Accept-Language Header the corresponding site config is choosen. Additional configuration in YAML site configuration is available like aliases and more',
    'category' => 'fe',
    'author' => 'Daniel Abplanalp',
    'author_email' => 'typo3@internetgalerie.ch',
    'author_company' => 'Internetgalerie AG',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.6',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.9.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
