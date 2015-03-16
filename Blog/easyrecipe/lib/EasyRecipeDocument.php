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


class EasyRecipeDocument extends EasyRecipeDOMDocument {
    public $isEasyRecipe = false;
    public $recipeVersion = 0;
    public $isFormatted;
    private $easyrecipes = array();
    private $easyrecipesHTML = array();


    /** @var EasyRecipeSettings */
    private $settings;

    private $preEasyRecipe;
    private $postEasyRecipe;

    private $recipeData = array();

    const regexEasyRecipe = '/<div\s+class\s*=\s*["\'](?:[^>]*\s+)?easyrecipe[ \'"]/si';
    const regexDOCTYPE = '%^<!DOCTYPE.*?</head>\s*<body>\s*(.*?)</body>\s*</html>\s*%si';
    const regexTime = '/^(?:([0-9]+) *(?:hours|hour|hrs|hr|h))? *(?:([0-9]+) *(?:minutes|minute|mins|min|mns|mn|m))?$/i';
    const regexImg = '%<img ([^>]*?)/?>%si';
    const regexPhotoClass = '/class\s*=\s*["\'](?:[a-z0-9-_]+ )*?photo[ \'"]/si';
    const regexShortCodes = '%(?:\[(i|b|u)\](.*?)\[/\1\])|(?:\[(img)(?:&nbsp; *| +|\p{Zs}+)(.*?) */?\])|(?:\[(url|a)(?:&nbsp; *| +|\p{Zs}+)([^\]]+?)\](.*?)\[/url\])|(?:\[(cap)(?:&nbsp; *| +|\p{Zs}+)([^\]]+?)\](.*?)\[/cap\])%iu';

    private $fractions = array(
        1 => array(2 => '&frac12;', 3 => '&#8531;', 4 => '&frac14;', 5 => '&#8533;', 6 => '&#8537;', 8 => '&#8539;'),
        2 => array(3 => '&#8532;'),
        3 => array(4 => '&frac34;', 8 => '&#8540;'),
        4 => array(5 => '&#8536;'),
        5 => array(6 => '&#8538;', 8 => '&#8541;'),
        7 => array(8 => '&#8542;'));

    /**
     * If there's an EasyRecipe in the content, load the HTML and pre-process, else just return
     *
     * @param      $content
     * @param bool $load
     */
    public function __construct($content, $load = true) {
        /**
         * If there's no EasyRecipe, just return
         */
        if (!@preg_match(self::regexEasyRecipe, $content)) {
            return;
        }

        /**
         * Load the html - make sure we could parse it
         */
        parent::__construct($content, $load);

        if (!$this->isValid()) {
            return;
        }

        /**
         * Find the easyrecipe(s)
         */
        $this->easyrecipes = $this->getElementsByClassName('easyrecipe');

        /**
         * Sanity check - make sure we could actually find at least one
         */
        if (count($this->easyrecipes) == 0) {
            // echo "<!-- ER COUNT = 0 -->\n";
            return;
        }

        /**
         * This is a valid easyrecipe post
         * Find a version number - the version will be the same for every recipe in a multi recipe post so just get the first
         */
        $this->isEasyRecipe = true;

        /* @var $node DOMElement */
        $node = $this->getElementByClassName("endeasyrecipe", "div", $this->easyrecipes[0], false);

        $this->recipeVersion = $node->nodeValue;

        /*
         * See if this post has already been formatted.
         * Wordpress replaces the parent post_content with the autosave post content (as already formatted by us) on a preview.
         * so we need to know if this post has already been formatted. This is a pretty icky way of doing it since it relies
         * on the style template having a specific title attribute on the endeasyrecipe div - need to make this more robust
         */
        $this->isFormatted = ($node !== null && $node->hasAttribute('title'));
    }

    function setSettings(EasyRecipeSettings $settings) {
        $this->settings = $settings;
    }

