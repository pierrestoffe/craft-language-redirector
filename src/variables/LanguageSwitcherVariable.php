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
use pierrestoffe\languageredirector\variables\LanguageRedirectorVariable;

/**
 * @author    Pierre Stoffe
 *
 * @since     1.0.0
 */
class LanguageSwitcherVariable
{
    /**
     * @deprecated
     */
    public function getUrls($urlOverrides = null, string $group = null)
    {
        Craft::$app->getDeprecator()->log('craft.languageSwitcher.getUrls', '`craft.languageSwitcher.getUrls()` has been deprecated. Use `craft.languageRedirector.getLanguageUrls()` instead.');

        $languageRedirectorVariable = new LanguageRedirectorVariable();
        return $languageRedirectorVariable->getUrls($urlOverrides, $group);
    }
}
