---
name: create-pull-request
description: Create a pull request for this repository. Use when opening, updating, or preparing a PR from a feature branch to develop or another target branch.
---

# Create pull request

Use this skill when the user wants to open a pull request for changes in this repository. This skill is for PR creation and preparation; it does not publish releases or deploy code.

## Safety rules

- Confirm the source branch contains the intended changes and is ready for review.
- Confirm the target branch is correct before creating the PR.
- Do not merge the PR or push unrelated changes.
- Keep the PR title and description aligned with the actual code changes.
- Respect repository conventions for the default review branch and branch naming.

## Before creating the PR

1. Check the current branch and working-tree status.
2. Review the recent commits and confirm the branch is based on the intended base branch.
3. Ensure the change is scoped to the task and does not include unrelated edits.
4. Identify the correct base branch, typically `develop` for feature work.
5. If the branch is not ready, explain what remains before creating the PR.

## PR content

1. Draft a clear PR title summarizing the change.
2. Write a concise description covering:
   - what changed
   - why the change is needed
   - relevant validation or testing performed
3. Include a note if the work is not yet fully complete or still awaiting validation.
4. Keep the description factual and review-focused.

## Create the PR

1. Open the PR from the current working branch to the chosen base branch.
2. Use the repository's standard review flow and target branch conventions.
3. If the repo uses draft PRs, create the PR as a draft when the work is incomplete.
4. If there are relevant reviewers or labels, include them when the workflow supports it.

## Completion checklist

- Source branch is verified.
- Target branch is confirmed.
- PR title is clear and specific.
- PR body explains the change and validation notes.
- PR is created successfully and is ready for review.
