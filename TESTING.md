# Testing AutoConvert Greeklish Permalinks

This guide describes the automated checks and the manual end-to-end checks to perform before a release. Run the live WordPress checks on a staging site, never on production.

## 1. Protect the site and its data

- Use a staging clone or disposable WordPress installation with a separate database and files.
- Take a fresh backup before installing or updating the plugin. Confirm you can restore it.
- Disable outgoing email, payment processing, webhooks, and other production integrations on staging. Use sandbox credentials if an integration is needed.
- Use test content only. Bulk conversion changes stored post and term slugs, which can change public URLs. Do not run it against production data as a test.
- Record the WordPress, PHP, theme, and relevant plugin versions used, especially when checking WooCommerce compatibility.

## 2. Validate current WordPress and WooCommerce compatibility

Before each release, identify the latest stable WordPress and WooCommerce releases from their official release information. Test on staging with those versions before updating the compatibility claims:

- [ ] Test the plugin on the latest stable WordPress release. If the plugin claims an older minimum WordPress version, also test that minimum when practical.
- [ ] If WooCommerce compatibility is claimed, test with the latest stable WooCommerce release on a WordPress/PHP combination WooCommerce supports. Test the plugin's relevant product, product category, and product tag flows.
- [ ] Record the exact WordPress, WooCommerce, and PHP versions and the test outcome.
- [ ] Only after successful validation, update `Tested up to` in `readme.txt` and `WC tested up to` in `auto-gr-permalinks.php` to the versions actually tested. Do not advance either field merely because a newer release exists.
- [ ] If the latest release fails or cannot be tested, document the limitation and do not claim that version as tested.

## 3. Run automated checks

The unit workflow runs PHPUnit on PHP 7.4 (the declared minimum) and the latest stable PHP release for pushes and pull requests targeting `develop`. The separate Playwright workflow runs on the same daily condition and is not a push/PR gate. Both workflows run before release tagging; confirm they pass before manual acceptance testing.

For a local run with dependencies installed:

```sh
composer install
vendor/bin/phpunit
npm ci
npm run build
```

The PHPUnit suite includes converter checks for diphthong modes and the `agp_convert_expressions` filter, and query checks for post/term conversion. Use `npm run test:e2e` for the Playwright suite.

### Playwright end-to-end tests

The E2E suite requires Node.js 24.18.0, npm 11.16.0, and Docker Desktop. It uses the local wp-env site at `http://localhost:8888`; never point it at staging or production. Install the browser once, then start the disposable site and run the tests:

```sh
npm ci
npx playwright install chromium
npm run wp-env:start
npm run test:e2e
npm run wp-env:stop
```

The suite covers the admin converter and settings UI, the authenticated `agp/v1` REST endpoints, and WP-CLI empty-selection validation. E2E fixtures use a unique run marker, create legacy Greek slugs only in the disposable wp-env database, and remove only their own posts and terms after each test. The separate wp-env demo records are retained.

The Playwright config runs tests serially because each interface shares the same database. On failure, screenshots and traces are written under `tests/e2e/output/`; CI uploads these diagnostics and stops wp-env even if a test fails.

## 4. Build and install the branch on staging

To test the exact branch rather than the currently published WordPress.org version:

1. Push the branch to GitHub.
2. Open **Actions → Build release zip → Run workflow**. Select the branch under **Use workflow from**, then run it.
3. Wait for the workflow to finish successfully and download its ZIP artifact.
4. On staging, install or update the plugin using **Plugins → Add New → Upload Plugin**. Activate it and confirm the expected plugin version appears in the Plugins screen.
5. If the workflow is unavailable or the artifact is missing, stop and resolve that before testing. Do not publish a GitHub Release just to get a test ZIP: a published, non-prerelease release deploys to WordPress.org.

Alternatively, build locally with `npm ci` and `npm run build`, then package the plugin using the repository's packaging workflow or tooling. Install the resulting package on staging, not production.

## 5. Prepare controlled test content

On staging, create:

- A Greek-titled draft post, for example `Καλημέρα Αθήνα`.
- A page and, if available, a test custom post type item with Greek titles.
- A Greek category and tag, plus terms in a test custom taxonomy if one is available.
- A second post or term whose intended Greeklish slug is already in use, to test collision handling.
- For the batch-progress check, at least 101 posts and/or terms with Greek characters in their slugs. Give them a recognizable prefix so they can be identified and cleaned up afterwards.

Keep a short inventory of each record's type, original slug, expected result, and final result. Do not use valuable content for these tests.

## 6. Manual acceptance checklist

### Installation and basic conversion

