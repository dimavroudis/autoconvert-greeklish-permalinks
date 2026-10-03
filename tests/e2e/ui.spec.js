import { expect } from "@playwright/test";
import {
  getFixturePosts,
  loginAsAdmin,
  runWpCli,
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

test("all automatic settings persist and control generated slugs", async ({
  page,
  fixtureId,
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
    `--post_title=Advanced ${fixtureId}`,
    `--post_name=μπαμπης-${fixtureId}`,
    "--post_status=publish",
    "--porcelain",
  );
  expect(runWpCli("post", "get", advancedPostId, "--field=post_name")).toBe(
    `babis-${fixtureId}`,
  );

  const advancedTerm = runWpCli(
    "term",
    "create",
    "category",
    `Advanced ${fixtureId}`,
    `--slug=μπαμπης-${fixtureId}`,
    "--porcelain",
  );
  expect(
    runWpCli("term", "get", "category", advancedTerm, "--field=slug"),
  ).toBe(`babis-${fixtureId}`);

  await page.locator("#agp_diphthongs_disable").check();
  await page.getByRole("button", { name: "Save Settings" }).click();
  const simplePostId = runWpCli(
    "post",
    "create",
    "--post_type=post",
    `--post_title=Simple ${fixtureId}`,
    `--post_name=μπαμπης-simple-${fixtureId}`,
    "--post_status=publish",
    "--porcelain",
  );
  expect(runWpCli("post", "get", simplePostId, "--field=post_name")).toBe(
    `mpampis-simple-${fixtureId}`,
  );
});

test("settings persist after saving", async ({ page, fixtureId }) => {
  await loginAsAdmin(page);
  await page.goto(`${settingsUrl}&tab=permalink_settings`);
  const automaticOption = page.locator("#agpAutomatic");
  const originalState = await automaticOption.isChecked();

  await page.locator("label.agp-switch").click();
  await page.getByRole("button", { name: "Save Settings" }).click();
  await expect(automaticOption).toBeChecked({ checked: !originalState });
});