    /**
     * Process the shotcodes.
     * Called as the preg_replace callback
     * TODO - this is a pretty naive implementation. It doesn't handle markdown embedded in markdown very well
     * e.g. [b]bold[b]another bold[/b][/b] won't work
     * It may not be worthwhile fixing this
     *
     * @param array $match The match array returned by the regex
     *
     * @return string The replacement code, or the original complete match if we don't recognise the shortcode
     */
    private function shortCodes($match) {
        switch ($match[1]) {
            case "i" :
                $replacement = "<em>{$match[2]}</em>";
                break;

            case "u" :
                $replacement = "<u>{$match[2]}</u>";
                break;

            case "b" :
                $replacement = "<strong>{$match[2]}</strong>";
                break;

            case "img" :
                $replacement = "<img {$match[2]} />";
                break;

            case "a" :
            case "url" :
                $replacement = "<a {$match[2]}>{$match[3]}</a>";
                break;

            case "cap" :
                $replacement = "[caption {$match[2]}]{$match[3]}[/caption]";
                break;

            default:
                return $match[0];

        }
        while (preg_match(self::regexShortCodes, $replacement)) {
            $replacement = preg_replace_callback('%\[(i|b|u)\](.*?)\[/\1\]%si', array($this, "shortCodes"), $replacement);
            $replacement = preg_replace_callback('%\[(img)(?:&nbsp; *| +|\p{Zs}+)(.*?) */?\]%iu', array($this, "shortCodes"), $replacement);
            $replacement = preg_replace_callback('%\[(url|a)(?:&nbsp; *| +|\p{Zs}+)([^\]]+?)\](.*?)\[/url\]%iu', array($this, "shortCodes"), $replacement);
            $replacement = preg_replace_callback('%\[(cap)(?:&nbsp; *| +|\p{Zs}+)([^\]]+?)\](.*?)\[/cap\]%iu', array($this, "shortCodes"), $replacement);

        }

        return $replacement;
    }

    /**
     * The original ER template didn't explicitly identify by class the individual
     * labels for various significant tags, just the tags themselves.
     * This method modifies the labels for those tags
     *
     * @param $className    string
     *                      The class of the tag
     * @param $value        string
     *                      The text value to set for the label (which will be the parent of $className)
     * @param $currentValue string
     *                      The value to replace
     */
    public function setParentValueByClassName($className, $value, $currentValue = "") {
        $nodes = $this->getElementsByClassName($className);
        for ($i = 0; $i < count($nodes); $i++) {
            $nodes[$i] = $nodes[$i]->parentNode;
        }
        for ($i = 0; $i < count($nodes); $i++) {
            if ($currentValue == "") {
                $nodes[$i]->nodeValue = $value;
            } else {
                if (preg_match("/^$currentValue(.*)$/", $nodes[$i]->firstChild->nodeValue, $regs)) {
                    $nodes[$i]->firstChild->nodeValue = $value . $regs[1];
                }
            }
        }
    }


