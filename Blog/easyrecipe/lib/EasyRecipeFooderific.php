<?php
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

class EasyRecipeFooderific {

    /**
     * The maximum time a scan can run for in seconds
     */
    const SCAN_TIMEOUT = 300;
    /**
     * The number of posts to batch up for each send
     */
    const BATCHSIZE = 20;

    const FOODERIFIC_SCAN = 'fooderific_scan';
    const FOODERIFIC_URL = 'http://www.fooderific.com/plugin/ping.php';


    /**
     * Which recipe plugins are active?
     *
     * @var bool
     */
    private $haveZiplist = false;
    private $haveGetMeCooking = false;
    private $haveRecipress = false;
    private $haveRecipeSEO = false;
    private $haveEasyRecipe = false;
    private $haveKitchenbug = false;
    private $haveRecipeCard = false;

    private $batchSize;
    private $results;

    private $nPostsSent;

    private $delay = 0;


    /**
     * A post has changed - schedule a FOODERIFIC scan.
     * Let the scan decide if we actually need to notify fooderific
     *
     * @param $postID
     * @param object $post
     */
    function postChanged(/** @noinspection PhpUnusedParameterInspection */
        $postID, $post = null) {


        if ($post && $post->post_status == 'publish') {

            wp_schedule_single_event(time(), self::FOODERIFIC_SCAN, array($post->ID));
//            wp_schedule_single_event(time(), self::FOODERIFIC_SCAN, $post->ID);
            spawn_cron();
        }
    }

    /**
     * A post's status has changed.  We're only interested if it's transition from or to published
     * @param $newStatus
     * @param $oldStatus
     * @param $post
     */
    function postStatusChanged($newStatus, $oldStatus, $post) {

        if ($oldStatus == 'publish' || $newStatus == 'publish') {
            if ($oldStatus != $newStatus) {

                wp_schedule_single_event(time(), self::FOODERIFIC_SCAN, array($post->ID));
                spawn_cron();
            }
        }
    }

    /**
     * Find active recipe plugins
     */
    private function getRecipePlugins() {
        /**
         * Figure out what plugins we should look for
         */
        $this->haveZiplist = $this->haveGetMeCooking = $this->haveRecipress = $this->haveRecipeSEO = $this->haveEasyRecipe = $this->haveKitchenbug = $this->haveRecipeCard = false;
        $plugins = get_option('active_plugins');
        foreach ($plugins as $plugin) {
            if (stripos($plugin, 'easyrecipe') !== false) {
                $this->haveEasyRecipe = true;
                continue;
            }
            if (stripos($plugin, 'ziplist') !== false) {
                $this->haveZiplist = true;
                continue;
            }
            if (stripos($plugin, 'recipeseo') !== false) {
                $this->haveRecipeSEO = true;
                continue;
            }
            if (stripos($plugin, 'getmecooking') !== false) {
                $this->haveGetMeCooking = true;
                continue;
            }
            if (stripos($plugin, 'recipress') !== false) {
                $this->haveRecipress = true;
                continue;
            }

            if (stripos($plugin, 'kitchenbug') !== false) {
                $this->haveKitchenbug = true;
                continue;
            }

            if (stripos($plugin, 'recipe-card') !== false) {
                $this->haveRecipeCard = true;
                continue;
            }

        }
    }

