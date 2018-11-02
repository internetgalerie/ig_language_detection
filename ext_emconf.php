<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Language Detection Redirect Resolver',
    'description' => 'Add simple language Detection on uri=/',
    'category' => 'fe',
    'author' => 'Daniel Abplanalp',
    'author_email' => 'typo3@internetgalerie.ch',
    'author_company' => 'Internetgalerie AG',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-9.5.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