    /**
     * Sets the URL in the print button <a> tag href
     *
     * Later versions of tinyMCE may silently remove the <a> tag altogether, so we need to put it back if it's not there
     *
     * @param     $recipe
     * @param     $template
     * @param     $data
     * @param int $nRecipe
     *
     * @return string
     */
    function formatRecipe($recipe, EasyRecipeTemplate $template, $data, $nRecipe = 0) {
        $data = $this->extractData($recipe, $data, $nRecipe);

        $html = $template->replace($data);


        /**
         * Convert fractions if asked to
         */
        if ($data->convertFractions) {
            $html = preg_replace_callback('%(. |^|>)([1-457])/([2-68])([^\d]|$)%', array($this, 'convertFractionsCallback'), $html);
        }

        /**
         * Handle our own shortcodes because Wordpress's braindead implementation doesn't handle consecutive shortcodes properly
         */
        $html = str_replace("[br]", "<br>", $html);

        /**
         * Do our own shortcode handling
         * Don't bother with the regex's if there's no need - saves a few cycles
         * Not a great way of doing these - shortcodes embedded in shortcodes aren't always handled all that well
         * TODO - Would be better implemented using a stack so we we can absolutely match beginning and end codes and eliminate the possibilty of infinite recursion
         */
        if (strpos($html, "[") !== false) {
            if (preg_match(self::regexShortCodes, $html)) {
                $html = preg_replace_callback('%\[(i|b|u)\](.*?)\[/\1\]%si', array($this, "shortCodes"), $html);
                $html = preg_replace_callback('%\[(img)(?:&nbsp; *| +|\p{Zs}+)(.*?) */?\]%iu', array($this, "shortCodes"), $html);
                $html = preg_replace_callback('%\[(url|a)(?:&nbsp; *| +|\p{Zs}+)([^\]]+?)\](.*?)\[/url\]%iu', array($this, "shortCodes"), $html);
                $html = preg_replace_callback('%\[(cap)(?:&nbsp; *| +|\p{Zs}+)([^\]]+?)\](.*?)\[/cap\]%iu', array($this, "shortCodes"), $html);
            }
        }

        /**
         * Process possible captions that have been exposed by the easyrecipe shortcode expansion
         */
        if (strpos($html, '[caption ') !== false) {
            $html = do_shortcode($html);
        }

        /**
         * Decode any quotes that have possibly been "double encoded" when we inserted an image
         */
        $html = str_replace("&amp;quot;", '&quot;', $html);

        /**
         * Remove leftover template comments and then remove linebreaks and blank lines
         */

        $html = preg_replace('/<!-- .*? -->/', '', $html);
        $lines = explode("\n", $html);
        $html = '';
        foreach ($lines as $line) {
            if (($trimmed = trim($line)) != '') {
                $html .= "$trimmed ";
            }
        }

        return $html;
    }

    /**
     * Replaces the raw easyrecipe(s) with the formatted version
     *
     * @param EasyRecipeTemplate $template
     * @param object               $originalData
     * @param null                 $recipe
     *
     * @return string
     */
    function applyStyle(EasyRecipeTemplate $template, $originalData, $recipe = null) {
        $nRecipe = 0;
        $recipes = ($recipe == null) ? $this->easyrecipes : array($recipe);

        foreach ($recipes as $recipe) {
            /**
             * Get a fresh copy of the original data because we may mess with it
             */
            $data = clone $originalData;
            /**
             * If no rating data has been passed in AND there's a self-rating, get and use the self rating
             * This badly needs to be rewritten. It's a hack to get over the problems caused by not originally allowing
             * for multiple recipes in a post and self rating.
             * $data-hasRating will be:
             *   true  - Using EasyRecipe ratings and ratings exist
             *   false - Using EasyRecipe ratings and ratings do NOT exist OR ratings are disabled
             *   not set - possibly using self rating
             */
            if (!isset($data->hasRating)) {
                $rating = $this->getElementAttributeByClassName('easyrecipe', 'data-rating');
                if (!empty($rating) && is_numeric($rating) && $rating > 0) {
                    $data->ratingCount = 1;
                    $data->ratingValue = $rating;
                    $data->ratingPC = $rating * 100 / 5;
                    $data->hasRating = true;
                }
            }
            /**
             * Format the recipe and save the formatted recipe HTML
             */
            $this->easyrecipesHTML[$nRecipe] = trim($this->formatRecipe($recipe, $template, $data, $nRecipe));

            /**
             * Insert a shortcode placeholder for the recipe. We need to remove the recipe from the content before wpauto() mangles it
             * It gets re-inserted during the "the_content" hook. The placeholder stores the postID and the index of the recipe on the post
             */

            /**
             * Replace the original recipe (the unformatted version from the post) with a place holder
             */
            $placeHolder = $this->createElement("div");
            $placeHolder->setAttribute("id", "_easyrecipe_" . $nRecipe);

            try {
                /** @var $recipe DOMNode */
                $recipe->parentNode->replaceChild($placeHolder, $recipe);
            } catch (Exception $e) {
            }

            $nRecipe++;
        }

        /**
         * Get the post's HTML which now has placeholders where the formatted recipes should be inserted
         */
        $html = $this->getHTML();

        /**
         * Return the content (now has shortcode placeholders for recipes) and the recipe HTML itself
         * Try plan C. Some themes don't call the_content() so we can't rely on hooking in to that to supply the formatted recipe HTML
         */
//        $result = new stdClass();
//        $result->html = $html;
//        $result->recipesHTML = $this->easyrecipesHTML;
//        return $result;

        /**
         * Replace the placeholders with the formatted recipe HTML
         * FIXME - why are we doing this?
         */
        for ($i = 0; $i < $nRecipe; $i++) {
            $html = str_replace("<div id=\"_easyrecipe_$i\"></div>", $this->easyrecipesHTML[$i], $html);
        }
        return $html;
    }