    /**
     * Process a single post.  Called from either scanRun() or directly via cron for a single post
     * Store the results and send in a batch when we have enough so we don't hit fooderific so hard
     *
     * @param $post object  Either the post object or the post ID
     */
    private function processPost($post) {
        global $wp_version;



        /**
         * Not interested if the post is passworded
         */
        if (!empty($post->post_password)) {
            return;
        }

        $haveRecipe = false;
        $source = '';
        /**
         * Scan post for recipe content
         * First check for known plugins
         */

        if ($this->haveZiplist) {
            $haveRecipe = strpos($post->post_content, '[amd-zlrecipe') !== false;
            if ($haveRecipe) {
                $source = 'ZIPLIST';
            }
        }

        if (!$haveRecipe && $this->haveRecipeSEO) {
            $haveRecipe = strpos($post->post_content, '[amd-recipeseo') !== false;
            if ($haveRecipe) {
                $source = 'RECIPESEO';
            }
        }

        if (!$haveRecipe && $this->haveRecipeSEO) {
            $haveRecipe = strpos($post->post_content, '[gmc_recipe') !== false;
            if ($haveRecipe) {
                $source = 'GETMECOOKING';
            }

        }

        if (!$haveRecipe && $this->haveEasyRecipe) {
            $haveRecipe = strpos($post->post_content, 'class="easyrecipe') !== false;
            if ($haveRecipe) {
                $source = 'EASYRECIPE';
            }
        }

        if (!$haveRecipe && $this->haveKitchenbug) {
            $haveRecipe = strpos($post->post_content, '[kitchenbug') !== false;
            if ($haveRecipe) {
                $source = 'KITCHENBUG';
            }
        }

        if (!$haveRecipe && $this->haveRecipeCard) {
            $haveRecipe = stripos($post->post_content, '[yumprint-recipe') !== false;
            if ($haveRecipe) {
                $source = 'RECIPECARD';
            }
        }

        if (!$haveRecipe && $this->haveRecipress) {
            $meta = get_post_meta($post->ID, 'hasRecipe', true);
            $haveRecipe = $meta == 'Yes';
            if ($haveRecipe) {
                $source = 'RECIPRESS';
            }
        }

        /**
         * If there's no recipe from a known plugin, check for posts with hard coded markup
         */
        if (!$haveRecipe) {
            $haveRecipe = stripos($post->post_content, 'hrecipe') !== false;
            if ($haveRecipe) {
                $source = 'HRECIPE';
            }

        }
        if (!$haveRecipe) {
            $haveRecipe = stripos($post->post_content, 'schema.org/recipe') !== false;
            if ($haveRecipe) {
                $source = 'SCHEMA';
            }

        }

        if (!$haveRecipe) {
            $haveRecipe = stripos($post->post_content, 'vocabulary.org/Recipe') !== false;
            if ($haveRecipe) {
                $source = 'VOCABULARY';
            }
        }

        /**
         * It seems there's no recipe in this post - ignore it
         */
        if (!$haveRecipe) {
            return;
        }

        /**
         * Send some basic details to fooderific
         */
        $result = new stdClass();
        $result->wpurl = get_bloginfo("wpurl");
        $result->postID = $post->ID;
        /**
         * If the batch size is 1 then this is a post insert/edit else it's a scan
         */
        $result->status = $this->batchSize == 1 ? $post->post_status : 'scan';

        /**
         * Get the details that we can't easily get from a crawl of the page (excerpt, tags and categories)
         * Also try to get an image in case it isn't explicitly specified in the markup or the explicitly specified one is too small
         * We also might need to use the original image if pagespeed is used on the site
         */
        $result->source = $source;
        $result->tags = array();
        $postTags = get_the_terms($post->ID, 'post_tag');
        if ($postTags) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            foreach ($postTags as $key => $postTag) {
                $result->tags[] = $postTag->name;
            }
        }
        $result->categories = array();
        $postCategories = get_the_terms($post->ID, 'category');
        if ($postCategories) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            foreach ($postCategories as $key => $postCategory) {
                $result->categories[] = $postCategory->name;
            }
        }
        if ($post->post_excerpt != '') {
            $result->excerpt = $post->post_excerpt;
        } else {
            $text = strip_shortcodes($post->post_content);
            $text = strip_tags($text);
            $result->excerpt = wp_trim_words($text, 55, '');
        }

        /**
         * Try to find an alternate image in case the recipe image is non-existent or too small
         *
         * If the theme is Thesis, try "thesis_post_image" meta
         * If there's no thesis image, try the post thumbnail
         */
        $image = '';
        if ($wp_version < '3.4') {
            /** @noinspection PhpDeprecationInspection */
            $themeName = get_current_theme();
        } else {
            /** @var $theme WP_Theme */
            $theme = wp_get_theme();
            $themeName = $theme->get_stylesheet();
        }

        if (stripos($themeName, 'thesis') !== false) {
            $image = get_post_meta($post->ID, 'thesis_post_image', true);
        }

        if ($image == '') {
            $image = get_the_post_thumbnail($post->ID, 'full');
        }
        $result->image = $image;
        $result->published = $post->post_date_gmt;
        $result->link = get_permalink($post->ID);


        $this->results[] = $result;

        /**
         * If we have enough for a batch, send it
         */
        if (count($this->results) == $this->batchSize) {
            $this->nPostsSent += count($this->results);
            $args = array('body' => array('data' => serialize($this->results)));
            wp_remote_post(self::FOODERIFIC_URL, $args);
            $this->results = array();
            sleep($this->delay);
        }
    }

    /**
     * Scan all posts for recipes if $postID == 0, or a single post if $postID <> 0 and send basic details to fooderific
     * If it's a sitewide scan, data is batched up to minimize network traffic
     *
     * @param int $postID Scan all posts if this is zero, else a single post if not
     */
    function scanRun($postID = 0) {
        /* @var $wpdb wpdb */
        global $wpdb;


        $settings = EasyRecipeSettings::getInstance();
        $this->delay = $settings->scanDelay;
        $this->getRecipePlugins();

        /**
         * Read each published post (or the specific post if we're processing a specific post's update) and if it contains a recipe, then shoot the details off to fooderific.com
         * We're not interested in attachments or revisions. Also probably not interested in other types, but we don't know about custom post types so process anything else
         */
        if ($postID != 0) {
            $q = "SELECT * FROM {$wpdb->prefix}posts WHERE ID = '$postID' AND post_type <> 'attachment' AND post_type <> 'revision'";
            $this->batchSize = 1;
        } else {
            $q = "SELECT * FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type <> 'attachment' AND post_type <> 'revision' ORDER BY ID DESC";
            $this->batchSize = self::BATCHSIZE;
            $settings->lastScanStarted = time();
            $settings->update();
        }

        $posts = $wpdb->get_results($q);

        /**
         * If this is a scan, notify fooderific that we're starting
         */
        if ($postID == 0) {
            $data = new stdClass();
            $data->action = 'start';
            $data->wpurl = get_bloginfo("wpurl");
            $data->count = count($posts);
            $args = array('body' => array('data' => serialize($data)));
            wp_remote_post(self::FOODERIFIC_URL, $args);
        }

        $this->nPostsSent = 0;
        $this->results = array();

        /**
         * Flag that we're running in scan so we don't schedule another scan on top of this one
         * Don't hold for longer than SCAN_TIMEOUT seconds so if the process crashes or has some kind of problem, it's not going to stop another scan later
         */
        set_transient(self::FOODERIFIC_SCAN, 'run', self::SCAN_TIMEOUT);

        /**
         * Also reset the run time limit to SCAN_TIMEOUT seconds so unintended loops or horribly slow processing doesn't tie this up forever
         */
        @set_time_limit(self::SCAN_TIMEOUT);

        foreach ($posts as $post) {
            $this->processPost($post);
        }
        if (count($this->results) > 0) {
            $this->nPostsSent += count($this->results);
            $args = array('body' => array('data' => serialize($this->results)));
            wp_remote_post(self::FOODERIFIC_URL, $args);
            $this->results = array();
        }

        /**
         * If this was a scan, notify fooderific that we're done
         */
        if ($postID == 0) {
            $data = new stdClass();
            $data->action = 'stop';
            $data->wpurl = get_bloginfo("wpurl");
            $data->count = $this->nPostsSent;
            $args = array('body' => array('data' => serialize($data)));
            wp_remote_post(self::FOODERIFIC_URL, $args);
        }

        delete_transient(self::FOODERIFIC_SCAN);

    }

    /**
     * Schedule a site scan immediately unless it's already scheduled or already running
     *
     * @return bool TRUE if the scan was scheduled, false if it wasn't (because it's already scheduled or running)
     */
    function scanSchedule() {

        if (!wp_next_scheduled(self::FOODERIFIC_SCAN)) {
            if (get_transient(self::FOODERIFIC_SCAN) != 'run') {

                /**
                 * Tell fooderific a scan was scheduled
                 * Really only for debugging so we can tell if scans aren't being run
                 */
                $data = new stdClass();
                $data->action = 'scheduled';
                $data->wpurl = get_bloginfo("wpurl");
                $data->count = 0;
                $args = array('body' => array('data' => serialize($data)));
                wp_remote_post(self::FOODERIFIC_URL, $args);

                wp_schedule_single_event(time(), self::FOODERIFIC_SCAN, array(0));
                spawn_cron();

                return true;
            } else {
            }
        } else {
        }
        return false;
    }


}

