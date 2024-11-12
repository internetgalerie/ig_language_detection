<?php
namespace  Ig\IgLanguageDetection\Tca;

use TYPO3\CMS\Core\Localization\LanguageService;

class UserFunctions
{


    /**
     * Used to build the IRRE title of a site language element
     */
    public function getAliasTitle(array &$parameters): void
    {
        $record = $parameters['row'];
        $languageId = (int)($record['languageId'][0] ?? 0);

        if ($languageId === PHP_INT_MAX && str_starts_with((string)($record['uid'] ?? ''), 'NEW')) {
            // If we deal with a new record, created via "Create new" (indicated by the PHP_INT_MAX placeholder),
            // we use a label as record title, until the real values, especially the language ID, are calculated.
            $parameters['title'] = '[' . $this->getLanguageService()->sL('LLL:EXT:backend/Resources/Private/Language/locallang_siteconfiguration_tca.xlf:site.languages.new') . ']';
            return;
        }

        $parameters['title'] = sprintf(
            '%s [%d]',
            //$record['enabled'] ? '' : '[' . $this->getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:disabled') . ']',
            $record['alias'],
            $languageId,
        );
    }
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}