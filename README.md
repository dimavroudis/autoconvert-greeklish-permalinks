![Autoconert Greeklish Permalinks](https://github.com/dimavroudis/AutoConvert-Greeklish-Permalink/blob/master/.wordpress-org/banner-1544x500.png)

[![WordPress.org rating](https://img.shields.io/wordpress/plugin/rating/autoconvert-greeklish-permalinks?label=WordPress.org%20rating)](https://wordpress.org/plugins/autoconvert-greeklish-permalinks/)
[![WP compatibility](https://plugintests.com/plugins/wporg/autoconvert-greeklish-permalinks/wp-badge.svg)](https://plugintests.com/plugins/wporg/autoconvert-greeklish-permalinks/latest)
[![PHP compatibility](https://plugintests.com/plugins/wporg/autoconvert-greeklish-permalinks/php-badge.svg)](https://plugintests.com/plugins/wporg/autoconvert-greeklish-permalinks/latest)
![Minimum PHP version](https://img.shields.io/wordpress/plugin/required-php/autoconvert-greeklish-permalinks)
![Last updated](https://img.shields.io/wordpress/plugin/last-updated/autoconvert-greeklish-permalinks)

AutoConvert Greeklish Permalinks is the WordPress plugin that converts greek characters to latin in all permalinks. The plugin makes sure that every new permalink is in greeklish and offers the option to convert all the old links with greek characters to latin.

## Features

- Convert automaticly the permalink of every new post and term.
- Convert all your older posts and terms with a click of a button.
- Choose how dipthongs are converted.
- Developed to be friendly to developers with WP-CLI support and filter to modify the converion.

## Frequently Asked Questions

### How do I install it?

After you [install and activate](https://codex.wordpress.org/Managing_Plugins#Automatic_Plugin_Installation) your plugin like every other WordPress plugin, every new post permalink will be now converted to greeklish automatically.

### Can I configure the conversion?

On Settings > Convert Greek Permalinks > Settings, you can also modify how the plugin converts the permalinks. Currently you can:

- Enable or disable automatic conversion
- Choose which post types and taxonomies you want to be affected by automatic conversion
- Choose how the dipthongs will be converted

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

## Installation

1. [Install and activate](https://codex.wordpress.org/Managing_Plugins#Automatic_Plugin_Installation) your plugin like every other WordPress plugin.
2. After installation the permalink of every new post will be converted to greeklish.
3. You can adjust conversion and disable automatic conversion on 'Settings' > 'Convert Greek Permalinks'.
4. To convert old posts/terms, go to 'Settings' > 'Convert Greek Permalinks' > 'Convert old posts/terms', select the post types and taxonomies you want to convert and click the "Convert Permalinks" button.

## Development and releases

The `develop` branch is used for development and pull-request testing. Releases are promoted to `master`.

See [TESTING.md](./TESTING.md) for automated checks and the staging end-to-end test checklist.

### Before a release

1. Merge the finished changes into `develop` through a reviewed pull request and make sure the PHPUnit workflow passes.
2. Check the latest stable WordPress and WooCommerce releases. Use staging to validate the plugin against them before claiming support; do not update compatibility metadata based only on a release announcement.
3. After validation, update `Tested up to` in `readme.txt` to the latest WordPress version tested, and `WC tested up to` in `auto-gr-permalinks.php` to the WooCommerce version tested. Keep these values at the highest versions actually verified.
4. Update the plugin `Version` header and `AGP_VERSION` in `auto-gr-permalinks.php`, and the `Stable tag` in `readme.txt` to the same new numeric plugin version (for example, `4.3.0`). Do not include a `v` prefix in plugin or WordPress.org metadata.
5. Build the generated admin assets with `npm ci` followed by `npm run build`, and review the resulting changes.
6. Promote the reviewed release changes from `develop` to `master`.

### Publishing

A push to `master` runs the build-and-tag workflow, which reads the numeric plugin header version (for example, `4.3.0`) and creates/pushes the corresponding `v`-prefixed Git tag (for example, `v4.3.0`). The `v` prefix is only for GitHub; WordPress.org plugin metadata stays numeric. The push also updates the WordPress.org readme and plugin assets. Check that these workflows complete successfully.

Publish a GitHub Release for that `vX.Y.Z` tag to deploy the plugin code to WordPress.org. Publishing a prerelease does not deploy it. The deployment workflow requires the GitHub Actions secrets `SVN_USERNAME` and `SVN_PASSWORD`, and the repository variable `SLUG`.

After deployment, verify the plugin version, readme, assets, and downloadable package on WordPress.org. Do not push another release to `master` with a version whose Git tag already exists.

## Changelog

See [CHANGELOG.md](./CHANGELOG.md) for the release history.
