<?php
declare(strict_types=1);

namespace Ig\IgLanguageDetection\Middleware;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/*

Based on one function of rlmp_language_detection by
 * @author    robert lemke medienprojekte <rl@robertlemke.de>
 * @author    Mathias Bolt Lesniak, LiliO Design <mathias@lilio.com>
 * @author    Joachim Mathes, punkt.de GmbH <t3extensions@punkt.de>
 * @author    Thomas Löffler <loeffler@spooner-web.de>
 * @author    Markus Klein <klein.t3@reelworx.at>


use the folowing code in user site config.yaml:

languageDetection:
  debug: false
  defaultLanguageId: 2
  aliases:
    -
      alias: en
      languageId: '1'
    -
      alias: it
      languageId: '0'


defaultLanguageId: Language Uid taken if nothing is found, if not defined the default language is taken Uid=0
aliases: to map other languages to the available languages
  alias: iso-639-1 of the language to map
debug: if true - no redirect, debug infos are displayed

*/

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Routing\SiteRouteResult;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;

/**
 * Resolves redirects of site if base is not /
 * Can be replaced or extended by extensions if GeoIP-based or user-agent based language redirects need to happen.
 */
class SiteBaseWithLanguageRedirectResolver implements MiddlewareInterface
{

    /**
     * Redirect to default language if required
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $site = $request->getAttribute('site', null);
        $language = $request->getAttribute('language', null);
        // Usually called when "https://www.example.com" was entered, but all sites have "https://www.example.com/lang-key/"
        if ($site instanceof Site && !($language instanceof SiteLanguage)) {
            $configurationLanguageDetection = $site->getConfiguration()['languageDetection'] ?? [];
            $debug = $configurationLanguageDetection['debug'] ?? false;
            $languages = $site->getLanguages();
            //$langIsoCodes=explode(',',reset($request->getHeader('accept-language')));
            $acceptLanguage = reset($request->getHeader('accept-language'));
            $langIsoCodes = $acceptLanguage === false ? [] : $this->getAcceptedLanguages($acceptLanguage);
            if ($debug) {
                echo('<h3>Browser Codes:</h3>');
                foreach ($langIsoCodes as $code => $quality) {
                    echo('<li>' . $code . ' (' . $quality . ')</li>');
                }
                echo('<h3>Languages in site config:</h3>');
                foreach ($languages as $language) {
                    echo('<li>' . $language->getTwoLetterIsoCode() . ' </li>');
                }
                echo('<h3>Test</h3>');
            }
            foreach ($langIsoCodes as $langIsoCode => $q) {
                $twoLetterIsoCode = substr($langIsoCode, 0, 2);
                foreach ($languages as $language) {
                    if ($debug) {
                        echo('<li>test browser languages with available languages: ' . $twoLetterIsoCode . '==' . $language->getTwoLetterIsoCode() . ' (id=' . $language->getLanguageId() . ')</li>');
                    }
                    if ($language->getTwoLetterIsoCode() == $twoLetterIsoCode) {
                        return $this->doRedirect($request, $language, $configurationLanguageDetection, $debug,
                            'found language');
                        /*
                        $uri=$this->getRedirect( $language, $requestTarget);
                        if($debug) {
                          die(  '<b>found language - redirect to ' . $uri );
                        }
                        return new RedirectResponse($uri, 307);
                        */
                    }
                }
            }
            // Aliases
            //var_dump($site->getConfiguration()['languages']['aliases']);
            if ($configurationLanguageDetection && isset($configurationLanguageDetection['aliases'])) {
                foreach ($langIsoCodes as $langIsoCode => $q) {
                    $twoLetterIsoCode = substr($langIsoCode, 0, 2);
                    foreach ($configurationLanguageDetection['aliases'] as $alias) {
                        if ($debug) {
                            echo('<li>test browser languages with aliases: ' . $twoLetterIsoCode . '==' . $alias['alias'] . ' (languageId=' . $alias['languageId'] . ')</li>');
                        }
                        if ($alias['alias'] == $twoLetterIsoCode) {
                            $language = $site->getLanguageById(intval($alias['languageId']));
                            // is confugured language active
                            if ($language->isEnabled()) {
                                return $this->doRedirect($request, $language, $configurationLanguageDetection, $debug,
                                    'found alias');
                            } else {
                                // config error
                                if ($debug) {
                                    echo('<b style="color: red;">Error: alias language is not enabled, id=' . $language->getLanguageId() . '</b><br />');
                                }
                            }
                        }
                    }
                }
            }
            // redirect to defaultLanguageId
            if ($configurationLanguageDetection && isset($configurationLanguageDetection['defaultLanguageId'])) {
                if ($debug) {
                    echo('<li>defaultLanguageId=' . $configurationLanguageDetection['defaultLanguageId'] . '</li>');
                }
                $language = $site->getLanguageById(intval($configurationLanguageDetection['defaultLanguageId']));
                // is configured language active
                if ($language->isEnabled()) {
                    return $this->doRedirect($request, $language, $configurationLanguageDetection, $debug,
                        'default language');
                }
                if ($debug) {
                    echo('<b style="color: red;">Error: defaultLanguageId=' . $language->getLanguageId() . ' is not enabled</b><br />');
                }
            }
            // take languageId=0
            $language = $site->getLanguageById(0);        //$site->getDefaultLanguage();
            if ($debug) {
                echo('<li>take default language (id=' . $language->getLanguageId() . ')</li>');
            }

            // do we have an active language id=0 otherwise take first active
            if (!$language->isEnabled()) {
                $language = reset($languages);
                if ($debug) {
                    echo('<h3>Selected language is not enabled - take first active language: id=' . $language->getLanguageId() . '</h3>');
                }
            }
            return $this->doRedirect($request, $language, $configurationLanguageDetection, $debug, 'default language');
            /*
            if($debug) {
              die(  '<b>Redirect with language  "' . $language->getTwoLetterIsoCode() . '" to ' . $language->getBase() . $requestPath );
            }
            return new RedirectResponse($language->getBase() . $requestPath, 307);
            */
        }
        return $handler->handle($request);
    }

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
    protected function getAcceptedLanguages(string $acceptLanguage): array
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

    // append current uri path if appendPath=true, rtrim getBase (really a function to rtrim instead of ltrim requestTarget?)
    public function doRedirect(
        ServerRequestInterface $request,
        SiteLanguage $language,
        array $configurationLanguageDetection,
        bool $debug,
        string $text
    ): ResponseInterface {
        if ($configurationLanguageDetection['appendPath'] && $request->getRequestTarget()) {
            $uri = rtrim((string)$language->getBase(), '/') . $request->getRequestTarget();
            if ($debug) {
                echo('<h3>appendPath is activated: add path "' . $request->getRequestTarget() . '"</h3>');
                die('<h3>' . $text . ', language="' . $language->getTwoLetterIsoCode() . '" - redirect with appendPath to ' . $uri . '</h3>');
            }
            return new RedirectResponse($uri, 307);
        }
        if ($debug) {
            die('<h3>' . $text . ' - redirect to ' . $language->getBase() . '</h3>');
        }
        return new RedirectResponse($language->getBase(), 307);
    }

}
