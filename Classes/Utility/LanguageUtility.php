<?php
declare(strict_types=1);

namespace Ig\IgLanguageDetection\Utility;

use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LanguageUtility
{
    /**
     * Returns the preferred languages ("accepted languages") from the visitor's
     * browser settings.
     * The accepted languages are described in RFC 2616.
     * It's a list of language codes (e.g. 'en' for english), separated by
     * comma (,). Each language may have a quality-value (e.g. 'q=0.7') which
     * defines a priority. If no q-value is given, '1' is assumed. The q-value
     * is separated from the language code by a semicolon (;) (e.g. 'de;q=0.7')
     *
     * @param string $acceptLanguage
     * @return array An array containing the accepted languages; key = iso code and value = quality, sorted by quality
     */
    public static function getAcceptedLanguages(string $acceptLanguage): array
    {
        $rawAcceptedLanguagesArr = GeneralUtility::trimExplode(',', $acceptLanguage, true);
        $acceptedLanguagesArr = [];
        foreach ($rawAcceptedLanguagesArr as $languageAndQualityStr) {
            list($languageCode, $quality) = GeneralUtility::trimExplode(';', $languageAndQualityStr);
            $acceptedLanguagesArr[$languageCode] = $quality ? (float)substr($quality, 2) : (float)1;
        }

        // Now sort the accepted languages by their quality
        if (is_array($acceptedLanguagesArr)) {
            arsort($acceptedLanguagesArr);
            return $acceptedLanguagesArr;
        }

        return [];
    }

    /**
     * Returns the all languages of a site, matching the given host
     * if no site identifier is given, all sites are searched for the given host. 
     * The first found site with a language matching the given host is returned
     *
     * @param string $requestHost host to search
     * @param string $siteIdentifier a site identifier or null for all sites
     * @return array ['site' => $site, 'siteLanguages' => $siteLanguages] An array containing the site and the matched siteLanguages for this site. 
     *         site is null and siteLanguages empty if no site is found
     */
    public static function getSiteAndSiteLanguagesByHost(string $requestHost, string $siteIdentifier = null)
    {
        $finder = GeneralUtility::makeInstance(SiteFinder::class);
        if ($siteIdentifier === null) {
            $sites = $finder->getAllSites();
        } else {
            $sites = [$finder->getSiteByIdentifier($siteIdentifier)];
        }
        $site = null;
        $siteLanguages = [];
        foreach ($sites as $possibleSite) {
            $languagesFound = [];
            foreach ($possibleSite->getAllLanguages() as $siteLanguage) {
                $uri = $siteLanguage->getBase();
                //echo ($uri->getHost() . '==='. $requestHost .'<br />');
                if($uri->getHost() == $requestHost && $siteLanguage->isEnabled()) {
                    $languagesFound[] = $siteLanguage;
                }
            }
            if (!empty($languagesFound)) {
                $site = $possibleSite;
                $siteLanguages = $languagesFound;
                break;
            }
        }
        return [
                    'site' => $site,
                    'siteLanguages' => $siteLanguages,
                ];
    }

    /**
     * Returns the all languages of a site, matching the given host
     * if no site identifier is given, all sites are searched for the given host. 
     * The first found site with a language matching the given host is returned
     *
     * @param string $requestHost host to search
     * @param string $siteIdentifier a site identifier or null for all sites
     * @return SiteLanguage $siteLanguage best matching siteLanguages for this site and host accorging accept-language. 
     */
    public static function getSiteLanguageByHost(string $requestHost, string $siteIdentifier = null)
    {
        $acceptLanguage = reset($GLOBALS['TYPO3_REQUEST']->getHeader('accept-language'));
        $langIsoCodes = static::getAcceptedLanguages($acceptLanguage);
        $siteAndSiteLanguages = static::getSiteAndSiteLanguagesByHost($requestHost, $siteIdentifier);
        $siteLanguages = $siteAndSiteLanguages['siteLanguages'];
        
        if (!empty($siteLanguages)) {
            foreach ($langIsoCodes as $langIsoCode => $q) {
                $twoLetterIsoCode = substr($langIsoCode, 0, 2);
                foreach ($siteLanguages as $siteLanguage) {
                    if ($siteLanguage->getTwoLetterIsoCode() == $twoLetterIsoCode) {
                        return $siteLanguage;
                    }
                }
            }
        }
        return null;
    }
}
