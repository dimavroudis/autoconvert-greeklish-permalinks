---
name: wordpress-plugin-release-validate
description: Validate compatibility for a WordPress plugin release. Use when checking the latest stable WordPress, WooCommerce, and PHP versions, staging the plugin, and documenting exactly what was tested.
---

# WordPress plugin release validation

Use this skill to validate compatibility and support claims before promoting a release. This is the evidence-gathering phase for release metadata and compatibility statements.

## Safety rules

- Do not claim compatibility based only on a release announcement from upstream projects.
- Only record support claims for versions that were actually tested or otherwise directly validated in a supported environment.
- If testing is not possible or fails, record the limitation and do not claim it as tested.
- Keep the release version and compatibility data consistent with the actual validation outcome.

## Validate compatibility

1. Identify the latest stable WordPress release from official release information.
2. Identify the latest stable WooCommerce release from official release information.
3. Identify the relevant PHP versions to test in this repository's supported matrix, especially PHP 7.4 and the latest stable PHP release.
4. Validate the plugin against the newest relevant stable versions on staging or another appropriate test environment.
5. Record the exact WordPress, WooCommerce, and PHP versions tested and the outcome of each validation.
6. Update only the support fields that correspond to tested versions:
   - `Tested up to` in `readme.txt` for WordPress.
   - `WC tested up to` in `auto-gr-permalinks.php` for WooCommerce.
7. Do not advance compatibility fields based on release announcements alone.
8. If the plugin fails on any tested upstream version, record the limitation and do not claim support for it.

## Evidence requirements

- Keep a concise record of environment versions and test outcomes.
- If a version is not tested, it should not be listed as supported or tested.
- If a test environment is unavailable, state that explicitly and do not infer compatibility.

## Repository workflow details

- The PHPUnit workflow checks PHP 7.4 and the latest stable PHP release.
- `develop` is the default development branch for release validation.
- Do not make compatibility claims beyond what was actually verified.

## Completion checklist

- Latest stable WordPress version is identified.
- Latest stable WooCommerce version is identified.
- Relevant PHP versions are identified.
- Plugin validation is performed on staging or equivalent.
- Version support fields match the actual test outcomes.
- Any unsupported or untested versions are explicitly documented as such.
