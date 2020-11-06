<?php
/**
 * Language Redirector plugin for Craft CMS 3.x
 *
 * Automatically redirect visitors to their preferred language
 *
 * @link      https://pierrestoffe.be
 * @copyright Copyright (c) 2018 Pierre Stoffe
 */

 /**
  * This file exists only as a template for the Language Redirector settings.
  * It does nothing on its own.
  *
  * Don't edit this file, instead copy it to 'craft/config' as 'language-redirector.php'
  * and make your changes there to override default settings.
  *
  * Once copied to 'craft/config', this file will be multi-environment aware as
  * well, so you can have different settings groups for each environment, just as
  * you do for 'general.php'
  */

return [
    // A list of all languages and their matching site
    'languages' => [],
    
    // The name of the URL query parameter
    'queryParameterName' => 'lang',
    
    // The name of the HTTP session key
    'sessionKeyName' => 'lang',
    
    // Should users with access to the Control Panel be redirected
    'redirectUsersWithCpAccess' => true,

    // Can users be automatically redirected or just use the plugin for language switching
    'canRedirect' => true,
    
    // The ID of the Entry that the getUrls method can default to when the current Entry is disabled in other Sites
    'defaultEntryId' => null,
    
];
