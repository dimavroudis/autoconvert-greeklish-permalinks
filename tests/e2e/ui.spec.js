import { expect } from "@playwright/test";
import {
  getFixturePosts,
  loginAsAdmin,
  runWpCli,
  tagFixturePost,
  tagFixtureTerm,
} from "./support.js";
import { test } from "./fixtures.js";

const settingsUrl = "/wp-admin/options-general.php?page=agp";

test("admin converter converts selected posts and terms", async ({
  page,
  fixtureId,
}) => {
  await loginAsAdmin(page);
  await page.goto(`${settingsUrl}&tab=generate_permalinks`);
  await page.locator("#selectAllPosts").click();
  await page.locator("#selectAllTaxonomies").click();
  await page.locator("#submit").click();

  await expect(page.locator("#messageOutput")).toBeVisible();
  await expect(page.locator("#messageOutput")).toContainText(
    "Conversion complete!",
  );
  await expect(page.locator("#submit")).toBeEnabled();

  const posts = getFixturePosts(fixtureId);
  const primary = posts.find(
    (post) =>
      post.post_type === "post" &&
      post.post_title === `Καλημέρα Αθήνα ${fixtureId}`,
  );
  const collision = posts.find(
    (post) =>
      post.post_type === "post" && post.post_title.includes("collision"),
  );
  const pageRecord = posts.find((post) => post.post_type === "page");

  expect(primary.post_name).toBe(`kalimera-athina-${fixtureId}`);
  expect(collision.post_name).toBe(`kalimera-athina-${fixtureId}-2`);
  expect(pageRecord.post_name).toMatch(/^[a-z0-9-]+$/);

  for (const taxonomy of ["category", "post_tag"]) {
    const terms = JSON.parse(
      runWpCli("term", "list", taxonomy, "--fields=slug", "--format=json"),
    );
    expect(
      terms.some(
        (term) =>
          term.slug.includes(fixtureId) && /^[a-z0-9-]+$/.test(term.slug),
      ),
    ).toBe(true);
  }

  await page.locator("#submit").click();
  await expect(page.locator("#messageOutput")).toBeVisible();
  await expect(page.locator("#messageOutput")).toContainText(
    "All your permalinks were already in greeklish.",
  );
});

test("converter reports empty selections and restores its controls", async ({
  page,
}) => {
  await loginAsAdmin(page);
  await page.goto(`${settingsUrl}&tab=generate_permalinks`);

  await page.locator("#submit").click();

  await expect(page.locator("#messageOutput")).toBeVisible();
  await expect(page.locator("#messageOutput")).toContainText(
    "No post types or taxonomies selected",
  );
  await expect(page.locator("#submit")).toBeEnabled();
  await expect(page.locator("#selectPosts")).toBeEnabled();
  await expect(page.locator("#selectTaxonomies")).toBeEnabled();
});

test("automatic settings require post types and taxonomies", async ({
  page,
}) => {
  await loginAsAdmin(page);
  await page.goto(`${settingsUrl}&tab=permalink_settings`);

  const automaticOption = page.locator("#agpAutomatic");
  if (!(await automaticOption.isChecked())) {
    await page.locator("label.agp-switch").click();
  }
  await page.locator("#selectPosts").selectOption([]);
  await page.locator("#selectTaxonomies").selectOption([]);

  expect(
    await page.locator("form").evaluate((form) => form.checkValidity()),
  ).toBe(false);
  await page.getByRole("button", { name: "Save Settings" }).click();
  await expect(page).toHaveURL(/page=agp&tab=permalink_settings/);
});

test("automatic conversion off preserves new Greek post and term slugs", async ({
  page,
  automaticOffFixtureId,
}) => {
  await loginAsAdmin(page);
  await page.goto(`${settingsUrl}&tab=permalink_settings`);

  const automaticOption = page.locator("#agpAutomatic");
  await expect(automaticOption).not.toBeChecked();

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
  tagFixturePost(automaticOffFixtureId, postId);
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
  tagFixtureTerm(automaticOffFixtureId, termId);
  expect(
    decodeURIComponent(
      runWpCli("term", "get", "category", termId, "--field=slug"),
    ),
  ).toBe(termSlug);
});

test("all automatic settings persist and control generated slugs", async ({
  page,
  emptyFixtureId,
}) => {
  await loginAsAdmin(page);
  await page.goto(`${settingsUrl}&tab=permalink_settings`);

  const automaticOption = page.locator("#agpAutomatic");
  if (!(await automaticOption.isChecked())) {
    await page.locator("label.agp-switch").click();
  }
  await page.locator("#selectPosts").selectOption(["all_options"]);
  await page.locator("#selectTaxonomies").selectOption(["all_options"]);
  await page.locator("#agp_diphthongs_enable").check();
  await page.getByRole("button", { name: "Save Settings" }).click();

  await page.reload();
  await expect(automaticOption).toBeChecked();
  expect(await page.locator("#selectPosts").inputValue()).toBe("all_options");
  expect(await page.locator("#selectTaxonomies").inputValue()).toBe(
    "all_options",
  );
  await expect(page.locator("#agp_diphthongs_enable")).toBeChecked();

  const advancedPostId = runWpCli(
    "post",
    "create",
    "--post_type=page",
    `--post_title=Advanced ${emptyFixtureId}`,
    `--post_name=μπαμπης-${emptyFixtureId}`,
    "--post_status=publish",
    "--porcelain",
  );
  tagFixturePost(emptyFixtureId, advancedPostId);
  expect(runWpCli("post", "get", advancedPostId, "--field=post_name")).toBe(
    `babis-${emptyFixtureId}`,
  );

  const advancedTerm = runWpCli(
    "term",
    "create",
    "category",
    `Advanced ${emptyFixtureId}`,
    `--slug=μπαμπης-${emptyFixtureId}`,
    "--porcelain",
  );
  tagFixtureTerm(emptyFixtureId, advancedTerm);
  expect(
    runWpCli("term", "get", "category", advancedTerm, "--field=slug"),
  ).toBe(`babis-${emptyFixtureId}`);

  await page.locator("#agp_diphthongs_disable").check();
  await page.getByRole("button", { name: "Save Settings" }).click();
  const simplePostId = runWpCli(
    "post",
    "create",
    "--post_type=post",
    `--post_title=Simple ${emptyFixtureId}`,
    `--post_name=μπαμπης-simple-${emptyFixtureId}`,
    "--post_status=publish",
    "--porcelain",
  );
  tagFixturePost(emptyFixtureId, simplePostId);
  expect(runWpCli("post", "get", simplePostId, "--field=post_name")).toBe(
    `mpampis-simple-${emptyFixtureId}`,
  );
});
