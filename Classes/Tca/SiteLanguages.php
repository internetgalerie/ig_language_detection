<?php

declare(strict_types=1);

namespace Ig\IgLanguageDetection\Tca;

use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SiteLanguages
{
    public function __construct(protected ?SiteFinder $siteFinder = null)
    {
        if (!$siteFinder instanceof \TYPO3\CMS\Core\Site\SiteFinder) {
            /** @var SiteFinder $siteFinder */
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            $this->siteFinder = $siteFinder;
        }
    }

    /**
     * @param mixed[] $configuration
     */
    public function get(array &$configuration): void
    {
        if (!isset($configuration['row']['identifier'])) {
            return;
        }

        try {
            $site = $this->siteFinder->getSiteByIdentifier($configuration['row']['identifier']);
        } catch (SiteNotFoundException) {
            return;
        }

        $configuration['items'][] = ['', ''];
        foreach ($site->getAllLanguages() as $language) {
            $configuration['items'][] = [$language->getTitle(), $language->getLanguageId()];
        }
    }
}