    /**
     * Find the first <img> in $html and add the class name "photo" to it
     *
     * If no <img> is found, returns false
     *
     * @param $html string
     *              The html to search
     *
     * @return boolean/string The adjusted html if an <img> was found, else false
     */
    private
    function makePhotoClass($html) {
        if (!@preg_match('/^(.*?)<img ([^>]+>)(.*)$/si', $html, $regs)) {
            return false;
        }
        $preamble = $regs[1];
        $imgTag = $regs[2];
        $postscript = $regs[3];
        /*
       * If there's no "class", add one else add "photo" to the existing one
       * Don't bother checking if "photo" already exists if there's an existing class
       */
        if (@preg_match('/^(.*)class="([^"]*".*)$/si', $imgTag, $regs)) {
            $imgTag = "<img " . $regs[1] . 'class="photo ' . $regs[2];
        } else {
            $imgTag = '<img class="photo" ' . $imgTag;
        }
        /*
       * Re-assemble the content
       */
        return "$preamble$imgTag$postscript";
    }

    /**
     * Add the "photo" class name to the first image in the html inside or outside the EasyRecipe
     * Check first to see if there is already an image anywhere in the post with the "photo" class
     */
    public
    function addPhotoClass() {
        /*
       * Check to see if there's an image anywhere in the post that already has a photo class
       */
        @preg_match_all(self::regexImg, $this->preEasyRecipe, $result, PREG_PATTERN_ORDER);
        foreach ($result[1] as $img) {
            if (preg_match(self::regexPhotoClass, $img)) {
                return;
            }
        }

        @preg_match_all(self::regexImg, $this->postEasyRecipe, $result, PREG_PATTERN_ORDER);
        foreach ($result[1] as $img) {
            if (preg_match(self::regexPhotoClass, $img)) {
                return;
            }
        }

        // if (@preg_match(self::regexPhotoClass, $this->preEasyRecipe)) {
        // return;
        // }
        // if (@preg_match(self::regexPhotoClass, $this->postEasyRecipe)) {
        // return;
        // }
        $photo = $this->getElementsByClassName("photo", "img");
        if (count($photo) > 0) {
            return;
        }
        /*
       * Search for the first image and if there is one, add the photo class to it
       */
        $html = $this->makePhotoClass($this->preEasyRecipe);
        if ($html !== false) {
            $this->preEasyRecipe = $html;
        } else {
            $photos = $this->getElementsByTagName("img");
            if ($photos && $photos->length > 0) {
                /** @noinspection PhpParamsInspection */
                $this->addClass($photos->item(0), "photo");
            } else {
                $html = $this->makePhotoClass($this->postEasyRecipe);
                if ($html !== false) {
                    $this->postEasyRecipe = $html;
                }
            }
        }
    }

