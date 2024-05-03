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
class LanguageRedirectorVariable
{
    /**
     * Get the URLs of all languages.
     *
     * @param array|null $urlOverrides
     * @param string|null $group
     *
     * @return array
     */
    public function getLanguageOptions($urlOverrides = null, string $group = null)
    {
        $siteLanguages = $this->_getSitesPerLanguage($group);
        if (!$siteLanguages) {
            return array();
        }

        $siteLanguages = array_keys($siteLanguages);
        $languageOptions = $this->_populateLanguages($siteLanguages, $urlOverrides, $group);

        return $languageOptions;
    }

    /**
     * Check if there is a language suggestion based on the user's preferences.
     *
     * @return bool
     */
    public function hasLanguageSuggestion()
    {
        $guessedLanguage = $this->getGuessedLanguage();

        if (!$guessedLanguage) {
            return false;
        }

        $languageRedirectorService = new LanguageRedirectorService();
        $guessedSite = $languageRedirectorService->getTargetSite($guessedLanguage);
        $currentSite = Craft::$app->sites->getCurrentSite();

        if (!$guessedSite || !$currentSite) {
            return false;
        }

        $hasLanguageSuggestion = $guessedSite->id != $currentSite->id;

        return $hasLanguageSuggestion;
    }

    /**
     * Get the guessed language based on the user's preferences.
     *
     * @return string|null
     */
    public function getGuessedLanguage()
    {
        $languageRedirectorService = new LanguageRedirectorService();
        $guessedLanguage = $languageRedirectorService->getLanguageFromGuess();

        if (!$guessedLanguage) {
            return;
        }

        return $guessedLanguage;
    }

    /**
     * Get the saved language from session.
     *
     * @return string|null
     */
    public function getSavedLanguage()
    {
        $languageRedirectorService = new LanguageRedirectorService();
        $savedLanguage = $languageRedirectorService->getLanguageFromSession();

        if (!$savedLanguage) {
            return;
        }

        return $savedLanguage;
    }

    /**
     * Get information for a specific language.
     *
     * @param string|null $language
     * @param array|null $urlOverrides
     *
     * @return array|null
     */
    public function getInformationForLanguage($language, $urlOverrides = null)
    {
        if (!$language) {
            return;
        }

        $informationForLanguage = $this->_populateLanguages([$language], $urlOverrides);

        return $informationForLanguage;
    }

    /**
     * Get sites per language for the given group.
     *
     * @param string|null $group
     *
     * @return array|null
     */
    protected function _getSitesPerLanguage($group)
    {
        $languageRedirectorService = new LanguageRedirectorService();
        $sitesPerLanguage = $languageRedirectorService->getSitesPerLanguage($group);

        return $sitesPerLanguage;
    }

    /**
     * Populate languages with their information.
     *
     * @param array $siteLanguages
     * @param array|null $urlOverrides
     * @param string|null $group
     *
     * @return array
     */
    protected function _populateLanguages(array $siteLanguages = [], $urlOverrides = null, string $group = null)
    {
        if (!$siteLanguages) {
            return array();
        }

        $populatedLanguages = array();
        $languageRedirectorService = new LanguageRedirectorService();
        $queryParameterName = LanguageRedirector::getInstance()->getSettings()->queryParameterName;
        foreach ($siteLanguages as $language) {
            $targetUrl = $urlOverrides[$language] ?? $languageRedirectorService->getTargetUrl($language, $group, true);
            $locale = Craft::$app->i18n->getLocaleById($language);

            if (null === $targetUrl) {
                continue;
            }

            $separator = false !== strpos($targetUrl, '?') ? '&' : '?';
            $populatedLanguages[$language]['id'] = $language;
            $populatedLanguages[$language]['name'] = $locale->getDisplayName(Craft::$app->language);
            $populatedLanguages[$language]['nativeName'] = $locale->getDisplayName($language);
            $populatedLanguages[$language]['url'] = $targetUrl . $separator . $queryParameterName . '=' . $language;
        }

        return $populatedLanguages;
    }
}
