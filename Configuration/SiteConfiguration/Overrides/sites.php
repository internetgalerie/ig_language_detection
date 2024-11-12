<?php

/*
use Ig\IgLanguageDetection\Tca\SiteLanguages;

$GLOBALS['SiteConfiguration']['site']['columns']['languageDetectionEnable'] = [
    'label' => 'LLL:EXT:ig_language_detection/Resources/Private/Language/locallang.xlf:enable',
    'config' => [
        'type' => 'check',
        'default' => '1',
    ],
];
*/

/*
  // need core changes
$GLOBALS['SiteConfiguration']['site']['columns']['languageDetectionAliases'] = [
    'label' => 'LLL:EXT:ig_language_detection/Resources/Private/Language/locallang.xlf:aliases',
    'config' => [
        'type' => 'inline',
        'foreign_table' => 'site_aliases',
        'appearance' => [
            'collapseAll' => true,
            'enabledControls' => [
                'info' => false,
            ],
        ],
    ],
];
*/

/*
$GLOBALS['SiteConfiguration']['site']['columns']['languageDetectionDebug'] = [
    'label' => 'LLL:EXT:ig_language_detection/Resources/Private/Language/locallang.xlf:debug',
    'config' => [
        'type' => 'check',
        'default' => '0',
    ],
];
$GLOBALS['SiteConfiguration']['site']['columns']['languageDetectionAppendPath'] = [
    'label' => 'LLL:EXT:ig_language_detection/Resources/Private/Language/locallang.xlf:appendPath',
    'config' => [
        'type' => 'check',
        'default' => '1',
    ],
];

$GLOBALS['SiteConfiguration']['site']['columns']['languageDetectionDefaultLanguageId'] = [
    'label' => 'LLL:EXT:ig_language_detection/Resources/Private/Language/locallang.xlf:default_language_id',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'itemsProcFunc' => SiteLanguages::class . '->get',
    ],
];

// languageDetectionAliases,
$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] = str_replace(
    ', languages,',
    ', languages,--div--;LLL:EXT:ig_language_detection/Resources/Private/Language/locallang.xlf:language.detection,languageDetectionEnable,languageDetectionDebug,languageDetectionAppendPath,languageDetectionDefaultLanguageId, ',
    $GLOBALS['SiteConfiguration']['site']['types']['0']['showitem']
);
*/



$GLOBALS['SiteConfiguration']['site_language']['columns']['languageDetectionExclude'] = [
    'label' => 'LLL:EXT:ig_language_detection/Resources/Private/Language/locallang.xlf:site_language.exclude',
    'config' => [
        'type' => 'check',
        'default' => '0',
    ],
];

$GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem'] = str_replace(
    'flag,',
    'flag, languageDetectionExclude,',
    $GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem']
);

