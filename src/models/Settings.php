<?php
/**
 * Language Redirector plugin for Craft CMS 3.x
 *
 * Automatically redirect visitors to their preferred language
 *
 * @link      https://pierrestoffe.be
 * @copyright Copyright (c) 2018 Pierre Stoffe
 */

namespace pierrestoffe\languageredirector\models;

use craft\base\Model;

/**
 * @author    Pierre Stoffe
 * @package   LanguageRedirector
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * A list of all languages and their matching site
     *
     * @var array
     */
    public $languages = [];

    /**
     * The name of the URL query parameter
     *
     * @var string
     */
    public $queryParameterName = 'lang';

    /**
     * The name of the HTTP session key
     *
     * @var string
     */
    public $sessionKeyName = 'lang';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['languages', 'default', 'value' => []],
            ['languages', 'required'],
            
            ['queryParameterName', 'string'],
            ['queryParameterName', 'default', 'value' => 'lang'],
            
            ['sessionKeyName', 'string'],
            ['sessionKeyName', 'default', 'value' => 'lang']
        ];
    }
}