    /**
     * WP 3.2.1 had a version of tinyMCE that removes without warning perfectly valid HTML which resolved to whitespace
     * (What do they think that "class" stuff is in there for???)
     *
     * This repairs the value-title classes necessary for times
     *
     * @param $timeElement
     */
    function fixTimes($timeElement) {
        foreach ($this->easyrecipes as $recipe) {
            $node = $this->getElementByClassName($timeElement, "span", $recipe);
            if (!$node || is_array($node)) {
                continue;
            }

            $hasNode = false;
            $h = $m = 0;
            /** @var $node DOMNode */
            /** @var $child  DOMElement */
            for ($child = $node->firstChild; $child; $child = $child->nextSibling) {
                if ($child->nodeName == "#text") {
                    if (preg_match('/(?:([0-9]+) *hours?)?(?: *([0-9]+) *min)?/i', $node->nodeValue, $regs)) {
                        $h = $regs[1];
                        $m = isset($regs[2]) ? $regs[2] : 0;
                    }
                } else if ($child->nodeName == "span") {
                    if ($child->getAttribute("class") == "value-title") {
                        $hasNode = true;
                        break;
                    }
                }
            }

            if (!$hasNode) {
                $valueElement = new DOMElement('span', ' ');
                $node->appendChild($valueElement);
                $valueElement->setAttribute("class", "value-title");
                $ISOTime = "PT";
                if ($h > 0) {
                    $ISOTime .= $h . "H";
                }
                if ($m > 0) {
                    $ISOTime .= $m . "M";
                }

                $valueElement->setAttribute("title", $ISOTime);
            }
        }
    }

    private
    function convertFractionsCallback($match) {
        if (isset($this->fractions[$match[2]][$match[3]])) {
            $pre = $match[1] != '' && is_numeric($match[1][0]) ? $match[1][0] : $match[1];
            return $pre . $this->fractions[$match[2]][$match[3]] . $match[4];
        }
        return $match[1] . $match[2] . '/' . $match[3] . $match[4];
    }

    /**
     * Get the processed html for the post.
     * Needs to remove the extra stuff saveHTML adds
     * The rtrim is needed because pcre regex's can't pick up repeated spaces after repeated "any character"
     *
     *         TODO - standardise the way body only is done!
     *
     * @param bool $bodyOnly
     *
     * @return bool|string
     */
    public
    function getHTML($bodyOnly = false) {
        $html = $this->saveHTML();
        return rtrim(preg_replace(self::regexDOCTYPE, '$1', $html));
    }

    public
    static function getPrintRecipe($content) {
        if (!@preg_match(self::regexEasyRecipe, $content, $regs)) {
            return "";
        }
        return $regs[3];
    }

    function getPostVersion() {
        return $this->getElementValueByClassName("endeasyrecipe", "div");
    }

    private function getISOTime($t) {
        if (!preg_match(self::regexTime, $t, $regs)) {
            return false;
        }
        $hr = isset($regs[1]) ? (int) $regs[1] : 0;
        $mn = isset($regs[2]) ? (int) $regs[2] : 0;

        $shr = $hr > 0 ? $hr . "H" : "";
        $smn = $mn > 0 ? $mn . "M" : "";
        return "PT$shr$smn";
    }

    function getRecipe($nRecipe = 0) {
        return $this->easyrecipes[$nRecipe];
    }

