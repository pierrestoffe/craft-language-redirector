<?php
/**
 * Language Redirector plugin for Craft CMS 3.x
 *
 * Automatically redirect visitors to their preferred language
 *
 * @link      https://pierrestoffe.be
 * @copyright Copyright (c) 2018 Pierre Stoffe
 */

namespace pierrestoffe\languageredirector;

use pierrestoffe\languageredirector\services\LanguageRedirectorService;
use pierrestoffe\languageredirector\models\Settings;
use pierrestoffe\languageredirector\variables\LanguageSwitcherVariable;

use Craft;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;

/**
 * @author    Pierre Stoffe
 * @package   LanguageRedirector
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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        $request = Craft::$app->getRequest();
        if(
            $request->isSiteRequest &&
            !$request->isConsoleRequest &&
            !$request->isActionRequest &&
            !$request->isLivePreview &&
            !$request->isAjax &&
            $this->getSettings()->enabled
        ) {
            $service = new LanguageRedirectorService();
            $service->redirectVisitor();
        }
        
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
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

}
