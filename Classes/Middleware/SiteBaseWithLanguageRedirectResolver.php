<?php
declare(strict_types=1);

namespace Ig\IgLanguageDetection\Middleware;

use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Http\Uri;
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

languages:
  -
    title: 'Deutsch'
    enabled: true
    languageId: 3
    languageDetectionExclude: 1

languageDetectionExclude: exlude language in detection process
*/

use Ig\IgLanguageDetection\Utility\LanguageUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Routing\SiteRouteResult;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;

use TYPO3\CMS\Core\Site\SiteFinder;
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
        $limitToLanguages = [];
        $this->useGetLocal = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getNumericTypo3Version()) > 12000000;
        // typo3 didn't found a Site configuration for us, search in base of languages and select a site and choose between languages with this base
        if (ApplicationType::fromRequest($request)->isFrontend() && $site instanceof NullSite) {
            $requestUri =$request->getUri();
            //echo ('requestUri  = '. (string) $requestUri . '<br />');
            $requestHost = $requestUri->getHost();
            $siteConf = LanguageUtility::getSiteAndSiteLanguagesByHost($requestHost);
            if ($siteConf !== null) {
                $site = $siteConf['site'];
                $limitToLanguages = $siteConf['siteLanguages'];
            }
        }

        // Usually called when "https://www.example.com" was entered, but all sites have "https://www.example.com/lang-key/"
        if ($site instanceof Site && !($language instanceof SiteLanguage)) {
            $configurationLanguageDetection = $site->getConfiguration()['languageDetection'] ?? [];
            $debug = $configurationLanguageDetection['debug'] ?? false;
            $languages = $site->getLanguages();
            if(!empty($limitToLanguages)) {
                // if we a new config we should do aome array intersect
                $languages = $limitToLanguages;
                if ($debug) {
                    echo('<h3>NullSite:</h3>');
                    echo('<li>SiteFinder not found a site configuration for us</li>');
                    echo('<li>found site with base from languages</li>');
                    echo('<li>only choose between languages with this base</li>');
                }
            }
            //$langIsoCodes=explode(',',reset($request->getHeader('accept-language')));
            $acceptLanguage = reset($request->getHeader('accept-language'));
            $langIsoCodes = $acceptLanguage === false ? [] : LanguageUtility::getAcceptedLanguages($acceptLanguage);
            if ($debug) {
                echo('<h3>Browser Codes:</h3>');
                foreach ($langIsoCodes as $code => $quality) {
                    echo('<li>' . $code . ' (' . $quality . ')</li>');
                }
                echo('<h3>Languages in site config:</h3>');
                foreach ($languages as $language) {
                    echo('<li>' . ($this->useGetLocal ? $language->getLocale()->getLanguageCode() : $language->getTwoLetterIsoCode()). ' </li>');
                }
                echo('<h3>Test</h3>');
            }
            foreach ($langIsoCodes as $langIsoCode => $q) {
                $twoLetterIsoCode = substr($langIsoCode, 0, 2);
                foreach ($languages as $language) {
                    $languageDetectionExclude = $language->toArray()['languageDetectionExclude'] ?? false;
                    if ($debug) {
                        echo('<li>test browser languages with available languages: ' . $twoLetterIsoCode . '==' . ($this->useGetLocal ? $language->getLocale()->getLanguageCode() : $language->getTwoLetterIsoCode()) . ' (id=' . $language->getLanguageId() . ')</li>');
                    }
                    if (!$languageDetectionExclude && ($this->useGetLocal ? $language->getLocale()->getLanguageCode() : $language->getTwoLetterIsoCode()) == $twoLetterIsoCode) {
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
            if (empty($limitToLanguages)) {
                // take languageId=0
                $language = $site->getLanguageById(0);        //$site->getDefaultLanguage();
            } else {                
                if ($debug) {
                    echo('<li>we have limited langauges so we take the first one not excluded (see above)</li>');
                }
                // take first defined language
                $language = null;
                foreach ($limitToLanguages as $limitToLanguage) {
                    $languageDetectionExclude = $limitToLanguage->toArray()['languageDetectionExclude'] ?? false;
                    if (!$languageDetectionExclude) {
                        $language = $limitToLanguage;
                        break;
                    }
                }
                if ($language === null) {
                    $language = $limitToLanguages[0];
                    if ($debug) {
                        echo('<li>we have limited langauges and all have languageDetectionExclude=1, so we take the first one (error in site config)</li>');
                    }
                }
            }
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
              die(  '<b>Redirect with language  "' . ($this->useGetLocal ? $language->getLocale()->getLanguageCode() : $language->getTwoLetterIsoCode()) . '" to ' . $language->getBase() . $requestPath );
            }
            return new RedirectResponse($language->getBase() . $requestPath, 307);
            */
        }

        // redirect a call to base language url without index
        $configurationLanguageDetection = $site->getConfiguration()['languageDetection'] ?? [];
        if ($configurationLanguageDetection['appendPath'] ?? false) {
            $basePath = rtrim($language->getBase()->getPath(), '/');
            $requestPath = $request->getUri()->getPath();
            if ($requestPath == $basePath || $requestPath == $basePath . '/') {
                $uri = $this->getLanguageBaseUriWithIndex($request, $language);
                return new RedirectResponse($uri, 307);
            }
        }
        return $handler->handle($request);
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
            //$site = $request->getAttribute('site', null);
            //var_dump($site->getConfiguration()['routeEnhancers']['PageTypeSuffix']['index'], $language->getBase(), $request->getRequestTarget(), $site);exit(0);
            $uri = rtrim((string)$language->getBase(), '/') . $request->getRequestTarget();

            $requestPath = $request->getUri()->getPath();
            // add index to base language uri if base url is called
            if ($requestPath === '/') {
                $uri = $this->getLanguageBaseUriWithIndex($request, $language);
            }

            if ($debug) {
                echo('<h3>appendPath is activated: add path "' . $request->getRequestTarget() . '"</h3>');
                die('<h3>' . $text . ', language="' . ($this->useGetLocal ? $language->getLocale()->getLanguageCode() : $language->getTwoLetterIsoCode()) . '" - redirect with appendPath to ' . $uri . '</h3>');
            }
            return new RedirectResponse($uri, 307);
        }
        if ($debug) {
            die('<h3>' . $text . ' - redirect to ' . $language->getBase() . '</h3>');
        }
        return new RedirectResponse($language->getBase(), 307);
    }

    // get the base uri of a language appended with index.html
    protected function getLanguageBaseUriWithIndex(ServerRequestInterface $request, SiteLanguage $language): Uri
    {
        $site = $request->getAttribute('site', null);
        $pageTypeSuffix = $site->getConfiguration()['routeEnhancers']['PageTypeSuffix'] ?? [];
        $index = $pageTypeSuffix['index'] ?? 'index';
        $suffix = $pageTypeSuffix['default'] ?? '.html';
        return $request->getUri()->withPath(rtrim($language->getBase()->getPath(), '/') . '/' . $index . $suffix);
    }
}
