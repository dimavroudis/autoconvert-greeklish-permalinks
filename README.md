![Autoconert Greeklish Permalinks](https://github.com/dimavroudis/AutoConvert-Greeklish-Permalink/blob/master/assets/banner-1544x500.png)

# AutoConvert Greeklish Permalinks

AutoConvert Greeklish Permalinks is the WordPress plugin that converts greek characters to latin in all permalinks. The plugin makes sure that every new permalink is in greeklish and offers the option to convert all the old links with greek characters to latin.

## Features 
* Convert automaticly the permalink of every new post and term.
* Convert all your older posts and terms with a click of a button.
* Choose how dipthongs are converted. 
* Developed to be friendly to developers with WP-CLI support and filter to modify the converion.


## Frequently Asked Questions

### How do I install it?

After you [install and activate](https://codex.wordpress.org/Managing_Plugins#Automatic_Plugin_Installation) your plugin like every other WordPress plugin, every new post permalink will be now converted to greeklish automatically.

### Can I configure the conversion?

On Settings > Convert Greek Permalinks > Settings, you can also modify how the plugin converts the permalinks. Currently you can:

* Enable or disable automatic conversion
* Choose which post types and taxonomies you want to be affected by automatic conversion
* Choose how the dipthongs will be converted

From version 3.4.0, the filter `agp_convert_expressions` has been added to allow you to make further changes.

```
function change_expressions( $expressions ) {
	// You can modify the rules of conversion
	$expressions['/[βΒ]/u'] ### 'g';
    return $expressions;
}
add_filter('agp_convert_expressions', 'change_expressions' );
```

### How do I convert old permalinks? 

If you want to convert all your older permalinks, go to Settings > Convert Greek Permalinks > Convert old posts/terms , select the post types and taxonomies you want to convert and click the "Convert Permalinks" button.

### Does it support WooCommerce? 

Yes. It supports all custom post types or taxonomies, including Products, Product Categories and Product Tags of WooCommerce.

### Does it support WP-CLI? 

Yes! As of 3.1 version, wp-cli commands have been included. You can convert all your permalinks with `wp agp convert` or just check how many greek permalinks you have with `wp agp check`. Use `wp help agp {command}` to learn more about how to use them.

## Installation ##

1. [Install and activate](https://codex.wordpress.org/Managing_Plugins#Automatic_Plugin_Installation) your plugin like every other WordPress plugin.
2. After installation the permalink of every new post will be converted to greeklish.
3. You can adjust conversion and disable automatic conversion on 'Settings' > 'Convert Greek Permalinks'.
4. To convert old posts/terms, go to 'Settings' > 'Convert Greek Permalinks' > 'Convert old posts/terms', select the post types and taxonomies you want to convert and click the "Convert Permalinks" button.

## Changelog ##

### 4.0.1 
* Removed warning

### 4.0.0 
* **New Convertor for old posts and terms**
* Removed WP Background Processing dependency
* Added WP Rest API endpoints

### 3.4.0 
* Added support for polytonic characters
* Added hook for modifying expressions

### 3.3.1 
* Fixed error on upgrade

### 3.3.0 
* Added wp-cli commands for getting (`wp agp get_options`) and updating the options( `wp agp update_options`) of the plugin
* Added support for multiple post types and taxonomies as arguments. Example:  `wp agp convert --post_types=post,page`
* Minor UI update
* Fix: Reduced slug length on 3.2.0 version
* Added warning about reduced slug length when selecting post types and taxonomies for automatic conversion

### 3.2.0 
* **Added the option to select which taxonomies and  post types affected by automatic conversion**
* Changed hook for automatic conversion from sanitize_title to wp_unique_post_slug and wp_unique_term_slug

### 3.1.0 
* **Added wp-cli support**

### 3.0.2 
* Fixes 404 error on archive pages

### 3.0.0 
* **Implemented asynchronous background conversion.**
* Added select all option
* Added panel for report of last conversion (duration, conversion percentage, errors)
* Added conversion progress notice
* Set default diphthongs option on advanced (affects only on new installations)

### 2.0.4 
* Limited loading of styles and javascript only to AutoConvert's admin pages (Fixes to select2 issue)

### 2.0.3 
* Copywriting review - Fixed grammar and syntax errors

### 2.0.2 
* New installations' options were not initialized properly

### 2.0.1 
* Fixed fatal error

### 2.0.0 
* **Rewrite of plugin as object-oriented**
* Improved the UI of the dipthongs option at settings
* Fixed issue when passing slug that already exists
* Added notices for success and failure of conversion
* Added uninstall function that deletes plugin's options stored in your database
* Better copywriting

### 1.3.8 
* Added support for two more letters, ΐ and ΰ. (Thanks to @princeofabyss)

### 1.3.6 
* Removed estimated slug on conversion
* Minor UI improvements

### 1.3.5 
* Added Greek translation

### 1.3.3 
* Improved UI: Used WordPress Colors

### 1.3.2 
* Fixed bug that didn't allow terms without posts to be converted

### 1.3.1 
* Fixed bug that didn't allow taxonomies to be converted

### 1.3 
* **Added conversion of old terms**
* Option to disable automatic transliteration of new posts and terms
* Improved UI with select2 for selects with multiple options and switches instead of checkboxes
* Improved UI by using post types' and taxonomies' labels

### 1.2.1 
* Fixed minor bug on previous update

### 1.2 
* **Added options for diphthongs conversion or not**

### 1.1 
* **Added options page**
* Fixed minor issues

### 1.0 
* Initial release
