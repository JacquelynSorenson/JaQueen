<?php

/*
 Copyright (c) 2010-2013 Box Hill LLC

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


/**
 * Class EasyRecipeSettings
 *
 * On the first run, we create an instance and use the defaults.
 * The Plus version reads and merges the Free version's settings (if they exist) and merges them into the Plus defaults
 *
 * The entire class is saved to options, and after the first run, the class is directly instantiated by unserializing the options
 *
 * TODO - remove "@property" when Gravity Forms implemented
 *
 * @property mixed gpUseGravity
 */
class EasyRecipeSettings {

    private static $defaultSettings
        = array(
            'licenseKey'            => '',
            'style'                 => 'style001',
            'printStyle'            => 'style001',
            'customCSS'             => '',
            'customPrintCSS'        => '',
            'extraCSS'              => '',
            'extraPrintCSS'         => '',
            'extraPrintHeader'      => '',
            'useFeaturedImage'      => false,
            'displayPrint'          => true,
            'allowLink'             => false,
            'convertFractions'      => true,
            'removeMicroformat'     => false,
            'enableSwoop'           => false,
            'swoopSiteID'           => '',
            'saveButton'            => 'BigOven',
            'ziplistPartnerKey'     => '',
            'author'                => '',
            'cuisines'              => 'African|American|French|Greek|Indian|Italian|Kosher|Mexican|Middle Eastern|Spanish',
            'recipeTypes'           => 'Appetizer|Breakfast|Brunch|Dessert|Drinks|Entree|Main',
            'ratings'               => 'EasyRecipe',
            'filterExcerpts'        => true,
            'filterFeeds'           => true,
            'lblIngredients'        => 'Ingredients',
            'lblInstructions'       => 'Instructions',
            'lblNotes'              => 'Notes',
            'lblNutrition'          => 'Nutrition Information',
            'lblAuthor'             => 'Author',
            'lblCuisine'            => 'Cuisine',
            'lblRecipeType'         => 'Recipe type',
            'lblPrepTime'           => 'Prep time',
            'lblCookTime'           => 'Cook time',
            'lblTotalTime'          => 'Total time',
            'lblServes'             => 'Serves',
            'lblServeSize'          => 'Serving size',
            'lblCalories'           => 'Calories',
            'lblSugar'              => 'Sugar',
            'lblSodium'             => 'Sodium',
            'lblFat'                => 'Fat',
            'lblSatFat'             => 'Saturated fat',
            'lblUnsatFat'           => 'Unsaturated fat',
            'lblTransFat'           => 'Trans fat',
            'lblCarbs'              => 'Carbohydrates',
            'lblFiber'              => 'Fiber',
            'lblProtein'            => 'Protein',
            'lblCholesterol'        => 'Cholesterol',
            'lblRateRecipe'         => 'Rate this recipe',
            'lblHour'               => 'hour',
            'lblHours'              => 'hours',
            'lblMinute'             => 'min',
            'lblMinutes'            => 'mins',
            'lblPrint'              => 'Print',
            'lblSave'               => 'Save',
            'gpUserID'              => 0, // #ifdef GRAVITY
            'gpGravityFormID'       => 0, // #endif
            'gpDetailsPage'         => 0,
            'gpEntryPage'           => 0,
            'gpThanksPage'          => 0,
            'gpHideFooter'          => true,
            'lblGPName'             => 'Name:',
            'lblGPEmail'            => 'Email:',
            'lblGPWebsite'          => 'Website URL:',
            'lblGPContinue'         => 'Continue',
            'lblGPPostTitle'        => 'Post title:',
            'lblGPHint'             => "Hint: Click on the chef's hat icon to enter the recipe part of your post:",
            'lblGPMessage'          => 'Leave me a private message (not for publication):',
            'lblGPSubmitPost'       => 'Submit Post',
            'erSubscribe'           => false,
            'erEmailAddress'        => '',
            'erFirstName'           => '',
            'customTemplates'       => '',
            'forcejQuery'           => false,
            'noHTMLWarn'            => false,
            'genesisGrid'           => false,
            'displayZiplist'        => false,
            'displayRecipeCard'     => false,
            'displayRecipage'       => false,
            'displayGMC'            => false,
            'displayUltimateRecipe' => false,
            'enableFooderific'      => '',
            'fooderificAPIKey'      => '',
            'lastScanStarted'       => 0,
            'lastScanFinished'      => 0,
            'scanDelay'             => 3,
            'settingsVersion'       => null
        );


