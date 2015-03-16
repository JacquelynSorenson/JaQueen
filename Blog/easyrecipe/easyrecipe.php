<?php
/*
Plugin Name: Easy Recipe
Plugin URI: http://www.easyrecipeplugin.com/
Description: The Wordpress recipe plugin for non-geeks. EasyRecipe makes it easy to enter, format and print your recipes, as well as automagically doing all the geeky stuff needed for Google's Recipe View.
Author: EasyRecipe
Version: 3.2.2929
Author URI: http://www.easyrecipeplugin.com
License: GPLv2 or later
*/

/*
 Copyright (c) 2010-2015 Box Hill LLC
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */


if (!function_exists('add_action')) {
    echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
    exit();
}

/**
 * We really don't want go through potentially CPU intensive processing for obvious 404 errors
 * $wp_query isn't set at this point so we can't just do is_404()
 * FIXME - check for allowable redirects for style stuff
 */
//$ext = pathinfo(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), PATHINFO_EXTENSION);
//if (in_array($ext, array('jpg', 'png', 'gif', 'wmv', 'mov', 'mp4', 'css', 'js'))) {
//    return;
//}

if (!class_exists('EasyRecipe', false)) {

    /**
     * Ignore ajax requests that don't concern us
     */
    if (defined('DOING_AJAX')) {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

        /**
         * Process for EasyRecipe calls and also process EasyIndex widget saves (need to register our taxonomies)
         */
        if (strpos($action, 'easyrecipe') !== 0) {
            $id_base = isset($_REQUEST['id_base']) ? $_REQUEST['id_base'] : '';
            if (!($action == 'save-widget' && $id_base == 'easyindex')) {
                return;
            }
        }
    }


    if (phpversion() < '5') {
        if (!function_exists('EasyRecipePHP5')) {
            function EasyRecipePHP5() {
                wp_die("This plugin requires PHP 5+.  Your server is running PHP" . phpversion() . '<br><a href="/wp-admin/plugins.php">Go back</a>');
            }
        }
        register_activation_hook(__FILE__, "EasyRecipePHP5");
        return;
    }

    /**
     * Autoload EasyIndex classes
     * @param $class
     */
    function EasyRecipeAutoload($class) {
        if (strpos($class, 'EasyRecipe') === 0 && strpos($class, 'EasyRecipePlus') === false && strpos($class, 'EasyRecipeBeta') === false) {
            /** @noinspection PhpIncludeInspection */
            @include(dirname(__FILE__) . "/lib/$class.php");
        }
    }

    spl_autoload_register("EasyRecipeAutoload");

    /**
     * Pass the version here so gulp doesn't have to re-generate the much larger class file on every build
     */
    $EasyRecipe = new EasyRecipe(dirname(__FILE__), rtrim(plugin_dir_url(__FILE__), '/'), '3.2.2929');

    /*
    * A little weirdness to handle WP's inability to get the plugin basename correct if wp-content/plugins is a symlink
    * Only required because our own test servers symlink the plugins directory
    */
    $f = basename(dirname(__FILE__)) . '/' . basename(__FILE__);
    register_activation_hook($f, array($EasyRecipe, "pluginActivated"));
    register_deactivation_hook($f, array($EasyRecipe, "pluginDeactivated"));
}

