---
name: wordpress-plugin-release
description: Safely prepare and publish this WordPress plugin to WordPress.org. Use when asked to release, publish, or prepare a release of AutoConvert Greeklish Permalinks.
---

# WordPress.org plugin release

Use this umbrella skill when you want a high-level release flow for this repository. It covers the full lifecycle: `develop` → `master` → published GitHub Release → WordPress.org. Treat publishing a GitHub Release as a live deployment.

For specific release stages, prefer the narrower skills below:
- `wordpress-plugin-release-prep` for preparation and readiness checks.
- `wordpress-plugin-release-validate` for compatibility testing and support validation.
- `wordpress-plugin-release-publish` for promotion, tagging, and live publication.

This umbrella skill is best used as a summary or to orient the workflow; the stage-specific skills are safer and easier to target for a specific task.

## Safety rules

- Start by checking the current branch, working-tree status, and recent commits. Do not discard, overwrite, stage, or commit unrelated user changes. If uncommitted changes affect the release or make its scope unclear, explain what is present and ask before proceeding.
- Do not merge branches, push to `master`, create a tag, or publish a GitHub Release unless the user explicitly asks you to perform that action. Publishing a non-prerelease GitHub Release triggers deployment to WordPress.org.
- Never display or expose deployment credentials. Check only whether required Actions secrets and variables are configured when that information is available.
- Keep the numeric plugin version consistent across the plugin header, `AGP_VERSION`, and WordPress.org `Stable tag`. The `v` prefix belongs only to the Git tag/GitHub Release name (for example, plugin version `4.3.0` maps to tag `v4.3.0`); do not add `v` to WordPress.org metadata.
- Do not reuse a numeric version whose corresponding `vX.Y.Z` Git tag already exists. The `v` prefix is a deliberate GitHub convention, not a version mismatch.

## Prepare the release

1. Confirm the intended changes are committed on a feature branch and merged into `develop` via review. Check that the `develop` pull-request CI passes.
2. Review the diff from the previous release and check the plugin's behavior, compatibility, and documentation. Identify the expected release version and confirm it has not already been tagged.
3. Identify the latest stable WordPress and WooCommerce releases from their official release information. Validate the plugin against them on staging before claiming compatibility; a new upstream release alone is not evidence of support. If either cannot be tested or fails, record the limitation and do not claim it as tested.
4. Only after validation, update `Tested up to` in `readme.txt` to the highest WordPress version actually tested, and `WC tested up to` in `auto-gr-permalinks.php` to the highest WooCommerce version actually tested. Record the WordPress, WooCommerce, and PHP versions and test outcomes. Do not advance these fields based only on a release announcement.
5. Set the same new plugin version in all authoritative plugin version fields:
   - `Version` in the plugin header and `AGP_VERSION` in `auto-gr-permalinks.php`.
   - `Stable tag` in `readme.txt`.
   Use the numeric version only in these fields (for example, `4.3.0`); the `Build and Tag` workflow adds the `v` prefix when creating the GitHub tag (`v4.3.0`).
6. Install the locked JavaScript dependencies and build the generated assets with `npm ci` and `npm run build`. Review generated changes and include the required production assets in the release.
7. Run the relevant checks. The PHPUnit workflow tests PHP 7.4 and the latest stable PHP release; run the repository's PHPUnit suite when possible. `npm test` is a placeholder that intentionally exits with an error, so do not report it as a functional test suite.
8. Inspect the final diff and working-tree status. Ensure the changes are limited to the intended release and that version and compatibility values match the validations before promotion.

## Promote and publish

1. Promote the reviewed release commit from `develop` to `master` using the repository's normal reviewed merge process.
2. Check the GitHub Actions workflows triggered by the `master` push:
   - `Build and Tag` builds assets and creates/pushes `vX.Y.Z` from the `Version` field in `auto-gr-permalinks.php`.
   - `Plugin asset/readme update` sends the WordPress.org readme and plugin assets.
   - `Build release zip` builds a ZIP artifact.
3. Resolve workflow failures before continuing. Confirm the expected `vX.Y.Z` tag points to the release commit, and that the numeric `X.Y.Z` matches the plugin header, `AGP_VERSION`, and `Stable tag`.
4. Only when explicitly authorized to publish, create a **published, non-prerelease GitHub Release** for the existing `vX.Y.Z` tag. The `v` prefix stays on the GitHub tag/release; WordPress.org receives the plugin files with numeric version `X.Y.Z`. This triggers `Deploy to WordPress.org`; a prerelease is skipped.
5. Confirm the deployment workflow succeeds. It requires repository Actions secrets `SVN_USERNAME` and `SVN_PASSWORD`, plus the Actions variable `SLUG`; do not reveal their values.
6. Verify the WordPress.org plugin page and downloadable package show the expected version, readme, and assets.

## Repository workflow details

- `develop` is the default development branch. PHPUnit CI runs on pushes to `develop` and pull requests targeting `develop`.
- Release workflows are configured for `master`. A push to `master` creates the version tag and updates WordPress.org assets/readme, but plugin code deployment waits for a published GitHub Release.
- Avoid another `master` push with a version whose tag already exists: the tag workflow does not handle duplicate tags as a new release.
- `npm run build` regenerates minified admin CSS/JavaScript; `npm run archive` creates the `dist` package. Use the ZIP workflow artifact when a release package needs inspection.