- [ ] Plugin activates without a fatal error or unexpected PHP warning.
- [ ] The plugin's admin page opens; settings and the old-permalink converter are available.
- [ ] Save a new draft with a Greek title. Verify its generated slug is Latin/Greeklish, then edit and save it again to check the slug remains stable unless deliberately changed.
- [ ] Repeat with a page and any relevant custom post type.
- [ ] Create a category/tag and any relevant custom taxonomy term with a Greek name; verify its slug is converted.
- [ ] Try uppercase and lowercase Greek, accented and polytonic characters where relevant, Latin text mixed with Greek, spaces, punctuation, and numerals. Confirm the result is readable and does not contain unexpected replacement characters.
- [ ] Create a title whose converted slug conflicts with an existing slug. Verify WordPress/plugin uniqueness behavior produces a usable unique slug and neither record is overwritten.

### Settings and automatic conversion scope

- [ ] In **Settings → Convert Greek Permalinks → Settings**, disable automatic conversion, save, and reload the page to confirm the setting persisted.
- [ ] Create a new test post and term while automatic conversion is disabled. Confirm the plugin does not apply its Greeklish conversion.
- [ ] Re-enable automatic conversion and select only a subset of post types and taxonomies. Save, reload, and confirm the selections persisted.
- [ ] Create one item in a selected type/taxonomy and one in an unselected type/taxonomy. Confirm only selected content is automatically converted.
- [ ] Change the diphthong option, save, and create a fresh test item each time. Verify a known pair changes according to the chosen mode. For example, the unit tests expect `αυτό` to become `afto` with advanced conversion enabled and `ayto` with it disabled.

### Convert existing posts and terms

- [ ] In **Settings → Convert Greek Permalinks → Convert old posts/terms**, choose only the test post type(s) and taxonomy/taxonomies containing the prepared test records.
- [ ] Click **Convert Permalinks**. Confirm the progress/status message is understandable, controls are disabled during conversion, and the operation completes with a success message.
- [ ] Inspect records in WordPress admin and on their front-end URLs. Confirm selected Greek slugs were converted and selected already-Greeklish slugs were not needlessly changed.
- [ ] Confirm unselected post types and taxonomies were left unchanged.
- [ ] Run the conversion again on the same selection. It should complete cleanly with no remaining Greek slugs to convert.
- [ ] With the prepared set of at least 101 records, confirm progress completes across multiple requests and the final converted count agrees with the records that needed conversion.
- [ ] Try the converter with no post types or taxonomies selected. Confirm it reports a useful validation message and does not change content.
- [ ] If a request fails during a test, confirm an error is shown and the controls become usable again. Check the browser Network panel and WordPress/PHP logs for the underlying error.

Changing a slug changes the URL. Check the site's expected redirect/404 behavior for old URLs; do not assume the plugin creates redirects. The conversion test itself should confirm the new URL loads and that unrelated URLs still work.

### WP-CLI (if available)

Run commands against the staging site's WP-CLI environment and restrict them to test records' types/taxonomies:

```sh
wp agp check --post_types=post,page --taxonomies=category,post_tag
wp agp convert --post_types=post,page --taxonomies=category,post_tag
wp agp check --post_types=post,page --taxonomies=category,post_tag
```

- [ ] The first `check` reports the expected remaining Greek-slug counts.
- [ ] `convert` reports completion and does not fail on the test records.
- [ ] The final `check` reports no remaining matching Greek slugs in those selected types/taxonomies.
- [ ] An invalid post type or taxonomy is reported as an error rather than silently ignored.

Use only the test types/taxonomies above; do not run an unrestricted conversion on a staging clone containing content you do not intend to modify.

### WooCommerce and other integrations (when applicable)

- [ ] With WooCommerce enabled, test a product, product category, and product tag using test data.
- [ ] Confirm conversion works with the site's active WooCommerce storage mode and does not introduce errors in WooCommerce admin or front-end pages.
- [ ] Repeat relevant checks with the site's other custom post types and taxonomies.

### Browser and server diagnostics

- [ ] Check the browser console for JavaScript errors while loading settings and running conversion.
- [ ] Check the Network panel for failed admin, REST, or AJAX requests; conversion should finish without unexplained failed requests.
- [ ] Review WordPress debug/PHP logs for new warnings, notices, or fatal errors during activation, save, and conversion.
- [ ] Confirm unrelated admin screens, front-end pages, and permalinks continue to work.

## 7. Record results and clean up

For each run, record the branch/commit, plugin version, ZIP artifact, WordPress/PHP versions, integration versions, test date, pass/fail results, and any defects found. Attach relevant screenshots and sanitized logs to the pull request or issue; remove credentials, personal data, and site URLs that should remain private.

After testing, restore the staging backup or delete the test content and return staging to its normal state. Fix failures and repeat the affected checks before promoting the release.

## 8. Release boundary

Passing this guide is not permission to publish. Promoting changes to `master` and publishing a GitHub Release are separate release steps. Publishing a non-prerelease GitHub Release triggers deployment to WordPress.org; do not use that as a substitute for staging tests.
