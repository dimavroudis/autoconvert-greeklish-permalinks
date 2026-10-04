---
name: wordpress-plugin-release-publish
description: Promote and publish a WordPress plugin release. Use when merging to master, validating tag creation, publishing a GitHub Release, and confirming deployment to WordPress.org.
---

# WordPress plugin release publishing

Use this skill only for the external live publication steps after the release has been prepared and validated. This workflow includes promotion, tagging, GitHub Release creation, and deployment verification.

## Safety rules

- Do not merge branches, push to `master`, create a tag, or publish a GitHub Release unless the user explicitly asks you to perform that action.
- Publishing a non-prerelease GitHub Release triggers deployment to WordPress.org.
- Never display or expose deployment credentials. Check only whether required Actions secrets and variables are configured when that information is available.
- Avoid another `master` push with a version whose tag already exists; a duplicate tag is not treated as a new release.

## Promote and publish

1. Promote the reviewed release commit from `develop` to `master` using the repository's normal reviewed merge process.
2. Check the GitHub Actions workflows triggered by the `master` push:
   - `Build and Tag` builds assets and creates/pushes `vX.Y.Z` from the `Version` field in `auto-gr-permalinks.php`.
   - `Plugin asset/readme update` sends the WordPress.org readme and plugin assets.
   - `Build release zip` builds a ZIP artifact.
3. Resolve workflow failures before continuing.
4. Confirm the expected tag points to the release commit and that its version matches the plugin header and `readme.txt`.
5. Only when explicitly authorized to publish, create a published, non-prerelease GitHub Release for the existing `vX.Y.Z` tag. A prerelease is skipped.
6. Confirm the deployment workflow succeeds. It requires repository Actions secrets `SVN_USERNAME` and `SVN_PASSWORD`, plus the Actions variable `SLUG`; do not reveal their values.
7. Verify that the WordPress.org plugin page and downloadable package show the expected version, readme, and assets.

## Repository workflow details

- Release workflows are configured for `master`.
- A push to `master` creates the version tag and updates WordPress.org assets/readme, but plugin code deployment waits for a published GitHub Release.
- Treat publishing a GitHub Release as a live deployment action.
- `npm run archive` creates the `dist` package; use the ZIP workflow artifact when a release package needs inspection.

## Completion checklist

- Release commit is promoted to `master` via the normal reviewed process.
- Tag and workflows are confirmed to match the release version.
- GitHub Release is published only with explicit authorization.
- Deployment to WordPress.org succeeds.
- WordPress.org page and package reflect the released version and assets.
