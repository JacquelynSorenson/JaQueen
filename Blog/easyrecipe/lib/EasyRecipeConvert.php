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

/**
 * Class EasyRecipePlusConvert
 *
 * Handles conversions from other plugins
 *
 * TODO - the converted structure is a hangover from the first "EasyRecipe hrecipe to EasyRecipe schema.org" conversion and badly needs to be cleaned up and simplified
 *
 */
class EasyRecipeConvert {
    private function yumprintTime($time) {
        $time = (int) $time;

        $minutes = $time % 60;
        $hours = floor(($time - $minutes) / 60);

        return sprintf("PT%dH%dM", $hours, $minutes);
    }

    /**
     * Attempt to recognise an arbitrary time string
     * Works for various variations of "xx hours yy mins" but is pretty basic
     *
     * @param string $timeString
     *
     * @return string The time in ISO format if we can recognise a time, else an empty string
     */
    private function convertTimeString($timeString) {
        if (empty($timeString)) {
            return '';
        }
        $timeString = preg_replace('/(hours?|hrs?|h)/', 'H', $timeString);
        $timeString = preg_replace('/(minutes?|mins?|mi|m)/', 'M', $timeString);
        $timeString = str_replace(' ', '', $timeString);
        if (!preg_match('/^(?:(\d+)H)?(?:(\d+)M)?$/', $timeString, $regs)) {
            return '';
        }
        $time = $regs[1] * 60 + $regs[2];
        $minutes = $time % 60;
        $hours = floor(($time - $minutes) / 60);

        return sprintf("PT%dH%dM", $hours, $minutes);

    }

    /**
     * Decode markdown
     * Convert Ziplist markdown to EasyRecipe markdown
     * Also converts HTML links and images to EasyRecipe markdown (affiliate type links e.g. Amazon don't (can't?) use Ziplist markdown)
     * TODO - this is a pretty naive implementation. It doesn't handle markdown embedded in markdown well
     * e.g. It will handle [*bold*|link.com] but not *[bold|link.com]*
     *
     * @param $string
     *
     * @return mixed
     */
    private function decodeMarkdown($string) {
        /**
         * Convert <a> and <img> tags
         */
        $string = preg_replace('%<a\s+([^>]+?)>(.+?)</a>%', '[url $1]$2[/url]', $string);
        $string = preg_replace('%<img\s+([^>]+?)\s*?/>%', '[img $1 /]', $string);
        /**
         * Convert Ziplist markdown
         */
        $string = preg_replace('/\[([^|[\]]+?)\|(.+?)\]/', '[url href="$2" target="_blank"]$1[/url]', $string);
        $string = preg_replace('/([\W]|^)_([^_]+?)_(\W|$)/', '$1[i]$2[/i]$3', $string);
        return preg_replace('/([\W]|^)\*([^*]+?)\*(\W|$)/', '$1[b]$2[/b]$3', $string);
    }

