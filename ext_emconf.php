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
    'version' => '1.1.5',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
