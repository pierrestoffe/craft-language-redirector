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

namespace pierrestoffe\languageredirector;

use Craft;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;
use pierrestoffe\languageredirector\models\Settings;
use pierrestoffe\languageredirector\services\LanguageRedirectorService;
use pierrestoffe\languageredirector\variables\LanguageSwitcherVariable;
use yii\base\Event;

/**
 * @author    Pierre Stoffe
 *
 * @since     1.0.0
 *
 * @property LanguageRedirectorService LanguageRedirectorService
 */
class LanguageRedirector extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var LanguageRedirector
     */
    public static $plugin;

    // Public Methods
    // =========================================================================

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Check for the best language match
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                $request = Craft::$app->getRequest();
                $canRedirect = false;
                
                if($request->isSiteRequest) {
                    $canRedirect = true;
                }
                
                if($request->isConsoleRequest) {
                    $canRedirect = false;
                }
                
                if($request->isActionRequest) {
                    $canRedirect = false;
                }
                
                if($request->isLivePreview) {
                    $canRedirect = false;
                }
                
                if($request->isAjax) {
                    $canRedirect = false;
                }
                
                if($request->getQueryParam('ignore-lang') !== null) {
                    $canRedirect = false;
                } 
                
                if(Craft::$app->user->checkPermission('accessCp') && LanguageRedirector::getInstance()->getSettings()->redirectUsersWithCpAccess == false) {
                    $canRedirect = false;
                }
                
                if ($canRedirect) {
                    $service = new LanguageRedirectorService();
                    $service->redirectVisitor();
                }
            }
        );

        // Register the variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('languageSwitcher', LanguageSwitcherVariable::class);
            }
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * {@inheritdoc}
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }
}