    private function doConvert($postID, $postType) {
        /** @global $wpdb wpdb */
        global $wpdb;

        $result = new stdClass();

        switch ($postType) {

            case 'ultimate-recipe' :
                $result->recipe = new stdClass();

                if ($postID == 'random') {
                    $posts = get_posts(array(
                        'post_type' => 'recipe',
                        'nopaging'  => true
                    ));
                    $post = $posts[array_rand($posts)];
                } else {
                    $post = get_post($postID);
                }

                $recipe = get_post_custom($post->ID);
                $user = get_userdata($post->post_author);
                $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
                $cuisine = wp_get_object_terms($post->ID, 'cuisine');
                if ($cuisine instanceof WP_Error) {
                    register_taxonomy('cuisine', 'recipe');
                    $cuisine = wp_get_object_terms($post->ID, 'cuisine');
                }

                $course = wp_get_object_terms($post->ID, 'course');
                if ($course instanceof WP_Error) {
                    register_taxonomy('course', 'recipe');
                    $course = wp_get_object_terms($post->ID, 'course');
                }

                /**
                 * Very dodgy way of processing times - not sure what else we can do!
                 * Times other than "x minutes" are going to be unparsable reliably
                 * Will have to adjust this based on real user values as we come across them
                 *
                 * Try for xlated "minutes" first
                 * If that doesn't work, at least try some likely English duration specifiers
                 */
                $xMinutes = __('minutes', 'wp-ultimate-recipe');
                $timeText = !empty($recipe['recipe_prep_time_text']) ? $recipe['recipe_prep_time_text'][0] : '';
                if ($timeText == $xMinutes || $timeText == 'minute') {
                    $result->recipe->prep_time = 'PT' . $recipe['recipe_prep_time'][0] . 'M';
                } elseif ($timeText == 'hours' || $timeText == 'hour') {
                    $result->recipe->prep_time = 'PT' . $recipe['recipe_prep_time'][0] . 'H0M';
                } else {
                    $result->recipe->prep_time ='';
                }

                $timeText = !empty($recipe['recipe_cook_time_text']) ? $recipe['recipe_cook_time_text'][0] : '';
                if ($timeText == $xMinutes || $timeText == 'minute') {
                    $result->recipe->cook_time = 'PT' . $recipe['recipe_cook_time'][0] . 'M';
                } elseif ($timeText == 'hours' || $timeText == 'hour') {
                    $result->recipe->cook_time = 'PT' . $recipe['recipe_cook_time'][0] . 'H0M';
                }else {
                    $result->recipe->cook_time ='';
                }

                $result->recipe->recipe_image = !empty($image) ? $image[0] : '';

                if (is_array($cuisine) && !empty($cuisine[0])) {
                    $result->recipe->cuisine = htmlspecialchars($cuisine[0]->name);
                }

                if (is_array($course) && !empty($course[0])) {
                    $result->recipe->mealType = htmlspecialchars($course[0]->name);
                }

                $result->recipe->recipe_title = !empty($recipe['recipe_title']) ? htmlspecialchars($recipe['recipe_title'][0]) : '';
                /** @noinspection PhpUndefinedFieldInspection */
                $result->recipe->author = htmlspecialchars($user->data->display_name);
                $result->recipe->summary = !empty($recipe['recipe_description']) ? htmlspecialchars($recipe['recipe_description'][0]) : '';

                $notes = !empty($recipe['recipe_notes'][0]) ? preg_replace_callback('%<(strong|em)>(.*?)</\1>%', array($this, 'notesConversion'), $recipe['recipe_notes'][0]) : '';
                $result->recipe->notes = preg_replace('%<a ([^>]+)>(.*?)</a>%i', '[url $1]$2[/url]', $notes);

                $section = '';
                $ingredients = array();

                $urIngredients = @unserialize($recipe['recipe_ingredients'][0]);
                if (!$urIngredients) {
                    $urIngredients = array();
                }
                foreach ($urIngredients as $urIngredient) {
                    if ($urIngredient['group'] != $section) {
                        $section = $urIngredient['group'];
                        $ingredients[] = '!' . htmlspecialchars($urIngredient['group']);
                    }
                    $ingredient = htmlspecialchars($urIngredient['amount']) . ' ' . htmlspecialchars($urIngredient['unit']) . ' ' . htmlspecialchars($urIngredient['ingredient']);
                    if (!empty($urIngredient['notes'])) {
                        $ingredient .= ' ' . htmlspecialchars($urIngredient['notes']);
                    }
                    $ingredients[] = $ingredient;
                }
                $result->ingredients = $ingredients;

                $section = '';
                $instructions = array();
                $urInstructions = @unserialize($recipe['recipe_instructions'][0]);
                if (!$urInstructions) {
                    $urInstructions = array();
                }
                foreach ($urInstructions as $urInstruction) {
                    if ($urInstruction['group'] != $section) {
                        $section = $urInstruction['group'];
                        $instructions[] = '!' . htmlspecialchars($urInstruction['group']);
                    }
                    $instruction = htmlspecialchars($urInstruction['description']);
                    if (!empty($urInstruction['image'])) {
                        $instructionImage = wp_get_attachment_image_src($urInstruction['image'], 'large');
                        if (!empty($instructionImage)) {
                            $instruction .= '[br][img src="' . $instructionImage[0] . '" width="' . $instructionImage[1] . '" height="' . $instructionImage[2] . '" /]';
                        }
                    }
                    $instructions[] = $instruction;
                }

                $result->recipe->instructions = implode("\n", $instructions);
                break;

            case 'recipage':
                /** @var WP_Post $post */
                $post = $wpdb->get_row("SELECT * FROM " . $wpdb->posts . " WHERE ID=" . $postID);
                $content = $post->post_content;
                $document = new EasyRecipeDOMDocument($content);
                if (!$document->isValid()) {
                    return null;
                }

                $hrecipe = $document->getElementByClassName('hrecipe');
                if (!$hrecipe) {
                    return null;
                }
                $result->recipe = new stdClass();
                $result->recipe->total_time = '';
                $result->recipe->serving_size = '';
                $result->recipe->notes = '';
                $result->recipe->calories = '';
                $result->recipe->fat = '';
                $result->recipe->rating = '';

                /** @var DOMElement $element */
                $element = $document->getElementByClassName('photo', 'img', $hrecipe);
                $result->recipe->recipe_image = $element != null ? $element->getAttribute('src') : '';

                $element = $document->getElementByClassName('fn', '*', $hrecipe);
                $result->recipe->recipe_title = $element != null ? htmlspecialchars($element->textContent) : '';

                $element = $document->getElementByClassName('author', '*', $hrecipe);
                $result->recipe->author = $element != null ? htmlspecialchars($element->textContent) : '';

                $element = $document->getElementByClassName('summary', '*', $hrecipe);
                $result->recipe->summary = $element != null ? htmlspecialchars($element->textContent) : '';

                $element = $document->getElementByClassName('yield', '*', $hrecipe);
                $result->recipe->yield = $element != null ? htmlspecialchars($element->textContent) : '';

                $element = $document->getElementByClassName('preptime', '*', $hrecipe);
                $prepTime = $element != null ? $element->textContent : '';
                $result->recipe->prep_time = $this->convertTimeString($prepTime);

                $element = $document->getElementByClassName('cooktime', '*', $hrecipe);
                $cookTime = $element != null ? $element->textContent : '';
                $result->recipe->cook_time = $this->convertTimeString($cookTime);

                $result->ingredients = array();
                $ingredients = $document->getElementsByClassName('ingredient', '*', $hrecipe);
                /** @var DOMElement $ingredient */
                foreach ($ingredients as $ingredient) {
                    $result->ingredients[] = trim($ingredient->textContent);
                }

                $instructions = array();
                $elements = $document->getElementsByClassName('instruction', '*', $hrecipe);
                /** @var DOMElement $instruction */
                foreach ($elements as $instruction) {
                    $instructions[] = trim($instruction->textContent);
                }
                $result->recipe->instructions = implode("\n", $instructions);

                break;


            case 'recipeseo' :
                $result->recipe = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "amd_recipeseo_recipes WHERE recipe_id=" . $postID);
                $ingredients = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "amd_recipeseo_ingredients WHERE recipe_id=" . $postID . " ORDER BY ingredient_id");

