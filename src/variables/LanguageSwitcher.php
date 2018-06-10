<?php
/**
 * Language Redirector plugin for Craft CMS 3.x.
 *
 * Automatically redirect visitors to their preferred language
 *
 * @see      https://pierrestoffe.be
 *
 * @copyright Copyright (c) 2018 Pierre Stoffe
 */

namespace pierrestoffe\languageredirector\variables;

use Craft;
use pierrestoffe\languageredirector\LanguageRedirector;
use pierrestoffe\languageredirector\services\LanguageRedirectorService;

/**
 * @author    Pierre Stoffe
 *
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

        if (!$siteLanguages) {
            return array();
        }

        $languages = array();

        foreach ($siteLanguages as $language => $site) {
            $languageService = new LanguageRedirectorService();
            $targetUrl = $languageService->getTargetUrl($language);

            if (null !== $targetUrl) {
                $separator = false !== strpos($targetUrl, '?') ? '&' : '?';
                $languages[$language]['id'] = $language;
                $languages[$language]['name'] = \Locale::getDisplayName($language, Craft::$app->language);
                $languages[$language]['nativeName'] = \Locale::getDisplayName($language, $language);
                $languages[$language]['url'] = $targetUrl.$separator.$queryParameterName.'='.$language;
            }
        }

        return $languages;
    }
}
