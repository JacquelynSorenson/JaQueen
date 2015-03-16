<?php

/**
 * Class EasyRecipeUpdate
 *
 * This is run after an update to check if there is any specific processing required for the newly installed update
 *
 * May also be called from other locations (e.g. from Settings) if some update condition is present.
 * It will also be called during CRON runs
 */
class EasyRecipeUpdate {

    static private $taxonomies;

    public static function check(EasyRecipeSettings $settings) {

//        $log = EasyRecipeLogger::getLog('update');

        /**
         * If the settings haven't been updated to show that we've created taxonomies, schedule the taxonomy creation.
         * Do this in the background because it may take quite some time especially on underpowered shared servers (10+ secs on our dedicated test server for our moderately sized test blog)
         */
        if (!$settings->taxonomiesCreated) {
            $scheduler = new EasyRecipeScheduler(EasyRecipeTaxonomies::UPDATE_TAXONOMIES);
            /**
             * If we are running in CRON, set up the hook to catch the update event when it fires
             * Otherwise, get the scheduler to schedule the update via cron right now
             * Both of these situations might occur multiple times before the taxonomy creation is complete. The scheduler will handle that
             */
            if (defined('DOING_CRON')) {
                /**
                 * If the job isn't already running, set it so but allow it to timeout after 10 mins so that if it fails, it won't be flagged as running forever
                 * Then setup the hook to actually do the work
                 */
                if (!$scheduler->isRunning()) {
                    self::$taxonomies = new EasyRecipeTaxonomies($scheduler);
                    add_action(EasyRecipeTaxonomies::UPDATE_TAXONOMIES, array(self::$taxonomies, 'updateAll'));
                }
            } else {
                $scheduler->runNow();
            }
        }

    }
}

