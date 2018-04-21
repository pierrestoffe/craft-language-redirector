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
use craft\base\ElementInterface;
use craft\helpers\UrlHelper;
use craft\models\Site;

use Jaybizzle\CrawlerDetect\CrawlerDetect;

/**
 * @author    Pierre Stoffe
 * @package   LanguageRedirector
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
     * by a crawler
     * 
     * @return bool
     */
    public function redirectVisitor()
    {
        $CrawlerDetect = new CrawlerDetect();
        
        // If a crawler is detected, stop here
        if($CrawlerDetect->isCrawler()) {
            return false;
        }
        
        $redirectUrl = $this->getTargetUrl();
        
        if($redirectUrl === null) {
            return false;
        }
        
        header("Location: {$redirectUrl}", true, 302);
    }

    /**
     * Get the localized url
     * 
     * @param string|null $language
     * @return string
     */
    public function getTargetUrl(string $language = null)
    {
        $targetElement = $this->getTargetElement($language);
        
        if($targetElement === null) {
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
     * Get the localized Element
     *
     * @param string|null $language
     * @return ElementInterface
     */
    public function getTargetElement(string $language = null)
    {
        $targetSite = $this->getTargetSite($language);
        
        $currentElement = Craft::$app->urlManager->getMatchedElement();
        
        if(!$currentElement) {
            return null;
        }
        
        if($targetSite === null) {
            return null;
        }
        
        $targetElement = Craft::$app->elements->getElementById($currentElement->getId(), null, $targetSite->id);
        
        // If element is not enabled for this site
        if($targetElement->enabledForSite == false && $this->_getLanguageFromQueryParameter() === null) {
            return null;
        }
        
        return $targetElement;
    }
    
    /**
     * Process all parameters and return the target Site
     * 
     * @param string|null $language
     * @return string
     */
    public function getTargetSite(string $language = null)
    {
        $targetSite = null;
        
        if($language !== null) {
            $targetSite = $this->getSiteFromLanguage($language);
            
            return $targetSite;
        }
        
        // Use the query parameter
        if($targetSite === null) {
            $language = $this->_getLanguageFromQueryParameter();
            $targetSite = $this->getSiteFromLanguage($language);
        }
        
        // Use the language session key
        if($targetSite === null) {
            $language = $this->_getLanguageFromSession();
            $targetSite = $this->getSiteFromLanguage($language);
        }
        
        // Guess the language
        if($targetSite === null) {
            $language = $this->_getLanguageFromGuess();
            $targetSite = $this->getSiteFromLanguage($language);
        }
        
        if($targetSite === null) {
            return null;
        }
        
        // Stop if continuing wouldn't result in anything different
        if($this->_checkIfSiteIsAlreadyInUse($targetSite->id) && $this->_getLanguageFromQueryParameter() === null) {
            return null;
        }
        
        // Set language session key
        $sessionKeyName = LanguageRedirector::getInstance()->getSettings()->sessionKeyName;
        Craft::$app->getSession()->set($sessionKeyName, $language);
        
        return $targetSite;
    }
    
    /**
     * Get the Site that matches the target language.
     *
     * @param string|null $language
     * @return Site
     */
    public function getSiteFromLanguage(string $language = null)
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

    // Private Methods
    // =========================================================================
    
    /**
     * Get the language that is set in the URL query parameters
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
     * Get the language that is set in the session
     *
     * @return string
     */
    private function _getLanguageFromSession()
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
    private function _getLanguageFromGuess()
    {
        $siteLanguages = array_keys(LanguageRedirector::getInstance()->getSettings()->languages);
        $language = Craft::$app->getRequest()->getPreferredLanguage($siteLanguages);

        return $language;
    }
    
    /**
     * Check whether the target language is already in use
     *
     * @param int $id
     * @return bool
     */
    private function _checkIfSiteIsAlreadyInUse(int $id = 0): bool
    {
        $currentSite = Craft::$app->sites->getCurrentSite();
        
        if($currentSite === null) {
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
}
