<?php

/**
 * Class EasyRecipeTaxonomies
 *
 * Taxonomy processing for a recipe's course and cuisine
 */
class EasyRecipeTaxonomies {

    const UPDATE_TAXONOMIES = 'easyrecipe_updatetaxonomies';

    /** @var  EasyRecipeScheduler */
    private $scheduler;

    private $countTerms = array();

    function __construct(EasyRecipeScheduler $scheduler = null) {
        $this->scheduler = $scheduler;
    }

    /**
     * @param WP_Post $post
     * @param bool    $updateAll TRUE if the update is part of updateAll() - if so, defer the term count
     */
    public function update($post, $updateAll = false) {
        /** @var wpdb $wpdb */
        global $wpdb;

        $content = $post->post_content;

        /**
         * Do a quick check that there IS a recipe before we go to the expense of instantiating a DOMDocument
         */
        if (strpos($content, 'endeasyrecipe') === false) {
            return;
        }

        $dom = new EasyRecipeDocument($content);
        if (!$dom->isEasyRecipe) {
            return;
        }

        $cuisineTerms = array();
        $courseTerms = array();
        $postID = $post->ID;

        if (!$updateAll) {
            $this->countTerms['cuisine'] = array();
            $this->countTerms['course'] = array();
        }
        /**
         * Read all the current cuisine/course terms for this post
         * We need this to decide if we need to insert a new relationship and whether we need to remove any old relationships no longer used
         */
        $q = "SELECT $wpdb->term_taxonomy.term_taxonomy_id, taxonomy FROM $wpdb->terms JOIN $wpdb->term_taxonomy ON $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id JOIN ";
        $q .= "$wpdb->term_relationships on $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id ";
        $q .= "WHERE object_id = $postID AND taxonomy in ('course','cuisine')";
        $existing = $wpdb->get_results($q);

        $existingUnused = array();
        foreach ($existing as $exist) {
            $existingUnused[$exist->term_taxonomy_id] = true;
            $this->countTerms[$exist->taxonomy][] = $exist->term_taxonomy_id;
        }

        /**
         * Get the course(s) and cuisine(s) from the recipe
         */
        $cuisines = $dom->getElementsByClassName('cuisine');
        $courses = $dom->getElementsByClassName('type');

        if (count($cuisines) > 0) {
            /** @var DOMElement $cuisine */
            foreach ($cuisines as $cuisine) {
                $term = $cuisine->nodeValue;

                /**
                 * Get the term info for the recipe's cuisine(s) - insert one of it doesn't yet exist
                 */
                if (empty($cuisineTerms[$term])) {
                    $termInfo = term_exists($term, 'cuisine');
                    if (!$termInfo) {
                        $termInfo = wp_insert_term($term, 'cuisine');
                    }
                    $cuisineTerms[$term] = $termInfo['term_taxonomy_id'];
                }
                /**
                 * Check to see if we already have the correct relationship for this cuisine term for this post
                 */
                $ttID = $cuisineTerms[$term];
                if (!empty($existingUnused[$ttID])) {
                    $existingUnused[$ttID] = false;
                    continue;
                }
                /**
                 * If we have multiple recipes in the post, it's possible we have already seen this cuisine and if so, don't insert it a second time
                 * Otherwise, the relationship didn't exist so insert it
                 */
                if (!in_array($ttID, $this->countTerms['cuisine'])) {
                    $this->countTerms['cuisine'][] = $ttID;
                    $wpdb->insert($wpdb->term_relationships, array('object_id' => $postID, 'term_taxonomy_id' => $ttID));
                }
            }
        }

        if (count($courses) > 0) {
            /** @var DOMElement $course */
            foreach ($courses as $course) {
                $term = $course->nodeValue;

                /**
                 * Get the term info for the recipe's course(s) - insert one of it doesn't yet exist
                 */
                if (empty($courseTerms[$term])) {
                    $termInfo = term_exists($term, 'course');
                    if (!$termInfo) {
                        $termInfo = wp_insert_term($term, 'course');
                    }
                    $courseTerms[$term] = $termInfo['term_taxonomy_id'];
                }
                /**
                 * Check to see if we already have the correct relationship for this course term for this post
                 */
                $ttID = $courseTerms[$term];
                if (!empty($existingUnused[$ttID])) {
                    $existingUnused[$ttID] = false;
                    continue;
                }
                /**
                 * If we have multiple recipes in the post, it's possible we have already seen this course and if so, don't insert it a second time
                 * Otherwise, the relationship didn't exist so insert it
                 */
                if (!in_array($ttID, $this->countTerms['course'])) {
                    $this->countTerms['course'][] = $ttID;
                    $wpdb->insert($wpdb->term_relationships, array('object_id' => $postID, 'term_taxonomy_id' => $ttID));
                }
            }
        }
        /**
         * Remove any existing term relationships that are now no longer used and adjust the list of terms that need to be updated
         */
        foreach ($existingUnused as $ttID => $unused) {
            if ($unused) {
                $wpdb->delete($wpdb->term_relationships, array('object_id' => $postID, 'term_taxonomy_id' => $ttID));
            } else {
                if (in_array($ttID, $this->countTerms['course'])) {

                }
            }
        }

        /**
         * Update any term counts that we may have adjusted unless this is part of an updateAll() run
         */
        if (!$updateAll) {
            if (count($this->countTerms['cuisine']) > 0) {
                wp_update_term_count_now(array_unique(array_keys($this->countTerms['cuisine'])), 'cuisine');
            }

            if (count($this->countTerms['course']) > 0) {
                wp_update_term_count_now(array_unique(array_keys($this->countTerms['course'])), 'course');
            }
        }

    }

    /**
     * Update all taxonomies.
     * This should only ever be called from a cron job scheduled by EasyRecipeScheduler because it can potentially take quite a while
     */
    function updateAll() {
        /** @var wpdb $wpdb */
        global $wpdb;
        /**
         * If we are already running, don't do it again
         */
        if ($this->scheduler->isRunning()) {
            return;
        }
        /**
         * Set as running
         * Set a "timeout" of 10 minutes. This will prevent it being re-run for 10 minutes if the current run terminates abnormally for any reason
         */
        $this->scheduler->setRunning(10 * 60);

        $q = "SELECT ID FROM $wpdb->posts WHERE post_type NOT IN ('attachment','index','nav_menu_item')";
        $postIDs = $wpdb->get_col($q);

        $this->countTerms['cuisine'] = array();
        $this->countTerms['course'] = array();

        foreach ($postIDs as $postID) {
            $post = WP_Post::get_instance($postID);
            $this->update($post, false);
        }

        /**
         * Update any term counts that we may have adjusted
         */
        if (count($this->countTerms['cuisine']) > 0) {
            wp_update_term_count_now(array_unique(array_keys($this->countTerms['cuisine'])), 'cuisine');
        }

        if (count($this->countTerms['course']) > 0) {
            wp_update_term_count_now(array_unique(array_keys($this->countTerms['course'])), 'course');
        }

        /**
         * Mark the taxonomies as having been created
         */
        $settings = EasyRecipeSettings::getInstance();
        $settings->taxonomiesCreated = true;
        $settings->update();
        /**
         * Mark this job as complete
         */
        $this->scheduler->terminate();

    }


}

