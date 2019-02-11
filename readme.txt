=== AutoConvert Greeklish Permalinks ===
Contributors: Dimitris Mavroudis, d1m1tr1s_mav
Author link: https://mavrou.gr
Tags: greek, greeklish permalink, greeklish, slugs, permalinks, links, autoconvert, convert greek, agp
Requires at least: 3.8
Requires PHP: 5.2
Tested up to: 5.1
Stable tag: 3.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Convert Greek characters to Latin on all your site's permalinks instantly.

== Description ==

AutoConvert Greeklish Permalinks converts greek characters to latin in all permalinks. The plugin makes sure that every new permalink is in greeklish and offers the option to convert all the old links with greek characters to latin.

The plugin is very easy to use. After installation the plugin is already set to go. Every new post will be now converted to greeklish. 

If you want to convert all your older permalinks, go to Settings > Convert Greek Permalinks > Convert old posts/terms , select the post types and taxonomies you want to convert and click the "Convert Permalinks" button.

On Settings > Convert Greek Permalinks > Settings, you can also modify how the plugin converts the permalinks. For example, you can add the option to convert diphthongs or disable automatic conversion.

== Features ==
* Automatic permalink conversion for every new post and term.
* Conversion tool for older posts and terms.
* Option to disable automatic permalink conversion.
* Option on how to convert diphthongs.

== Screenshots ==
1. As simple as that. Write your title, draft, schedule or publish your post and the permalink is set.
2. Need to convert your old permalinks? We have you covered! Go to Settings > Convert Greek Permalinks > Convert old posts/terms and change all your permalinks at once.
3. Now you have the option to choose how diphthongs are converted.

== Installation ==

1. Install and activate your plugin like every other WordPress plugin.
2. After installation the permalink of every new post will be converted to greeklish.
3. You can adjust conversion and disable automatic conversion on 'Settings' > 'Convert Greek Permalinks'.
4. To convert old posts/terms, go to 'Settings' > 'Convert Greek Permalinks' > 'Convert old posts/terms', select the post types and taxonomies you want to convert and click the "Convert Permalinks" button.

== Upgrade Notice ==

Now you can convert as many old permalinks as you want, no matter the server restrictions!

== Changelog ==

= 3.0.0 =
* **Implemented asynchronous background conversion.**
* Added select all option
* Added panel for report of last conversion (duration, conversion percentage, errors)
* Added conversion progress notice
* Set default diphthongs option on advanced (affects only on new installations)

= 2.0.4 =
* Limited loading of styles and javascript only to AutoConvert's admin pages (Fixes to select2 issue)

= 2.0.3 =
* Copywriting review - Fixed grammar and syntax errors

= 2.0.2 =
* New installations' options were not initialized properly

= 2.0.1 =
* Fixed fatal error

= 2.0.0 =
* **Rewrite of plugin as object-oriented**
* Improved the UI of the dipthongs option at settings
* Fixed issue when passing slug that already exists
* Added notices for success and failure of conversion
* Added uninstall function that deletes plugin's options stored in your database
* Better copywriting

= 1.3.8 =
* Added support for two more letters, ΐ and ΰ. (Thanks to @princeofabyss)

= 1.3.6 =
* Removed estimated slug on conversion
* Minor UI improvements

= 1.3.5 =
* Added Greek translation

= 1.3.3 =
* Improved UI: Used WordPress Colors

= 1.3.2 =
* Fixed bug that didn't allow terms without posts to be converted

= 1.3.1 =
* Fixed bug that didn't allow taxonomies to be converted

= 1.3 =
* **Added conversion of old terms**
* Option to disable automatic transliteration of new posts and terms
* Improved UI with select2 for selects with multiple options and switches instead of checkboxes
* Improved UI by using post types' and taxonomies' labels

= 1.2.1 =
* Fixed minor bug on previous update

= 1.2 =
* **Added options for diphthongs conversion or not**

= 1.1 =
* **Added options page**
* Fixed minor issues

= 1.0 =
* Initial release
