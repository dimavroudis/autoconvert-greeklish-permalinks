import { test, expect } from "@playwright/test";
import {
  cleanupFixtures,
  createFixtureId,
  getFixturePosts,
  loginAsAdmin,
  runWpCli,
  setupFixtures,
} from "./support.js";

const settingsUrl = "/wp-admin/options-general.php?page=agp";

test("admin converter converts selected posts and terms", async ({ page }) => {
  const fixtureId = createFixtureId();
  setupFixtures(fixtureId);

  try {
    await loginAsAdmin(page);
    await page.goto(`${settingsUrl}&tab=generate_permalinks`);
    await page.locator("#selectPosts").selectOption(["post", "page"]);
    await page
      .locator("#selectTaxonomies")
      .selectOption(["category", "post_tag"]);
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
        post.post_title.startsWith("Καλημέρα Αθήνα"),
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
  } finally {
    cleanupFixtures(fixtureId);
  }
});

test("settings persist after saving", async ({ page }) => {
  const fixtureId = createFixtureId();
  setupFixtures(fixtureId);

  try {
    await loginAsAdmin(page);
    await page.goto(`${settingsUrl}&tab=permalink_settings`);
    const automaticOption = page.locator("#agpAutomatic");
    const originalState = await automaticOption.isChecked();

    try {
      await page.locator("label.agp-switch").click();
      await page.getByRole("button", { name: "Save Settings" }).click();
      await expect(automaticOption).toBeChecked({ checked: !originalState });
    } finally {
      if ((await automaticOption.isChecked()) !== originalState) {
        await page.locator("label.agp-switch").click();
        await page.getByRole("button", { name: "Save Settings" }).click();
        await expect(automaticOption).toBeChecked({ checked: originalState });
      }
    }
  } finally {
    cleanupFixtures(fixtureId);
  }
});