    public $licenseKey;
    public $style;
    public $printStyle;
    public $customCSS;
    public $customPrintCSS;
    public $extraCSS;
    public $extraPrintCSS;
    public $extraPrintHeader;
    public $displayPrint;
    public $allowLink;

    public $useFeaturedImage;

    public $convertFractions;
    public $removeMicroformat;
    public $enableSwoop;
    public $swoopSiteID;
    public $saveButton;
    public $ziplistPartnerKey;
    public $author;
    public $cuisines;
    public $recipeTypes;
    public $ratings;
    public $filterExcerpts;
    public $lblIngredients;
    public $lblInstructions;
    public $lblNotes;
    public $lblNutrition;
    public $lblAuthor;
    public $lblCuisine;
    public $lblRecipeType;
    public $lblPrepTime;
    public $lblCookTime;
    public $lblTotalTime;
    public $lblServes;
    public $lblServeSize;
    public $lblCalories;
    public $lblSugar;
    public $lblSodium;
    public $lblFat;
    public $lblSatFat;
    public $lblUnsatFat;
    public $lblTransFat;
    public $lblCarbs;
    public $lblFiber;
    public $lblProtein;
    public $lblCholesterol;
    public $lblRateRecipe;
    public $lblPrint;
    public $lblSave;

    public $lblHour;
    public $lblHours;
    public $lblMinute;
    public $lblMinutes;

    public $gpUserID;
    public $gpCopyDetails;

    public $gpDetailsPage;
    public $gpEntryPage;
    public $gpThanksPage;
    public $gpHideFooter;

    public $lblGPName;
    public $lblGPEmail;
    public $lblGPWebsite;
    public $lblGPContinue;
    public $lblGPPostTitle;
    public $lblGPHint;
    public $lblGPMessage;
    public $lblGPSubmitPost;

    public $erSubscribe;
    public $erEmailAddress;
    public $erFirstName;
    public $customTemplates;
    public $forcejQuery;
    public $noHTMLWarn;
    public $genesisGrid;
    public $enableFooderific;
    public $lastScanStarted;
    public $lastScanFinished;
    public $scanDelay;
    public $fooderificAPIKey;

    public $displayZiplist;
    public $displayRecipeCard;
    public $displayRecipage;
    public $displayGMC;
    public $displayUltimateRecipe;

    /**
     * @var string The plugin version that these settings were saved with.
     * This should be the same as the plugin version except on the first run after an update
     */
    public $settingsVersion;

    /**
     * @var bool Will be set to true by the Taxonomy creation process
     */
    public $taxonomiesCreated = false;

    /**
     * @var EasyRecipeSettings
     */
    private static $instance;


    /**
     * @static
     * @return EasyRecipeSettings
     */
    static function getInstance() {
        $freeSettings = null;
        $updateOptions = false;

        /**
         * If we haven't already instantiated settings, try to do it from the options
         */
        if (!self::$instance) {
            self::$instance = get_option('EasyRecipe', false);

            if (!self::$instance) {
                self::$instance = new EasyRecipeSettings();

                /**
                 * If we're updating from a very early version, copy the old settings which are still relevant
                 * Any not set in the defaults are deprecated and we can drop them
                 */
                $v31Settings = get_option('ERSettings');

                if (!empty($v31Settings)) {
                    foreach ($v31Settings as $setting => $value) {
                        if (isset(self::$defaultSettings[$setting])) {
                            self::$instance->$setting = $value;
                        }
                    }
                    $updateOptions = true;
                }
            }

            /**
             * Fixup possible legacy problems where the options weren't stored as the correct class
             */
            if (!(self::$instance instanceof EasyRecipeSettings)) {
                self::$instance = new EasyRecipeSettings(self::$instance);
                $updateOptions = true;
            }

            /**
             * If this is the first run of the plugin after an update, see if we need to do any processing specific to this update.
             * Also do the update check if the taxonomies haven't been created yet
             *
             * TODO - determine if this is a new install -  won't need to check if so?
             */
            $updateCheck = version_compare(self::$instance->settingsVersion, EasyRecipe::$pluginVersion) == -1 || !self::$instance->taxonomiesCreated;
            if ($updateCheck) {
                EasyRecipeUpdate::check(self::$instance);
                /**
                 * Save the new settings version (will be the same as the installed pluginVersion)
                 */
                self::$instance->settingsVersion = EasyRecipe::$pluginVersion;
                $updateOptions = true;
            }

            /**
             * Set any defaults which haven't been set in the current version (i.e. new settings just introduced)
             * TODO - remove any options no longer needed?
             */
            foreach (self::$defaultSettings as $setting => $default) {
                if (!isset(self::$instance->$setting)) {
                    self::$instance->$setting = $default;
                    $updateOptions = true;
                }
            }

            /**
             * Update the settings if we changed them during construction
             */
            if ($updateOptions) {
                update_option('EasyRecipe', self::$instance);
            }
        }
        return self::$instance;
    }

