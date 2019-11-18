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

namespace pierrestoffe\languageredirector\services;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\helpers\UrlHelper;
use craft\models\Site;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use pierrestoffe\languageredirector\LanguageRedirector;

/**
 * @author    Pierre Stoffe
 *
 * @since     1.0.0
 */
class LanguageRedirectorService extends Component
{
    // Protected Properties
    // =========================================================================

    /**
     * @var array The URL query parameters
     */
    private $_queryParameters;

    // Public Methods
    // =========================================================================

    /**
     * Constructor function.
     */
    public function __construct()
    {
        $this->_setQueryParameters();
    }

    /**
     * Redirect to a localized URL if needed, unless the page is being visited
     * by a crawler.
     *
     * @return bool
     */
    public function redirectVisitor()
    {
        $CrawlerDetect = new CrawlerDetect();

        // If a crawler is detected, stop here
        if ($CrawlerDetect->isCrawler()) {
            $this->redirectCrawler();
            
            return false;
        }

        $redirectUrl = $this->getTargetUrl();

        if (null === $redirectUrl) {
            return false;
        }

        header("Location: {$redirectUrl}", true, 302);
        exit();
    }
    
    /**
     * Redirect crawlers to the same page, without the lang URL query parameter
     *
     * @return bool
     */
    public function redirectCrawler() {
        $queryParameter = $this->_getLanguageFromQueryParameter();
        
        if (empty($queryParameter)) {
            return false;
        }
        
        // Get current page URL
        $currentElement = Craft::$app->urlManager->getMatchedElement();
        $redirectUrl = $currentElement->url ?? null;
        
        if (null === $redirectUrl) {
            return false;
        }
        
        // Remove unnecessary URL query parameters from $_GET
        unset($_GET['p']);
        unset($_GET['lang']);
        $queryString = http_build_query($_GET);
        
        // Create URL
        if (!empty($queryString)) {
            $redirectUrl .= '?' . $queryString;
        }
        
        header("Location: {$redirectUrl}", true, 302);
        exit();
    }

    /**
     * Get the localized url.
     *
     * @param string|null $language
     *
     * @return string
     */
    public function getTargetUrl(string $language = null)
    {
        $targetElement = $this->getTargetElement($language);

        if (null === $targetElement) {
            return null;
        }

        // Unset language URL query parameter
        $queryParameters = $this->_getQueryParameters();
        $queryParameterName = LanguageRedirector::getInstance()->getSettings()->queryParameterName;
        unset($queryParameters[$queryParameterName]);

        $targetUrl = UrlHelper::url($targetElement->getUrl(), $queryParameters, null, $targetElement->siteId);
        $targetUrl = Craft::getAlias($targetUrl);

        return $targetUrl;
    }

    /**
     * Get the localized Element.
     *
     * @param string|null $language
     *
     * @return ElementInterface
     */
    public function getTargetElement(string $language = null)
    {
        $targetSite = $this->getTargetSite($language);

        $currentElement = Craft::$app->urlManager->getMatchedElement();

        if (!$currentElement) {
            return null;
        }

        if (null === $targetSite) {
            return null;
        }

        $targetElement = Craft::$app->elements->getElementById($currentElement->getId(), null, $targetSite->id);

        if (null === $targetElement) {
            return null;
        }

        // If element is not enabled for this site
        if (false == $targetElement->enabledForSite && null === $this->_getLanguageFromQueryParameter()) {
            return null;
        }

        return $targetElement;
    }

    /**
     * Process all parameters and return the target Site.
     *
     * @param string|null $language
     *
     * @return string
     */
    public function getTargetSite(string $language = null)
    {
        $targetSite = null;

        if (null !== $language) {
            $targetSite = $this->getSiteFromLanguage($language);

            return $targetSite;
        }

        // Use the query parameter
        if (null === $targetSite) {
            $language = $this->_getLanguageFromQueryParameter();
            $targetSite = $this->getSiteFromLanguage($language);
        }

        // Use the language session key
        if (null === $targetSite) {
            $language = $this->_getLanguageFromSession();
            $targetSite = $this->getSiteFromLanguage($language);
        }

        // Guess the language
        if (null === $targetSite) {
            $language = $this->_getLanguageFromGuess();
            $targetSite = $this->getSiteFromLanguage($language);
        }

        if (null === $targetSite) {
            return null;
        }

        // Stop if continuing wouldn't result in anything different
        if ($this->_checkIfSiteIsAlreadyInUse($targetSite->id) && null === $this->_getLanguageFromQueryParameter()) {
            return null;
        }

        // Set language session key
        $sessionKeyName = LanguageRedirector::getInstance()->getSettings()->sessionKeyName;
        $id = $this->_getSiteGroupId();
        Craft::$app->getSession()->set($id . '-' . $sessionKeyName, $language);

        return $targetSite;
    }

    /**
     * Get the Site that matches the target language.
     *
     * @param string|null $language
     *
     * @return Site
     */
    public function getSiteFromLanguage(string $language = null)
    {
        if (null === $language) {
            return null;
        }

        $sitesPerLanguage = $this->getSitesPerLanguage();
        $siteFromLanguage = $sitesPerLanguage[$language] ?? 0;

        if (is_int($siteFromLanguage)) {
            $site = Craft::$app->sites->getSiteById($siteFromLanguage);

            if (null != $site) {
                return $site;
            }
        } else {
            $site = Craft::$app->sites->getSiteByHandle($siteFromLanguage);

            if (null != $site) {
                return $site;
            }
        }

        return null;
    }

