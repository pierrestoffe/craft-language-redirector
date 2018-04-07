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
use pierrestoffe\languageredirector\services\LanguageService;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\helpers\UrlHelper;

use Jaybizzle\CrawlerDetect\CrawlerDetect;

/**
 * @author    Pierre Stoffe
 * @package   LanguageRedirector
 * @since     1.0.0
 */
class RedirectService extends Component
{
    // Public Methods
    // =========================================================================
    
    /**
     * Redirect to a localized URL if needed, unless the page is being visited
     * by a crawler
     * 
     * @return bool
     */
    public function redirect(): bool
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
    public function getTargetUrl(string $language = null): string
    {
        $targetElement = $this->getTargetElement($language);
        
        if($targetElement === null) {
            return null;
        }
        
        // Unset language URL query parameter
        $languageService = new LanguageService();
        $queryParameters = $languageService->getQueryParameters();
        $queryParameterName = LanguageRedirector::getInstance()->getSettings()->queryParameterName;
        unset($queryParameters[$queryParameterName]);
        
        $targetUrl = UrlHelper::url($targetElement->getUrl(), $queryParameters, null, $targetElement->siteId);
        $targetUrl = Craft::getAlias($targetUrl);
        
        return $targetUrl;
    }
    
    /**
     * Get the localized Element after processing all parameters
     *
     * @param string|null $language
     * @return ElementInterface
     */
    public function getTargetElement(string $language = null): ElementInterface
    {
        $languageService = new LanguageService();
        $targetSite = $languageService->getTargetSite($language);
        
        $currentElement = Craft::$app->urlManager->getMatchedElement();
        
        if(!$currentElement) {
            return null;
        }
        
        if($targetSite === null) {
            return null;
        }
        
        $targetElement = Craft::$app->elements->getElementById($currentElement->getId(), null, $targetSite->id);
        
        return $targetElement;
    }
}