    /**
     * Constructor is only ever called from getInstance
     *
     * @param null $settings Possible settings as a stdClass retrieved from options. Seems some version in the past wrote settings as a stdClass
     */
    private function __construct($settings = null) {
        $defaultSettings = empty($settings) ? self::$defaultSettings : $settings;
        foreach ($defaultSettings as $setting => $default) {
            $this->{$setting} = $default;
        }
    }

    public function showPage() {
        /* @var $wp_rewrite WP_Rewrite */
        global $wp_rewrite;

        /** @var $wpdb wpdb */
        global $wpdb;

        global $wp_version;

        if (isset($_POST['action']) && $_POST['action'] == 'save') {
            $this->save($_POST["EasyRecipe"]);
        }

        $data = new stdClass();
        foreach (self::$defaultSettings as $setting => $default) {
            $data->{$setting} = isset($this->{$setting}) ? $this->{$setting} : $default;
        }

        $data->settingsname = 'EasyRecipe';
        $wpurl = get_bloginfo("wpurl");
        $data->fdsite = preg_replace('%^(?:http://)(.*)$%i', '$1', $wpurl);
        $isWP39 = version_compare($wp_version, '3.9.dev', '>') > 0 ? 'true' : 'false';
        $editURL = "$wpurl/wp-admin/edit.php";
        $data->pluginversion = EasyRecipe::$pluginVersion;
        $license = $this->licenseKey;

        /**
         * Figure out what we need to display on the Fooderific tab
         *
         * If we had MBRB enabled but this is the first run, show the welcome (firstRun = true) and hide the "retrieving" splash
         * Otherwise, show the "retrieving" splash
         */
        $data->fdFirstRun = false;
        $data->fdNotEnabled = false;
        $fdAPIKey = $this->fooderificAPIKey;
        if (!$this->enableFooderific) {
            $data->fdNotEnabled = true;
            $data->retrieveclass = 'FDDisplayNone';
            $lastScan = 0;
        } else {
            if ($this->lastScanStarted == 0) {
                $data->fdFirstRun = true;
                $data->retrieveclass = 'FDDisplayNone';
                $lastScan = 0;
            } else {
                $data->retrieveclass = '';
                $tzOffet = get_option('gmt_offset');
                $lastScan = date_i18n("j M y g:ia", $this->lastScanStarted + $tzOffet * 3600);
            }
        }

        $pluginVersion = EasyRecipe::$pluginVersion;
        $data->javascript
            = <<<EOD
<script type="text/javascript">
//<![CDATA[
    window.EASYRECIPE = window.EASYRECIPE || {};
    EASYRECIPE.settingsName = 'EasyRecipe';
    EASYRECIPE.editURL = '$editURL';
    EASYRECIPE.pluginVersion = '$pluginVersion';
    EASYRECIPE.wpurl = '$wpurl';
    EASYRECIPE.slug = 'easyrecipe';
    EASYRECIPE.license = '$license';
    EASYRECIPE.lastScan = '$lastScan';
    EASYRECIPE.fdAPIKey = '$fdAPIKey';
    EASYRECIPE.isWP39 = $isWP39;
//]]>
</script>
EOD;


        /**
         * If the site isn't using permalinks then just pass the print stuff as a qurerystring param
         */
        $data->siteDiagnosticsURL = home_url();
        if (!$wp_rewrite->using_permalinks()) {
            $data->siteDiagnosticsURL .= "?";
        }


        $data->useFeaturedImageChecked = $this->useFeaturedImage ? 'checked="checked"' : '';
        $data->displayPrintChecked = $this->displayPrint ? 'checked="checked"' : '';
        $data->filterExcerptsChecked = $this->filterExcerpts ? 'checked="checked"' : '';
        $data->displayZiplistChecked = $this->displayZiplist ? 'checked="checked"' : '';
        $data->displayRecipeCardChecked = $this->displayRecipeCard ? 'checked="checked"' : '';
        $data->displayRecipageChecked = $this->displayRecipage ? 'checked="checked"' : '';
        $data->displayGMCChecked = $this->displayGMC ? 'checked="checked"' : '';
        $data->displayUltimateRecipeChecked = $this->displayUltimateRecipe ? 'checked="checked"' : '';
        $data->allowLinkChecked = $this->allowLink ? 'checked="checked"' : '';
        $data->convertFractionsChecked = $this->convertFractions ? 'checked="checked"' : '';
        $data->removeMFChecked = $this->removeMicroformat ? 'checked="checked"' : '';
        $data->fdLinkChecked = $this->enableFooderific ? 'checked="checked"' : '';
        $data->enableSwoopChecked = $this->enableSwoop ? 'checked="checked"' : '';
        $data->swoopclass = $this->enableSwoop ? '' : 'ERSNoSwoop';
        $data->forcejQueryChecked = $this->forcejQuery ? 'checked="checked"' : '';
        $data->noHTMLWarnChecked = $this->noHTMLWarn ? 'checked="checked"' : '';
        $data->genesisGridChecked = $this->genesisGrid ? 'checked="checked"' : '';

        $data->saveButtonBigOvenChecked = $data->saveButtonZiplistChecked = $data->saveButtonNoneChecked = '';

        $data->ziplistclass = "ERSDisplayNone";

        /**
         * Only show the Ziplist stuff if we are already using it and then only so it can be unselected
         */
        $data->showZiplist = false;
        switch ($data->saveButton) {
            case 'BigOven':
                $data->saveButtonBigOvenChecked = 'checked="checked"';
                break;

            case 'Ziplist':
                $data->saveButtonZiplistChecked = 'checked="checked"';
                $data->ziplistclass = '';
                $data->showZiplist = true;
                break;

            default:
                $data->saveButtonNoneChecked = 'checked="checked"';
                break;
        }


        $data->ratingEasyRecipeChecked = $data->ratingSelfRatedChecked = $data->ratingDisabledChecked = '';
        $ratingChecked = "rating" . $this->ratings . "Checked";
        $data->{$ratingChecked} = 'checked="checked"';

        $data->erSubscribeChecked = $this->erSubscribe ? 'checked="checked"' : '';
        $data->subscribeclass = $this->erSubscribe ? '' : 'ERSNoSubscribe';

        /*
         * Set up Swoop stuff
        */
        if ($data->swoopSiteID != '') {
            $data->registerswoop = 'ERSDisplayNone';
            $data->loginswoop = '';
        } else {
            $data->registerswoop = '';
            $data->loginswoop = 'ERSDisplayNone';
        }
        /*
         * Set up the register data even if we're already registered in case we remove the current ID
        */
        $swoopData = new stdClass();
        $swoopData->email = get_bloginfo("admin_email");
        $swoopData->blog_url = get_bloginfo("wpurl");
        $swoopData->blog_title = get_bloginfo("description");
        $swoopData->rss_url = get_bloginfo("rss_url");
        $swoopData->tz = get_option('timezone_string');
        /** @noinspection PhpParamsInspection */
        $data->swoopqs = http_build_query($swoopData);

        $data->easyrecipeURL = EasyRecipe::$EasyRecipeUrl;
        $data->siteurl = get_site_url();



        $data->erplus = '';
        $data->author = $this->author;
        $data->cuisines = str_replace('|', "\n", $this->cuisines);
        $data->recipeTypes = str_replace('|', "\n", $this->recipeTypes);
        $data->plus = "EasyRecipe" == "easyrecipeplus" ? "Plus" : "";
        $data->pluginName = "EasyRecipe";
        $optionsHTML = "<input type='hidden' name='option_page' value='EROptionSettings' />";
        $optionsHTML .= '<input type="hidden" name="action" value="update" />';
        $optionsHTML .= wp_nonce_field("EROptionSettings-options", '_wpnonce', true, false);
        $optionsHTML .= wp_referer_field(false);

        $styles = EasyRecipeStyles::getStyles($this->customTemplates);
//        $styles = call_user_func(array ($this->stylesClass, 'getStyles'), $this->settings['customTemplates']);

        $data->styleDirectory = $this->style;
        $styleNum = 0;
        $styleTab = 1;
        $styleItem = false;
        $data->STYLETABS = array();
        foreach ($styles as $style) {
            if ($styleNum % 3 == 0) {
                if ($styleItem !== false) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $styleItem->styleTab = $styleTab++;
                    $data->STYLETABS[] = $styleItem;
                }
                $styleItem = new stdClass();
                $styleItem->STYLES = array();
            }
            $style->selected = $data->style == $style->directory ? 'ERSStyleSelected' : '';

            $styleItem->STYLES[] = $style;
            $styleNum++;
        }
        if ($styleItem) {
            $styleItem->styleTab = $styleTab;
            $data->STYLETABS[] = $styleItem;
        }
        $styles = EasyRecipeStyles::getStyles($this->customTemplates, EasyRecipeStyles::ISPRINT);
        //$styles = call_user_func(array ($this->stylesClass, 'getStyles'), $this->settings['customTemplates'], constant("$this->stylesClass::ISPRINT"));