                $result->ingredients = array();
                foreach ($ingredients as $ingredient) {
                    $result->ingredients[] = $ingredient->amount . " " . $ingredient->name;
                }
                break;

            case 'zlrecipe' :
                $result->recipe = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "amd_zlrecipe_recipes WHERE recipe_id=" . $postID);
                $result->recipe->nReviews = 1;
                $result->recipe->summary = $this->decodeMarkdown($result->recipe->summary);
                $result->recipe->notes = $this->decodeMarkdown($result->recipe->notes);
                /**
                 * If only total time is specified, use it as the cook time
                 * TODO - Do this for all plugins?
                 */
                if ($result->recipe->cook_time == '' && $result->recipe->prep_time == '') {
                    $result->recipe->cook_time = $result->recipe->total_time;
                }
                $ingredients = explode("\n", str_replace("\r", "", $result->recipe->ingredients));
                $result->ingredients = array();
                foreach ($ingredients as $ingredient) {
                    $ingredient = trim($ingredient);
                    if ($ingredient != '') {
                        if (preg_match('/^%([^\s]+)/', $ingredient, $regs)) {
                            if (($n = count($result->ingredients)) > 0 && $result->ingredients[$n - 1][0] != '!') {
                                $result->ingredients[$n - 1] .= '[br][img src="' . $regs[1] . '"]';
                                continue;
                            } else {
                                $ingredient = '[img src="' . $regs[1] . '"]';
                            }
                        } else {
                            $ingredient = $this->decodeMarkdown($ingredient);
                        }
                        $result->ingredients[] = $ingredient;
                    }
                }
                unset($result->recipe->ingredients);

                $instructions = explode("\n", str_replace("\r", "", $result->recipe->instructions));
                $convertedInstructions = array();
                foreach ($instructions as $instruction) {
                    $instruction = trim($instruction);
                    if ($instruction != '') {
                        if (preg_match('/^%([^\s]+)/', $instruction, $regs)) {
                            if (($n = count($convertedInstructions)) > 0 && $convertedInstructions[$n - 1][0] != '!') {
                                $convertedInstructions[$n - 1] .= '[br][img src="' . $regs[1] . '"]';
                                continue;
                            } else {
                                $instruction = '[img src="' . $regs[1] . '"]';
                            }
                        } else {
                            $instruction = $this->decodeMarkdown($instruction);
                        }

                        $convertedInstructions[] = $instruction;
                    }
                }
                /**
                 * For silly historical reasons, instructions are returned as a string
                 */
                $result->recipe->instructions = implode("\n", $convertedInstructions);
                break;

            case 'recipress' :
                $meta = get_post_custom($postID);
                $result->recipe = new stdClass();
                $result->ingredients = array();
                $result->recipe->instructions = '';
                /** @noinspection PhpUndefinedFunctionInspection */
                $size = recipress_options('instruction_image_size');

                $result->recipe->recipe_title = $meta['title'][0] ? $meta['title'][0] : '';
                $photo = wp_get_attachment_image_src($meta['photo'][0], 'thumbnail', false);
                $result->recipe->recipe_image = $photo ? $photo[0] : '';
                $result->recipe->summary = $meta['summary'][0] ? $meta['summary'][0] : '';
                $terms = get_the_terms($postID, 'cuisine');
                $result->recipe->cuisine = $terms[0]->name;

                $terms = get_the_terms($postID, 'course');
                $result->recipe->mealType = $terms[0]->name;

                /** @noinspection PhpUndefinedFunctionInspection */
                $result->recipe->cook_time = $meta['cook_time'][0] ? recipress_time($meta['cook_time'][0], 'iso') : '';
                /** @noinspection PhpUndefinedFunctionInspection */
                $result->recipe->prep_time = $meta['prep_time'][0] ? recipress_time($meta['prep_time'][0], 'iso') : '';

                $result->recipe->yield = $meta['yield'][0] ? $meta['yield'][0] : '';
                $result->recipe->serves = $meta['servings'][0] ? $meta['servings'][0] : '';

                $ingredients = $meta['ingredient'];
                $ingredients = unserialize($ingredients[0]);
                foreach ($ingredients as $ingredient) {
                    $newIngredient = $ingredient['amount'] . ' ' . $ingredient['measurement'] . ' ' . $ingredient['ingredient'];
                    if (!empty($ingredient['notes'])) {
                        $newIngredient .= ', ' . $ingredient['notes'];
                    }
                    $result->ingredients[] = trim(str_replace('  ', ' ', $newIngredient));
                }

                $instructions = $meta['instruction'];
                $instructions = unserialize($instructions[0]);
                foreach ($instructions as $instruction) {
                    $result->recipe->instructions .= $instruction['description'];
                    if (!empty($instruction['image'])) {
                        $result->recipe->instructions .= "[br]" . wp_get_attachment_image($instruction['image'], $size, false);
                        $result->recipe->instructions = str_replace('<', '[', $result->recipe->instructions);
                        $result->recipe->instructions = str_replace('>', ']', $result->recipe->instructions);
                    }
                    $result->recipe->instructions .= "\n";
                }
                break;
            /**
             * Make the Recipe Card data look like RecipeSEO/Ziplist - only because we already have the JS for those
             */
            case 'yumprint' :
                $post = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "yumprint_recipe_recipe WHERE id=" . $postID);
                $nutrition = json_decode($post->nutrition);
                $result->recipe = json_decode($post->recipe);
                $result->recipe->recipe_title = $result->recipe->title;
                $result->recipe->recipe_image = $result->recipe->image;
                $result->ingredients = $result->recipe->ingredients[0]->lines;
                $result->recipe->instructions = implode("\n", $result->recipe->directions[0]->lines);
                $result->recipe->notes = implode("\n", $result->recipe->notes[0]->lines);

                $result->recipe->prep_time = $this->yumprintTime($result->recipe->prepTime);
                $result->recipe->cook_time = $this->yumprintTime($result->recipe->cookTime);

                $result->recipe->yield = $result->recipe->yields;
                $result->recipe->serving_size = $result->recipe->servings;

                $result->recipe->nutrition = $nutrition;

                $serves = (int) $result->recipe->servings;

                $div = !empty($serves) ? $serves : 1;

                /** @noinspection PhpUnusedLocalVariableInspection */
                foreach ($nutrition as $key => &$value) {
                    $value /= $div;
                }

                $nutrition->calories = round($nutrition->calories);
                $nutrition->totalFat = round($nutrition->totalFat) . 'g';
                $nutrition->saturatedFat = round($nutrition->saturatedFat) . 'g';
                $nutrition->transFat = round($nutrition->transFat) . 'g';
                $nutrition->polyunsaturatedFat = round($nutrition->polyunsaturatedFat);
                $nutrition->monounsaturatedFat = round($nutrition->monounsaturatedFat);
                $nutrition->unsaturatedFat = ($nutrition->polyunsaturatedFat + $nutrition->monounsaturatedFat) . 'g';
                $nutrition->cholesterol = round($nutrition->cholesterol) . 'mg';
                $nutrition->sodium = round($nutrition->sodium) . 'mg';
                $nutrition->totalCarbohydrates = round($nutrition->totalCarbohydrates) . 'g';
                $nutrition->dietaryFiber = round($nutrition->dietaryFiber) . 'g';
                $nutrition->sugars = round($nutrition->sugars) . 'g';
                $nutrition->protein = round($nutrition->protein) . 'g';
                $result->recipe->nutrition = $nutrition;

                unset($result->recipe->prepTime);
                unset($result->recipe->cookTime);
                unset($result->recipe->totalTime);
                unset($result->recipe->yields);
                unset($result->recipe->servings);

                unset($result->recipe->title);
                unset($result->recipe->image);
                unset($result->recipe->ingredients);
                unset($result->recipe->directions);
                break;

            /**
             * Get me cooking
             */
            case 'gmc' :
            case 'gmc_recipe' :
                /**
                 * If GMC is installed, use it to get ingredients, but turn off error reporting to stop it crashing
                 */
                if (function_exists('print_ingredient_description')) {
                    error_reporting(0);
                    $gmcInstalled = true;
                } else {
                    $gmcInstalled = false;
                }
                $result->recipe = new stdClass();

                $post = get_post($postID);

                $result->recipe->recipe_title = html_entity_decode(get_the_title($postID), ENT_COMPAT, 'UTF-8');
                $result->recipe->author = get_post_meta($postID, 'gmc-source-name', true);
                $result->recipe->summary = get_post_meta($post->ID, "gmc-description", true);

                $thumbID = get_post_thumbnail_id($postID);
                $postThumb = $thumbID != 0 ? wp_get_attachment_image_src($thumbID, 'medium') : '';
                if (!empty($postThumb)) {
                    $result->recipe->recipe_image = $postThumb[0];
                }
                $prepHour = (int) get_post_meta($postID, "gmc-prep-time-hours", true);
                $prepMinute = (int) get_post_meta($postID, "gmc-prep-time-mins", true);
                $cookHour = (int) get_post_meta($postID, "gmc-cooking-time-hours", true);
                $cookMinute = (int) get_post_meta($postID, "gmc-cooking-time-mins", true);
                $result->recipe->prep_time = "PT{$prepHour}H{$prepMinute}M";
                $result->recipe->cook_time = "PT{$cookHour}H{$cookMinute}M";

                $mealTypes = wp_get_object_terms($postID, 'gmc_course');
                if ($mealTypes instanceof WP_Error) {
                    register_taxonomy('gmc_course', 'gmc_recipe');
                    $mealTypes = wp_get_object_terms($postID, 'gmc_course');
                }
                if (is_array($mealTypes) && count($mealTypes) > 0) {
                    $result->recipe->mealType = $mealTypes[0]->name;
                }

                $regions = wp_get_object_terms($postID, 'gmc_region');
                if ($regions instanceof WP_Error) {
                    register_taxonomy('gmc_region', 'gmc_recipe');
                    $regions = wp_get_object_terms($postID, 'gmc_region');
                }
                if (is_array($regions) && count($regions) > 0) {
                    $result->recipe->cuisine = $regions[0]->name;
                }

                $result->ingredients = array();
                $result->recipe->instructions = '';
                $currentSection = '';
                $steps = get_posts('post_status=publish&post_type=gmc_recipestep&nopaging=1&orderby=menu_order&order=ASC&post_parent=' . $postID);
                /** @var $step WP_Post */
                foreach ($steps as $step) {
                    if (!empty($step->post_content)) {
                        $section = get_post_meta($step->ID, 'gmc_stepgroup', true);
                        if (!empty($section) && $section != $currentSection) {
                            $result->recipe->instructions .= "!$section\n";
                            $currentSection = $section;
                        }
                        $content = preg_replace('/\r?\n/i', '[br]', $step->post_content);
                        $thumbID = get_post_thumbnail_id($step->ID);
                        if ($thumbID) {
                            $postThumb = wp_get_attachment_image_src($thumbID, 'medium');
                            if ($postThumb) {
                                $content .= '[br][img src="' . $postThumb[0] . '" width="' . $postThumb[1] . '" height="' . $postThumb[2] . '"]';
                            }
                        }

                        $result->recipe->instructions .= $content . "\n";
                    }
                }
                $result->recipe->instructions = rtrim($result->recipe->instructions, "\n");
                $ingredients = get_posts('post_status=publish&post_type=gmc_recipeingredient&nopaging=1&orderby=menu_order&order=ASC&post_parent=' . $postID);
                $currentSection = '';
                /** @var $ingredient WP_Post */
                foreach ($ingredients as $ingredient) {
                    $section = get_post_meta($ingredient->ID, "gmc-ingredientgroup", true);
                    if (!empty($section) && $section != $currentSection) {
                        $result->ingredients[] = "!$section";
                        $currentSection = $section;
                    }
                    if ($gmcInstalled) {
                        /** @noinspection PhpUndefinedFunctionInspection */
                        $iLine = trim(print_ingredient_description($ingredient));
                    } else {
                        $quantity = get_post_meta($ingredient->ID, "gmc-ingredientquantity", true);
                        $measurement = get_post_meta($ingredient->ID, 'gmc-ingredientmeasurement', true);
                        $title = $ingredient->post_title;
                        $iLine = trim("$quantity $measurement $title");
                    }
                    if (!empty($iLine)) {
                        $result->ingredients[] = $iLine;
                    }

                }

                /**
                 * Try to convert HTML in notes into something EasyRecipe will understand
                 */
                $notes = preg_replace_callback('%<(strong|em)>(.*?)</\1>%', array($this, 'notesConversion'), $post->post_content);
                $notes = preg_replace('%<a ([^>]+)>(.*?)</a>%i', '[url $1]$2[/url]', $notes);
                $result->recipe->notes = strip_tags($notes);
                $result->recipe->yield = $servings = get_post_meta($post->ID, "gmc-nr-servings", true);
                $nutrition = new stdClass();
                if (get_post_meta($postID, "gmc_has_nutrition", true)) {

                    $result->recipe->serving_size = get_post_meta($post->ID, "gmc_gda_servings", true);
                    $nutrition->calories = get_post_meta($post->ID, "gmc_nutrition_kcal_serving", true);
                    if ($nutrition->calories != '') {
                        $nutrition->calories *= 1000;
                    }
                    $nutrition->totalFat = round(get_post_meta($post->ID, "gmc_nutrition_fat_total_serving", true)) . 'g';
                    $nutrition->saturatedFat = round(get_post_meta($post->ID, "gmc_nutrition_fat_sat_serving", true)) . 'g';
                    $nutrition->sodium = round(get_post_meta($post->ID, "gmc_nutrition_salt_sod_serving", true)) . 'mg';
                    $nutrition->totalCarbohydrates = round(get_post_meta($post->ID, "gmc_nutrition_carb_total_serving", true)) . 'g';
                    $nutrition->dietaryFiber = round(get_post_meta($post->ID, "gmc_nutrition_fibre_serving", true)) . 'g';
                    $nutrition->sugars = round(get_post_meta($post->ID, "gmc_nutrition_carb_sugar_serving", true)) . 'g';
                    $nutrition->protein = round(get_post_meta($post->ID, "gmc_nutrition_protein_serving", true)) . 'g';
                }
                $result->recipe->nutrition = $nutrition;

                break;

            case 'kitchebug' :
                break;

        }
        return $result;

    }

    /**
     * Convert HTML we can handle to shortcodes
     *
     * @param $matches
     *
     * @return string
     */
    function notesConversion($matches) {
        $shortcode = $matches[1] == 'em' ? 'i' : 'b';
        return "[$shortcode]" . $matches[2] . "[/$shortcode]";
    }

    /**
     * Return RecipeSEO/ZipList data/Yumprint/ReciPress
     */
    function convertRecipe() {
        echo json_encode($this->doConvert((int) $_POST['id'], $_POST['type']));
        die();
    }


}

