import { expect } from "@playwright/test";
import {
  getFixturePosts,
  runWpCli,
  runWpCliOutput,
} from "./support.js";
import { test } from "./fixtures.js";

test("WP-CLI reports an empty selection without changing content", () => {
  const result = runWpCliOutput(
    "agp",
    "check",
    "--post_types=none",
    "--taxonomies=none",
  );

  expect(result).toContain("No post types or taxonomies selected");
});

test("WP-CLI converts Greek fixture posts successfully", async ({ fixtureId }) => {
  const result = runWpCliOutput(
    "agp",
    "convert",
    "--post_types=post",
    "--taxonomies=none",
  );
  expect(result).toMatch(/\d+ posts and 0 terms converted/);

  const fixturePosts = getFixturePosts(fixtureId).filter(
    (post) => post.post_type === "post",
  );
  expect(fixturePosts).toHaveLength(2);
  expect(
    fixturePosts.every(
      (post) =>
        post.post_name.includes(fixtureId) &&
        /^[a-z0-9-]+$/.test(post.post_name),
    ),
  ).toBe(true);
});

test("WP-CLI preserves new Greek post and term slugs when automatic conversion is off", async ({
  automaticOffFixtureId,
}) => {
  const postSlug = `νέο-άρθρο-${automaticOffFixtureId}`;
  const postId = runWpCli(
    "post",
    "create",
    "--post_type=post",
    `--post_title=Νέο άρθρο ${automaticOffFixtureId}`,
    `--post_name=${postSlug}`,
    "--post_status=publish",
    "--porcelain",
  );
  expect(
    decodeURIComponent(runWpCli("post", "get", postId, "--field=post_name")),
  ).toBe(postSlug);

  const termSlug = `νέα-κατηγορία-${automaticOffFixtureId}`;
  const termId = runWpCli(
    "term",
    "create",
    "category",
    `Νέα κατηγορία ${automaticOffFixtureId}`,
    `--slug=${termSlug}`,
    "--porcelain",
  );
  expect(
    decodeURIComponent(
      runWpCli("term", "get", "category", termId, "--field=slug"),
    ),
  ).toBe(termSlug);
});

const invalidSelections = [
  [
    "post types in check",
    ["agp", "check", "--post_types=e2e-invalid-post-type"],
    "is not a registered post type",
  ],
  [
    "taxonomies in check",
    ["agp", "check", "--taxonomies=e2e-invalid-taxonomy"],
    "is not a registered taxonomy",
  ],
  [
    "post types in convert",
    ["agp", "convert", "--post_types=e2e-invalid-post-type"],
    "is not a registered post type",
  ],
  [
    "taxonomies in convert",
    ["agp", "convert", "--taxonomies=e2e-invalid-taxonomy"],
    "is not a registered taxonomy",
  ],
];

for (const [description, args, errorMessage] of invalidSelections) {
  test(`WP-CLI rejects invalid ${description}`, () => {
    expect(() => runWpCli(...args)).toThrow(errorMessage);
  });
}
