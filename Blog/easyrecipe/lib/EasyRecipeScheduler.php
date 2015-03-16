<?php

/**
 * Class EasyRecipeScheduler
 *
 * WP Cron scheduler helper
 */
class EasyRecipeScheduler {

    private $cronHook;
    private $timestamp;
    private $args;

    /** @var  EasyLoggerLog */
    private $log;

    function __construct($cronHook) {
        $this->cronHook = $cronHook;
        $this->log = EasyRecipeLogger::getLog('scheduler');
        $this->log->info("create $cronHook");
    }

    /**
     * Schedule a run NOW unless it's already scheduled or running
     *
     * @param bool  $forceRun If true, schedule run regardless of current status
     * @param array $args
     */
    function runNow($forceRun = false, $args = array()) {
        $this->log->info("$this->cronHook: run now");
        $this->runAt(time(), $forceRun, $args);
    }

    /**
     * Schedule a run at time "$time" unless a it's already scheduled or running
     *
     * @param integer $time     Unix timestamp
     * @param bool    $forceRun If true, schedule a run regardless of whether it's already running and/or scheduled
     * @param array   $args
     *
     * @return bool
     */
    function runAt($time, $forceRun = false, $args = array()) {
        if ($forceRun || !$this->isScheduled()) {
            if ($forceRun || !$this->isRunning()) {
                $this->log->info("$this->cronHook: schedule at $time");
                $this->timestamp = $time;
                $this->args = $args;
                wp_schedule_single_event($time, $this->cronHook, $args);
                spawn_cron();
                return true;
            } else {
                $this->log->info("$this->cronHook: schedule at $time but is already running");
            }
        } else {
            $this->log->info("$this->cronHook: schedule at $time but is already scheduled");
        }
        return false;
    }

    /**
     * Returns TRUE if a run is already scheduled
     *
     * @return bool
     */
    function isRunning() {
        return get_transient($this->cronHook) == 'run';
    }

    /**
     * Returns the timestamp of the next scheduled run or FALSE if there is no scheduled run.
     *
     * @return bool|int
     */
    function isScheduled() {
        return wp_next_scheduled($this->cronHook);
    }


    /**
     * Sets the status of this CRON as "running" with an optional timeout
     *
     * @param int $timeout
     */
    function setRunning($timeout = 0) {
        $this->log->info("$this->cronHook: set running");
        set_transient($this->cronHook, 'run', $timeout);
    }

    /**
     * Mark a run as terminated, and/or cancel a run by removing the 'run' status (terminate) and removing the event (cancel) in case it never actually ran
     */
    function terminate() {
        $this->log->info("$this->cronHook: terminate");
        delete_transient($this->cronHook);
        wp_unschedule_event($this->timestamp, $this->cronHook, $this->args);
    }
}