    /**
     * Get the list of all languages defined in the settings or in the Sites table, and their corresponding Site.
     *
     * @param string|null $group
     *
     * @return array
     */
    public function getSitesPerLanguage()
    {
        $languages = LanguageRedirector::getInstance()->getSettings()->languages;
        
        if (is_array(reset($languages))) {
            $languages = $this->_getSitesPerLanguageInGroup();
        }
        
        if (!empty($languages)) {
            return $languages;
        }

        $languages = [];
        foreach (Craft::$app->sites->getAllSites() as $site) {
            $languages[$site->language] = (int) $site->id;
        }

        return $languages;
    }

    // Private Methods
    // =========================================================================

    /**
     * Get the language that is set in the URL query parameters.
     *
     * @return string
     */
    private function _getLanguageFromQueryParameter()
    {
        $queryParameterName = LanguageRedirector::getInstance()->getSettings()->queryParameterName;
        $language = $this->_getQueryParameters()[$queryParameterName] ?? null;

        return $language;
    }

    /**
     * Get the language that is set in the session.
     *
     * @return string
     */
    private function _getLanguageFromSession()
    {
        $sessionKeyName = LanguageRedirector::getInstance()->getSettings()->sessionKeyName;
        $id = $this->_getSiteGroupId();
        $language = Craft::$app->getSession()->get($id . '-' . $sessionKeyName);

        return $language;
    }

    /**
     * Check whether a match can be made between any of the browser's languages
     * and any of Craft's languages.
     *
     * @return string|null
     */
    private function _getLanguageFromGuess()
    {
        $sitesPerLanguage = $this->getSitesPerLanguage();
        $siteLanguages = array_keys($sitesPerLanguage);

        // Get exact languages matches (required when working with country specific locales)
        $languages = array_intersect(array_map('strtolower', Craft::$app->getRequest()->getAcceptableLanguages()), array_map('strtolower', $siteLanguages));

        if (!empty($languages)) {
            return array_values($languages)[0];
        }

        // Get the most appropriate match
        $language = $this->_getPreferredLanguage($siteLanguages);

        return $language;
    }

    /**
     * Compare the list of defined languages in the config file and the list of
     * languages defined in the visitor's browser preferences. Get the most
     * appropriate match between these two lists.
     *
     * This function is copied from Yii2's Request class, with the exception
     * that it returns null if no match can be made.
     *
     * @param array $languages
     *
     * @return string|null
     */
    private function _getPreferredLanguage(array $languages = [])
    {
        if (empty($languages)) {
            return null;
        }

        foreach (Craft::$app->getRequest()->getAcceptableLanguages() as $acceptableLanguage) {
            $acceptableLanguage = str_replace('_', '-', strtolower($acceptableLanguage));
            foreach ($languages as $language) {
                $normalizedLanguage = str_replace('_', '-', strtolower($language));

                if (
                    $normalizedLanguage === $acceptableLanguage // en-us==en-us
                    || 0 === strpos($acceptableLanguage, $normalizedLanguage.'-') // en==en-us
                    || 0 === strpos($normalizedLanguage, $acceptableLanguage.'-') // en-us==en
                ) {
                    return $language;
                }
            }
        }

        return null;
    }

    /**
     * Check whether the target language is already in use.
     *
     * @param int $id
     *
     * @return bool
     */
    private function _checkIfSiteIsAlreadyInUse(int $id = 0): bool
    {
        $currentSite = Craft::$app->sites->getCurrentSite();

        if (null === $currentSite) {
            return false;
        }

        $siteIsAlreadyInUse = (int) $currentSite->id === (int) $id;

        return $siteIsAlreadyInUse;
    }

    /**
     * Setter for the `queryParameters` property.
     *
     * @return @void
     */
    private function _setQueryParameters()
    {
        parse_str(html_entity_decode(Craft::$app->request->getQueryStringWithoutPath()), $queryParameters);

        $this->_queryParameters = $queryParameters;
    }

    /**
     * Getter for the `queryParameters` property.
     *
     * @return array
     */
    private function _getQueryParameters()
    {
        return $this->_queryParameters;
    }
    
    /**
     * Get the list of all languages defined in the settings, in the current group
     *
     * @return array
     */
    public function _getSitesPerLanguageInGroup()
    {
        $languages = LanguageRedirector::getInstance()->getSettings()->languages;
        $siteGroup = $this->_getSiteGroup();
        $languagesInGroup = $languages[$siteGroup];
        
        return $languagesInGroup;
    }
    
    /**
     * Get the array key of the current language group, as defined in the settings
     *
     * @return int|string
     */
    public function _getSiteGroup()
    {
        $currentSite = Craft::$app->sites->getCurrentSite();
        $currentSiteHandle = $currentSite->handle;
        
        $languages = LanguageRedirector::getInstance()->getSettings()->languages;
        
        $siteGroup = null;
        foreach($languages as $key => $group){
            if(is_array($group) && in_array($currentSiteHandle, $group)) {
                $siteGroup = $key;
            }
        }
        
        return $siteGroup;
    }
    
    /**
     * Generate an ID for the current language group
     *
     * @return string
     */
    public function _getSiteGroupId()
    {
        $group = $this->_getSiteGroup();
        $groupId = md5($group);
        
        return $groupId;
    }
}
