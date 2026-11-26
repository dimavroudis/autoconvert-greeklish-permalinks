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

test("admin converter converts selected posts and terms only", async ({
  page,
  fixtureId,
}) => {
  await loginAsAdmin(page);
  await page.goto(`${settingsUrl}&tab=generate_permalinks`);
  await page.locator("#selectPosts").selectOption(["post"]);
  await page.locator("#selectTaxonomies").selectOption(["category"]);
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
  expect(decodeURIComponent(pageRecord.post_name)).toBe(
    `οδηγός-δοκιμών-${fixtureId}`,
  );

  const categories = JSON.parse(
    runWpCli(
      "term",
      "list",
      "category",
      `--search=${fixtureId}`,
      "--fields=name,slug",
      "--format=json",
    ),
  );
  const category = categories.find((term) =>
    term.name.includes(`category ${fixtureId}`),
  );
  expect(decodeURIComponent(category.slug)).toBe(
    `elliniki-katigoria-${fixtureId}`,
  );

  const tags = JSON.parse(
    runWpCli(
      "term",
      "list",
      "post_tag",
      `--search=${fixtureId}`,
      "--fields=name,slug",
      "--format=json",
    ),
  );
  const tag = tags.find((term) => term.name.includes(`tag ${fixtureId}`));
  expect(decodeURIComponent(tag.slug)).toBe(`δοκιμή-${fixtureId}`);

  await page.locator("#submit").click();
  await expect(page.locator("#messageOutput")).toBeVisible();
  await expect(page.locator("#messageOutput")).toContainText(
    "All your permalinks were already in greeklish.",
  );
});

