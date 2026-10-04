# Changelog

## 4.2.0

- Fix issue in diphthongs conversion

## 4.1.2

- Added support for WooCommerce HPOS

## 4.1.1

- Minor security fix

## 4.1.0

- Added more polytonic characters

## 4.0.3

- Fix js dependency

## 4.0.2

- Optimize js/css files
- Fix php notice

## 4.0.1

- Removed warning

## 4.0.0

- **New Convertor for old posts and terms**
- Removed WP Background Processing dependency
- Added WP Rest API endpoints

## 3.4.0

- Added support for polytonic characters
- Added hook for modifying expressions

## 3.3.1

- Fixed error on upgrade

## 3.3.0

- Added wp-cli commands for getting (`wp agp get_options`) and updating the options( `wp agp update_options`) of the plugin
- Added support for multiple post types and taxonomies as arguments. Example: `wp agp convert --post_types=post,page`
- Minor UI update
- Fix: Reduced slug length on 3.2.0 version
- Added warning about reduced slug length when selecting post types and taxonomies for automatic conversion

## 3.2.0

- **Added the option to select which taxonomies and post types affected by automatic conversion**
- Changed hook for automatic conversion from sanitize_title to wp_unique_post_slug and wp_unique_term_slug

## 3.1.0

- **Added wp-cli support**

## 3.0.2

- Fixes 404 error on archive pages

## 3.0.0

- **Implemented asynchronous background conversion.**
- Added select all option
- Added panel for report of last conversion (duration, conversion percentage, errors)
- Added conversion progress notice
- Set default diphthongs option on advanced (affects only on new installations)

## 2.0.4

- Limited loading of styles and javascript only to AutoConvert's admin pages (Fixes to select2 issue)

## 2.0.3

- Copywriting review - Fixed grammar and syntax errors

## 2.0.2

- New installations' options were not initialized properly

## 2.0.1

- Fixed fatal error

## 2.0.0

- **Rewrite of plugin as object-oriented**
- Improved the UI of the dipthongs option at settings
- Fixed issue when passing slug that already exists
- Added notices for success and failure of conversion
- Added uninstall function that deletes plugin's options stored in your database
- Better copywriting

## 1.3.8

- Added support for two more letters, ΐ and ΰ. (Thanks to @princeofabyss)

## 1.3.6

- Removed estimated slug on conversion
- Minor UI improvements

## 1.3.5

- Added Greek translation

## 1.3.3

- Improved UI: Used WordPress Colors

## 1.3.2

- Fixed bug that didn't allow terms without posts to be converted

## 1.3.1

- Fixed bug that didn't allow taxonomies to be converted

## 1.3

- **Added conversion of old terms**
- Option to disable automatic transliteration of new posts and terms
- Improved UI with select2 for selects with multiple options and switches instead of checkboxes
- Improved UI by using post types' and taxonomies' labels

## 1.2.1

- Fixed minor bug on previous update

## 1.2

- **Added options for diphthongs conversion or not**

## 1.1

- **Added options page**
- Fixed minor issues

## 1.0

- Initial release
