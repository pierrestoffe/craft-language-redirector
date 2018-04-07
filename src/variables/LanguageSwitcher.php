<?php
/**
 * Language Redirector plugin for Craft CMS 3.x
 *
 * Automatically redirect visitors to their preferred language
 *
 * @link      https://pierrestoffe.be
 * @copyright Copyright (c) 2018 Pierre Stoffe
 */

namespace pierrestoffe\languageredirector\variables;

use pierrestoffe\languageredirector\LanguageRedirector;
use pierrestoffe\languageredirector\services\RedirectService;

use Craft;

/**
 * @author    Pierre Stoffe
 * @package   LanguageRedirector
 * @since     1.0.0
 */
class LanguageSwitcherVariable
{
    /**
     * Get the URLs of all languages.
     *
     * @return array
     */
    public function getUrls(): array
    {
        $urls = array();
        
        $currentElement = Craft::$app->urlManager->getMatchedElement();
        $siteLanguages = LanguageRedirector::getInstance()->getSettings()->languages;
        $queryParameterName = LanguageRedirector::getInstance()->getSettings()->queryParameterName;
        
        if(!$currentElement || !$siteLanguages) {
            return null;
        }
        
        foreach($siteLanguages as $language => $site) {
            $redirectService = new RedirectService();
            $targetUrl = $redirectService->getTargetUrl($language);
            
            $urls[$language]['id'] = $language;
            $urls[$language]['name'] = \Locale::getDisplayName($language, Craft::$app->language);
            $urls[$language]['nativeName'] = \Locale::getDisplayName($language, $language);
            $urls[$language]['url'] = $targetUrl . '?' . $queryParameterName . '=' . $language;
        }
        
        return $urls;
    }
}