    function findPhotoURL($recipe) {
        $photoURL = false;
        if ($this->recipeVersion > '3') {
            $photoURL = $this->getElementAttributeByTagName('link', 'href', "itemprop", 'image', $recipe);
        }
        if (!$photoURL) {
            $photoURL = $this->getElementAttributeByClassName('photo', 'src');
            if (!$photoURL) {
                $images = $this->getElementsByTagName("img");
                if ($images->length > 0) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $photoURL = $images->item(0)->getAttribute('src');
                }
            }
        }
        return $photoURL;
    }

    /**
     * Translate the time labels to custome labels (if they're different)
     *
     * @param $time
     *
     * @return mixed
     */
    private function timeTranslate($time) {
        if ($this->settings->lblHours != 'hours') {
            $time = preg_replace('/\bhours\b/', $this->settings->lblHours, $time);
        }
        if ($this->settings->lblHour != 'hour') {
            $time = preg_replace('/\bhour\b/', $this->settings->lblHour, $time);
        }
        if ($this->settings->lblMinutes != 'mins') {
            $time = preg_replace('/\bmins\b/', $this->settings->lblMinutes, $time);
        }
        if ($this->settings->lblMinute != 'min') {
            $time = preg_replace('/\bmin\b/', $this->settings->lblMinute, $time);
        }
        return $time;
    }

    function extractData($recipe, $data, $nRecipe = 0) {
        $photoURL = $this->findPhotoURL($recipe);
        if ($photoURL) {
            $data->hasPhoto = true;
            $data->photoURL = $photoURL;
        }
        $data->recipeIX = $nRecipe;

        $data->version = $this->recipeVersion;

        $data->name = $this->getElementValueByClassName("ERName", "*", $recipe);
        $data->cuisine = $this->getElementValueByClassName("cuisine", "span", $recipe);

        $data->type = $this->getElementValueByClassName("type", "span", $recipe);
        // TODO - oops for OC
        if (!$data->type) {
            $data->type = $this->getElementValueByClassName("tag", "span", $recipe);
        }
        $data->author = $this->getElementValueByClassName("author", "span", $recipe);

        if ($this->recipeVersion < '3') {
            $data->preptime = $this->getElementValueByClassName("preptime", "span", $recipe);
            $data->cooktime = $this->getElementValueByClassName("cooktime", "span", $recipe);
            $data->totaltime = $this->getElementValueByClassName("duration", "span", $recipe);
        } else {
            $data->preptime = $this->getElementValueByProperty('time', 'itemprop', 'prepTime', $recipe);
            $data->cooktime = $this->getElementValueByProperty('time', 'itemprop', 'cookTime', $recipe);
            $data->totaltime = $this->getElementValueByProperty('time', 'itemprop', 'totalTime', $recipe);
        }

        /**
         * Hack for awkward convert of times from Ziplist
         */
        if ($data->preptime == '0 min') {
            unset($data->preptime);
        }
        if ($data->cooktime == '0 min') {
            unset($data->cooktime);
        }
        if ($data->totaltime == '0 min') {
            unset($data->totaltime);
        }

        $data->hasTimes = (!empty($data->preptime) || !empty($data->cooktime) || !empty($data->totaltime));

        if ($data->hasTimes) {
            $data->preptimeISO = $this->getISOTime($data->preptime);
            $data->cooktimeISO = $this->getISOTime($data->cooktime);
            $data->totaltimeISO = $this->getISOTime($data->totaltime);

            $data->preptime = $this->timeTranslate($data->preptime);
            $data->cooktime = $this->timeTranslate($data->cooktime);
            $data->totaltime = $this->timeTranslate($data->totaltime);
        }

        $data->yield = $this->getElementValueByClassName("yield", "span", $recipe);
        $data->summary = $this->getElementValueByClassName("summary", "*", $recipe);

        $data->servingSize = $this->getElementValueByClassName("servingSize", "span", $recipe);
        $data->calories = $this->getElementValueByClassName("calories", "span", $recipe);
        $data->fat = $this->getElementValueByClassName("fat", "span", $recipe);
        $data->saturatedFat = $this->getElementValueByClassName("saturatedFat", "span", $recipe);
        $data->unsaturatedFat = $this->getElementValueByClassName("unsaturatedFat", "span", $recipe);
        $data->transFat = $this->getElementValueByClassName("transFat", "span", $recipe);
        $data->carbohydrates = $this->getElementValueByClassName("carbohydrates", "span", $recipe);
        $data->sugar = $this->getElementValueByClassName("sugar", "span", $recipe);
        $data->sodium = $this->getElementValueByClassName("sodium", "span", $recipe);
        $data->fiber = $this->getElementValueByClassName("fiber", "span", $recipe);
        $data->protein = $this->getElementValueByClassName("protein", "span", $recipe);
        $data->cholesterol = $this->getElementValueByClassName("cholesterol", "span", $recipe);
        $data->hasNutrition =
            $data->servingSize || $data->calories || $data->fat || $data->saturatedFat || $data->unsaturatedFat || $data->carbohydrates || $data->sugar || $data->fiber || $data->protein || $data->cholesterol || $data->sodium || $data->transFat;

        $data->notes = $this->getElementValueByClassName("ERNotes", "div", $recipe);

        $data->INGREDIENTSECTIONS = array();
        $section = null;
        // $ingredientsList = $this->getElementByClassName('ingredients', 'ul', $recipe);
        $ingredientsLists = $this->getElementsByClassName('ingredients', 'ul', $recipe);

        foreach ($ingredientsLists as $ingredientsList) {
            $ingredients = $this->getElementsByClassName("ingredient|ERSeparator", "*", $ingredientsList);

            foreach ($ingredients as $ingredient) {
                $hasHeading = $this->hasClass($ingredient, 'ERSeparator');
                if ($hasHeading || $section == null) {
                    if ($section != null) {
                        $data->INGREDIENTSECTIONS[] = $section;
                    }
                    $section = new stdClass();
                    $section->INGREDIENTS = array();
                    if ($hasHeading) {
                        $section->heading = $ingredient->nodeValue;
                        continue;
                    }
                }
                $item = new stdClass();
                $item->ingredient = $ingredient->nodeValue;
                $item->isImage = preg_match('/^\s*(?:\[[^]]+\])*\s*\[img /i', $ingredient->nodeValue) != 0;
                $section->INGREDIENTS[] = $item;
            }
        }
        $data->hasIngredients = count($ingredientsLists) > 0;
        if ($data->hasIngredients) {
            $data->INGREDIENTSECTIONS[] = $section;
        }

        $data->INSTRUCTIONSTEPS = array();
        $section = null;
        // $instructionsList = $this->getElementByClassName('instructions', 'div', $recipe);
        $instructionsLists = $this->getElementsByClassName('instructions', 'div', $recipe);
        foreach ($instructionsLists as $instructionsList) {
            $instructions = $this->getElementsByClassName("instruction|ERSeparator", "*", $instructionsList);
            foreach ($instructions as $instruction) {
                $hasHeading = $this->hasClass($instruction, 'ERSeparator');
                if ($hasHeading || $section == null) {
                    if ($section != null) {
                        $data->INSTRUCTIONSTEPS[] = $section;
                    }
                    $section = new stdClass();
                    $section->INSTRUCTIONS = array();
                    if ($hasHeading) {
                        $section->heading = $instruction->nodeValue;
                        continue;
                    }
                }
                $item = new stdClass();
                $item->instruction = $instruction->nodeValue;
                $item->isImage = preg_match('/^\s*(?:\[[^]]+\])*\s*\[img /i', $instruction->nodeValue) != 0;
                $section->INSTRUCTIONS[] = $item;
            }
        }

        $data->hasInstructions = $section != null;
        if ($data->hasInstructions) {
            $data->INSTRUCTIONSTEPS[] = $section;
        }

        return $data;
    }

    /**
     * Strips wrappers around recipes that the entry javascript added to enable inserting lines before and after a recipe
     *
     * @return string The post content with the wrappers stripped out or null if there were errors
     */
    function stripWrappers() {
        $wrappers = $this->getElementsByClassName("easyrecipeWrapper");
        foreach ($wrappers as $wrapper) {
            /** @var $wrapper DOMNode */
            /*
             * First take out possible "above" and "below" divs
             */
            $nodes = $this->getElementsByClassName("easyrecipeAbove", "div", $wrapper);
            foreach ($nodes as $node) {
                try {
                    $wrapper->removeChild($node);
                } catch (Exception $e) {
                    return null;
                }
            }
            $nodes = $this->getElementsByClassName("easyrecipeBelow", "div", $wrapper);
            foreach ($nodes as $node) {
                try {
                    $wrapper->removeChild($node);
                } catch (Exception $e) {
                    return null;
                }
            }
            /*
             * Then insert the recipe itself into the DOM just above the wrapper
             */
            $recipe = $this->getElementByClassName("easyrecipe", "div", $wrapper);
            try {
                $wrapper->parentNode->insertBefore($recipe, $wrapper);
            } catch (Exception $e) {
                return null;
            }

            /*
             * Finally remove the wrapper itself
             */
            try {
                $wrapper->parentNode->removeChild($wrapper);
            } catch (Exception $e) {
                return null;
            }
        }
        return $this->getHTML(true);
    }
}

