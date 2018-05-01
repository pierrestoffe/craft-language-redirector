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
use pierrestoffe\languageredirector\services\LanguageRedirectorService;

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
        $queryParameterName = LanguageRedirector::getInstance()->getSettings()->queryParameterName;
        $siteLanguages = LanguageRedirector::getInstance()->getSettings()->languages;
        
        if(!$siteLanguages) {
            return null;
        }
        
        $languages = array();
        
        foreach($siteLanguages as $language => $site) {
            $languageService = new LanguageRedirectorService();
            $targetUrl = $languageService->getTargetUrl($language);
            
            if($targetUrl !== null) {
                $separator = strpos($targetUrl,'?') !== false ? '&' : '?';
                $languages[$language]['id'] = $language;
                $languages[$language]['name'] = \Locale::getDisplayName($language, Craft::$app->language);
                $languages[$language]['nativeName'] = \Locale::getDisplayName($language, $language);
                $languages[$language]['url'] = $targetUrl . $separator . $queryParameterName . '=' . $language;
            }
        }
        
        return $languages;
    }
}
