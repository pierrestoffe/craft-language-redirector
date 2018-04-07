<?php
/**
 * Language Redirector plugin for Craft CMS 3.x
 *
 * Automatically redirect visitors to their preferred language
 *
 * @link      https://pierrestoffe.be
 * @copyright Copyright (c) 2018 Pierre Stoffe
 */

namespace pierrestoffe\languageredirector\services;

use pierrestoffe\languageredirector\LanguageRedirector;

use Craft;
use craft\base\Component;
use craft\models\Site;

/**
 * @author    Pierre Stoffe
 * @package   LanguageRedirector
 * @since     1.0.0
 */
class LanguageService extends Component
{
    // Protected Properties
    // =========================================================================
    
    /**
     * @var array The URL query parameters
     */
    protected $queryParameters;
    
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
     * Getter for the `queryParameters` property.
     *
     * @return array
     */
    public function getQueryParameters()
    {
        return $this->queryParameters;
    }
    
    /**
     * Getter for a single key of the `queryParameters` property.
     *
     * @param string|null $parameter The name of the URL query parameter
     * @return string
     */
    public function getQueryParameter(string $parameter = null): string
    {
        return $this->queryParameters[$parameter] ?? null;
    }
    
    /**
     * Process all parameters and return the target language
     * 
     * @param string|null $targetLanguage
     * @return string
     */
    public function getTargetSite(string $targetLanguage = null): string
    {
        $targetSite = null;
        
        if($targetLanguage !== null) {
            $targetSite = $this->getSiteFromLanguage($targetLanguage);
            
            return $targetSite;
        }
        
        // Use the query parameter
        if($targetSite === null) {
            $targetLanguage = $this->getLanguageFromQueryParameter();
            $targetSite = $this->getSiteFromLanguage($targetLanguage);
        }
        
        // Use the language session key
        if($targetSite === null) {
            $targetLanguage = $this->getLanguageFromSession();
            $targetSite = $this->getSiteFromLanguage($targetLanguage);
        }
        
        // Guess the language
        if($targetSite === null) {
            $targetLanguage = $this->getLanguageFromGuess();
            $targetSite = $this->getSiteFromLanguage($targetLanguage);
        }
        
        if($targetSite === null) {
            return null;
        }
        
        // Stop if continuing wouldn't result in anything different
        if($this->checkIfSiteIsAlreadyInUse($targetSite->id) && $this->getLanguageFromQueryParameter() === null) {
            return null;
        }
        
        // Set language session key
        $this->_setLanguageSession($targetLanguage);
        
        return $targetSite;
    }
    
    /**
     * Get the Site that matches the target language.
     *
     * @param string|null $language
     * @return Site
     */
    public function getSiteFromLanguage(string $language = null): Site
    {
        if($language === null) {
            return null;
        }
        
        $siteForLanguage = LanguageRedirector::getInstance()->getSettings()->languages[$language] ?? 0;
        
        if(is_int($siteForLanguage)) {
            $site = Craft::$app->sites->getSiteById($siteForLanguage);
            
            if($site != null) {
                return $site;
            }
        } else {
            $site = Craft::$app->sites->getSiteByHandle($siteForLanguage);
            
            if($site != null) {
                return $site;
            }
        }
        
        return null;
    }
    
    /**
     * Check whether the target language is already in use
     *
     * @param int $id
     * @return bool
     */
    public function checkIfSiteIsAlreadyInUse(int $id = 0): bool
    {
        $currentSite = Craft::$app->sites->getCurrentSite();
        
        if($currentSite === null) {
            return false;
        }
        
        $siteIsAlreadyInUse = (int) $currentSite->id === (int) $id;
        
        return $siteIsAlreadyInUse;
    }

    // Protected Methods
    // =========================================================================
    
    /**
     * Get the language that is set in the URL query parameters
     *
     * @return string
     */
    protected function getLanguageFromQueryParameter(): string
    {
        $queryParameterName = LanguageRedirector::getInstance()->getSettings()->queryParameterName;
        $language = $this->getQueryParameter($queryParameterName);
        
        return $language;
    }
    
    /**
     * Get the language that is set in the session
     *
     * @return string
     */
    protected function getLanguageFromSession(): string
    {
        $sessionKeyName = LanguageRedirector::getInstance()->getSettings()->sessionKeyName;
        $language = Craft::$app->getSession()->get($sessionKeyName);
        
        return $language;
    }
    
    /**
     * Check whether a match can be made between any of the browser's languages
     * and any of Craft's languages
     *
     * @return string
     */
    protected function getLanguageFromGuess(): string
    {
        $siteLanguages = array_keys(LanguageRedirector::getInstance()->getSettings()->languages);
        $language = Craft::$app->getRequest()->getPreferredLanguage($siteLanguages);

        return $language;
    }

    // Private Methods
    // =========================================================================
    
    /**
     * Setter for the `queryParameters` property.
     * 
     * @return @void
     */
    private function _setQueryParameters()
    {
        parse_str(html_entity_decode(Craft::$app->request->getQueryStringWithoutPath()), $queryParameters);

        $this->queryParameters = $queryParameters;
    }

    /**
     * Set the language session key
     *
     * @param string|null $language
     * @return @void
     */
    private function _setLanguageSession(string $language = null)
    {
        $sessionKeyName = LanguageRedirector::getInstance()->getSettings()->sessionKeyName;
        Craft::$app->getSession()->set($sessionKeyName, $language);
    }
}
