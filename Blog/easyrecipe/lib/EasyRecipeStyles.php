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
class EasyRecipeStyles {
    const ISPRINT = true;
    private static $styles;
    private static $printStyles;

    /**
     * Returns the data for style "$styleName".
     * If $styleName begins with an underscore, it's a custom style
     *
     * @param string $styleName
     *            Name of the style to retrieve
     * @param string $customTemplates
     *            The custom templates directory - empty string if none
     * @param boolean $isPrint
     *            TRUE if this is a print style
     * @return stdClass Style data object
     */
    static public function getStyleData($styleName, $customTemplates, $isPrint = false) {
        $isFormatting = false;
        $isFonts = false;
        $pluginURL = EasyRecipe::$EasyRecipeUrl;
        if ($styleName[0] == '_') {
            $directory = $customTemplates;
            $url = get_bloginfo('wpurl') . "/easyrecipe-style";
            $styleDirectory = substr($styleName, 1);
        } else {
            $directory = EasyRecipe::$EasyRecipeDir;
            $url = $pluginURL;
            $styleDirectory = $styleName;
        }

        $styleData = new stdClass();
        $styleData->style = '';
        $styleData->author = '';
        $styleData->version = '1.0';
        $styleData->description = '';
        $styleData->formatting = '[]';
        $styleData->fonts = '';
        $styleData->directory = $styleName;

        // FIXME - what if no style.txt?

        if ($isPrint) {
            $dir = 'print';
        } else {
            $dir = '';
        }

        $lines = @file("$directory/{$dir}styles/$styleDirectory/style.txt");
        if ($lines !== false) {
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line == '') {
                    continue;
                }

                if ($isFormatting) {
                    if ($line[strlen($line) - 1] == "\\") {
                        $styleData->formatting .= rtrim($line, "\\");
                        continue;
                    }
                    $styleData->formatting .= $line;
                    $isFormatting = false;
                } else if ($isFonts) {
                    if ($line[strlen($line) - 1] == "\\") {
                        $styleData->fonts .= rtrim($line, "\\");
                        continue;
                    }
                    $styleData->fonts .= $line;
                    $isFonts = false;
                } else if (preg_match('/^([a-z]{1,16})\s*:\s*(.*)$/i', $line, $regs)) {
                    $item = strtolower($regs[1]);
                    $styleData->$item = $regs[2];
                    if ($item == 'formatting' && $regs[2][strlen($regs[2]) - 1] == "\\") {
                        $styleData->formatting = rtrim($regs[2], "\\");
                        $isFormatting = true;
                    }
                    if ($item == 'fonts' && $regs[2][strlen($regs[2]) - 1] == "\\") {
                        $styleData->fonts = rtrim($regs[2], "\\");
                        $isFonts = true;
                    }
                }
            }
        }
        if (isset($styleData->thumbnail)) {
            $styleData->thumbnail = "$url/{$dir}styles/$styleDirectory/" . $styleData->thumbnail;
        } else {
            $styleData->thumbnail = "$pluginURL/images/nopreview.gif";
        }
        if ($styleData->fonts != '') {
            preg_match_all('/(.*?)\((.*?)\);?/i', $styleData->fonts, $result, PREG_SET_ORDER);
            $styleData->fonts = array ();
            for ($matchi = 0; $matchi < count($result); $matchi++) {
                $styleData->fonts[$matchi]['provider'] = strtolower($result[$matchi][1]);
                $styleData->fonts[$matchi]['fonts'] = $result[$matchi][2];
            }
        } else {
            $styleData->fonts = array ();
        }
        return $styleData;
    }

    static private function getStyleNames($directory, $isCustom = false) {
        $names = array ();
        try {
            $stylesDir = new DirectoryIterator($directory);
        } catch (Exception $e) {
            return $names;
        }


        foreach ($stylesDir as $dir) {
            /** @var $dir DirectoryIterator */
            if (!$dir->isDir()) {
                continue;
            }
            $style = $dir->getFilename();
            if ($style == '.' || $style == '..') {
                continue;
            }
            if (!file_exists("$directory/$style/style.txt")) {
                continue;
            }

            $names[] = $isCustom ? "_$style" : $style;
        }

        return $names;
    }

    /**
     * Returns an array of all the styles we know about
     *
     * @param string $customTemplates
     *            The directory where custom styles are stored. This will be an emprty string if there are no custom styles
     * @param boolean $isPrint
     *            Return print styles if TRUE
     * @return array An array of style data
     */
    static public function getStyles($customTemplates, $isPrint = false) {
        $names = array ();
        $styles = array ();
        /*
         * Do we need to create the styles data?
         */
        if ((!$isPrint && !isset(self::$styles)) || ($isPrint && !isset(self::$printStyles))) {

            $directory = EasyRecipe::$EasyRecipeDir;
            $directory = $isPrint ? "$directory/printstyles" : "$directory/styles";

            $names = array_merge($names, self::getStyleNames($directory));

            sort($names);
            foreach ($names as $name) {
                $style = self::getStyleData($name, $customTemplates, $isPrint);
                $styles[] = $style;
            }
        }
        if ($isPrint) {
            self::$printStyles = $styles;
        } else {
            self::$styles = $styles;
        }

        return $isPrint ? self::$printStyles : self::$styles;
    }
}