test("admin converter continues through multiple batches and reports browser/API errors", async ({
  page,
  largeFixtureId,
}) => {
  await loginAsAdmin(page);
  const browserErrors = [];
  const failedApiRequests = [];
  const failedApiResponses = [];
  const conversionResponsePromises = [];

  page.on("console", (message) => {
    if (message.type() === "error") {
      browserErrors.push(message.text());
    }
  });
  page.on("pageerror", (error) => browserErrors.push(error.message));
  page.on("requestfailed", (request) => {
    if (new URL(request.url()).pathname.startsWith("/wp-json/agp/v1/")) {
      failedApiRequests.push(`${request.url()}: ${request.failure()?.errorText}`);
    }
  });
  page.on("response", (response) => {
    if (!new URL(response.url()).pathname.startsWith("/wp-json/agp/v1/")) {
      return;
    }
    if (!response.ok()) {
      failedApiResponses.push(`${response.status()} ${response.url()}`);
    }
    if (new URL(response.url()).pathname.endsWith("/convert-permalinks")) {
      conversionResponsePromises.push(response.json());
    }
  });

  await page.goto(`${settingsUrl}&tab=generate_permalinks`);
  await page.locator("#selectPosts").selectOption(["post"]);
  await page.locator("#submit").click();

  await expect(page.locator("#messageOutput")).toContainText(
    "Converted 101 posts and 0 terms.",
  );
  await expect(page.locator("#submit")).toBeEnabled();

  const conversionResponses = await Promise.all(conversionResponsePromises);
  expect(conversionResponses.map((response) => response.data)).toEqual([
    { posts: 100, terms: 0 },
    { posts: 1, terms: 0 },
    { posts: 0, terms: 0 },
  ]);
  expect(browserErrors).toEqual([]);
  expect(failedApiRequests).toEqual([]);
  expect(failedApiResponses).toEqual([]);
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

test("automatic conversion converts post and term slugs created or edited in the admin UI", async ({
  page,
  emptyFixtureId,
}) => {
  await loginAsAdmin(page);
  await page.goto(`${settingsUrl}&tab=permalink_settings`);

  const automaticOption = page.locator("#agpAutomatic");
  if (!(await automaticOption.isChecked())) {
    await page.locator("label.agp-switch").click();
  }
  await page.locator("#selectPosts").selectOption(["post"]);
  await page.locator("#selectTaxonomies").selectOption(["category"]);
  await page.getByRole("button", { name: "Save Settings" }).click();

  const postTitle = `Μπάμπης Post ${emptyFixtureId}`;
  await page.goto("/wp-admin/post-new.php");
  const closeEditorGuide = page.getByRole("button", { name: "Close" });
  if (await closeEditorGuide.isVisible()) {
    await closeEditorGuide.click();
  }
  await page
    .frameLocator('iframe[name="editor-canvas"]')
    .getByRole("textbox", { name: "Add title" })
    .fill(postTitle);
  await page
    .getByRole("button", { name: "Publish", exact: true })
    .first()
    .click();
  await page
    .getByRole("button", { name: "Publish", exact: true })
    .last()
    .click();

  await expect(page).toHaveURL(/post\.php\?post=\d+&action=edit/);
  const postId = new URL(page.url()).searchParams.get("post");
  tagFixturePost(emptyFixtureId, postId);
  expect(
    runWpCli("post", "get", postId, "--field=post_name"),
  ).toBe(`mpampis-post-${emptyFixtureId}`);

  const originalPostUrl = runWpCli("post", "get", postId, "--field=url");
  const originalPostResponse = await page.goto(originalPostUrl);
  expect(originalPostResponse.status()).toBe(200);
  await expect(page.locator("body")).toContainText(postTitle);

  const editedPostSlug = `μπαμπης-edited-post-${emptyFixtureId}`;
  await page.goto("/wp-admin/edit.php");
  const postRow = page.locator("#the-list tr").filter({ hasText: postTitle });
  await postRow.hover();
  await postRow.locator(".editinline").click();
  const quickEdit = page.locator(`#edit-${postId}`);
  await quickEdit.getByRole("textbox", { name: "Slug" }).fill(editedPostSlug);
  await quickEdit.locator(".save").first().click();
  await expect(quickEdit).toBeHidden();
  expect(
    runWpCli("post", "get", postId, "--field=post_name"),
  ).toBe(`mpampis-edited-post-${emptyFixtureId}`);

  const updatedPostUrl = runWpCli("post", "get", postId, "--field=url");
  expect(updatedPostUrl).not.toBe(originalPostUrl);
  const updatedPostResponse = await page.goto(updatedPostUrl);
  expect(updatedPostResponse.status()).toBe(200);
  await expect(page.locator("body")).toContainText(postTitle);

  const oldPostResponse = await page.goto(originalPostUrl);
  expect(oldPostResponse.status()).toBe(200);
  await expect(page).toHaveURL(updatedPostUrl);

  const pageTitle = `Ελληνική Σελίδα ${emptyFixtureId}`;
  await page.goto("/wp-admin/post-new.php?post_type=page");
  await page
    .frameLocator('iframe[name="editor-canvas"]')
    .getByRole("textbox", { name: "Add title" })
    .fill(pageTitle);
  await page
    .getByRole("button", { name: "Publish", exact: true })
    .first()
    .click();
  await page
    .getByRole("button", { name: "Publish", exact: true })
    .last()
    .click();
  await expect(page).toHaveURL(/post\.php\?post=\d+&action=edit/);
  const pageId = new URL(page.url()).searchParams.get("post");
  tagFixturePost(emptyFixtureId, pageId);
  expect(
    decodeURIComponent(
      runWpCli("post", "get", pageId, "--field=post_name"),
    ),
  ).toBe(`ελληνική-σελίδα-${emptyFixtureId}`);

  const termName = `Μπάμπης Κατηγορία ${emptyFixtureId}`;
  await page.goto("/wp-admin/edit-tags.php?taxonomy=category");
  await page.locator("#tag-name").fill(termName);
  await page.locator("#submit").click();

  const terms = JSON.parse(
    runWpCli(
      "term",
      "list",
      "category",
      `--search=${termName}`,
      "--fields=term_id,name,slug",
      "--format=json",
    ),
  );
  const term = terms.find((entry) => entry.name === termName);
  expect(term).toBeDefined();
  tagFixtureTerm(emptyFixtureId, term.term_id);
  expect(term.slug).toBe(`mpampis-katigoria-${emptyFixtureId}`);

  const termRow = page.locator("#the-list tr").filter({ hasText: termName });
  await expect(termRow.locator(".column-slug")).toHaveText(
    `mpampis-katigoria-${emptyFixtureId}`,
  );

  const editedTermSlug = `μπαμπης-edited-category-${emptyFixtureId}`;
  await page.goto(
    `/wp-admin/term.php?taxonomy=category&tag_ID=${term.term_id}&post_type=post`,
  );
  await page.locator("#slug").fill(editedTermSlug);
  await page.getByRole("button", { name: "Update", exact: true }).click();
  await expect(page.locator("#slug")).toHaveValue(
    `mpampis-edited-category-${emptyFixtureId}`,
  );
  expect(
    runWpCli("term", "get", "category", term.term_id, "--field=slug"),
  ).toBe(`mpampis-edited-category-${emptyFixtureId}`);

  const tagName = `Νέα Ετικέτα ${emptyFixtureId}`;
  await page.goto("/wp-admin/edit-tags.php?taxonomy=post_tag");
  await page.locator("#tag-name").fill(tagName);
  await page.locator("#submit").click();
  const createdTags = JSON.parse(
    runWpCli(
      "term",
      "list",
      "post_tag",
      `--search=${emptyFixtureId}`,
      "--fields=term_id,name,slug",
      "--format=json",
    ),
  );
  const createdTag = createdTags.find((entry) => entry.name === tagName);
  expect(createdTag).toBeDefined();
  tagFixtureTerm(emptyFixtureId, createdTag.term_id);
  expect(
    decodeURIComponent(createdTag.slug),
  ).toBe(`νέα-ετικέτα-${emptyFixtureId}`);
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
