# TYPO3 Extension  'ig_language_detection'

[![Latest Stable Version](https://poser.pugx.org/internetgalerie/ig-language-detection/v/stable)](https://packagist.org/packages/internetgalerie/ig-language-detection)
[![Monthly Downloads](https://poser.pugx.org/internetgalerie/ig-language-detection/d/monthly)](https://packagist.org/packages/internetgalerie/ig-language-detection)
[![License](https://poser.pugx.org/internetgalerie/ig-language-detection/license)](https://packagist.org/packages/internetgalerie/ig-language-detection)

Language detection in PSR-15 middleware stack on uri=/. Based on the Accept-Language Header the corresponding site config is choosen. Additional configuration in YAML site configuration is available like aliases and more

## 1. What does it do?




- The language detection is only called when “https://www.example.com” was entered, but all sites have “https://www.example.com/lang-key/”.
- Extra configuraion is done in the site YAML config file (e.g. typo3conf/sites/website/config.yaml)
- The language detection is done with accept header (HTTP_ACCEPT_LANGUAGE) from the browser.
- If you just want the language detection on the activated languages of the site configuraion, you don’t need any extra configuraion.
- [Documentation is available][1]

## 2. Usage


### 1) Installation

#### Installation using Composer

The recommended way to install the extension is by using [Composer][2]. In your Composer based TYPO3 project root, just do `composer require internetgalerie/ig-language-detection`. 

#### Installation as extension from TYPO3 Extension Repository (TER)

Download and install the extension with the extension manager module.

TYPO3 10.4: Enable Feature in Settings -> "Feature Toggles" -> "Rearranged redirect middlewares"  (rearrangedRedirectMiddlewares) for redirects

[1]: https://docs.typo3.org/typo3cms/extensions/ig_language_detection/
[2]: https://getcomposer.org/