        $data->printStyleDirectory = $this->printStyle;
        $styleNum = 0;
        $styleTab = 1;
        $styleItem = false;
        $data->PRINTSTYLETABS = array();
        foreach ($styles as $style) {
            if ($styleNum % 3 == 0) {
                if ($styleItem !== false) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $styleItem->styleTab = $styleTab++;
                    $data->PRINTSTYLETABS[] = $styleItem;
                }
                $styleItem = new stdClass();
                $styleItem->PRINTSTYLES = array();
            }
            $style->selected = $data->printStyle == $style->directory ? 'ERSStyleSelected' : '';
            $styleItem->PRINTSTYLES[] = $style;
            $styleNum++;
        }
        if ($styleItem) {
            $styleItem->styleTab = $styleTab;
            $data->PRINTSTYLETABS[] = $styleItem;
        }

        $data->optionsHTML = $optionsHTML;

        $data->customTemplates = $this->customTemplates;



        /*
         * We need to preserve whitespace on this template because newlines in the the textareas are significant
        */
        $template = new EasyRecipeTemplate(EasyRecipe::$EasyRecipeDir . "/templates/easyrecipe-settings.html");
        $html = $template->replace($data, EasyRecipeTemplate::PRESERVEWHITESPACE);

        echo $html;

        $data = new stdClass();
        $data->easyrecipeURL = EasyRecipe::$EasyRecipeUrl;
        $template = new EasyRecipeTemplate(EasyRecipe::$EasyRecipeDir . "/templates/easyrecipe-upgrade.html");
        echo $template->replace($data);

    }

    /**
     * @param $data stdClass Updates $data with the current custom label settings
     */
    public function getLabels($data) {
        foreach (self::$defaultSettings as $key => $nil) {
            if (strncmp($key, 'lbl', 3) === 0) {
                $data->$key = $this->$key;
            }
        }
    }

    /**
     * Save the settings
     * TODO check nonce?
     *
     * @param $settings Array Key/value array of settings
     */
    public function save($settings) {
        if (!isset($settings)) {
            return;
        }
        $settings = stripslashes_deep($settings);

        foreach (self::$defaultSettings as $key => $value) {
            switch ($key) {
                case 'displayPrint' :
                case 'allowLink' :
                case 'filterExcerpts':
                case 'displayZiplist':
                case 'displayRecipeCard':
                case 'displayRecipage':
                case 'displayGMC':
                case 'displayUltimateRecipe':
                case 'useFeaturedImage' :
                case 'convertFractions' :
                case 'removeMicroformat' :
                case 'enableFooderific' :
                case 'enableSwoop' :
                case 'erSubscribe' :
                case 'gpHideFooter' :
                case 'forcejQuery' :
                case 'noHTMLWarn' :
                case 'genesisGrid' :
                    // case 'gpUseGravity' :
                    $this->$key = isset($settings[$key]);
                    break;

                case 'cuisines' :
                case 'recipeTypes' :
                    /*
                    * Replace CRLF, CR with LF and implode
                    */
                    $values = isset($settings[$key]) ? preg_replace('/[\r\n]+/', "\n", trim($settings[$key])) : '';
                    $this->$key = str_replace('"', "&quot;", stripslashes(preg_replace('/ *\n+ */', '|', $values)));
                    break;

                case 'customCSS' :
                case 'customPrintCSS' :
                    break;

                case 'extraCSS' :
                case 'extraPrintCSS' :
                case 'extraPrintHeader' :
                    $this->$key = trim($settings[$key]);
                    break;

                case 'erFirstName' :
                case 'erEmailAddress' :
                    $this->$key = str_replace('"', "&quot;", trim(stripslashes($settings[$key])));
                    break;

                default :
                    if (isset($settings[$key])) {
                        $this->$key = str_replace('"', "&quot;", trim(stripslashes($settings[$key])));
                    }
                    break;
            }

        }

        update_option('EasyRecipe', $this);
    }

    public function add() {
        add_option('EasyRecipe', $this);
    }

    public function update() {
        update_option('EasyRecipe', $this);
    }
}

