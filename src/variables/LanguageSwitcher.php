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
     * @param array|null $urlOverrides
     *
     * @return array
     */
    public function getUrls($urlOverrides = null): array
    {
        $queryParameterName = LanguageRedirector::getInstance()->getSettings()->queryParameterName;
        $languageRedirectorService = new LanguageRedirectorService();
        $siteLanguages = $languageRedirectorService->getSitesPerLanguage();

        if (!$siteLanguages) {
            return array();
        }

        $languages = array();

        foreach ($siteLanguages as $language => $site) {
            $targetUrl = $urlOverrides[$language] ?? $languageRedirectorService->getTargetUrl($language);
            $locale = Craft::$app->i18n->getLocaleById($language);

            if (null !== $targetUrl) {
                $separator = false !== strpos($targetUrl, '?') ? '&' : '?';
                $languages[$language]['id'] = $language;
                $languages[$language]['name'] = $locale->getDisplayName(Craft::$app->language);
                $languages[$language]['nativeName'] = $locale->getDisplayName($language);
                $languages[$language]['url'] = $targetUrl . $separator . $queryParameterName . '=' . $language;
            }
        }

        return $languages;
    }
}
