---
name: wordpress-plugin-release-prep
description: Prepare a release of AutoConvert Greeklish Permalinks. Use when checking branch status, reviewing the release diff, updating version metadata, building assets, and verifying the release is ready to promote.
---

# WordPress plugin release preparation

Use this skill to prepare a release without publishing anything externally. This is the read/verify/build phase before promotion or deployment.

## Safety rules

- Start by checking the current branch, working-tree status, and recent commits. Do not discard, overwrite, stage, or commit unrelated user changes. If uncommitted changes affect the release or make its scope unclear, explain what is present and ask before proceeding.
- Do not push to `master`, create a tag, or publish a GitHub Release from this workflow. Use the publish skill only for live deployment actions.
- Keep the release version consistent and do not reuse a version whose `vX.Y.Z` tag already exists.
- Treat validation results as evidence; do not claim compatibility beyond what was actually tested.

## Prepare the release

1. Confirm the intended changes are committed on a feature branch and merged into `develop` via review.
2. Check that the `develop` pull-request CI passes before proceeding.
3. Review the diff from the previous release and confirm the changes match the intended scope.
4. Review the plugin behavior, compatibility, and documentation. Confirm the code and docs reflect the intended release.
5. Identify the expected release version and confirm it has not already been tagged.
6. Update version fields only after the release scope and compatibility have been reviewed.
7. Set the same new plugin version in all authoritative plugin version fields:
   - `Version` in the plugin header and `AGP_VERSION` in `auto-gr-permalinks.php`.
   - `Stable tag` in `readme.txt`.
8. Install the locked JavaScript dependencies and build the generated assets with `npm ci` and `npm run build`.
9. Review generated changes and include the required production assets in the release.
10. Run the relevant checks. The PHPUnit workflow tests PHP 7.4 and the latest stable PHP release; run the repository's PHPUnit suite when possible.
11. Inspect the final diff and working-tree status. Ensure the changes are limited to the intended release and that version metadata aligns with the release scope and validations.

## Repository workflow details

- `develop` is the default development branch. PHPUnit CI runs on pushes to `develop` and pull requests targeting `develop`.
- Release workflows are configured for `master`; do not trigger them from this skill.
- `npm run test` is a placeholder that intentionally exits with an error; do not report it as a functional test suite.
- `npm run build` regenerates minified admin CSS/JavaScript; review generated output before release.

## Completion checklist

- Branch state is understood and safe.
- Release scope and version are identified.
- Version metadata matches the intended release.
- Build artifacts are generated and reviewed.
- Relevant tests have been run or clearly documented as not possible.
- Final diff is limited to the release and ready for promotion.
