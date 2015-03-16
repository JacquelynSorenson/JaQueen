=== Easy Recipe ===
Contributors: Jayce53
Tags: recipe, seo, hrecipe, Recipe View, microformatting, easy recipe, rich snippet, microdata
Requires at least: 3.6
Tested up to: 4.0.1
Stable tag:  3.2.2929
License: GPLv2 or later

EasyRecipe makes it easy to enter, format and print recipes, as well as automagically doing the geeky stuff needed for Google's Recipe View.

== Description ==

The most fully featured Wordpress recipe plugin that doesn't require a degree in geek!

EasyRecipe makes recipe entry a breeze, with features like cut and paste, auto conversion of your plain text recipe posts, live custom formatting, Google Recipe View formatting and preview button, automatic ratings, conversion from other recipe plugins like ZipList, RecipeSEO and Recipe Card.

Adding a recipe and getting the [Recipe View](http://googleblog.blogspot.com/2011/02/slice-and-dice-your-recipe-search.html) microdata correct is not only time consuming but it's also pretty geeky and most cooks prefer to cook and share, not code webpages.

Enter EasyRecipe.

EasyRecipe gives you all the advantages of microdata, without any of the messy coding. We've done all the geeky work for you.

Enter your post, upload your photos and enter your recipe.  EasyRecipe formats it all and allows your visitors to view your recipe in the format YOU create - there's no messy CSS or PHP editing required.

And of course there's a print button that allows your visitors to print your recipes in a format you can change to be just how you want it.

It's quick. It's easy. The recipes look great.  Your recipes will have ratings and print features and most importantly you have a much better chance for your recipes showing up in Google's Recipe View search results.

To convert an existing plain text recipe post to EasyRecipe, edit the post, select the Ingredients and Instructions with your mouse and click the EasyRecipe icon. As long as there's an appropriate header above the ingredients and instructions, EasyRecipe can import your post directly.

To convert a ZipList, RecipeSEO, Recipress, GetMeCooking or Recipe Card post to EasyRecipe, edit the post and click the EasyRecipe icon. That's it!  How easy was that?

Requires PHP 5+

== Installation ==

You can download and install the EasyRecipe plugin using the built in WordPress plugin installer. If you download the EasyRecipe plugin manually, just upload it to your Wordpress plugins directory and unzip it.

Activate the EasyRecipe plugin in the "Plugins" admin panel using the "Activate" link.

To use the plugin, click the EasyRecipe button (the chef icon) on the "Add Post" or "Edit Post" pages, right next to the editor formatting buttons. Then enter (or cut and paste) your recipe into EasyRecipe.  Any ingredient or instruction line beginning with an exclamation mark (!) is formatted as a heading. Click the "Add Recipe" button and it will save your recipe and insert it into your blog post.

== Frequently Asked Questions ==

= Why do you put a placeholder into my Edit Post page, instead of the actual recipe? =

Because of the way the WordPress editor works, if you make changes to your recipe using the html editor, you're at risk of messing up the microdata and then you'll be upset with us.  We want you to show up in Google so EasyRecipe requires that you make changes by clicking the placeholder and changing your previous entry.

= Why Is EasyRecipe Better? =

Because not only is EasyRecipe an easy way to let the search engines get your recipes in the format that helps you get the most exposure for your recipes, it really IS easy to use.  Simply cut and paste your recipe in EasyRecipe and hit publish. The options on our EasyRecipe screen are the ones Google looks for.

= How can I request a feature to be added in future versions of EasyRecipe Plugin? =

You can [contact us](http://www.easyrecipeplugin.com/support.php) with your requests.  We may not be able to implement every request, but we want to make this the easiest recipe plugin for everyone to use.

= Can You Convert Your Recipes From Plain Text or Another Plugin? =

We CAN but it all depends on what's in the text and/or what other things another plugin has added.  We can't guarantee that you won't need to do a little additional manual editing, but EasyRecipe can usually do 95% of the work for you.

= Where Can I Get Help? =

If you have comments, questions or problems, we want to help.

The best way to contact us is from the Support tab in the EasyRecipe settings.

You can also visit [EasyRecipe Support](http://support.easyrecipeplugin.com/)

== Screenshots ==
1. Simple recipe entry. You don't have to pre-define ingredients, enter quantities separately or worry about taxonomies
2. You can optionally enter nutrition data if you want. If you leave it out, EasyRecipe just suppresses that section on your post.
3. You can enter optional notes. EasyRecipe is smart enough to hide the notes section on your post if you don't have any.
4. You can even cut and paste a plain text recipe and have EasyRecipe convert it for you.
5. Just one of the display styles  available. You can choose from a range of pre-defined styles, or create your own (in [EasyRecipe PLUS](http://www.easyrecipeplugin.com))
6. If the style isn't quite what you wanted, you can tweak it with EasyRecipe's "Live Formatting". Change the colors, spacing, border, fonts and more right on your blog and see exactly how it will look as you change it. No fussing with CSS or other geeky stuff.
7. Set up EasyRecipe just how you want it so it's easy to enter your recipes.
8. Select from a range of pre-defined recipe templates (or make your own with [EasyRecipe PLUS](http://www.easyrecipeplugin.com)). All of the templates can be tweaked with EasyRecipe's Live Formatting
9. We take support seriously. You can even add a ticket to our support database right from your blog admin.

== Changelog ==
= 3.2.2929 =
* Enhancement: Added BigOven save button
* Bug fix: Fix Recipe Card automatic conversion (Plus version)

= 3.2 Build 2925 =
* Enhancement: Added BigOven save button (Plus version)
* Bug fix: Don't try to insert duplicate taxonomy terms if multiple recipes on a post use the same cuisine/course
* Update: Attempt to recover corrupted recipes in the recipe editor

= 3.2 Build 2885 =
* Enhancement: Enable Recipage recipe conversion
* Enhancement: Display Recipage recipes as EasyRecipe recipes (Plus version)
* Update: Better handling of shortcodes within a recipe
* Update: Handle links in instruction section headings
* Update: Don't open formatting popup if a link in a recipe is clicked when logged in as admin
* Update: Cater for sites that have huge numbers of registered users (Plus version)
* Update: Remove Ziplist Save button functionality (Plus version)
* Bug fix: Fix microformatting on Ziplist style when times missing and on nutrition data (Plus version)
* Bug fix: Supress times display when times missing on Ziplist recipes (Plus version)
* Bug fix: Live Formatting handling of quoted font names
* Bug fix: Improve popup window stacking handling on recipe entry

= 3.2 Build 2802 =
* Enhancement: Generate taxonomies that can be used by the EasyIndex plugin
* Update: Bring version numbers into line with EasyRecipe Plus
* Update: Changes for Wordpress 4.0
* Update: Use the more correct "ratingCount" rather than "reviewCount"
* Update: re-label "Serves" as the more correct "Yield"
* Bug fix: Self ratings weren't being displayed in some circumstances

= 3.2 Build 1311 =
* Enhancement: Display Get Me Cooking recipes without the need to convert (Plus version)
* Enhancement: Display Recipe Card recipes without the need to convert (Plus version)
* Enhancement: Display WP Ultimate Recipe recipes without the need to convert (Plus version)
* Enhancement: Strip Jetpack and TinyMCE spellcheck plugin data before editing recipes (Plus version)
* Update: Changes to Google live snippet preview: timeout and format (Plus version)
* Bug fix: Some Live Formatting CSS saved with earlier versions crashed Live Formatting
* Bug fix: Captions on images inside recipes were not processed in some circumstances


= 3.2 Build 1310 =
* Bug fix: Oops again! Fix Live Formatting sections not being displayed

= 3.2 Build 1309 =
* Bug fix: Oops! The link fix for IE11 in the previous version broke link inserts outside recipes.

= 3.2 Build 1308 =
* Update: Strip blank lines from Ziplist ingredients and instructions (Plus version)
* Update: Allow for non-breaking spaces in [img], [url] shortcodes
* Update: Remove some old unused code
* Update: Standardise Live Formatting popup layout across more themes
* Update: Changes to handle themes that globally set &lt;div&gt; spacing (recipe entry)
* Update: Handle Genesis grid items better (Plus version)
* Bug fix: Fix recipe displaying as a shortcode when some other plugins present
* Bug fix: Process [br] shortcodes in Ziplist recipes correctly (Plus version)
* Bug fix: Remove extra "!important" from Live Formatting CSS
* Bug fix: Strip slashes from quoted extra CSS
* Bug fix: Remove spurious &lt;a&gt; tag when addding links in IE11

= 3.2 Build 1303 =
* Bug fix: Handle embedded links properly in Ziplist recipes (Plus version)
* Bug fix: Suppress rating display properly if ratings disabled or no ratings present
* Bug fix: Handle nested formatting shortcodes better
* Bug fix: Do external shortcode processing for shortcodes in recipes

= 3.2 Build 1300 =
* Enhancement: Display Ziplist recipes without the need to convert (Plus version)
* Enhancement: Added "Self Rating" option
* Bug fix: Fix popup mask overlaying Settings page on WP versions prior to 3.9

= 3.2 Build 1294 =
* Enhancement: Added option to suppress warning when editor switched to Text mode
* Bug fix: Fixed missing photo microdata under WP 3.9
* Bug fix: Removed photo processing that caused the recipe print to crash on some servers

= 3.2 Build 1290 =
* Enhancement: Convert italic and bold formatting and image links in Ziplist recipes
* Enhancement: Use latest WP media manager
* Enhancement: Get caption, alt text and title data for images inserted into recipes (Plus version)
* Update: Changes for Wordpress 3.9

= 3.2 Build 1284 =
* Update: Changed support/diganostics to use the EasySupport plugin

= 3.2 Build 1283 =
* Update: Changes for Wordpress 3.9
* Enhancement: Use total time when converting from Ziplist if there is no prep or cooking time
* Bug fix: Javascript error on custom post pages that don't have an editor (e.g. Soliloquy slider, EasyIndex)
* Bug fix: Minor display error on Ziplist conversion popup
* Bug fix: Fix [br] shortcodes on print
* Bug fix: Fix grey overlay on popup in settings
* Bug fix: Print showing blank page in some circumstances

= 3.2 Build 1275 =
* Bug fix: Filter excerpt option messed up formatting on some themes in some circumstances

= 3.2 Build 1272 =
* Enhancement: Cleaner display of settings page
* Enhancement: Add EasyRecipe button on text editor toolbar
* Enhancement: Add option to filter non-display items from excerpts
* Enhancement: Suppress empty Ingredient and Instruction sections
* Bug fix: Error popups not being displayed on top
* Bug fix: Diagnostics sent to support did not include settings
* Bug fix: Style setting was lost when saved from Live Formatting and permalinks aren't used
* Bug fix: Fractions not converted to HTML entities on print
* Bug fix: Display diagnostics data when no permalinks on Windows servers

= 3.2 Build 1271 =
* Bug fix: Recipe entry:  Image insertion and save messed up by featured images

= 3.2 Build 1269 =
* Tested with WP 3.8
* Workaround for WP bug that generates invalid HTML for multiple line breaks
* Recipe editor now recognises post thumbnails (featured image)
* Added Author, Recipe type, Cuisine and Yield to Live Formatting on Tastefully Simple styles
* Fixed PHP warning on diagnostics
* Confirm when closing a recipe entry popup withoput saving
* Fix for popups opening behind some themes' elements
* Fixed incompatiblity with Pinterest Pin It for Images plugin that disabled Print and Save buttons
* Convert 3/8 to HTML enitity fraction
* Clean up 16 pixel chef icon

= 3.2 Build 1263 =
* Tested with WP 3.7.1
* Added option for extra <head> content on print
* Fix for "grey overlay" on recipe entry caused by some other plugins (e.g. Easy Rotator)
* Fix for secure admin URLs
* Fix for jQuery UI 1.10 differences in Live Formatting

= 3.2 Build 1255 =
* Workaround for bad "title" shortcode replacement done in some themes
* Tested with WP 3.6

= 3.2 Build 1251 =
* Tested with WP 3.5.2
* Retain line breaks in Notes
* Workaround for tinyMCE/Chrome bug that caused notes and some nutrition fields to get dropped after an autosave
* Better protection from inadvertent delete of recipe data in post edit

= 3.2 Build 1249 =
* Fix for bad ratings

= 3.2 Build 1246 =
* Fix error on save

= 3.2 Build 1244 =
* Added ReciPress conversion
* Add Get Me Cooking conversion
* Added custom labels for times
* Workaround for javascript library incompatibility for Bootstrap based themes
* Improved the efficiency of ratings retrieval
* Added live Google snippet test (Plus version)
* Added custom labels for guest post pages (Plus version)
* Added import from MacGourmet and Yummy Soup (Plus version)

= 3.2 Build 1230 =
* Added import from Paprika recipes (Plus version)
* Added import from Meal-Master recipes (Plus version)
* Added conversion from Recipe Card recipes
* Added Recipe Card to the plugins Fooderific recognizes
* Added underlining to basic formatting
* Styles with images changed to better handle responsive themes
* Javascript workarounds for themes that hijack jQuery.widget (e.g. Nevada)
* Workaround for glitch in the Wordpress SEO plugin
* Reduced minimum capability for style changes from edit_plugins to edit_theme_options
* Fix missing image markup on Provencale style


= 3.2 Build 1226 =
* Tested with WP 3.5.1
* Supress photo section on the Celebration style if no image
* Display error message if diagnostic send fails
* Only show "Format" link on the admin toolbar if user has "edit_plugins" capability
* Fix for 7/8ths display
* Fix for Live Formatting on print
* Fix for Live Formatting with theme "Camber"

= 3.2 Build 1215 =
* Improvements to the Fooderific scan
* Added custom labels for Print and Ziplist Save
* Workaround for TinyMCE non-editable plugin bug
* Fix for the Modish style that had incorrect nutrition markup
* Fix for some styles not marking up images correctly
* Fix for print/diagnostics where there's a 404 handler
* Fix for custom notes header in old Legacy style
* Fix for special characters in Notes
* Prevent Wordpress stripping times and images on scheduled posts

= 3.2 Build 1199 =
* Added the Fooderific.com interface
* Fix print for sites that are not installed in the root directory
* Fix print for browsers that hijack the 404 page
* Fix fisplay of non-ASCII characters in custom labels
* Fixes for WP 3.5 compatibility

= 3.1.09 =
* Converting from plain text now recognises custom labels as recipe markers (Ingredients, Instructions and Notes)
* Styles now override a theme's custom background on bullets
* Changes to better handle badly behaved themes and plugins
* Clean up "Tastefully Simple" style when there are no times present
* Fix for glitches when previewing
* Fix for Live Formatting resetting formats if a section was missing in the receipe used to format
* Changes to better handle broken Mastercook import files (Plus)

= 3.1.08 =
* Added Ziplist save button option (Plus version)
* Added configurable title on guest post details page (Plus version)
* Added "Force jQuery library load" option to handle badly behaved themes and plugins
* Allow blank custom labels
* Workaround for Internet Explorer bugs when displaying errors on the Settings page
* Various CSS tweaks to better handle more themes
* Fix for print and preview pages when W3 Total Cache Object cache is enabled
* Fix headings displaying when they shouldn't when multiple recipes in a single post (Plus version)
* Fix for custom labels for Ingredients and Instructions on the Legacy display style
* Fix for apostrophes and quotes in settings

= 3.1.07 =
* Only display warning once when switching to HTML editor
* Fix previews

= 3.1.05 =
* Fix recipe updates in Chrome and Safari

= 3.1.04 =
* Styles can now be trialled on previews and blogs not using permalinks
* Added EasyRecipe entry for editors, authors and contibutors
* Workaround to pick up all instructions on recipes that have been manually modified and have a non standard EasyRecipe structure
* Minor tweak or the Tastefully Simple print style
* Fixed print for blogs that don't use permalinks
* Fix for Notes Heading not opening in live formatting for the Celebration style
* Fix excerpt and other fields inadvertently being written on a save from the HTML editor

= 3.1.03 =
* Fix print not working on some blogs
* Fix weird stuff happening when W3 Total Cache installed
* Workaround for recipes that have been manually modified and have a non EasyRecipe standard structure
* Made live formatting CSS more specific so themes are less likely to override custom formatting

= 3.1.02 =
* Fix for themes that ignore modification of posts by plugins and displayed unformatted recipes (Thanks Nicole!)
* Fix for print on blogs with non-root Wordpress installs
* Fixed the ratings markup on some styles
* Fix is_file() warning when open_basedir is restricted

= 3.1.01 =
* Fix print redirect being broken by automatic updates
* Fix minor glitches in style templates

= 3.1 =
* This is a major update!
* Uses microdata (schema.org) instead of microformatting (hrecipe)
* Added template based output
* Choose from a range of display and print styles
* Added "Cut and paste" plain text recipe entry
* Added Swoop integration
* Added Default Author
* Added Cuisine type
* Added Recipe type defaults
* Added Trans Fat and Sodium to nutrition data
* Added "Disable ratings"
* Option to convert fractions (e.g. 1/2 becomes &frac12;)
= Available in EasyRecipe PLUS: =
* Multiple recipes per page/post
* Select the image to use as the "main" image
* Easy insertion of images and links in recipes
* Guest posts
* Import from cookbooks
* Custom recipe templates

= 2.2.7 =
* Fix broken formatting link in admin menu bar
* Fix bad formatting on font change dropdowns
* Changes for new EasyRecipe site

= 2.2.6 =
* Fix for broken ratings on some themes
* Fix incompatibility with some other plugin "Tools"

= 2.2.5 =
* Fix for the Live Formatting window opening too high on the page on small screens

= 2.2.4 =
* Fix corrupt css images

= 2.2.3 =
* Fixed some incompatibilities with some other themes/plugins

= 2.2.2 =
* Added ZipList conversion
* Updated RecipeSEO conversion
* Added meta tag for MyBigRecipeBox.com crawler permissions
* Fixed some options not being correctly saved
* Fix "Recipe Details" formatting not saved
* Fix "Carbs" label not displaying

= 2.2.1 =
* Fix My Big Recipe Box ping (hung on publish)

= 2.2.0 =
* Fix fatal error in extra CSS processing

= 2.1.9 =
* Fix changelog

= 2.1.8 =
* Updated for WP 3.3.1
* Fixed invalid time formats
* Fixed defaults not handled correctly in WP 3.3.1
* Added "noindex" to print page
* Added &lt;div&gt; wrappers around Ingredients and Instructions for easier own formatting
* No longer strips leading non alphanumerics from pasted ingredient lists

= 2.1.7 =
* Added the ability to select "transparent" as a color
* Fix occasional PHP error when linkback removed
* Fix for Notes and Nutrition format customizations not saved


= 2.1.6 =
* Fix problem when an image in the EasyRecipe itself is used as the microformat photo

= 2.1.5 =
* Allow any photo to be used as the microformat photo
* Added mailing list subscription option
* Fixed some problems with excerpts
* Fixed Print loading the wrong url

= 2.1.4 =
* Fix for excerpts not displaying correctly

= 2.1.3 =
* Fix for options not being saved correctly

= 2.1.2 =
* Fix character encoding - fixes weird characters being displayed
* Correctly identify EasyRecipe posts - fixes ratings on non-EasyRecipe posts
* Check for DOMDocument existence at plugin registration
* Disable PHP errors for DOM parse and manipulation

= 2.1.1 =
* Fix for fatal error on corrupted posts
* Fix for jQuery noconflict conflict

= 2.1 =
* Added comprehensive formatting options
* Added embedded image and link capability
* Extended the template customization for non-english languages or other specific text
* Fixed occasional loss of print capability in WP 3.2
* Fixed occasional loss of time microformatting in WP 3.2

= 2.0 =
* Beta release of version 2

= 1.3 =
* Added the ability to replace labels with language or other specific text
* Fixed an issue of the Print button sometimes not being displayed
* Fix Cholesterol typo

= 1.2.4 =
* Fix EasyRecipe not being inserted into the post on some browsers when there is no initial editor content and/or the cursor is not in the editor body
* Only ping MyBigRecipeBox.com if the published post contains an EasyRecipe
* Fix typo on RecipeSEO posts conversion

= 1.2.3 =
* Fix for images that somehow got corrupted in 1.2.2

= 1.2.2 =
* Added diagnostics
* Fixed the removal of the linkback when requested

= 1.2.1 =
* Fix for settings not being stored correctly

= 1.2 =
* Added blog title and URL to the bottom of the printed recipe
* Added a button to the html editor toolbar to prevent confusion
* Added checks for valid color values in settings to prevent the recipe border and background not displaying correctly when colors were invalid
* Added a workaround for a bug in the WP editor which inconsistently removes empty HTML tags. This sometimes resulted in the total cooking time not being recognised by Google
* Only display stars on comments if the comment actually has a non-zero rating
* Removes the ratings microformat information if there are no ratings to keep the Google test page happy

= 1.1.2 =
* Fix for image not printing on servers with allow_url_fopen off

= 1.1.1 =
* Only accept and display comment ratings if the post is an EasyRecipe

= 1.1 =
* Fixed a problem when Wordpress autosave adds spurious paragraphs

== Upgrade Notice ==
= 2.1.3 =
Various bug fixes for version 2

