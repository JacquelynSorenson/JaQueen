<?php

/**
 * Copyright (c) 2010-2013 Box Hill LLC
 *
 * All Rights Reserved
 * No part of this software may be reproduced, copied, modified or adapted, without the prior written consent of Box Hill LLC.
 * Commercial use and distribution of any part of this software is not allowed without express and prior written consent of Box Hill LLC.
 */


/**
 * Class EasyRecipeAutoUpdate
 *
 * Code to do plugin update checks
 */
class EasyRecipeAutoUpdate {

    private $slug = 'easyrecipe';
    private $version;
    private $updateURL;
    private $licenseKey;


    function __construct($version, $updateURL, $licenseKey = '', $licenseRequired = true) {
        $this->licenseKey = $licenseKey;
        $this->updateURL = $updateURL;
        $this->version = $version;

        add_filter('pre_set_site_transient_update_plugins', array($this, 'checkUpdate'));
        add_filter('plugins_api', array($this, 'checkInfo'), 10, 3);
        /**
         * This action gets fired when when we call update.php?action=update-easyrecipe (which is what the plugin site check version api should return as the "update" link)
         * Need to concatenate the strings below because the phing token replacement croaks on 4 underscores
         */
        add_action('update-custom_' . 'easyrecipe-update', array($this, 'forceUpdate'));

        /**
         * If the plugin requires a license to update automatically, add a hook to check for the existence of a license key on the plugin download page
         * This is so we can display a reasonable error message when the license key is missing from the download URL
         */
        if ($licenseRequired) {
            add_filter('upgrader_pre_download', array($this, 'checkLicense'), 1, 2);
        }

    }

    /**
     * Check for the existence of a license key. (not its validity)
     * A common problem is that users don't enter their license key and get a cryptic error on the download failure
     *
     * @param $value
     * @param $package
     *
     * @return WP_Error
     */
    public function checkLicense($value, $package) {
        /**
         * We are only interested in our specific plugin when there's no license key
         */

        if (strpos($package, 'easyrecipe.zip') !== false && empty($this->licenseKey)) {
            $value = new WP_Error('noeasyrecipelicense', __('You must enter your license key to update'));
        }
        return $value;
    }

    /**
     * We need to force WP to do an update check because WP won't recheck for updates unless the _site_transient_update_plugins transient has expired
     * i.e. if WP checked for updates within the past 12 hrs, it (probably!) won't recheck and therefore may not have the very latest data for our own plugin
     * Deleting the update_plugins transient will force WP to re-check for updates (so the transient can be re-inserted).
     * The re-insert is done in wp_update_plugins() which is called via the "load-update" (load-$page) action in admin.php
     * wp_update_plugins() is badly named - it really should be "wp_update_plugin_versions()" - it (possibly) updates the versions in the transient - it doesn't "update plugins"
     * What a horribly convoluted way of doing a simple task!
     *
     * TODO - will force a recheck of ALL plugins. We should try to force the re-check of only the plugin that instantiates this class
     */
    public function forceUpdate() {
        delete_site_transient('update_plugins');

        $nonce = wp_create_nonce("upgrade-plugin_$this->slug/$this->slug.php");

        $url = get_bloginfo('wpurl') . "/wp-admin/update.php?action=upgrade-plugin&plugin=$this->slug/$this->slug.php&_wpnonce=$nonce";

        header("Location: $url");
        exit;
    }

    /**
     * Gets data from the update server
     *
     * @param string $action
     *
     * @return bool|object
     */
    private function getData($action) {
        $args = array();
        $args['k'] = $this->licenseKey;
        $args['a'] = $action;
        $args['v'] = $this->version;
        $args['s'] = $this->slug;
        $args['u'] = get_bloginfo("wpurl");
        $args['p'] = 0;

        $request = wp_remote_post($this->updateURL, array('body' => $args));
        if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
            return false;
        }
        $response = unserialize(wp_remote_retrieve_body($request));
        return is_object($response) ? $response : false;
    }

    /**
     * This is called when the "update_plugins" site transient is updated
     * If $transient->checked isn't set, then the transient isn't properly set up yet so do nothing
     * Otherwise find the latest data for our own plugin and replace (or insert) it in the transient
     *
     * @param object $transient
     *
     * @return object
     */
    public function checkUpdate($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        /**
         * Get the data - returns false if the installed version is not less than the current version
         */
        $response = $this->getData('vcheck');

        if ($response !== false) {
            $transient->response["$this->slug/$this->slug.php"] = $response;
        }
        return $transient;
    }

    /**
     * Get the latest plugin info
     *
     * @param boolean $value
     * @param array   $action
     * @param object  $args
     *
     * @return bool|object
     */
    public function checkInfo(/** @noinspection PhpUnusedParameterInspection */
        $value, $action, $args) {
        if (!empty($args->slug) && $args->slug == $this->slug) {
            $information = $this->getData('info');
            return $information;
        }
        return $value;
    }

}

